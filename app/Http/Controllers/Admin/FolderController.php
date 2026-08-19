<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Company;
use App\Models\Destination;
use App\Models\Folder;
use App\Models\FolderPayment;
use App\Models\User;
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
    public function __construct()
    {
        $this->middleware('can:folders.access')->only(['index', 'show', 'upcoming']);
        $this->middleware('can:folders.edit')->only([
            'create',
            'store',
            'edit',
            'update',
            'saveSectionDraft',
            'toggleLock',
            'destroy',
        ]);
    }

    public function index(Request $request): View
    {
        $params = $this->adminFolderFilterParams($request);

        $folders = Folder::query()
            ->with(['agent', 'company', 'destination', 'itineraries'])
            ->withCount('passengers')
            ->withIncompleteBookingFlag();
        apply_staff_company_records_scope($folders, $request->user(), 'folders');
        $this->applyAdminFolderListFilters(
            $folders,
            $params['search'],
            $params['agentId'],
            $params['companyId'],
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

        return view('admin.folders.index', [
            'folders' => $folders,
            'search' => $params['search'],
            'selectedAgentId' => $params['agentId'],
            'selectedCompanyId' => $params['companyId'],
            'selectedDestinationId' => $params['destinationId'],
            'selectedOrderType' => $params['orderType'],
            'selectedTravelArrivalFrom' => $params['travelArrivalFrom'],
            'selectedTravelArrivalTo' => $params['travelArrivalTo'],
            'selectedBookingStatus' => $params['bookingStatus'],
            'agents' => User::recordAssigneesVisibleTo($request->user())->orderBy('name')->get(['id', 'name']),
            'companies' => companies_visible_to_staff($request->user())->get(['id', 'name']),
            'destinations' => Destination::query()->orderBy('name')->get(['id', 'name']),
            'canManageFolders' => user_is_staff_portal($request->user()),
        ]);
    }

    public function upcoming(Request $request): View
    {
        $params = $this->adminFolderFilterParams($request);

        $folders = Folder::query()
            ->with(['agent', 'company', 'destination', 'itineraries'])
            ->withCount('passengers')
            ->withIncompleteBookingFlag()
            ->upcomingByTravelDate(Folder::UPCOMING_TRAVEL_DATE_WINDOW_DAYS);
        apply_staff_company_records_scope($folders, $request->user(), 'folders');
        $this->applyAdminFolderListFilters(
            $folders,
            $params['search'],
            $params['agentId'],
            $params['companyId'],
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

        return view('admin.folders.upcoming', [
            'folders' => $folders,
            'canManageFolders' => user_is_staff_portal($request->user()),
            'search' => $params['search'],
            'selectedAgentId' => $params['agentId'],
            'selectedCompanyId' => $params['companyId'],
            'selectedDestinationId' => $params['destinationId'],
            'selectedOrderType' => $params['orderType'],
            'selectedBookingStatus' => $params['bookingStatus'],
            'agents' => User::recordAssigneesVisibleTo($request->user())->orderBy('name')->get(['id', 'name']),
            'companies' => companies_visible_to_staff($request->user())->get(['id', 'name']),
            'destinations' => Destination::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): View
    {
        $drafts = folder_draft_sections_from_session($request);

        return view('agent.folders.create', [
            'companies' => companies_visible_to_staff($request->user())->with('country')->get(),
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
            'leadRoutePrefix' => portal_route_prefix(),
            'leadRouteResource' => 'folders',
            'leadLayout' => 'layouts.admin',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateFolder($request->all());
        [$flashType, $flashMessage, $_folder] = $this->persistFolder($validated, null, $request);

        if ($flashType === 'error') {
            return back()
                ->withInput()
                ->with($flashType, $flashMessage);
        }

        return redirect()
            ->route(portal_route_prefix().'.folders.index')
            ->with($flashType, $flashMessage);
    }

    public function saveSectionDraft(Request $request, string $section): JsonResponse
    {
        $rulesBySection = $this->sectionDraftRules();
        if (! array_key_exists($section, $rulesBySection)) {
            abort(404);
        }

        $folder = null;
        if ($request->filled('folder_id')) {
            $folder = Folder::query()->find($request->integer('folder_id'));
            if ($folder === null) {
                return response()->json([
                    'message' => __('Folder not found.'),
                ], 404);
            }

            if (! staff_can_access_agent_record($request->user(), $folder->agent_id, $folder->company_id)) {
                return response()->json([
                    'message' => __('Folder not found.'),
                ], 404);
            }
        }

        if ($section === 'payments') {
            $payments = folder_filter_non_empty_payment_rows($request->input('payments'));
            if ($folder !== null) {
                $payments = folder_strip_locked_payment_rows($folder, $payments);
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

        if ($section === 'package_costs') {
            $request->merge([
                'package_costs' => folder_filter_non_empty_package_cost_rows($request->input('package_costs')),
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
        $rows = $validated[$section] ?? [];

        // Edit mode: persist this section to the folder in the database.
        if ($folder !== null) {
            try {
                $persistedPayments = folder_persist_section_rows(
                    $folder,
                    $section,
                    $rows,
                    FolderPayment::STATUS_APPROVED,
                    $request,
                );
            } catch (Throwable $e) {
                report($e);

                return response()->json([
                    'message' => __('Could not save section. Please try again.'),
                ], 500);
            }

            folder_put_draft_section($request, $folder->id, $section, $rows);

            $response = [
                'message' => __('Section saved successfully.'),
            ];

            if ($section === 'payments') {
                $response['payments'] = $persistedPayments ?? [];
            }

            return response()->json($response);
        }

        // Create mode: session draft only.
        folder_put_draft_section($request, null, $section, $rows);

        return response()->json([
            'message' => __('Section saved successfully.'),
        ]);
    }

    public function show(Request $request, Folder $folder): View
    {
        if (! staff_can_access_agent_record($request->user(), $folder->agent_id, $folder->company_id)) {
            abort(404);
        }

        $folder->load(['agent', 'company', 'destination', 'itineraries', 'passengers', 'packageCosts', 'hotelDetails', 'transportDetails', 'visaDetails', 'otherDetails', 'payments.bank']);

        return view('admin.folders.show', [
            'folder' => $folder,
            'canManageFolders' => user_is_staff_portal(request()->user()),
        ]);
    }

    public function destroy(Request $request, Folder $folder): RedirectResponse
    {
        if (! staff_can_delete_records($request->user())) {
            abort(403);
        }

        if (! staff_can_access_agent_record($request->user(), $folder->agent_id, $folder->company_id)) {
            abort(404);
        }

        try {
            $folder->delete();
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', __('Could not delete folder. Please try again.'));
        }

        return redirect()
            ->route(portal_route_prefix().'.folders.index')
            ->with('status', __('Folder deleted successfully.'));
    }

    public function toggleLock(Request $request, Folder $folder): RedirectResponse
    {
        if (! staff_can_access_agent_record($request->user(), $folder->agent_id, $folder->company_id)) {
            abort(404);
        }

        $folder->update([
            'lock' => $folder->isLocked() ? 0 : 1,
        ]);

        return back()->with(
            'status',
            $folder->fresh()->isLocked()
                ? __('Folder locked successfully.')
                : __('Folder unlocked successfully.'),
        );
    }

    public function edit(Request $request, Folder $folder): View
    {
        if (! staff_can_access_agent_record($request->user(), $folder->agent_id, $folder->company_id)) {
            abort(404);
        }

        $folder->load(['itineraries', 'passengers', 'packageCosts', 'hotelDetails', 'transportDetails', 'visaDetails', 'otherDetails', 'payments']);
        $drafts = folder_draft_sections_from_session($request, $folder->id);

        return view('agent.folders.create', [
            'lead' => $folder,
            'companies' => companies_visible_to_staff($request->user())->with('country')->get(),
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
            'leadRoutePrefix' => portal_route_prefix(),
            'leadRouteResource' => 'folders',
            'leadLayout' => 'layouts.admin',
        ]);
    }

    public function update(Request $request, Folder $folder): RedirectResponse
    {
        if (! staff_can_access_agent_record($request->user(), $folder->agent_id, $folder->company_id)) {
            abort(404);
        }

        $validated = $this->validateFolder($request->all(), $folder);
        [$flashType, $flashMessage, $_folder] = $this->persistFolder($validated, $folder, $request);

        if ($flashType === 'error') {
            return back()
                ->withInput()
                ->with($flashType, $flashMessage);
        }

        return redirect()
            ->route(portal_route_prefix().'.folders.index')
            ->with($flashType, $flashMessage);
    }

    private function validateFolder(array $payload, ?Folder $folder = null): array
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
        $payload['package_costs'] = folder_filter_non_empty_package_cost_rows($payload['package_costs'] ?? null);

        $validated = Validator::make($payload, [
            'agent_id' => team_member_user_id_validation_rules(),
            'order_type' => ['required', 'string', Rule::in(folder_order_types())],
            'vendor_reference' => ['required', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'company_id' => [
                'required',
                'integer',
                'exists:companies,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    assert_staff_company_allowed(request()->user(), $value, $fail);
                },
            ],
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
            ...folder_package_costs_validation_rules(),
            ...folder_hotel_details_validation_rules(),
            ...folder_transport_details_validation_rules(),
            ...folder_visa_details_validation_rules(),
            ...folder_other_details_validation_rules(),
            ...folder_payments_validation_rules(),
        ])->validate();

        $firstItinerary = folder_first_itinerary_row($validated['itineraries'] ?? []);

        if ($firstItinerary !== null && isset($firstItinerary['departure_date'])) {
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
            'package_costs' => folder_package_costs_validation_rules(),
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
    private function persistFolder(array $validated, ?Folder $folder, Request $request): array
    {
        $draftFolderId = $folder?->id;

        try {
            DB::transaction(function () use ($validated, &$folder, $request): void {
                $agentId = $folder === null
                    ? $request->user()?->getAuthIdentifier()
                    : ($validated['agent_id'] ?? $folder->agent_id);
                $agentName = User::withTrashed()
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
                    $folder->forceDeleteReplaceableSections();
                }

                $folder->itineraries()->createMany($validated['itineraries'] ?? []);
                $folder->passengers()->createMany($validated['passengers'] ?? []);
                $folder->packageCosts()->createMany($validated['package_costs'] ?? []);
                $folder->hotelDetails()->createMany(folder_hotel_details_for_storage($validated['hotel_details'] ?? null));
                $folder->transportDetails()->createMany($validated['transport_details'] ?? []);
                $folder->visaDetails()->createMany($validated['visa_details'] ?? []);
                $folder->otherDetails()->createMany(folder_other_details_for_storage($validated['other_details'] ?? null));
                folder_sync_folder_payments($folder, $validated['payments'] ?? [], FolderPayment::STATUS_APPROVED, $request);
            });
        } catch (Throwable $e) {
            report($e);

            return ['error', __('Could not save folder. Please try again.'), null];
        }

        folder_forget_draft_sections($request, $draftFolderId);

        return ['status', __('Folder saved successfully.'), $folder];
    }

    /**
     * @return array{search: string, agentId: int|null, destinationId: int|null, orderType: string, travelArrivalFrom: string, travelArrivalTo: string, bookingStatus: string}
     */
    private function adminFolderFilterParams(Request $request): array
    {
        $search = trim((string) $request->string('search')->value());
        $agentId = $request->integer('agent_id') ?: null;
        $companyId = resolve_staff_company_filter(
            $request->user(),
            $request->integer('company_id') ?: null,
        );
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
            'agentId' => $agentId,
            'companyId' => $companyId,
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
    private function applyAdminFolderListFilters(
        Builder $query,
        string $search,
        ?int $agentId,
        ?int $companyId,
        ?int $destinationId,
        string $orderType,
        string $travelArrivalFrom,
        string $travelArrivalTo,
        string $bookingStatus,
    ): void {
        $query
            ->when($agentId !== null, fn ($q) => $q->where('agent_id', $agentId))
            ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
            ->when($destinationId !== null, fn ($q) => $q->where('destination_id', $destinationId))
            ->when($orderType !== '', fn ($q) => $q->where('order_type', $orderType))
            ->when($bookingStatus === 'incomplete', fn ($q) => $q->whereHas('hotelDetails', fn ($hq) => $hq->where('status', 'issue_later')))
            ->when($bookingStatus === 'successful', fn ($q) => $q->whereDoesntHave('hotelDetails', fn ($hq) => $hq->where('status', 'issue_later')))
            ->when($travelArrivalFrom !== '' || $travelArrivalTo !== '', function ($q) use ($travelArrivalFrom, $travelArrivalTo) {
                apply_folder_travel_date_filter($q, $travelArrivalFrom, $travelArrivalTo);
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
}
