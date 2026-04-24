<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Destination;
use App\Models\Folder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

class FolderController extends Controller
{
    public function index(Request $request): View
    {
        $folders = Folder::query()
        ->with(['agent', 'company', 'destination'])
        ->latest()
        ->paginate(15)
        ->withQueryString();

        return view('agent.folders.index', [
            'folders' => $folders,
        ]);
    }

    public function create(): View
    {
        $drafts = $this->draftSections(request());

        return view('agent.folders.create', [
            'companies' => Company::query()->with('country')->orderBy('name')->get(),
            'destinations' => Destination::query()->orderBy('name')->get(),
            'draftItineraryRows' => $drafts['itineraries'] ?? [[]],
            'draftPassengerRows' => $drafts['passengers'] ?? [[]],
            'draftPackageCostRows' => $drafts['package_costs'] ?? [[]],
            'draftHotelDetailRows' => $drafts['hotel_details'] ?? [[]],
            'leadRoutePrefix' => 'agent',
            'leadRouteResource' => 'folders',
            'leadLayout' => 'layouts.agent',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateFolder($this->mergeWithDraftSections($request), $request);
        [$flashType, $flashMessage] = $this->persistFolder($validated);

        if ($flashType === 'error') {
            return back()
                ->withInput()
                ->with($flashType, $flashMessage);
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
            ]),
        ]);
    }

    public function edit(Folder $folder): View
    {
        if ((int) $folder->agent_id !== (int) request()->user()->id) {
            abort(404);
        }

        $folder->load(['itineraries', 'passengers', 'packageCosts', 'hotelDetails']);
        $drafts = $this->draftSections(request());

        return view('agent.folders.create', [
            'lead' => $folder,
            'companies' => Company::query()->with('country')->orderBy('name')->get(),
            'destinations' => Destination::query()->orderBy('name')->get(),
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
                    'cost' => $hotel->cost,
                    'margin' => $hotel->margin,
                    'sell' => $hotel->sell,
                    'hotel_city' => $hotel->hotel_city,
                ])->toArray(),
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

        $validated = $this->validateFolder($this->mergeWithDraftSections($request), $request);
        [$flashType, $flashMessage] = $this->persistFolder($validated, $folder);

        if ($flashType === 'error') {
            return back()
                ->withInput()
                ->with($flashType, $flashMessage);
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

    private function validateFolder(array $payload, Request $request): array
    {
        return Validator::make($payload, [
            'agent_id' => ['nullable', 'integer', 'exists:users,id'],
            'order_type' => ['required', 'string', 'max:255'],
            'vendor_reference' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'travel_date' => ['required', 'date'],
            'balance_due_date' => ['required', 'date'],
            'ziarat_makkah' => ['nullable', 'boolean'],
            'ziarat_madinah' => ['nullable', 'boolean'],
            'itineraries' => ['required', 'array', 'min:1'],
            'itineraries.*.sr_no' => ['required', 'integer', 'min:1'],
            'itineraries.*.airline_code' => ['required', 'string', 'max:20'],
            'itineraries.*.airline_number' => ['required', 'string', 'max:30'],
            'itineraries.*.class' => ['required', 'string', 'max:20'],
            'itineraries.*.departure_date' => ['required', 'date'],
            'itineraries.*.departure_airport' => ['required', 'string', 'max:30'],
            'itineraries.*.arrival_airport' => ['required', 'string', 'max:30'],
            'itineraries.*.departure_time' => ['required', 'date_format:H:i'],
            'itineraries.*.arrival_time' => ['required', 'date_format:H:i'],
            'itineraries.*.arrival_date' => ['required', 'date'],
            'passengers' => ['required', 'array', 'min:1'],
            'passengers.*.title' => ['required', 'string', 'max:20'],
            'passengers.*.first_name' => ['required', 'string', 'max:100'],
            'passengers.*.middle_name' => ['nullable', 'string', 'max:100'],
            'passengers.*.last_name' => ['required', 'string', 'max:100'],
            'passengers.*.passenger_type' => ['required', 'string', 'max:30'],
            'passengers.*.email' => ['required', 'email', 'max:255'],
            'passengers.*.phone' => ['required', 'string', 'max:30'],
            'passengers.*.date_of_birth' => ['required', 'date'],
            'passengers.*.passport_details' => ['nullable', 'string', 'max:255'],
            'package_costs' => ['required', 'array', 'min:1'],
            'package_costs.*.ticket_no' => ['nullable', 'string', 'max:50'],
            'package_costs.*.ticket_date' => ['nullable', 'date'],
            'package_costs.*.airline_from' => ['required', 'string', 'max:30'],
            'package_costs.*.airline_to' => ['required', 'string', 'max:30'],
            'package_costs.*.fare' => ['required', 'numeric', 'min:0'],
            'package_costs.*.tax' => ['nullable', 'numeric', 'min:0'],
            'package_costs.*.total_cost' => ['required', 'numeric', 'min:0'],
            'package_costs.*.margin' => ['required', 'numeric', 'min:0'],
            'package_costs.*.sell' => ['required', 'numeric', 'min:0'],
            'package_costs.*.supplier' => ['required', 'string', 'max:100'],
            'package_costs.*.pnr' => ['nullable', 'string', 'max:50'],
            'hotel_details' => ['required', 'array', 'min:1'],
            'hotel_details.*.sr_no' => ['required', 'integer', 'min:1'],
            'hotel_details.*.supplier' => ['required', 'string', 'max:100'],
            'hotel_details.*.hotel_name' => ['required', 'string', 'max:150'],
            'hotel_details.*.guest_name' => ['required', 'string', 'max:150'],
            'hotel_details.*.rooms' => ['required', 'integer', 'min:0'],
            'hotel_details.*.type' => ['required', 'string', 'max:100'],
            'hotel_details.*.meals' => ['required', 'string', 'max:100'],
            'hotel_details.*.date_in' => ['required', 'date'],
            'hotel_details.*.date_out' => ['required', 'date'],
            'hotel_details.*.nights' => ['required', 'integer', 'min:0'],
            'hotel_details.*.supplier_ref' => ['required', 'string', 'max:100'],
            'hotel_details.*.cost' => ['required', 'numeric', 'min:0'],
            'hotel_details.*.margin' => ['required', 'numeric'],
            'hotel_details.*.sell' => ['nullable', 'numeric', 'min:0'],
            'hotel_details.*.hotel_city' => ['required', 'string', 'max:100'],
        ])->validate();
    }

    private function mergeWithDraftSections(Request $request): array
    {
        $payload = $request->all();
        $drafts = $this->draftSections($request);

        foreach (['itineraries', 'passengers', 'package_costs', 'hotel_details'] as $section) {
            if (! empty($drafts[$section])) {
                $payload[$section] = $drafts[$section];
            }
        }

        return $payload;
    }

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
            'itineraries' => [
                'itineraries' => ['required', 'array', 'min:1'],
                'itineraries.*.sr_no' => ['required', 'integer', 'min:1'],
                'itineraries.*.airline_code' => ['required', 'string', 'max:20'],
                'itineraries.*.airline_number' => ['required', 'string', 'max:30'],
                'itineraries.*.class' => ['required', 'string', 'max:20'],
                'itineraries.*.departure_date' => ['required', 'date'],
                'itineraries.*.departure_airport' => ['required', 'string', 'max:30'],
                'itineraries.*.arrival_airport' => ['required', 'string', 'max:30'],
                'itineraries.*.departure_time' => ['required', 'date_format:H:i'],
                'itineraries.*.arrival_time' => ['required', 'date_format:H:i'],
                'itineraries.*.arrival_date' => ['required', 'date'],
            ],
            'passengers' => [
                'passengers' => ['required', 'array', 'min:1'],
                'passengers.*.title' => ['required', 'string', 'max:20'],
                'passengers.*.first_name' => ['required', 'string', 'max:100'],
                'passengers.*.middle_name' => ['nullable', 'string', 'max:100'],
                'passengers.*.last_name' => ['required', 'string', 'max:100'],
                'passengers.*.passenger_type' => ['required', 'string', 'max:30'],
                'passengers.*.email' => ['required', 'email', 'max:255'],
                'passengers.*.phone' => ['required', 'string', 'max:30'],
                'passengers.*.date_of_birth' => ['required', 'date'],
                'passengers.*.passport_details' => ['nullable', 'string', 'max:255'],
            ],
            'package_costs' => [
                'package_costs' => ['required', 'array', 'min:1'],
                'package_costs.*.ticket_no' => ['nullable', 'string', 'max:50'],
                'package_costs.*.ticket_date' => ['nullable', 'date'],
                'package_costs.*.airline_from' => ['required', 'string', 'max:30'],
                'package_costs.*.airline_to' => ['required', 'string', 'max:30'],
                'package_costs.*.fare' => ['required', 'numeric', 'min:0'],
                'package_costs.*.tax' => ['nullable', 'numeric', 'min:0'],
                'package_costs.*.total_cost' => ['required', 'numeric', 'min:0'],
                'package_costs.*.margin' => ['required', 'numeric', 'min:0'],
                'package_costs.*.sell' => ['required', 'numeric', 'min:0'],
                'package_costs.*.supplier' => ['required', 'string', 'max:100'],
                'package_costs.*.pnr' => ['nullable', 'string', 'max:50'],
            ],
            'hotel_details' => [
                'hotel_details' => ['required', 'array', 'min:1'],
                'hotel_details.*.sr_no' => ['required', 'integer', 'min:1'],
                'hotel_details.*.supplier' => ['required', 'string', 'max:100'],
                'hotel_details.*.hotel_name' => ['required', 'string', 'max:150'],
                'hotel_details.*.guest_name' => ['required', 'string', 'max:150'],
                'hotel_details.*.rooms' => ['required', 'integer', 'min:0'],
                'hotel_details.*.type' => ['required', 'string', 'max:100'],
                'hotel_details.*.meals' => ['required', 'string', 'max:100'],
                'hotel_details.*.date_in' => ['required', 'date'],
                'hotel_details.*.date_out' => ['required', 'date'],
                'hotel_details.*.nights' => ['required', 'integer', 'min:0'],
                'hotel_details.*.supplier_ref' => ['required', 'string', 'max:100'],
                'hotel_details.*.cost' => ['required', 'numeric', 'min:0'],
                'hotel_details.*.margin' => ['required', 'numeric'],
                'hotel_details.*.sell' => ['nullable', 'numeric', 'min:0'],
                'hotel_details.*.hotel_city' => ['required', 'string', 'max:100'],
            ],
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function persistFolder(array $validated, ?Folder $folder = null): array
    {
        try {
            DB::transaction(function () use ($validated, &$folder): void {
                $folderPayload = [
                    'agent_id' => $validated['agent_id'] ?? request()->user()?->getAuthIdentifier(),
                    'order_type' => $validated['order_type'],
                    'vendor_reference' => $validated['vendor_reference'] ?? null,
                    'status' => $validated['status'] ?? null,
                    'company_id' => $validated['company_id'],
                    'destination_id' => $validated['destination_id'],
                    'travel_date' => $validated['travel_date'],
                    'balance_due_date' => $validated['balance_due_date'] ?? null,
                    'makkah_ziarat' => (bool) ($validated['ziarat_makkah'] ?? false),
                    'madinah_ziarat' => (bool) ($validated['ziarat_madinah'] ?? false),
                ];

                if ($folder === null) {
                    $folder = Folder::create($folderPayload);
                } else {
                    $folder->update($folderPayload);
                    $folder->itineraries()->delete();
                    $folder->passengers()->delete();
                    $folder->packageCosts()->delete();
                    $folder->hotelDetails()->delete();
                }

                $folder->itineraries()->createMany($validated['itineraries'] ?? []);
                $folder->passengers()->createMany($validated['passengers'] ?? []);
                $folder->packageCosts()->createMany($validated['package_costs'] ?? []);
                $folder->hotelDetails()->createMany($validated['hotel_details'] ?? []);
            });
        } catch (Throwable $e) {
            report($e);

            return ['error', __('Could not save folder. Please try again.')];
        }

        request()->session()->forget($this->draftSessionKey(request()));

        return ['status', __('Folder saved successfully.')];
    }
}
