<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Company;
use App\Models\Destination;
use App\Models\Folder;
use App\Models\FolderPayment;
use App\Models\User;
use App\Notifications\FolderPaymentsPendingApprovalNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class FolderController extends Controller
{
    public function index(Request $request): View
    {
        $params = $this->agentFolderFilterParams($request);

        $folders = Folder::query()
            ->with(['agent', 'company', 'destination', 'itineraries'])
            ->withCount('passengers')
            ->withIncompleteBookingFlag()
            ->where('agent_id', $request->user()->getAuthIdentifier());
        $this->applyAgentFolderListFilters(
            $folders,
            $params['search'],
            $params['destinationId'],
            $params['orderType'],
            $params['travelArrivalFrom'],
            $params['travelArrivalTo'],
            $params['bookingStatus'],
        );
        $folders = $folders
            ->orderByIncompleteBookingFirst()
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('agent.folders.index', [
            'folders' => $folders,
            'search' => $params['search'],
            'selectedDestinationId' => $params['destinationId'],
            'selectedOrderType' => $params['orderType'],
            'selectedTravelArrivalFrom' => $params['travelArrivalFrom'],
            'selectedTravelArrivalTo' => $params['travelArrivalTo'],
            'selectedBookingStatus' => $params['bookingStatus'],
            'destinations' => Destination::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function upcoming(Request $request): View
    {
        $params = $this->agentFolderFilterParams($request);

        $folders = Folder::query()
            ->with(['agent', 'company', 'destination', 'itineraries'])
            ->withCount('passengers')
            ->withIncompleteBookingFlag()
            ->where('agent_id', $request->user()->getAuthIdentifier())
            ->upcomingByTravelDate(Folder::UPCOMING_TRAVEL_DATE_WINDOW_DAYS);
        $this->applyAgentFolderListFilters(
            $folders,
            $params['search'],
            $params['destinationId'],
            $params['orderType'],
            '',
            '',
            $params['bookingStatus'],
        );
        $folders = $folders
            ->orderByIncompleteBookingFirst()
            ->orderBy('travel_date')
            ->orderBy('id')
            ->paginate(15);
        $folders->appends(Arr::except($request->query(), ['travel_arrival_from', 'travel_arrival_to']));

        return view('agent.folders.upcoming', [
            'folders' => $folders,
            'search' => $params['search'],
            'selectedDestinationId' => $params['destinationId'],
            'selectedOrderType' => $params['orderType'],
            'selectedBookingStatus' => $params['bookingStatus'],
            'destinations' => Destination::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        $drafts = $this->draftSections(request());

        return view('agent.folders.create', [
            'companies' => $this->companiesForAgentFolderForm(request()->user()),
            'destinations' => Destination::query()->orderBy('name')->get(),
            'banks' => Bank::query()->orderBy('name')->get(),
            'draftItineraryRows' => $drafts['itineraries'] ?? [[]],
            'draftPassengerRows' => $drafts['passengers'] ?? [[]],
            'draftPackageCostRows' => $drafts['package_costs'] ?? [[]],
            'draftHotelDetailRows' => $drafts['hotel_details'] ?? [[]],
            'draftTransportDetailRows' => $drafts['transport_details'] ?? [[]],
            'draftVisaDetailRows' => $drafts['visa_details'] ?? [[]],
            'draftOtherDetailRows' => $drafts['other_details'] ?? [],
            'draftPaymentRows' => $drafts['payments'] ?? [[]],
            'leadRoutePrefix' => 'agent',
            'leadRouteResource' => 'folders',
            'leadLayout' => 'layouts.agent',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateFolder($this->mergeWithDraftSections($request), $request);
        [$flashType, $flashMessage, $savedFolder] = $this->persistFolder($validated);

        if ($flashType === 'error') {
            return back()
                ->withInput()
                ->with($flashType, $flashMessage);
        }

        if ($savedFolder !== null && ! empty($validated['payments'])) {
            $this->notifySuperAdminsOfPendingFolderPayments($savedFolder, $request->user());
        }

        return redirect()
            ->route('agent.folders.index')
            ->with($flashType, $flashMessage);
    }

    public function saveSectionDraft(Request $request, string $section): JsonResponse
    {
        $rulesBySection = $this->sectionDraftRules();
        if (! array_key_exists($section, $rulesBySection)) {
            abort(404);
        }

        if ($section === 'payments') {
            $payments = folder_filter_non_empty_payment_rows($request->input('payments'));
            if ($request->filled('folder_id')) {
                $folder = Folder::query()->find($request->integer('folder_id'));
                if ($folder !== null && (int) $folder->agent_id === (int) $request->user()->id) {
                    $payments = folder_strip_locked_payment_rows($folder, $payments);
                }
            }
            $request->merge(['payments' => $payments]);
        }

        if ($section === 'other_details') {
            $request->merge([
                'other_details' => folder_filter_non_empty_other_detail_rows($request->input('other_details')),
            ]);
        }

        if ($section === 'itineraries') {
            $request->merge([
                'itineraries' => folder_filter_non_empty_itinerary_rows($request->input('itineraries')),
            ]);
        }

        if ($section === 'hotel_details') {
            $request->merge([
                'hotel_details' => folder_filter_non_empty_hotel_detail_rows($request->input('hotel_details')),
            ]);
        }

        if ($section === 'transport_details') {
            $request->merge([
                'transport_details' => folder_filter_non_empty_transport_detail_rows($request->input('transport_details')),
            ]);
        }

        if ($section === 'visa_details') {
            $request->merge([
                'visa_details' => folder_filter_non_empty_visa_detail_rows($request->input('visa_details')),
            ]);
        }

        $validator = Validator::make($request->all(), $rulesBySection[$section]);
        if ($validator->fails()) {
            return response()->json([
                'message' => __('Please complete required fields in this section before saving.'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $drafts = $this->draftSections($request);
        $drafts[$section] = $validated[$section] ?? [];
        $request->session()->put($this->draftSessionKey($request), $drafts);

        return response()->json([
            'message' => __('Section saved successfully.'),
        ]);
    }

    public function show(Folder $folder): View
    {
        if ((int) $folder->agent_id !== (int) request()->user()->id) {
            abort(404);
        }

        return view('agent.folders.show', [
            'folder' => $folder->load([
                'agent',
                'company',
                'destination',
                'itineraries',
                'passengers',
                'packageCosts',
                'hotelDetails',
                'transportDetails',
                'visaDetails',
                'otherDetails',
                'payments.bank',
            ]),
        ]);
    }

    public function edit(Folder $folder): View
    {
        if ((int) $folder->agent_id !== (int) request()->user()->id) {
            abort(404);
        }

        $folder->load(['itineraries', 'passengers', 'packageCosts', 'hotelDetails', 'transportDetails', 'visaDetails', 'otherDetails', 'payments']);
        $drafts = $this->draftSections(request());

        return view('agent.folders.create', [
            'lead' => $folder,
            'companies' => $this->companiesForAgentFolderForm(request()->user()),
            'destinations' => Destination::query()->orderBy('name')->get(),
            'banks' => Bank::query()->orderBy('name')->get(),
            'draftItineraryRows' => $drafts['itineraries'] ?? $folder->itineraries
                ->map(fn ($itinerary) => [
                    'sr_no' => $itinerary->sr_no,
                    'airline_code' => $itinerary->airline_code,
                    'airline_number' => $itinerary->airline_number,
                    'class' => $itinerary->class,
                    'departure_date' => optional($itinerary->departure_date)->format('Y-m-d'),
                    'departure_airport' => $itinerary->departure_airport,
                    'arrival_airport' => $itinerary->arrival_airport,
                    'departure_time' => $itinerary->departure_time,
                    'arrival_time' => $itinerary->arrival_time,
                    'arrival_date' => optional($itinerary->arrival_date)->format('Y-m-d'),
                ])->toArray(),
            'draftPassengerRows' => $drafts['passengers'] ?? $folder->passengers
                ->map(fn ($passenger) => [
                    'title' => $passenger->title,
                    'first_name' => $passenger->first_name,
                    'middle_name' => $passenger->middle_name,
                    'last_name' => $passenger->last_name,
                    'passenger_type' => $passenger->passenger_type,
                    'email' => $passenger->email,
                    'phone' => $passenger->phone,
                    'date_of_birth' => optional($passenger->date_of_birth)->format('Y-m-d'),
                    'passport_details' => $passenger->passport_details,
                ])->toArray(),
            'draftPackageCostRows' => $drafts['package_costs'] ?? $folder->packageCosts
                ->map(fn ($cost) => [
                    'ticket_no' => $cost->ticket_no,
                    'ticket_date' => optional($cost->ticket_date)->format('Y-m-d'),
                    'airline_from' => $cost->airline_from,
                    'airline_to' => $cost->airline_to,
                    'fare' => $cost->fare,
                    'tax' => $cost->tax,
                    'total_cost' => $cost->total_cost,
                    'margin' => $cost->margin,
                    'sell' => $cost->sell,
                    'supplier' => $cost->supplier,
                    'pnr' => $cost->pnr,
                ])->toArray(),
            'draftHotelDetailRows' => $drafts['hotel_details'] ?? $folder->hotelDetails
                ->map(fn ($hotel) => [
                    'sr_no' => $hotel->sr_no,
                    'supplier' => $hotel->supplier,
                    'hotel_name' => $hotel->hotel_name,
                    'guest_name' => $hotel->guest_name,
                    'rooms' => $hotel->rooms,
                    'type' => $hotel->type,
                    'meals' => $hotel->meals,
                    'date_in' => optional($hotel->date_in)->format('Y-m-d'),
                    'date_out' => optional($hotel->date_out)->format('Y-m-d'),
                    'nights' => $hotel->nights,
                    'supplier_ref' => $hotel->supplier_ref,
                    'status' => $hotel->status,
                    'cost' => $hotel->cost,
                    'margin' => $hotel->margin,
                    'sell' => $hotel->sell,
                    'hotel_city' => $hotel->hotel_city,
                ])->toArray(),
            'draftTransportDetailRows' => $drafts['transport_details'] ?? $folder->transportDetails
                ->map(fn ($t) => [
                    'supplier' => $t->supplier,
                    'description' => $t->description,
                    'origin' => $t->origin,
                    'destination' => $t->destination,
                    'service_date' => optional($t->service_date)->format('Y-m-d'),
                    'pickup_time' => $t->pickup_time,
                    'vehicle_type' => $t->vehicle_type,
                    'cost' => $t->cost,
                    'margin' => $t->margin,
                    'sell' => $t->sell,
                    'sar' => $t->sar,
                ])->toArray(),
            'draftVisaDetailRows' => $drafts['visa_details'] ?? $folder->visaDetails
                ->map(fn ($v) => [
                    'supplier' => $v->supplier,
                    'description' => $v->description,
                    'cost' => $v->cost,
                    'margin' => $v->margin,
                    'sell' => $v->sell,
                ])->toArray(),
            'draftOtherDetailRows' => $drafts['other_details'] ?? $folder->otherDetails
                ->map(fn ($o) => [
                    'supplier' => $o->supplier,
                    'description' => $o->description,
                    'cost' => $o->cost,
                    'margin' => $o->margin,
                    'sell' => $o->sell,
                ])->toArray(),
            'draftPaymentRows' => array_key_exists('payments', $drafts)
                ? $drafts['payments']
                : ($folder->payments->isEmpty()
                    ? [[]]
                    : $folder->payments
                        ->map(fn ($p) => [
                            'id' => $p->id,
                            'amount' => $p->amount,
                            'reference_no' => $p->reference_no,
                            'payment_date' => optional($p->payment_date)->format('Y-m-d'),
                            'mode_of_payment' => $p->mode_of_payment,
                            'bank_id' => $p->bank_id,
                            'approval_status' => $p->approval_status,
                            'is_locked' => $p->isLocked(),
                            'image_url' => $p->imageUrl(),
                        ])->toArray()),
            'isEditMode' => true,
            'leadRoutePrefix' => 'agent',
            'leadRouteResource' => 'folders',
            'leadLayout' => 'layouts.agent',
        ]);
    }

    public function update(Request $request, Folder $folder): RedirectResponse
    {
        if ((int) $folder->agent_id !== (int) $request->user()->id) {
            abort(404);
        }

        $validated = $this->validateFolder($this->mergeWithDraftSections($request), $request, $folder);
        [$flashType, $flashMessage, $savedFolder] = $this->persistFolder($validated, $folder);

        if ($flashType === 'error') {
            return back()
                ->withInput()
                ->with($flashType, $flashMessage);
        }

        if ($savedFolder !== null && ! empty($validated['payments'])) {
            $this->notifySuperAdminsOfPendingFolderPayments($savedFolder, $request->user());
        }

        return redirect()
            ->route('agent.folders.index')
            ->with($flashType, $flashMessage);
    }

    public function destroy(Request $request, Folder $folder): RedirectResponse
    {
        if ((int) $folder->agent_id !== (int) $request->user()->id) {
            abort(404);
        }

        try {
            $folder->delete();
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('agent.folders.index')
                ->with('error', __('Could not delete folder. Please try again.'));
        }

        return redirect()
            ->route('agent.folders.index')
            ->with('status', __('Folder deleted successfully.'));
    }

    private function validateFolder(array $payload, Request $request, ?Folder $folder = null): array
    {
        if ($folder !== null && is_array($payload['payments'] ?? null)) {
            $payload['payments'] = folder_strip_locked_payment_rows($folder, $payload['payments']);
        }

        $payload['payments'] = folder_filter_non_empty_payment_rows($payload['payments'] ?? null);
        $payload['other_details'] = folder_filter_non_empty_other_detail_rows($payload['other_details'] ?? null);
        $payload['itineraries'] = folder_filter_non_empty_itinerary_rows($payload['itineraries'] ?? null);
        $payload['hotel_details'] = folder_filter_non_empty_hotel_detail_rows($payload['hotel_details'] ?? null);
        $payload['transport_details'] = folder_filter_non_empty_transport_detail_rows($payload['transport_details'] ?? null);
        $payload['visa_details'] = folder_filter_non_empty_visa_detail_rows($payload['visa_details'] ?? null);

        $agentCompanyId = (int) ($request->user()?->company_id ?? 0);
        if ($agentCompanyId > 0) {
            $payload['company_id'] = $agentCompanyId;
        }

        $validated = Validator::make($payload, [
            'agent_id' => team_member_user_id_validation_rules(),
            'order_type' => ['required', 'string', Rule::in(folder_order_types())],
            'vendor_reference' => ['required', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'company_id' => $agentCompanyId > 0
                ? ['required', 'integer', Rule::in([$agentCompanyId])]
                : ['prohibited'],
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'travel_date' => ['required', 'date'],
            'booking_date' => ['required', 'date'],
            'balance_due_date' => ['required', 'date'],
            'ziarat_option' => ['nullable', 'array'],
            'ziarat_option.*' => ['string', Rule::in(['makkah', 'madinah'])],
            ...folder_itineraries_validation_rules(),
            'passengers' => ['required', 'array', 'min:1'],
            'passengers.*.title' => ['required', 'string', Rule::in(folder_passenger_titles())],
            'passengers.*.first_name' => ['required', 'string', 'max:100'],
            'passengers.*.middle_name' => ['nullable', 'string', 'max:100'],
            'passengers.*.last_name' => ['required', 'string', 'max:100'],
            'passengers.*.passenger_type' => ['required', 'string', Rule::in(folder_passenger_types())],
            'passengers.*.email' => ['required', 'email', 'max:255'],
            'passengers.*.phone' => ['required', 'string', 'max:30'],
            'passengers.*.date_of_birth' => ['nullable', 'date'],
            'passengers.*.passport_details' => ['nullable', 'string', 'max:255'],
            'package_costs' => ['required', 'array', 'min:1'],
            'package_costs.*.ticket_no' => ['nullable', 'string', 'max:50'],
            'package_costs.*.ticket_date' => ['required', 'date'],
            'package_costs.*.airline_from' => ['required', 'string', 'max:30'],
            'package_costs.*.airline_to' => ['required', 'string', 'max:30'],
            'package_costs.*.fare' => ['required', 'numeric', 'min:0'],
            'package_costs.*.tax' => ['nullable', 'numeric', 'min:0'],
            'package_costs.*.total_cost' => ['required', 'numeric', 'min:0'],
            'package_costs.*.margin' => ['required', 'numeric', 'min:0'],
            'package_costs.*.sell' => ['required', 'numeric', 'min:0'],
            'package_costs.*.supplier' => ['required', 'string', 'max:100'],
            'package_costs.*.pnr' => ['required', 'string', 'max:50'],
            ...folder_hotel_details_validation_rules(),
            ...folder_transport_details_validation_rules(),
            ...folder_visa_details_validation_rules(),
            ...folder_other_details_validation_rules(),
            ...folder_payments_validation_rules(),
        ])->validate();

        $itineraries = collect($validated['itineraries'] ?? [])
            ->sortBy(fn ($itinerary) => (int) ($itinerary['sr_no'] ?? PHP_INT_MAX))
            ->values();
        $firstItinerary = $itineraries->first();

        if (is_array($firstItinerary) && isset($firstItinerary['departure_date'])) {
            $travelDate = (string) ($validated['travel_date'] ?? '');
            $firstDepartureDate = (string) $firstItinerary['departure_date'];

            if ($travelDate !== '' && $firstDepartureDate !== '' && $travelDate !== $firstDepartureDate) {
                throw ValidationException::withMessages([
                    'travel_date' => __('Travel date must match the first itinerary departure date.'),
                    'itineraries.0.departure_date' => __('First itinerary departure date must match travel date.'),
                ]);
            }
        }

        return $validated;
    }

    private function mergeWithDraftSections(Request $request): array
    {
        $payload = $request->all();
        $drafts = $this->draftSections($request);

        foreach (['itineraries', 'passengers', 'package_costs', 'hotel_details', 'transport_details', 'visa_details', 'other_details'] as $section) {
            if (! empty($drafts[$section])) {
                $payload[$section] = $drafts[$section];
            }
        }

        if (array_key_exists('payments', $drafts) && is_array($drafts['payments'])) {
            $payload['payments'] = $drafts['payments'];
        }

        return $payload;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{amount: mixed, payment_date: mixed, mode_of_payment: string, bank_id: int|null}>
     */
    /**
     * @return array<string, mixed>
     */
    private function draftSections(Request $request): array
    {
        return (array) $request->session()->get($this->draftSessionKey($request), []);
    }

    private function draftSessionKey(Request $request): string
    {
        return 'folder_section_drafts.user.'.(string) $request->user()?->getAuthIdentifier();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function sectionDraftRules(): array
    {
        return [
            'itineraries' => folder_itineraries_validation_rules(),
            'passengers' => [
                'passengers' => ['required', 'array', 'min:1'],
                'passengers.*.title' => ['required', 'string', Rule::in(folder_passenger_titles())],
                'passengers.*.first_name' => ['required', 'string', 'max:100'],
                'passengers.*.middle_name' => ['nullable', 'string', 'max:100'],
                'passengers.*.last_name' => ['required', 'string', 'max:100'],
                'passengers.*.passenger_type' => ['required', 'string', Rule::in(folder_passenger_types())],
                'passengers.*.email' => ['required', 'email', 'max:255'],
                'passengers.*.phone' => ['required', 'string', 'max:30'],
                'passengers.*.date_of_birth' => ['nullable', 'date'],
                'passengers.*.passport_details' => ['nullable', 'string', 'max:255'],
            ],
            'package_costs' => [
                'package_costs' => ['required', 'array', 'min:1'],
                'package_costs.*.ticket_no' => ['nullable', 'string', 'max:50'],
                'package_costs.*.ticket_date' => ['required', 'date'],
                'package_costs.*.airline_from' => ['required', 'string', 'max:30'],
                'package_costs.*.airline_to' => ['required', 'string', 'max:30'],
                'package_costs.*.fare' => ['required', 'numeric', 'min:0'],
                'package_costs.*.tax' => ['nullable', 'numeric', 'min:0'],
                'package_costs.*.total_cost' => ['required', 'numeric', 'min:0'],
                'package_costs.*.margin' => ['required', 'numeric', 'min:0'],
                'package_costs.*.sell' => ['required', 'numeric', 'min:0'],
                'package_costs.*.supplier' => ['required', 'string', 'max:100'],
                'package_costs.*.pnr' => ['required', 'string', 'max:50'],
            ],
            'hotel_details' => folder_hotel_details_validation_rules(),
            'transport_details' => folder_transport_details_validation_rules(),
            'visa_details' => folder_visa_details_validation_rules(),
            'other_details' => folder_other_details_validation_rules(),
            'payments' => folder_payments_validation_rules(),
        ];
    }

    /**
     * @return array{0: string, 1: string, 2: ?Folder}
     */
    private function persistFolder(array $validated, ?Folder $folder = null): array
    {
        try {
            DB::transaction(function () use ($validated, &$folder): void {
                $agentId = $folder === null
                    ? request()->user()?->getAuthIdentifier()
                    : ($validated['agent_id'] ?? $folder->agent_id);
                $agentName = \App\Models\User::withTrashed()
                    ->whereKey($agentId)
                    ->value('name');

                $folderPayload = [
                    'agent_id' => $agentId,
                    'agent_name' => $agentName,
                    'order_type' => $validated['order_type'],
                    'vendor_reference' => $validated['vendor_reference'] ?? null,
                    'customer_name' => $validated['customer_name'],
                    'company_id' => $validated['company_id'],
                    'destination_id' => $validated['destination_id'],
                    'travel_date' => $validated['travel_date'],
                    'booking_date' => $validated['booking_date'],
                    'balance_due_date' => $validated['balance_due_date'] ?? null,
                    'makkah_ziarat' => in_array('makkah', $validated['ziarat_option'] ?? [], true),
                    'madinah_ziarat' => in_array('madinah', $validated['ziarat_option'] ?? [], true),
                ];

                if ($folder === null) {
                    $folder = Folder::create($folderPayload);
                } else {
                    $folder->update($folderPayload);
                    $folder->itineraries()->delete();
                    $folder->passengers()->delete();
                    $folder->packageCosts()->delete();
                    $folder->hotelDetails()->delete();
                    $folder->transportDetails()->delete();
                    $folder->visaDetails()->delete();
                    $folder->otherDetails()->delete();
                }

                $folder->itineraries()->createMany($validated['itineraries'] ?? []);
                $folder->passengers()->createMany($validated['passengers'] ?? []);
                $folder->packageCosts()->createMany($validated['package_costs'] ?? []);
                $folder->hotelDetails()->createMany(folder_hotel_details_for_storage($validated['hotel_details'] ?? null));
                $folder->transportDetails()->createMany($validated['transport_details'] ?? []);
                $folder->visaDetails()->createMany($validated['visa_details'] ?? []);
                $folder->otherDetails()->createMany(folder_other_details_for_storage($validated['other_details'] ?? null));
                folder_sync_folder_payments($folder, $validated['payments'] ?? [], FolderPayment::STATUS_PENDING, request());
            });
        } catch (Throwable $e) {
            report($e);

            return ['error', __('Could not save folder. Please try again.'), null];
        }

        request()->session()->forget($this->draftSessionKey(request()));

        return ['status', __('Folder saved successfully.'), $folder];
    }

    private function notifySuperAdminsOfPendingFolderPayments(Folder $folder, User $agent): void
    {
        $count = (int) $folder->payments()->where('approval_status', 'pending')->count();
        if ($count < 1) {
            return;
        }

        $notification = new FolderPaymentsPendingApprovalNotification($folder, $agent, $count);

        foreach (User::role('super-admin')->get() as $admin) {
            $admin->notify($notification);
        }
    }

    /**
     * @return array{search: string, destinationId: int|null, orderType: string, travelArrivalFrom: string, travelArrivalTo: string, bookingStatus: string}
     */
    private function agentFolderFilterParams(Request $request): array
    {
        $search = trim((string) $request->string('search')->value());
        $destinationId = $request->integer('destination_id') ?: null;
        $travelArrivalFrom = trim((string) $request->query('travel_arrival_from', ''));
        $travelArrivalTo = trim((string) $request->query('travel_arrival_to', ''));
        $orderType = trim((string) $request->query('order_type', ''));
        if ($orderType !== '' && ! in_array($orderType, folder_order_types(), true)) {
            $orderType = '';
        }
        $bookingStatus = trim((string) $request->query('booking_status', ''));
        if ($bookingStatus !== '' && ! array_key_exists($bookingStatus, folder_booking_status_filter_options())) {
            $bookingStatus = '';
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $travelArrivalFrom)) {
            $travelArrivalFrom = '';
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $travelArrivalTo)) {
            $travelArrivalTo = '';
        }

        return [
            'search' => $search,
            'destinationId' => $destinationId,
            'orderType' => $orderType,
            'travelArrivalFrom' => $travelArrivalFrom,
            'travelArrivalTo' => $travelArrivalTo,
            'bookingStatus' => $bookingStatus,
        ];
    }

    /**
     * @param  Builder<Folder>  $query
     */
    private function applyAgentFolderListFilters(
        Builder $query,
        string $search,
        ?int $destinationId,
        string $orderType,
        string $travelArrivalFrom,
        string $travelArrivalTo,
        string $bookingStatus,
    ): void {
        $query
            ->when($destinationId !== null, fn ($q) => $q->where('destination_id', $destinationId))
            ->when($orderType !== '', fn ($q) => $q->where('order_type', $orderType))
            ->when($bookingStatus === 'incomplete', fn ($q) => $q->whereHas('hotelDetails', fn ($hq) => $hq->where('status', 'issue_later')))
            ->when($bookingStatus === 'successful', fn ($q) => $q->whereDoesntHave('hotelDetails', fn ($hq) => $hq->where('status', 'issue_later')))
            ->when($travelArrivalFrom !== '' || $travelArrivalTo !== '', function ($q) use ($travelArrivalFrom, $travelArrivalTo) {
                $q->whereExists(function ($itineraryQuery) use ($travelArrivalFrom, $travelArrivalTo) {
                    $itineraryQuery
                        ->selectRaw('1')
                        ->from('folder_itineraries as fi')
                        ->whereColumn('fi.folder_id', 'folders.id')
                        ->whereRaw(
                            'fi.sr_no = (select min(fi2.sr_no) from folder_itineraries as fi2 where fi2.folder_id = folders.id)'
                        )
                        ->when($travelArrivalFrom !== '', fn ($sub) => $sub->whereDate('fi.departure_date', '>=', $travelArrivalFrom))
                        ->when($travelArrivalTo !== '', fn ($sub) => $sub->whereDate('fi.arrival_date', '<=', $travelArrivalTo));
                });
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('order_type', 'like', '%'.$search.'%')
                        ->orWhere('vendor_reference', 'like', '%'.$search.'%');
                });
            });
    }

    /**
     * @return \Illuminate\Support\Collection<int, Company>
     */
    private function companiesForAgentFolderForm(?User $agent)
    {
        $companyId = (int) ($agent?->company_id ?? 0);
        if ($companyId < 1) {
            return collect();
        }

        return Company::query()
            ->with('country')
            ->whereKey($companyId)
            ->orderBy('name')
            ->get();
    }
}
