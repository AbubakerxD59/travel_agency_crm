@extends($leadLayout ?? 'layouts.admin')

@php
    $isEditMode = (bool) ($isEditMode ?? false);
@endphp

@section('title', $isEditMode ? 'Edit folder' : 'New folder')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">
                    {{ $isEditMode ? 'Edit folder #'.$lead->id : 'New folder' }}
                </h1>
                <p class="mt-1 text-sm text-concierge-muted">
                    {{ $isEditMode ? 'Update folder details.' : 'Create a booking record.' }}
                </p>
            </div>
            <a href="{{ route(($leadRoutePrefix ?? 'admin') . '.' . ($leadRouteResource ?? 'leads') . '.index') }}"
                class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-concierge-navy shadow-sm transition hover:bg-slate-50">
                Back to folders
            </a>
        </div>

        <div class="mt-8 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8">
            <form id="lead-create-form" method="POST"
                action="{{ $isEditMode ? route(($leadRoutePrefix ?? 'admin') . '.' . ($leadRouteResource ?? 'leads') . '.update', $lead) : route(($leadRoutePrefix ?? 'admin') . '.' . ($leadRouteResource ?? 'leads') . '.store') }}"
                class="space-y-4">
                @csrf
                @if ($isEditMode)
                    @method('PATCH')
                @endif

                @if (session('error'))
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"
                        role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"
                        role="alert">
                        <p class="font-medium">Please fix the following:</p>
                        <ul class="mt-2 list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $fieldClass =
                        'mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20';
                    $itineraryRows = old('itineraries', $draftItineraryRows ?? [[]]);
                    $passengerRows = old('passengers', $draftPassengerRows ?? [[]]);
                    $packageCostRows = old('package_costs', $draftPackageCostRows ?? [[]]);
                    $hotelDetailRows = old('hotel_details', $draftHotelDetailRows ?? [[]]);
                    if (!is_array($itineraryRows) || count($itineraryRows) === 0) {
                        $itineraryRows = [[]];
                    }
                    if (!is_array($passengerRows) || count($passengerRows) === 0) {
                        $passengerRows = [[]];
                    }
                    if (!is_array($packageCostRows) || count($packageCostRows) === 0) {
                        $packageCostRows = [[]];
                    }
                    if (!is_array($hotelDetailRows) || count($hotelDetailRows) === 0) {
                        $hotelDetailRows = [[]];
                    }
                @endphp

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <div class="min-w-0">
                        <label for="lead_order_type" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Order type</label>
                        <input id="lead_order_type" name="order_type" type="text" required
                            value="{{ old('order_type', $lead->order_type ?? '') }}" placeholder="e.g. Umrah package" class="{{ $fieldClass }}">
                    </div>
                    <div class="min-w-0">
                        <label for="lead_vendor_reference" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Vendor
                            reference</label>
                        <input id="lead_vendor_reference" name="vendor_reference" type="text" required
                            value="{{ old('vendor_reference', $lead->vendor_reference ?? '') }}" class="{{ $fieldClass }}">
                    </div>
                    <div class="min-w-0">
                        <label for="lead_status" class="block text-sm font-medium text-concierge-navy">Status</label>
                        <input id="lead_status" name="status" type="text"
                            value="{{ old('status', $lead->status ?? '') }}" class="{{ $fieldClass }}">
                    </div>

                    <div class="min-w-0">
                        <label for="lead_company_id" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Company</label>
                        <select id="lead_company_id" name="company_id" required class="{{ $fieldClass }}">
                            <option value="" disabled @selected(!old('company_id', $lead->company_id ?? null))>Select company</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected(old('company_id', $lead->company_id ?? null) == $company->id)>{{ $company->name }}
                                    ({{ $company->country?->name ?? '—' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-0">
                        <label for="lead_destination_id" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Destination</label>
                        <select id="lead_destination_id" name="destination_id" required class="{{ $fieldClass }}">
                            <option value="" disabled @selected(!old('destination_id', $lead->destination_id ?? null))>Select destination</option>
                            @foreach ($destinations as $destination)
                                <option value="{{ $destination->id }}" @selected(old('destination_id', $lead->destination_id ?? null) == $destination->id)>
                                    {{ $destination->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-0">
                        <label for="lead_travel_date" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Travel date</label>
                        <input id="lead_travel_date" name="travel_date" type="date" required
                            value="{{ old('travel_date', optional($lead->travel_date ?? null)->format('Y-m-d')) }}" class="{{ $fieldClass }}">
                    </div>
                    <div class="min-w-0">
                        <label for="lead_balance_due_date" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Balance due
                            date</label>
                        <input id="lead_balance_due_date" name="balance_due_date" type="date" required
                            value="{{ old('balance_due_date', optional($lead->balance_due_date ?? null)->format('Y-m-d')) }}" class="{{ $fieldClass }}">
                    </div>
                    <div class="min-w-0">
                        <span class="block text-sm font-medium text-concierge-navy">Ziarat</span>
                        <div
                            class="mt-1.5 flex min-h-[2.625rem] flex-row flex-nowrap items-center gap-6 rounded-xl border border-slate-200/80 bg-slate-50/50 px-4 py-2.5">
                            <label
                                class="flex cursor-pointer items-center gap-2 text-sm text-concierge-navy whitespace-nowrap">
                                <input type="checkbox" name="ziarat_makkah" value="1"
                                    class="rounded border-slate-300 text-concierge-accent focus:ring-concierge-accent/30"
                                    @checked(old('ziarat_makkah', $lead->ziarat_makkah ?? false))>
                                Makkah
                            </label>
                            <label
                                class="flex cursor-pointer items-center gap-2 text-sm text-concierge-navy whitespace-nowrap">
                                <input type="checkbox" name="ziarat_madinah" value="1"
                                    class="rounded border-slate-300 text-concierge-accent focus:ring-concierge-accent/30"
                                    @checked(old('ziarat_madinah', $lead->ziarat_madinah ?? false))>
                                Madinah
                            </label>
                        </div>
                    </div>
                </div>

                <div class="min-w-0">
                    <label for="lead_flight_itinerary" class="block text-sm font-medium text-concierge-navy">Flight
                        itinerary
                        <span class="font-normal text-concierge-muted">(optional)</span></label>
                    <textarea id="lead_flight_itinerary" name="flight_itinerary" rows="4" class="{{ $fieldClass }}">{{ old('flight_itinerary', $lead->flight_itinerary ?? '') }}</textarea>
                </div>

                <div class="space-y-3 rounded-2xl border border-slate-200/80 bg-slate-50/40 p-4 sm:p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="inline-flex items-center gap-2">
                            <h2 class="text-base font-semibold text-concierge-navy"><span class="text-rose-600">*</span>
                                Itineraries</h2>
                            <button type="button" id="save-itineraries-section"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700"
                                title="Save Itineraries">
                                <svg width="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path
                                        d="M19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16L21 8V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M17 21V13H7V21" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M7 3V8H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="inline-flex items-center gap-2">
                            <button type="button" id="generate-itinerary"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-concierge-navy px-4 py-2 text-xs font-semibold text-concierge-navy hover:bg-slate-100">
                                Generate Itinerary
                            </button>
                            <button type="button" id="add-itinerary-row"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2 text-xs font-semibold text-white hover:bg-concierge-navy-deep">
                                Add itinerary
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-[1150px] w-full border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-slate-100 text-left text-concierge-muted">
                                    <th class="border border-slate-200 px-2 py-2">Action</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span> Sr.
                                        No.</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Airline code</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Airline number</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Class</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Departure date</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Departure airport</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Arrival airport</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Departure time</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Arrival time</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Arrival date</th>
                                </tr>
                            </thead>
                            <tbody id="itinerary-rows">
                                @foreach ($itineraryRows as $i => $row)
                                    <tr class="itinerary-row bg-white">
                                        <td class="border border-slate-200 px-2 py-2 align-top">
                                            <button type="button"
                                                class="remove-itinerary-row inline-flex cursor-pointer items-center justify-center rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50">
                                                X
                                            </button>
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="number" min="1"
                                                name="itineraries[{{ $i }}][sr_no]"
                                                value="{{ data_get($row, 'sr_no') }}"
                                                class="w-12 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="itineraries[{{ $i }}][airline_code]"
                                                value="{{ data_get($row, 'airline_code') }}"
                                                class="w-14 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="itineraries[{{ $i }}][airline_number]"
                                                value="{{ data_get($row, 'airline_number') }}"
                                                class="w-14 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="itineraries[{{ $i }}][class]"
                                                value="{{ data_get($row, 'class') }}"
                                                class="w-10 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="date" name="itineraries[{{ $i }}][departure_date]"
                                                value="{{ data_get($row, 'departure_date') }}"
                                                class="w-36 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text"
                                                name="itineraries[{{ $i }}][departure_airport]"
                                                value="{{ data_get($row, 'departure_airport') }}"
                                                class="w-28 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text"
                                                name="itineraries[{{ $i }}][arrival_airport]"
                                                value="{{ data_get($row, 'arrival_airport') }}"
                                                class="w-28 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="time" name="itineraries[{{ $i }}][departure_time]"
                                                value="{{ data_get($row, 'departure_time') }}"
                                                class="w-28 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="time" name="itineraries[{{ $i }}][arrival_time]"
                                                value="{{ data_get($row, 'arrival_time') }}"
                                                class="w-28 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="date" name="itineraries[{{ $i }}][arrival_date]"
                                                value="{{ data_get($row, 'arrival_date') }}"
                                                class="w-36 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-concierge-muted">At least one itinerary row is required.</p>
                </div>

                <div class="space-y-3 rounded-2xl border border-slate-200/80 bg-slate-50/40 p-4 sm:p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="inline-flex items-center gap-2">
                            <h2 class="text-base font-semibold text-concierge-navy"><span class="text-rose-600">*</span>
                                Passenger details</h2>
                            <button type="button" id="save-passengers-section"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700"
                                title="Save Passenger Details">
                                <svg width="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path
                                        d="M19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16L21 8V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M17 21V13H7V21" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M7 3V8H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="inline-flex items-center gap-2">
                            <button type="button" id="add-passenger-row"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2 text-xs font-semibold text-white hover:bg-concierge-navy-deep">
                                Add new passenger
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-[1200px] w-full border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-slate-100 text-left text-concierge-muted">
                                    <th class="border border-slate-200 px-2 py-2">Action</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Title</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        First name</th>
                                    <th class="border border-slate-200 px-2 py-2">Middle name</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Last name</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Passenger type</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Email</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Phone</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Date of birth</th>
                                    <th class="border border-slate-200 px-2 py-2">Passport details</th>
                                </tr>
                            </thead>
                            <tbody id="passenger-rows">
                                @foreach ($passengerRows as $i => $row)
                                    <tr class="passenger-row bg-white">
                                        <td class="border border-slate-200 px-2 py-2 align-top">
                                            <button type="button"
                                                class="remove-passenger-row inline-flex cursor-pointer items-center justify-center rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50">
                                                X
                                            </button>
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="passengers[{{ $i }}][title]"
                                                value="{{ data_get($row, 'title') }}"
                                                class="w-16 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="passengers[{{ $i }}][first_name]"
                                                value="{{ data_get($row, 'first_name') }}"
                                                class="w-28 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="passengers[{{ $i }}][middle_name]"
                                                value="{{ data_get($row, 'middle_name') }}"
                                                class="w-28 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="passengers[{{ $i }}][last_name]"
                                                value="{{ data_get($row, 'last_name') }}"
                                                class="w-28 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="passengers[{{ $i }}][passenger_type]"
                                                value="{{ data_get($row, 'passenger_type') }}"
                                                class="w-24 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="email" name="passengers[{{ $i }}][email]"
                                                value="{{ data_get($row, 'email') }}"
                                                class="w-40 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="passengers[{{ $i }}][phone]"
                                                value="{{ data_get($row, 'phone') }}"
                                                class="w-32 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="date" name="passengers[{{ $i }}][date_of_birth]"
                                                value="{{ data_get($row, 'date_of_birth') }}"
                                                class="w-36 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text"
                                                name="passengers[{{ $i }}][passport_details]"
                                                value="{{ data_get($row, 'passport_details') }}"
                                                class="w-40 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-concierge-muted">At least one passenger row is required.</p>
                </div>

                <div class="space-y-3 rounded-2xl border border-slate-200/80 bg-slate-50/40 p-4 sm:p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="inline-flex items-center gap-2">
                            <h2 class="text-base font-semibold text-concierge-navy"><span class="text-rose-600">*</span>
                                Ticket/Package Cost</h2>
                            <button type="button" id="save-package-costs-section"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700"
                                title="Save Ticket/Package Cost">
                                <svg width="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path
                                        d="M19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16L21 8V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M17 21V13H7V21" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M7 3V8H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="inline-flex items-center gap-2">
                            <button type="button" id="add-package-cost-row"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2 text-xs font-semibold text-white hover:bg-concierge-navy-deep">
                                Add new package cost
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-[1400px] w-full border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-slate-100 text-left text-concierge-muted">
                                    <th class="border border-slate-200 px-2 py-2">Action</th>
                                    <th class="border border-slate-200 px-2 py-2">Ticket no</th>
                                    <th class="border border-slate-200 px-2 py-2">Ticket date</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Airline from</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Airline to</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Fare</th>
                                    <th class="border border-slate-200 px-2 py-2">Tax</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Total cost</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Margin</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Sell</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Supplier</th>
                                    <th class="border border-slate-200 px-2 py-2">PNR</th>
                                </tr>
                            </thead>
                            <tbody id="package-cost-rows">
                                @foreach ($packageCostRows as $i => $row)
                                    <tr class="package-cost-row bg-white">
                                        <td class="border border-slate-200 px-2 py-2 align-top">
                                            <button type="button"
                                                class="remove-package-cost-row inline-flex cursor-pointer items-center justify-center rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50">
                                                X
                                            </button>
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="package_costs[{{ $i }}][ticket_no]"
                                                value="{{ data_get($row, 'ticket_no') }}"
                                                class="w-24 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="date" name="package_costs[{{ $i }}][ticket_date]"
                                                value="{{ data_get($row, 'ticket_date') }}"
                                                class="w-36 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="package_costs[{{ $i }}][airline_from]"
                                                value="{{ data_get($row, 'airline_from') }}"
                                                class="w-24 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="package_costs[{{ $i }}][airline_to]"
                                                value="{{ data_get($row, 'airline_to') }}"
                                                class="w-24 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="number" min="0" step="0.01"
                                                name="package_costs[{{ $i }}][fare]"
                                                value="{{ data_get($row, 'fare') }}"
                                                class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="number" min="0" step="0.01"
                                                name="package_costs[{{ $i }}][tax]"
                                                value="{{ data_get($row, 'tax') }}"
                                                class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="number" min="0" step="0.01"
                                                name="package_costs[{{ $i }}][total_cost]"
                                                value="{{ data_get($row, 'total_cost') }}"
                                                class="w-24 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="number" min="0" step="0.01"
                                                name="package_costs[{{ $i }}][margin]"
                                                value="{{ data_get($row, 'margin') }}"
                                                class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="number" min="0" step="0.01"
                                                name="package_costs[{{ $i }}][sell]"
                                                value="{{ data_get($row, 'sell') }}"
                                                class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="package_costs[{{ $i }}][supplier]"
                                                value="{{ data_get($row, 'supplier') }}"
                                                class="w-24 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="package_costs[{{ $i }}][pnr]"
                                                value="{{ data_get($row, 'pnr') }}"
                                                class="w-24 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-concierge-muted">At least one ticket/package cost row is required.</p>
                </div>

                <div class="space-y-3 rounded-2xl border border-slate-200/80 bg-slate-50/40 p-4 sm:p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="inline-flex items-center gap-2">
                            <h2 class="text-base font-semibold text-concierge-navy">Hotel Details</h2>
                            <button type="button" id="save-hotel-details-section"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700"
                                title="Save Hotel Details">
                                <svg width="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path
                                        d="M19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16L21 8V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M17 21V13H7V21" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M7 3V8H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="inline-flex items-center gap-2">
                            <button type="button" id="add-hotel-detail-row"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2 text-xs font-semibold text-white hover:bg-concierge-navy-deep">
                                Add new hotel
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-[1650px] w-full border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-slate-100 text-left text-concierge-muted">
                                    <th class="border border-slate-200 px-2 py-2">Action</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span> Sr.
                                        No.</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Supplier</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Hotel name</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Guest name</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span> No.
                                        of rooms</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Type</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Meals</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Date in</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Date out</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Nights</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Supplier ref</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Cost</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Margin</th>
                                    <th class="border border-slate-200 px-2 py-2">Sell</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Hotel city</th>
                                </tr>
                            </thead>
                            <tbody id="hotel-detail-rows">
                                @foreach ($hotelDetailRows as $i => $row)
                                    <tr class="hotel-detail-row bg-white">
                                        <td class="border border-slate-200 px-2 py-2 align-top">
                                            <button type="button"
                                                class="remove-hotel-detail-row inline-flex cursor-pointer items-center justify-center rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50">
                                                X
                                            </button>
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="number" min="1"
                                                name="hotel_details[{{ $i }}][sr_no]"
                                                value="{{ data_get($row, 'sr_no') }}"
                                                class="w-12 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="hotel_details[{{ $i }}][supplier]"
                                                value="{{ data_get($row, 'supplier') }}"
                                                class="w-24 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="hotel_details[{{ $i }}][hotel_name]"
                                                value="{{ data_get($row, 'hotel_name') }}"
                                                class="w-32 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="hotel_details[{{ $i }}][guest_name]"
                                                value="{{ data_get($row, 'guest_name') }}"
                                                class="w-32 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="number" min="0"
                                                name="hotel_details[{{ $i }}][rooms]"
                                                value="{{ data_get($row, 'rooms') }}"
                                                class="w-16 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="hotel_details[{{ $i }}][type]"
                                                value="{{ data_get($row, 'type') }}"
                                                class="w-24 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="hotel_details[{{ $i }}][meals]"
                                                value="{{ data_get($row, 'meals') }}"
                                                class="w-24 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="date" name="hotel_details[{{ $i }}][date_in]"
                                                value="{{ data_get($row, 'date_in') }}"
                                                class="w-36 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="date" name="hotel_details[{{ $i }}][date_out]"
                                                value="{{ data_get($row, 'date_out') }}"
                                                class="w-36 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="number" min="0"
                                                name="hotel_details[{{ $i }}][nights]"
                                                value="{{ data_get($row, 'nights') }}"
                                                class="w-16 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text"
                                                name="hotel_details[{{ $i }}][supplier_ref]"
                                                value="{{ data_get($row, 'supplier_ref') }}"
                                                class="w-24 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="number" min="0" step="0.01"
                                                name="hotel_details[{{ $i }}][cost]"
                                                value="{{ data_get($row, 'cost') }}"
                                                class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="number" step="0.01"
                                                name="hotel_details[{{ $i }}][margin]"
                                                value="{{ data_get($row, 'margin') }}"
                                                class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="number" min="0" step="0.01"
                                                name="hotel_details[{{ $i }}][sell]"
                                                value="{{ data_get($row, 'sell') }}"
                                                class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="hotel_details[{{ $i }}][hotel_city]"
                                                value="{{ data_get($row, 'hotel_city') }}"
                                                class="w-24 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route(($leadRoutePrefix ?? 'admin') . '.' . ($leadRouteResource ?? 'leads') . '.index') }}"
                        class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-200 py-2.5 text-center text-sm font-medium text-concierge-navy hover:bg-slate-50 sm:px-6">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy py-2.5 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 hover:bg-concierge-navy-deep sm:px-6">
                        {{ $isEditMode ? 'Save folder' : 'Create folder' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        (() => {
            const tableBody = document.getElementById('itinerary-rows');
            const addButton = document.getElementById('add-itinerary-row');
            const generateButton = document.getElementById('generate-itinerary');
            const itineraryTextArea = document.getElementById('lead_flight_itinerary');
            if (!tableBody || !addButton) {
                return;
            }

            const fields = [
                ['sr_no', 'number', 'w-12', '1'],
                ['airline_code', 'text', 'w-14', null],
                ['airline_number', 'text', 'w-14', null],
                ['class', 'text', 'w-10', null],
                ['departure_date', 'date', 'w-36', null],
                ['departure_airport', 'text', 'w-28', null],
                ['arrival_airport', 'text', 'w-28', null],
                ['departure_time', 'time', 'w-28', null],
                ['arrival_time', 'time', 'w-28', null],
                ['arrival_date', 'date', 'w-36', null],
            ];

            function inputClass(sizeClass) {
                return `${sizeClass} rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm`;
            }

            function makeInput(index, field, type, sizeClass, minValue) {
                const input = document.createElement('input');
                input.type = type;
                input.name = `itineraries[${index}][${field}]`;
                input.className = inputClass(sizeClass);
                if (minValue != null) {
                    input.min = minValue;
                }
                return input;
            }

            function createRow(index) {
                const row = document.createElement('tr');
                row.className = 'itinerary-row bg-white';

                const actionCell = document.createElement('td');
                actionCell.className = 'border border-slate-200 px-2 py-2 align-top';
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className =
                    'remove-itinerary-row inline-flex cursor-pointer items-center justify-center rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50';
                removeBtn.textContent = 'X';
                actionCell.appendChild(removeBtn);
                row.appendChild(actionCell);

                fields.forEach(([field, type, sizeClass, minValue]) => {
                    const cell = document.createElement('td');
                    cell.className = 'border border-slate-200 px-2 py-2';
                    cell.appendChild(makeInput(index, field, type, sizeClass, minValue));
                    row.appendChild(cell);
                });

                return row;
            }

            function renumberRows() {
                [...tableBody.querySelectorAll('.itinerary-row')].forEach((row, idx) => {
                    row.querySelectorAll('input[name^="itineraries["]').forEach((input) => {
                        input.name = input.name.replace(/itineraries\[\d+\]/, `itineraries[${idx}]`);
                    });
                });
            }

            function ensureOneRow() {
                if (tableBody.querySelectorAll('.itinerary-row').length === 0) {
                    tableBody.appendChild(createRow(0));
                }
                renumberRows();
            }

            function showError(message) {
                if (window.toastr) {
                    window.toastr.error(message);
                    return;
                }
                alert(message);
            }

            function parseDateToken(token, fallbackYear) {
                const months = {
                    JAN: 0,
                    FEB: 1,
                    MAR: 2,
                    APR: 3,
                    MAY: 4,
                    JUN: 5,
                    JUL: 6,
                    AUG: 7,
                    SEP: 8,
                    OCT: 9,
                    NOV: 10,
                    DEC: 11,
                };
                const match = token.match(/^(\d{2})([A-Z]{3})(\d{2})?$/);
                if (!match) {
                    return null;
                }
                const day = Number(match[1]);
                const month = months[match[2]];
                const year = match[3] ? (2000 + Number(match[3])) : fallbackYear;
                if (month === undefined) {
                    return null;
                }
                const date = new Date(year, month, day);
                if (Number.isNaN(date.getTime())) {
                    return null;
                }
                return date;
            }

            function toInputDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function parseTimeToken(token) {
                const match = token.match(/^(\d{3,4})([AP])$/);
                if (!match) {
                    return null;
                }
                const raw = match[1].padStart(4, '0');
                let hour = Number(raw.slice(0, 2));
                const minute = Number(raw.slice(2, 4));
                const meridiem = match[2];
                if (minute > 59 || hour < 1 || hour > 12) {
                    return null;
                }
                if (meridiem === 'P' && hour !== 12) {
                    hour += 12;
                }
                if (meridiem === 'A' && hour === 12) {
                    hour = 0;
                }
                return `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
            }

            function parseItineraryCommand(line) {
                const parts = line.trim().toUpperCase().split(/\s+/).filter(Boolean);
                let cursor = 0;
                if (parts.length < 6) {
                    return null;
                }

                const srNo = parts[cursor++];
                const airlineToken = parts[cursor++];
                const departureDateToken = parts[cursor++];
                let classToken = null;
                if (/^[A-Z]$/.test(parts[cursor] ?? '')) {
                    classToken = parts[cursor];
                    cursor += 1;
                }
                const routeToken = parts[cursor++];
                const departureTimeToken = parts[cursor++];
                const arrivalTimeToken = parts[cursor++];
                const arrivalDateToken = parts[cursor] ?? null;
                if (cursor + (arrivalDateToken ? 1 : 0) !== parts.length) {
                    return null;
                }

                const flightMatch = airlineToken.match(/^([A-Z]{2})(\d+)([A-Z])?$/);
                const routeMatch = routeToken.match(/^([A-Z]{3})([A-Z]{3})$/);
                if (!/^\d+$/.test(srNo) || !flightMatch || !routeMatch) {
                    return null;
                }
                const cabinClass = classToken ?? flightMatch[3] ?? null;
                if (!cabinClass) {
                    return null;
                }

                const currentYear = new Date().getFullYear();
                const departureDate = parseDateToken(departureDateToken, currentYear);
                const departureTime = parseTimeToken(departureTimeToken);
                const arrivalTime = parseTimeToken(arrivalTimeToken);
                if (!departureDate || !departureTime || !arrivalTime) {
                    return null;
                }

                let arrivalDate = arrivalDateToken ? parseDateToken(arrivalDateToken, currentYear) : null;
                if (!arrivalDate) {
                    const [depHour, depMinute] = departureTime.split(':').map(Number);
                    const [arrHour, arrMinute] = arrivalTime.split(':').map(Number);
                    const depMinutes = (depHour * 60) + depMinute;
                    const arrMinutes = (arrHour * 60) + arrMinute;
                    arrivalDate = new Date(departureDate);
                    if (arrMinutes < depMinutes) {
                        arrivalDate.setDate(arrivalDate.getDate() + 1);
                    }
                }

                return {
                    sr_no: srNo,
                    airline_code: flightMatch[1],
                    airline_number: flightMatch[2],
                    class: cabinClass,
                    departure_date: toInputDate(departureDate),
                    departure_airport: routeMatch[1],
                    arrival_airport: routeMatch[2],
                    departure_time: departureTime,
                    arrival_time: arrivalTime,
                    arrival_date: toInputDate(arrivalDate),
                };
            }

            function fillRowFromData(row, data) {
                Object.entries(data).forEach(([field, value]) => {
                    const input = row.querySelector(`input[name$="[${field}]"]`);
                    if (input) {
                        input.value = value;
                    }
                });
            }

            addButton.addEventListener('click', () => {
                const nextIndex = tableBody.querySelectorAll('.itinerary-row').length;
                tableBody.appendChild(createRow(nextIndex));
            });

            generateButton?.addEventListener('click', () => {
                const rawText = itineraryTextArea?.value?.trim() ?? '';
                if (!rawText) {
                    showError('Enter itinerary commands in Flight itinerary first.');
                    return;
                }

                const lines = rawText.split('\n').map((line) => line.trim()).filter(Boolean);
                const parsedRows = [];
                for (let i = 0; i < lines.length; i += 1) {
                    const parsed = parseItineraryCommand(lines[i]);
                    if (!parsed) {
                        showError(`Invalid itinerary command on line ${i + 1}.`);
                        return;
                    }
                    parsedRows.push(parsed);
                }

                tableBody.innerHTML = '';
                parsedRows.forEach((rowData, index) => {
                    const row = createRow(index);
                    fillRowFromData(row, rowData);
                    tableBody.appendChild(row);
                });
                ensureOneRow();
            });

            tableBody.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }
                const removeBtn = target.closest('.remove-itinerary-row');
                if (!removeBtn) {
                    return;
                }
                removeBtn.closest('.itinerary-row')?.remove();
                ensureOneRow();
            });

            ensureOneRow();
        })();

        (() => {
            const tableBody = document.getElementById('passenger-rows');
            const addButton = document.getElementById('add-passenger-row');
            if (!tableBody || !addButton) {
                return;
            }

            const fields = [
                ['title', 'text', 'w-16', null],
                ['first_name', 'text', 'w-28', null],
                ['middle_name', 'text', 'w-28', null],
                ['last_name', 'text', 'w-28', null],
                ['passenger_type', 'text', 'w-24', null],
                ['email', 'email', 'w-40', null],
                ['phone', 'text', 'w-32', null],
                ['date_of_birth', 'date', 'w-36', null],
                ['passport_details', 'text', 'w-40', null],
            ];

            function inputClass(sizeClass) {
                return `${sizeClass} rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm`;
            }

            function makeInput(index, field, type, sizeClass, minValue) {
                const input = document.createElement('input');
                input.type = type;
                input.name = `passengers[${index}][${field}]`;
                input.className = inputClass(sizeClass);
                if (minValue != null) {
                    input.min = minValue;
                }
                return input;
            }

            function createRow(index) {
                const row = document.createElement('tr');
                row.className = 'passenger-row bg-white';

                const actionCell = document.createElement('td');
                actionCell.className = 'border border-slate-200 px-2 py-2 align-top';
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className =
                    'remove-passenger-row inline-flex cursor-pointer items-center justify-center rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50';
                removeBtn.textContent = 'X';
                actionCell.appendChild(removeBtn);
                row.appendChild(actionCell);

                fields.forEach(([field, type, sizeClass, minValue]) => {
                    const cell = document.createElement('td');
                    cell.className = 'border border-slate-200 px-2 py-2';
                    cell.appendChild(makeInput(index, field, type, sizeClass, minValue));
                    row.appendChild(cell);
                });

                return row;
            }

            function renumberRows() {
                [...tableBody.querySelectorAll('.passenger-row')].forEach((row, idx) => {
                    row.querySelectorAll('input[name^="passengers["]').forEach((input) => {
                        input.name = input.name.replace(/passengers\[\d+\]/, `passengers[${idx}]`);
                    });
                });
            }

            function ensureOneRow() {
                if (tableBody.querySelectorAll('.passenger-row').length === 0) {
                    tableBody.appendChild(createRow(0));
                }
                renumberRows();
            }

            addButton.addEventListener('click', () => {
                const nextIndex = tableBody.querySelectorAll('.passenger-row').length;
                tableBody.appendChild(createRow(nextIndex));
            });

            tableBody.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }
                const removeBtn = target.closest('.remove-passenger-row');
                if (!removeBtn) {
                    return;
                }
                removeBtn.closest('.passenger-row')?.remove();
                ensureOneRow();
            });

            ensureOneRow();
        })();

        (() => {
            const tableBody = document.getElementById('package-cost-rows');
            const addButton = document.getElementById('add-package-cost-row');
            if (!tableBody || !addButton) {
                return;
            }

            const fields = [
                ['ticket_no', 'text', 'w-24', null, null],
                ['ticket_date', 'date', 'w-36', null, null],
                ['airline_from', 'text', 'w-24', null, null],
                ['airline_to', 'text', 'w-24', null, null],
                ['fare', 'number', 'w-20', '0', '0.01'],
                ['tax', 'number', 'w-20', '0', '0.01'],
                ['total_cost', 'number', 'w-24', '0', '0.01'],
                ['margin', 'number', 'w-20', '0', '0.01'],
                ['sell', 'number', 'w-20', '0', '0.01'],
                ['supplier', 'text', 'w-24', null, null],
                ['pnr', 'text', 'w-24', null, null],
            ];

            function inputClass(sizeClass) {
                return `${sizeClass} rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm`;
            }

            function makeInput(index, field, type, sizeClass, minValue, stepValue) {
                const input = document.createElement('input');
                input.type = type;
                input.name = `package_costs[${index}][${field}]`;
                input.className = inputClass(sizeClass);
                if (minValue != null) {
                    input.min = minValue;
                }
                if (stepValue != null) {
                    input.step = stepValue;
                }
                return input;
            }

            function createRow(index) {
                const row = document.createElement('tr');
                row.className = 'package-cost-row bg-white';

                const actionCell = document.createElement('td');
                actionCell.className = 'border border-slate-200 px-2 py-2 align-top';
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className =
                    'remove-package-cost-row inline-flex cursor-pointer items-center justify-center rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50';
                removeBtn.textContent = 'X';
                actionCell.appendChild(removeBtn);
                row.appendChild(actionCell);

                fields.forEach(([field, type, sizeClass, minValue, stepValue]) => {
                    const cell = document.createElement('td');
                    cell.className = 'border border-slate-200 px-2 py-2';
                    cell.appendChild(makeInput(index, field, type, sizeClass, minValue, stepValue));
                    row.appendChild(cell);
                });

                return row;
            }

            function renumberRows() {
                [...tableBody.querySelectorAll('.package-cost-row')].forEach((row, idx) => {
                    row.querySelectorAll('input[name^="package_costs["]').forEach((input) => {
                        input.name = input.name.replace(/package_costs\[\d+\]/,
                            `package_costs[${idx}]`);
                    });
                });
            }

            function ensureOneRow() {
                if (tableBody.querySelectorAll('.package-cost-row').length === 0) {
                    tableBody.appendChild(createRow(0));
                }
                renumberRows();
            }

            addButton.addEventListener('click', () => {
                const nextIndex = tableBody.querySelectorAll('.package-cost-row').length;
                tableBody.appendChild(createRow(nextIndex));
            });

            tableBody.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }
                const removeBtn = target.closest('.remove-package-cost-row');
                if (!removeBtn) {
                    return;
                }
                removeBtn.closest('.package-cost-row')?.remove();
                ensureOneRow();
            });

            ensureOneRow();
        })();

        (() => {
            const tableBody = document.getElementById('hotel-detail-rows');
            const addButton = document.getElementById('add-hotel-detail-row');
            if (!tableBody || !addButton) {
                return;
            }

            const fields = [
                ['sr_no', 'number', 'w-12', '1', null],
                ['supplier', 'text', 'w-24', null, null],
                ['hotel_name', 'text', 'w-32', null, null],
                ['guest_name', 'text', 'w-32', null, null],
                ['rooms', 'number', 'w-16', '0', null],
                ['type', 'text', 'w-24', null, null],
                ['meals', 'text', 'w-24', null, null],
                ['date_in', 'date', 'w-36', null, null],
                ['date_out', 'date', 'w-36', null, null],
                ['nights', 'number', 'w-16', '0', null],
                ['supplier_ref', 'text', 'w-24', null, null],
                ['cost', 'number', 'w-20', '0', '0.01'],
                ['margin', 'number', 'w-20', null, '0.01'],
                ['sell', 'number', 'w-20', '0', '0.01'],
                ['hotel_city', 'text', 'w-24', null, null],
            ];

            function inputClass(sizeClass) {
                return `${sizeClass} rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm`;
            }

            function makeInput(index, field, type, sizeClass, minValue, stepValue) {
                const input = document.createElement('input');
                input.type = type;
                input.name = `hotel_details[${index}][${field}]`;
                input.className = inputClass(sizeClass);
                if (minValue != null) {
                    input.min = minValue;
                }
                if (stepValue != null) {
                    input.step = stepValue;
                }
                return input;
            }

            function createRow(index) {
                const row = document.createElement('tr');
                row.className = 'hotel-detail-row bg-white';

                const actionCell = document.createElement('td');
                actionCell.className = 'border border-slate-200 px-2 py-2 align-top';
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className =
                    'remove-hotel-detail-row inline-flex cursor-pointer items-center justify-center rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50';
                removeBtn.textContent = 'X';
                actionCell.appendChild(removeBtn);
                row.appendChild(actionCell);

                fields.forEach(([field, type, sizeClass, minValue, stepValue]) => {
                    const cell = document.createElement('td');
                    cell.className = 'border border-slate-200 px-2 py-2';
                    cell.appendChild(makeInput(index, field, type, sizeClass, minValue, stepValue));
                    row.appendChild(cell);
                });

                return row;
            }

            function renumberRows() {
                [...tableBody.querySelectorAll('.hotel-detail-row')].forEach((row, idx) => {
                    row.querySelectorAll('input[name^="hotel_details["]').forEach((input) => {
                        input.name = input.name.replace(/hotel_details\[\d+\]/,
                            `hotel_details[${idx}]`);
                    });
                });
            }

            function ensureOneRow() {
                if (tableBody.querySelectorAll('.hotel-detail-row').length === 0) {
                    tableBody.appendChild(createRow(0));
                }
                renumberRows();
            }

            addButton.addEventListener('click', () => {
                const nextIndex = tableBody.querySelectorAll('.hotel-detail-row').length;
                tableBody.appendChild(createRow(nextIndex));
            });

            tableBody.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }
                const removeBtn = target.closest('.remove-hotel-detail-row');
                if (!removeBtn) {
                    return;
                }
                removeBtn.closest('.hotel-detail-row')?.remove();
                ensureOneRow();
            });

            ensureOneRow();
        })();

        (() => {
            const form = document.getElementById('lead-create-form');
            if (!form) {
                return;
            }
            const sectionSaveUrlTemplate = @json(route('agent.folders.sections.save', ['section' => '__SECTION__']));
            const csrfToken = form.querySelector('input[name="_token"]')?.value ?? '';

            if (!document.getElementById('toastr-css-cdn')) {
                const link = document.createElement('link');
                link.id = 'toastr-css-cdn';
                link.rel = 'stylesheet';
                link.href = 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css';
                document.head.appendChild(link);
            }

            if (window.toastr) {
                window.toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 3500,
                    extendedTimeOut: 1500,
                };
            }

            function showError(message) {
                if (window.toastr) {
                    window.toastr.error(message);
                    return;
                }
                alert(message);
            }

            const itineraryRequiredFields = [
                'sr_no',
                'airline_code',
                'airline_number',
                'class',
                'departure_date',
                'departure_airport',
                'arrival_airport',
                'departure_time',
                'arrival_time',
                'arrival_date',
            ];
            const passengerRequiredFields = [
                'title',
                'first_name',
                'last_name',
                'passenger_type',
                'email',
                'phone',
                'date_of_birth',
            ];
            const packageRequiredFields = [
                'airline_from',
                'airline_to',
                'fare',
                'total_cost',
                'margin',
                'sell',
                'supplier',
            ];
            const hotelRequiredFields = [
                'sr_no',
                'supplier',
                'hotel_name',
                'guest_name',
                'rooms',
                'type',
                'meals',
                'date_in',
                'date_out',
                'nights',
                'supplier_ref',
                'cost',
                'margin',
                'hotel_city',
            ];

            function removeFieldError(field) {
                field.classList.remove('border-rose-500', 'ring-1', 'ring-rose-200');
            }

            function markFieldError(field) {
                field.classList.add('border-rose-500', 'ring-1', 'ring-rose-200');
            }

            function clearRowErrors(rowSelector) {
                document.querySelectorAll(`${rowSelector} input, ${rowSelector} select, ${rowSelector} textarea`)
                    .forEach(removeFieldError);
            }

            function validateRequiredRowFields(rowSelector, requiredFields, inputPrefix) {
                clearRowErrors(rowSelector);
                const rows = [...document.querySelectorAll(rowSelector)];
                let firstInvalidField = null;

                rows.forEach((row) => {
                    requiredFields.forEach((fieldName) => {
                        const input = row.querySelector(
                            `input[name^="${inputPrefix}["][name$="[${fieldName}]"], select[name^="${inputPrefix}["][name$="[${fieldName}]"], textarea[name^="${inputPrefix}["][name$="[${fieldName}]"]`,
                        );
                        if (!input) {
                            return;
                        }
                        if (String(input.value ?? '').trim() === '') {
                            markFieldError(input);
                            if (!firstInvalidField) {
                                firstInvalidField = input;
                            }
                        }
                    });
                });

                if (firstInvalidField) {
                    firstInvalidField.focus();
                    return false;
                }

                return true;
            }

            function sectionHasAtLeastOneFilledRow(selector) {
                const rows = [...document.querySelectorAll(selector)];
                return rows.some((row) => [...row.querySelectorAll('input, select, textarea')].some((field) => {
                    const value = field.value;
                    return typeof value === 'string' && value.trim() !== '';
                }));
            }

            function collectSectionRows(sectionName, rowSelector) {
                return [...document.querySelectorAll(rowSelector)].map((row) => {
                    const rowData = {};
                    row.querySelectorAll(
                            `input[name^="${sectionName}["], select[name^="${sectionName}["], textarea[name^="${sectionName}["]`
                        )
                        .forEach((field) => {
                            const match = field.name.match(/\[([^\]]+)\]$/);
                            if (!match) {
                                return;
                            }
                            rowData[match[1]] = field.value;
                        });
                    return rowData;
                });
            }

            async function saveSectionDraft(sectionName, rowSelector, buttonElement) {
                if (!csrfToken) {
                    showError('Could not verify form token. Please refresh and try again.');
                    return;
                }

                const rows = collectSectionRows(sectionName, rowSelector);
                const url = sectionSaveUrlTemplate.replace('__SECTION__', sectionName);

                buttonElement?.setAttribute('disabled', 'disabled');
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            [sectionName]: rows,
                        }),
                    });

                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        showError(data.message || 'Could not save section.');
                        return;
                    }

                    if (window.toastr) {
                        window.toastr.success(data.message || 'Section saved successfully.');
                    }
                } catch (error) {
                    showError('Could not save section right now. Please try again.');
                } finally {
                    buttonElement?.removeAttribute('disabled');
                }
            }

            form.addEventListener('submit', (event) => {
                if (!sectionHasAtLeastOneFilledRow('#itinerary-rows .itinerary-row')) {
                    event.preventDefault();
                    showError('Please fill at least one itinerary row.');
                    return;
                }

                if (!validateRequiredRowFields('#itinerary-rows .itinerary-row', itineraryRequiredFields,
                        'itineraries')) {
                    event.preventDefault();
                    showError('Please complete all itinerary fields.');
                    return;
                }

                if (!sectionHasAtLeastOneFilledRow('#passenger-rows .passenger-row')) {
                    event.preventDefault();
                    showError('Please fill at least one passenger row.');
                    return;
                }

                if (!validateRequiredRowFields('#passenger-rows .passenger-row', passengerRequiredFields,
                        'passengers')) {
                    event.preventDefault();
                    showError('Please complete required passenger fields.');
                    return;
                }

                if (!sectionHasAtLeastOneFilledRow('#package-cost-rows .package-cost-row')) {
                    event.preventDefault();
                    showError('Please fill at least one ticket/package cost row.');
                    return;
                }

                if (!validateRequiredRowFields('#package-cost-rows .package-cost-row', packageRequiredFields,
                        'package_costs')) {
                    event.preventDefault();
                    showError('Please complete required ticket/package cost fields.');
                    return;
                }

                if (!sectionHasAtLeastOneFilledRow('#hotel-detail-rows .hotel-detail-row')) {
                    event.preventDefault();
                    showError('Please fill at least one hotel details row.');
                    return;
                }

                if (!validateRequiredRowFields('#hotel-detail-rows .hotel-detail-row', hotelRequiredFields,
                        'hotel_details')) {
                    event.preventDefault();
                    showError('Please complete required hotel details fields.');
                    return;
                }
            });

            form.addEventListener('input', (event) => {
                const target = event.target;
                if (!(target instanceof HTMLInputElement)) {
                    return;
                }
                if (!target.name.startsWith('itineraries[') &&
                    !target.name.startsWith('passengers[') &&
                    !target.name.startsWith('package_costs[') &&
                    !target.name.startsWith('hotel_details[')) {
                    return;
                }
                removeFieldError(target);
            });

            document.getElementById('save-itineraries-section')?.addEventListener('click', () => {
                saveSectionDraft('itineraries', '#itinerary-rows .itinerary-row', document.getElementById(
                    'save-itineraries-section'));
            });

            document.getElementById('save-passengers-section')?.addEventListener('click', () => {
                saveSectionDraft('passengers', '#passenger-rows .passenger-row', document.getElementById(
                    'save-passengers-section'));
            });

            document.getElementById('save-package-costs-section')?.addEventListener('click', () => {
                saveSectionDraft('package_costs', '#package-cost-rows .package-cost-row', document
                    .getElementById('save-package-costs-section'));
            });

            document.getElementById('save-hotel-details-section')?.addEventListener('click', () => {
                saveSectionDraft('hotel_details', '#hotel-detail-rows .hotel-detail-row', document
                    .getElementById('save-hotel-details-section'));
            });
        })();
    </script>
@endpush
