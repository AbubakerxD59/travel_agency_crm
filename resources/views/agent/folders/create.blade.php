@extends($leadLayout ?? 'layouts.admin')

@php
    $isEditMode = (bool) ($isEditMode ?? false);
    $showPaymentStatusColumn = $isEditMode;
@endphp

@section('title', $isEditMode ? 'Edit folder' : 'New folder')
@section('content')
    <div class="mx-auto max-w-8xl">
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
            <form id="lead-create-form" method="POST" enctype="multipart/form-data"
                action="{{ $isEditMode ? route(($leadRoutePrefix ?? 'admin') . '.' . ($leadRouteResource ?? 'leads') . '.update', $lead) : route(($leadRoutePrefix ?? 'admin') . '.' . ($leadRouteResource ?? 'leads') . '.store') }}"
                data-folder-list-url="{{ route(($leadRoutePrefix ?? 'admin') . '.' . ($leadRouteResource ?? 'leads') . '.index') }}"
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
                    $transportDetailRows = old('transport_details', $draftTransportDetailRows ?? [[]]);
                    $visaDetailRows = old('visa_details', $draftVisaDetailRows ?? [[]]);
                    $otherDetailRows = old('other_details', $draftOtherDetailRows ?? []);
                    $paymentRows = old('payments', $draftPaymentRows ?? [[]]);
                    $paymentModes = folder_payment_modes();
                    $banksForForm = isset($banks) ? $banks : collect();
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
                    if (!is_array($transportDetailRows) || count($transportDetailRows) === 0) {
                        $transportDetailRows = [[]];
                    }
                    if (!is_array($visaDetailRows) || count($visaDetailRows) === 0) {
                        $visaDetailRows = [[]];
                    }
                    if (!is_array($otherDetailRows) || count($otherDetailRows) === 0) {
                        $otherDetailRows = [[]];
                    }
                    if (!is_array($paymentRows) || count($paymentRows) === 0) {
                        $paymentRows = [[]];
                    }
                    $passengerTitles = \App\Models\FolderPassenger::titles();
                    $passengerTypes = \App\Models\FolderPassenger::passengerTypes();
                @endphp

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <div class="min-w-0">
                        <label for="lead_order_type" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Order type</label>
                        <select id="lead_order_type" name="order_type" required class="{{ $fieldClass }}">
                            <option value="" disabled @selected(!old('order_type', $lead->order_type ?? null))>Select order type</option>
                            @foreach (\App\Models\Folder::orderTypes() as $orderType)
                                <option value="{{ $orderType }}" @selected(old('order_type', $lead->order_type ?? null) === $orderType)>
                                    {{ $orderType }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-0">
                        <label for="lead_vendor_reference" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Invoice Number</label>
                        <input id="lead_vendor_reference" name="vendor_reference" type="text" required
                            value="{{ old('vendor_reference', $lead->vendor_reference ?? '') }}" class="{{ $fieldClass }}">
                    </div>
                    <div class="min-w-0">
                        <label for="lead_customer_name" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Customer Name</label>
                        <input id="lead_customer_name" name="customer_name" type="text" required
                            value="{{ old('customer_name', $lead->customer_name ?? '') }}" class="{{ $fieldClass }}">
                    </div>

                    <div class="min-w-0">
                        @php
                            $selectedCompanyId = old(
                                'company_id',
                                $lead->company_id ?? (($leadRoutePrefix ?? 'admin') === 'agent' ? auth()->user()?->company_id : null),
                            );
                        @endphp
                        <label for="lead_company_id" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Company</label>
                        <select id="lead_company_id" name="company_id" required class="{{ $fieldClass }}">
                            <option value="" disabled @selected(! $selectedCompanyId)>Select company</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected($selectedCompanyId == $company->id)>{{ $company->name }}
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
                        <label for="lead_booking_date" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Booking date</label>
                        <input id="lead_booking_date" name="booking_date" type="date" required
                            value="{{ old('booking_date', optional($lead->booking_date ?? null)->format('Y-m-d') ?: now()->format('Y-m-d')) }}"
                            class="{{ $fieldClass }}">
                    </div>
                    <div class="min-w-0">
                        <label for="lead_balance_due_date" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Balance due
                            date</label>
                        <input id="lead_balance_due_date" name="balance_due_date" type="date" required
                            value="{{ old('balance_due_date', optional($lead->balance_due_date ?? null)->format('Y-m-d')) }}" class="{{ $fieldClass }}">
                    </div>
                    <div class="min-w-0">
                        @php
                            $selectedZiarat = old('ziarat_option');
                            if (!is_array($selectedZiarat)) {
                                $selectedZiarat = [];
                            }
                            if ($selectedZiarat === []) {
                                if ($lead->makkah_ziarat ?? false) {
                                    $selectedZiarat[] = 'makkah';
                                }
                                if ($lead->madinah_ziarat ?? false) {
                                    $selectedZiarat[] = 'madinah';
                                }
                            }
                        @endphp
                        <span class="block text-sm font-medium text-concierge-navy">Ziarat</span>
                        <div class="relative mt-1.5" id="ziarat-dropdown-wrapper">
                            <button type="button" id="ziarat-dropdown-toggle"
                                class="flex min-h-[2.625rem] w-full items-center justify-between rounded-xl border border-slate-200/80 bg-slate-50/50 px-4 py-2.5 text-sm text-concierge-navy transition hover:bg-slate-100">
                                <span id="ziarat-dropdown-label">
                                    @if (count($selectedZiarat) === 2)
                                        Makkah, Madinah
                                    @elseif(in_array('makkah', $selectedZiarat, true))
                                        Makkah
                                    @elseif(in_array('madinah', $selectedZiarat, true))
                                        Madinah
                                    @else
                                        Select ziarat
                                    @endif
                                </span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-concierge-muted" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25 12 15.75 4.5 8.25" />
                                </svg>
                            </button>
                            <div id="ziarat-dropdown-menu"
                                class="absolute z-20 mt-1 hidden w-full rounded-xl border border-slate-200 bg-white p-3 shadow-lg">
                                <label class="flex cursor-pointer items-center gap-2 text-sm text-concierge-navy">
                                    <input type="checkbox" data-ziarat-checkbox value="makkah" name="ziarat_option[]"
                                        class="rounded border-slate-300 text-concierge-accent focus:ring-concierge-accent/30"
                                        @checked(in_array('makkah', $selectedZiarat, true))>
                                    Makkah
                                </label>
                                <label class="mt-2 flex cursor-pointer items-center gap-2 text-sm text-concierge-navy">
                                    <input type="checkbox" data-ziarat-checkbox value="madinah" name="ziarat_option[]"
                                        class="rounded border-slate-300 text-concierge-accent focus:ring-concierge-accent/30"
                                        @checked(in_array('madinah', $selectedZiarat, true))>
                                    Madinah
                                </label>
                            </div>
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
                            <h2 class="text-base font-semibold text-concierge-navy">Itineraries</h2>
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
                                    <th class="border border-slate-200 px-2 py-2">Sr. No.</th>
                                    <th class="border border-slate-200 px-2 py-2">Airline code</th>
                                    <th class="border border-slate-200 px-2 py-2">Airline number</th>
                                    <th class="border border-slate-200 px-2 py-2">Class</th>
                                    <th class="border border-slate-200 px-2 py-2">Departure date</th>
                                    <th class="border border-slate-200 px-2 py-2">Departure airport</th>
                                    <th class="border border-slate-200 px-2 py-2">Arrival airport</th>
                                    <th class="border border-slate-200 px-2 py-2">Departure time</th>
                                    <th class="border border-slate-200 px-2 py-2">Arrival time</th>
                                    <th class="border border-slate-200 px-2 py-2">Arrival date</th>
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
                                            <input type="text" inputmode="numeric" autocomplete="off" data-folder-numeric="integer"
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
                    <p class="text-xs text-concierge-muted">Optional. Add rows or paste commands above and generate.</p>
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
                                    <th class="border border-slate-200 px-2 py-2">Date of birth</th>
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
                                            @php
                                                $selTitle = old("passengers.$i.title", data_get($row, 'title'));
                                                $titleValid = in_array($selTitle, $passengerTitles, true);
                                            @endphp
                                            <select name="passengers[{{ $i }}][title]" required
                                                class="min-w-[8rem] w-full max-w-[10rem] rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm focus:border-concierge-accent focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                                                <option value="" disabled @selected(!$titleValid)>Select title</option>
                                                @foreach ($passengerTitles as $passengerTitle)
                                                    <option value="{{ $passengerTitle }}"
                                                        @selected($titleValid && $selTitle === $passengerTitle)>{{ $passengerTitle }}</option>
                                                @endforeach
                                            </select>
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
                                            @php
                                                $selPassengerType = old("passengers.$i.passenger_type", data_get($row, 'passenger_type'));
                                                $passengerTypeValid = in_array($selPassengerType, $passengerTypes, true);
                                            @endphp
                                            <select name="passengers[{{ $i }}][passenger_type]" required
                                                class="min-w-[10rem] w-full max-w-[12rem] rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm focus:border-concierge-accent focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                                                <option value="" disabled @selected(!$passengerTypeValid)>Select passenger type</option>
                                                @foreach ($passengerTypes as $passengerType)
                                                    <option value="{{ $passengerType }}"
                                                        @selected($passengerTypeValid && $selPassengerType === $passengerType)>{{ $passengerType }}</option>
                                                @endforeach
                                            </select>
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
                            <h2 class="text-base font-semibold text-concierge-navy">Ticket/Package Cost</h2>
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
                                    <th class="border border-slate-200 px-2 py-2">Airline from</th>
                                    <th class="border border-slate-200 px-2 py-2">Airline to</th>
                                    <th class="border border-slate-200 px-2 py-2">Fare</th>
                                    <th class="border border-slate-200 px-2 py-2">Tax</th>
                                    <th class="border border-slate-200 px-2 py-2">Total cost</th>
                                    <th class="border border-slate-200 px-2 py-2">Margin</th>
                                    <th class="border border-slate-200 px-2 py-2">Sell</th>
                                    <th class="border border-slate-200 px-2 py-2">Supplier</th>
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
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal"
                                                name="package_costs[{{ $i }}][fare]"
                                                value="{{ data_get($row, 'fare') }}"
                                                class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal"
                                                name="package_costs[{{ $i }}][tax]"
                                                value="{{ data_get($row, 'tax') }}"
                                                class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal"
                                                name="package_costs[{{ $i }}][total_cost]"
                                                value="{{ data_get($row, 'total_cost') }}"
                                                class="w-24 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal" readonly tabindex="-1"
                                                name="package_costs[{{ $i }}][margin]"
                                                value="{{ data_get($row, 'margin') }}"
                                                class="w-20 cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50/80 px-2 py-1.5 text-sm text-slate-700">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal"
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

                    @php
                        $hotelDetailStatuses = folder_hotel_detail_statuses();
                        $hotelCities = folder_hotel_cities();
                        $hotelMealsOptions = folder_hotel_meals_options();
                    @endphp
                    <div class="overflow-x-auto">
                        <table class="min-w-[1780px] w-full border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-slate-100 text-left text-concierge-muted">
                                    <th class="border border-slate-200 px-2 py-2">Action</th>
                                    <th class="border border-slate-200 px-2 py-2">Sr. No.</th>
                                    <th class="border border-slate-200 px-2 py-2">Supplier</th>
                                    <th class="border border-slate-200 px-2 py-2">Hotel name</th>
                                    <th class="border border-slate-200 px-2 py-2">Guest name</th>
                                    <th class="border border-slate-200 px-2 py-2">No. of rooms</th>
                                    <th class="border border-slate-200 px-2 py-2">Type</th>
                                    <th class="border border-slate-200 px-2 py-2">Meals</th>
                                    <th class="border border-slate-200 px-2 py-2">Check-in</th>
                                    <th class="border border-slate-200 px-2 py-2">Check-out</th>
                                    <th class="border border-slate-200 px-2 py-2">Nights</th>
                                    <th class="border border-slate-200 px-2 py-2">Supplier ref</th>
                                    <th class="border border-slate-200 px-2 py-2">Status</th>
                                    <th class="border border-slate-200 px-2 py-2">Cost</th>
                                    <th class="border border-slate-200 px-2 py-2">Margin</th>
                                    <th class="border border-slate-200 px-2 py-2">Sell</th>
                                    <th class="border border-slate-200 px-2 py-2">Hotel city</th>
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
                                            <input type="text" inputmode="numeric" autocomplete="off" data-folder-numeric="integer"
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
                                            <input type="text" inputmode="numeric" autocomplete="off" data-folder-numeric="integer"
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
                                            @php
                                                $hotelMealsValue = (string) data_get($row, 'meals');
                                            @endphp
                                            <select name="hotel_details[{{ $i }}][meals]"
                                                class="w-36 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                                <option value="" @selected($hotelMealsValue === '')>
                                                    {{ __('Select meals') }}</option>
                                                @foreach ($hotelMealsOptions as $mealsOption)
                                                    <option value="{{ $mealsOption }}"
                                                        @selected($hotelMealsValue === $mealsOption)>{{ $mealsOption }}
                                                    </option>
                                                @endforeach
                                            </select>
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
                                            <input type="text" inputmode="numeric" autocomplete="off" data-folder-numeric="integer"
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
                                            <select name="hotel_details[{{ $i }}][status]"
                                                class="w-36 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                                @foreach ($hotelDetailStatuses as $statusValue => $statusLabel)
                                                    <option value="{{ $statusValue }}"
                                                        @selected((string) data_get($row, 'status', 'issue_later') === (string) $statusValue)>{{ $statusLabel }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal"
                                                name="hotel_details[{{ $i }}][cost]"
                                                value="{{ data_get($row, 'cost') }}"
                                                class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal" readonly tabindex="-1"
                                                name="hotel_details[{{ $i }}][margin]"
                                                value="{{ data_get($row, 'margin') }}"
                                                class="w-20 cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50/80 px-2 py-1.5 text-sm text-slate-700">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal"
                                                name="hotel_details[{{ $i }}][sell]"
                                                value="{{ data_get($row, 'sell') }}"
                                                class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            @php
                                                $hotelCityValue = (string) data_get($row, 'hotel_city');
                                            @endphp
                                            <select name="hotel_details[{{ $i }}][hotel_city]"
                                                class="w-36 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                                <option value="" @selected($hotelCityValue === '')>
                                                    {{ __('Select city') }}</option>
                                                @foreach ($hotelCities as $city)
                                                    <option value="{{ $city }}" @selected($hotelCityValue === $city)>
                                                        {{ $city }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <template id="hotel-detail-status-select-skeleton">
                        <select
                            class="w-36 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                            @foreach ($hotelDetailStatuses as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}">{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                    </template>
                    <template id="hotel-detail-city-select-skeleton">
                        <select
                            class="w-36 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                            <option value="" selected>{{ __('Select city') }}</option>
                            @foreach ($hotelCities as $city)
                                <option value="{{ $city }}">{{ $city }}</option>
                            @endforeach
                        </select>
                    </template>
                    <template id="hotel-detail-meals-select-skeleton">
                        <select
                            class="w-36 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                            <option value="" selected>{{ __('Select meals') }}</option>
                            @foreach ($hotelMealsOptions as $mealsOption)
                                <option value="{{ $mealsOption }}">{{ $mealsOption }}</option>
                            @endforeach
                        </select>
                    </template>
                    <p class="text-xs text-concierge-muted">Optional. Add hotel rows when needed.</p>
                </div>

                <div class="space-y-3 rounded-2xl border border-slate-200/80 bg-slate-50/40 p-4 sm:p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="inline-flex items-center gap-2">
                            <h2 class="text-base font-semibold text-concierge-navy">Transport Details</h2>
                            <button type="button" id="save-transport-details-section"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700"
                                title="Save Transport Details">
                                <svg width="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path
                                        d="M19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16L21 8V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M17 21V13H7V21" stroke="currentColor" stroke-width="2"
                                        stroke-linejoin="round" />
                                    <path d="M7 3V8H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="inline-flex items-center gap-2">
                            <button type="button" id="add-transport-detail-row"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2 text-xs font-semibold text-white hover:bg-concierge-navy-deep">
                                Add New Transport
                            </button>
                        </div>
                    </div>

                    @php
                        $transportVehicleTypes = folder_transport_vehicle_types();
                    @endphp
                    <div class="overflow-x-auto">
                        <table class="min-w-[1320px] w-full border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-slate-100 text-left text-concierge-muted">
                                    <th class="border border-slate-200 px-2 py-2">Action</th>
                                    <th class="border border-slate-200 px-2 py-2">Supplier</th>
                                    <th class="border border-slate-200 px-2 py-2">Description</th>
                                    <th class="border border-slate-200 px-2 py-2">From</th>
                                    <th class="border border-slate-200 px-2 py-2">To</th>
                                    <th class="border border-slate-200 px-2 py-2">Date</th>
                                    <th class="border border-slate-200 px-2 py-2">Pickup Time</th>
                                    <th class="border border-slate-200 px-2 py-2">Vehicle Type</th>
                                    <th class="border border-slate-200 px-2 py-2">Cost</th>
                                    <th class="border border-slate-200 px-2 py-2">Margin</th>
                                    <th class="border border-slate-200 px-2 py-2">Sell</th>
                                    <th class="border border-slate-200 px-2 py-2">SAR</th>
                                </tr>
                            </thead>
                            <tbody id="transport-detail-rows">
                                @foreach ($transportDetailRows as $i => $row)
                                    <tr class="transport-detail-row bg-white">
                                        <td class="border border-slate-200 px-2 py-2 align-top">
                                            <button type="button"
                                                class="remove-transport-detail-row inline-flex cursor-pointer items-center justify-center rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50">
                                                X
                                            </button>
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="transport_details[{{ $i }}][supplier]"
                                                value="{{ data_get($row, 'supplier') }}"
                                                class="w-28 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="transport_details[{{ $i }}][description]"
                                                value="{{ data_get($row, 'description') }}"
                                                class="w-40 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="transport_details[{{ $i }}][origin]"
                                                value="{{ data_get($row, 'origin') }}"
                                                class="w-32 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="transport_details[{{ $i }}][destination]"
                                                value="{{ data_get($row, 'destination') }}"
                                                class="w-32 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="date" name="transport_details[{{ $i }}][service_date]"
                                                value="{{ data_get($row, 'service_date') }}"
                                                class="w-36 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="time" name="transport_details[{{ $i }}][pickup_time]"
                                                value="{{ data_get($row, 'pickup_time') }}"
                                                class="w-28 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            @php
                                                $vehicleTypeValue = (string) data_get($row, 'vehicle_type');
                                            @endphp
                                            <select name="transport_details[{{ $i }}][vehicle_type]"
                                                class="w-28 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                                <option value="" @selected($vehicleTypeValue === '')>
                                                    {{ __('Select vehicle') }}</option>
                                                @foreach ($transportVehicleTypes as $vehicleType)
                                                    <option value="{{ $vehicleType }}"
                                                        @selected($vehicleTypeValue === $vehicleType)>{{ $vehicleType }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal"
                                                name="transport_details[{{ $i }}][cost]"
                                                value="{{ data_get($row, 'cost') }}"
                                                class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal" readonly tabindex="-1"
                                                name="transport_details[{{ $i }}][margin]"
                                                value="{{ data_get($row, 'margin') }}"
                                                class="w-20 cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50/80 px-2 py-1.5 text-sm text-slate-700">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal"
                                                name="transport_details[{{ $i }}][sell]"
                                                value="{{ data_get($row, 'sell') }}"
                                                class="transport-detail-sell-input w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal"
                                                name="transport_details[{{ $i }}][sar]"
                                                value="{{ data_get($row, 'sar') }}"
                                                class="w-20 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <template id="transport-detail-vehicle-type-select-skeleton">
                        <select
                            class="w-28 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                            <option value="" selected>{{ __('Select vehicle') }}</option>
                            @foreach ($transportVehicleTypes as $vehicleType)
                                <option value="{{ $vehicleType }}">{{ $vehicleType }}</option>
                            @endforeach
                        </select>
                    </template>
                    <p class="text-xs text-concierge-muted">Optional. Add transport rows when needed.</p>
                </div>

                <div class="space-y-3 rounded-2xl border border-slate-200/80 bg-slate-50/40 p-4 sm:p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="inline-flex items-center gap-2">
                            <h2 class="text-base font-semibold text-concierge-navy">Visa Details</h2>
                            <button type="button" id="save-visa-details-section"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700"
                                title="Save Visa Details">
                                <svg width="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path
                                        d="M19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16L21 8V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M17 21V13H7V21" stroke="currentColor" stroke-width="2"
                                        stroke-linejoin="round" />
                                    <path d="M7 3V8H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="inline-flex items-center gap-2">
                            <button type="button" id="add-visa-detail-row"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2 text-xs font-semibold text-white hover:bg-concierge-navy-deep">
                                Add New Visa
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-[720px] w-full border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-slate-100 text-left text-concierge-muted">
                                    <th class="border border-slate-200 px-2 py-2">Action</th>
                                    <th class="border border-slate-200 px-2 py-2">Supplier</th>
                                    <th class="border border-slate-200 px-2 py-2">Description</th>
                                    <th class="border border-slate-200 px-2 py-2">Cost</th>
                                    <th class="border border-slate-200 px-2 py-2">Margin</th>
                                    <th class="border border-slate-200 px-2 py-2">Sell</th>
                                </tr>
                            </thead>
                            <tbody id="visa-detail-rows">
                                @foreach ($visaDetailRows as $i => $row)
                                    <tr class="visa-detail-row bg-white">
                                        <td class="border border-slate-200 px-2 py-2 align-top">
                                            <button type="button"
                                                class="remove-visa-detail-row inline-flex cursor-pointer items-center justify-center rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50">
                                                X
                                            </button>
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="visa_details[{{ $i }}][supplier]"
                                                value="{{ data_get($row, 'supplier') }}"
                                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="visa_details[{{ $i }}][description]"
                                                value="{{ data_get($row, 'description') }}"
                                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal"
                                                name="visa_details[{{ $i }}][cost]"
                                                value="{{ data_get($row, 'cost') }}"
                                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal" readonly tabindex="-1"
                                                name="visa_details[{{ $i }}][margin]"
                                                value="{{ data_get($row, 'margin') }}"
                                                class="w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50/80 px-2 py-1.5 text-sm text-slate-700">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal"
                                                name="visa_details[{{ $i }}][sell]"
                                                value="{{ data_get($row, 'sell') }}"
                                                class="visa-detail-sell-input w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-concierge-muted">Optional. Margin is sell minus cost when rows are added.
                    </p>
                </div>

                <div class="space-y-3 rounded-2xl border border-slate-200/80 bg-slate-50/40 p-4 sm:p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="inline-flex items-center gap-2">
                            <h2 class="text-base font-semibold text-concierge-navy">Other Details</h2>
                            <button type="button" id="save-other-details-section"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700"
                                title="Save Other Details">
                                <svg width="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path
                                        d="M19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16L21 8V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M17 21V13H7V21" stroke="currentColor" stroke-width="2"
                                        stroke-linejoin="round" />
                                    <path d="M7 3V8H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="inline-flex items-center gap-2">
                            <button type="button" id="add-other-detail-row"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2 text-xs font-semibold text-white hover:bg-concierge-navy-deep">
                                Add New Details
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-[720px] w-full border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-slate-100 text-left text-concierge-muted">
                                    <th class="border border-slate-200 px-2 py-2">Action</th>
                                    <th class="border border-slate-200 px-2 py-2">Supplier</th>
                                    <th class="border border-slate-200 px-2 py-2">Description</th>
                                    <th class="border border-slate-200 px-2 py-2">Cost</th>
                                    <th class="border border-slate-200 px-2 py-2">Margin</th>
                                    <th class="border border-slate-200 px-2 py-2">Sell</th>
                                </tr>
                            </thead>
                            <tbody id="other-detail-rows">
                                @foreach ($otherDetailRows as $i => $row)
                                    <tr class="other-detail-row bg-white">
                                        <td class="border border-slate-200 px-2 py-2 align-top">
                                            <button type="button"
                                                class="remove-other-detail-row inline-flex cursor-pointer items-center justify-center rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50">
                                                X
                                            </button>
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="other_details[{{ $i }}][supplier]"
                                                value="{{ data_get($row, 'supplier') }}"
                                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="other_details[{{ $i }}][description]"
                                                value="{{ data_get($row, 'description') }}"
                                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal"
                                                name="other_details[{{ $i }}][cost]"
                                                value="{{ data_get($row, 'cost') }}"
                                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal" readonly tabindex="-1"
                                                name="other_details[{{ $i }}][margin]"
                                                value="{{ data_get($row, 'margin') }}"
                                                class="w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50/80 px-2 py-1.5 text-sm text-slate-700">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal"
                                                name="other_details[{{ $i }}][sell]"
                                                value="{{ data_get($row, 'sell') }}"
                                                class="other-detail-sell-input w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-concierge-muted">Other details are optional. Add rows as needed. Margin is
                        sell minus cost.</p>
                </div>

                <div class="space-y-3 rounded-2xl border border-slate-200/80 bg-slate-50/40 p-4 sm:p-5" id="folder-cost-summary">
                    <h2 class="text-base font-semibold text-concierge-navy">Cost summary</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-[920px] w-full border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-slate-100 text-left text-concierge-muted">
                                    <th class="border border-slate-200 px-3 py-2">Total sale</th>
                                    <th class="border border-slate-200 px-3 py-2">Flight cost</th>
                                    <th class="border border-slate-200 px-3 py-2">Hotel cost</th>
                                    <th class="border border-slate-200 px-3 py-2">Transport cost</th>
                                    <th class="border border-slate-200 px-3 py-2">Visa cost</th>
                                    <th class="border border-slate-200 px-3 py-2">Others cost</th>
                                    <th class="border border-slate-200 px-3 py-2">Margin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-white">
                                    <td class="folder-cost-text-emerald-600 border border-slate-200 px-3 py-2 text-sm font-semibold tabular-nums"
                                        data-folder-summary="total-sale">—</td>
                                    <td class="border border-slate-200 px-3 py-2 text-sm font-medium tabular-nums text-rose-600"
                                        data-folder-summary="flight-cost">—</td>
                                    <td class="border border-slate-200 px-3 py-2 text-sm font-medium tabular-nums text-rose-600"
                                        data-folder-summary="hotel-cost">—</td>
                                    <td class="border border-slate-200 px-3 py-2 text-sm font-medium tabular-nums text-rose-600"
                                        data-folder-summary="transport-cost">—</td>
                                    <td class="border border-slate-200 px-3 py-2 text-sm font-medium tabular-nums text-rose-600"
                                        data-folder-summary="visa-cost">—</td>
                                    <td class="border border-slate-200 px-3 py-2 text-sm font-medium tabular-nums text-rose-600"
                                        data-folder-summary="others-cost">—</td>
                                    <td class="border border-slate-200 px-3 py-2 text-sm font-semibold tabular-nums text-rose-600"
                                        data-folder-summary="summary-margin">—</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-3 rounded-2xl border border-slate-200/80 bg-slate-50/40 p-4 sm:p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="inline-flex items-center gap-2">
                            <h2 class="text-base font-semibold text-concierge-navy">Payments</h2>
                            <button type="button" id="save-payments-section"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700"
                                title="Save Payments">
                                <svg width="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path
                                        d="M19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16L21 8V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M17 21V13H7V21" stroke="currentColor" stroke-width="2"
                                        stroke-linejoin="round" />
                                    <path d="M7 3V8H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="inline-flex items-center gap-2">
                            <button type="button" id="add-payment-row"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2 text-xs font-semibold text-white hover:bg-concierge-navy-deep">
                                Add New Payment
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-[860px] w-full border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-slate-100 text-left text-concierge-muted">
                                    <th class="border border-slate-200 px-2 py-2">Action</th>
                                    <th class="border border-slate-200 px-2 py-2">Amount</th>
                                    <th class="border border-slate-200 px-2 py-2">Reference No</th>
                                    <th class="border border-slate-200 px-2 py-2">Date of Payment</th>
                                    <th class="border border-slate-200 px-2 py-2">Mode of Payment</th>
                                    <th class="border border-slate-200 px-2 py-2">Bank</th>
                                    <th class="border border-slate-200 px-2 py-2">Receipt</th>
                                    @if ($showPaymentStatusColumn)
                                        <th class="border border-slate-200 px-2 py-2">Status</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="payment-rows">
                                @foreach ($paymentRows as $i => $row)
                                    @php
                                        $paymentLocked = (bool) data_get($row, 'is_locked', false);
                                        $paymentStatus = (string) data_get($row, 'approval_status', '');
                                        if ($paymentStatus === '' && data_get($row, 'id')) {
                                            $paymentStatus = 'pending';
                                        }
                                    @endphp
                                    <tr class="payment-row bg-white" data-locked="{{ $paymentLocked ? '1' : '0' }}">
                                        <td class="border border-slate-200 px-2 py-2 align-top">
                                            @if ($paymentLocked)
                                                <input type="hidden" name="payments[{{ $i }}][id]"
                                                    value="{{ data_get($row, 'id') }}">
                                                @include('partials.folders.payment-locked-icon')
                                            @else
                                                <button type="button"
                                                    class="remove-payment-row inline-flex cursor-pointer items-center justify-center rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50">
                                                    X
                                                </button>
                                            @endif
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" inputmode="decimal" autocomplete="off" data-folder-numeric="decimal"
                                                name="payments[{{ $i }}][amount]"
                                                value="{{ data_get($row, 'amount') }}"
                                                @disabled($paymentLocked) @readonly($paymentLocked)
                                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm disabled:cursor-not-allowed disabled:bg-slate-50">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="text" name="payments[{{ $i }}][reference_no]"
                                                value="{{ data_get($row, 'reference_no') }}"
                                                maxlength="100"
                                                @disabled($paymentLocked) @readonly($paymentLocked)
                                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm disabled:cursor-not-allowed disabled:bg-slate-50">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <input type="date" name="payments[{{ $i }}][payment_date]"
                                                value="{{ data_get($row, 'payment_date') }}"
                                                @disabled($paymentLocked) @readonly($paymentLocked)
                                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm disabled:cursor-not-allowed disabled:bg-slate-50">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <select name="payments[{{ $i }}][mode_of_payment]"
                                                @disabled($paymentLocked)
                                                class="payment-mode-select w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm disabled:cursor-not-allowed disabled:bg-slate-50">
                                                <option value="" disabled @selected(!data_get($row, 'mode_of_payment'))>Select mode</option>
                                                @foreach ($paymentModes as $pm)
                                                    <option value="{{ $pm }}" @selected(data_get($row, 'mode_of_payment') == $pm)>{{ $pm }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2">
                                            <select name="payments[{{ $i }}][bank_id]"
                                                @disabled($paymentLocked)
                                                class="payment-bank-select w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm disabled:cursor-not-allowed disabled:bg-slate-50">
                                                <option value="">—</option>
                                                @foreach ($banksForForm as $bank)
                                                    <option value="{{ $bank->id }}" @selected((string) data_get($row, 'bank_id') === (string) $bank->id)>{{ $bank->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2 align-top">
                                            @include('partials.folders.payment-image-field', [
                                                'index' => $i,
                                                'row' => $row,
                                                'locked' => $paymentLocked,
                                            ])
                                        </td>
                                        @if ($showPaymentStatusColumn)
                                            <td class="border border-slate-200 px-2 py-2 align-top">
                                                @include('partials.folders.payment-status-badge', [
                                                    'status' => $paymentStatus,
                                                    'locked' => $paymentLocked,
                                                ])
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-concierge-muted">Optional. Attach a receipt image per payment (JPEG, PNG, GIF, or WebP, max 2 MB).
                        @if ($showPaymentStatusColumn)
                            {{ __('Approved or rejected payments are locked and cannot be changed.') }}
                        @endif
                    </p>
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
    @vite(['resources/js/folder-form-unsaved-guard.js', 'resources/js/folder-numeric-inputs.js'])
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        function setFolderNumericInputType(input, type, stepValue = null) {
            if (type === 'number') {
                input.type = 'text';
                input.dataset.folderNumeric = stepValue != null ? 'decimal' : 'integer';
                input.inputMode = stepValue != null ? 'decimal' : 'numeric';
                input.autocomplete = 'off';
                return;
            }

            input.type = type;
        }

        (() => {
            const ziaratWrapper = document.getElementById('ziarat-dropdown-wrapper');
            const ziaratToggleButton = document.getElementById('ziarat-dropdown-toggle');
            const ziaratMenu = document.getElementById('ziarat-dropdown-menu');
            const ziaratLabel = document.getElementById('ziarat-dropdown-label');
            const ziaratCheckboxes = [...document.querySelectorAll('input[data-ziarat-checkbox]')];

            function closeZiaratMenu() {
                ziaratMenu?.classList.add('hidden');
            }

            function updateZiaratLabel() {
                if (!ziaratLabel) {
                    return;
                }
                const selected = ziaratCheckboxes
                    .filter((checkbox) => checkbox instanceof HTMLInputElement && checkbox.checked)
                    .map((checkbox) => checkbox.value);

                if (selected.length === 2) {
                    ziaratLabel.textContent = 'Makkah, Madinah';
                } else if (selected.includes('makkah')) {
                    ziaratLabel.textContent = 'Makkah';
                } else if (selected.includes('madinah')) {
                    ziaratLabel.textContent = 'Madinah';
                } else {
                    ziaratLabel.textContent = 'Select ziarat';
                }
            }

            ziaratToggleButton?.addEventListener('click', () => {
                ziaratMenu?.classList.toggle('hidden');
            });

            ziaratCheckboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    if (!(checkbox instanceof HTMLInputElement)) {
                        return;
                    }
                    updateZiaratLabel();
                });
            });

            updateZiaratLabel();

            document.addEventListener('click', (event) => {
                if (!ziaratWrapper || !(event.target instanceof Node)) {
                    return;
                }
                if (!ziaratWrapper.contains(event.target)) {
                    closeZiaratMenu();
                }
            });
        })();

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
                setFolderNumericInputType(input, type);
                input.name = `itineraries[${index}][${field}]`;
                input.className = inputClass(sizeClass);
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
                if (!/^\d+$/.test(srNo)) {
                    return null;
                }

                let airlineCode = null;
                let airlineNumber = null;
                let cabinClass = null;

                if (/^[A-Z]{2}$/.test(parts[cursor] ?? '') && /^\d+[A-Z]?$/.test(parts[cursor + 1] ?? '')) {
                    airlineCode = parts[cursor++];
                    const flightToken = parts[cursor++];
                    const splitFlightMatch = flightToken.match(/^(\d+)([A-Z])?$/);
                    if (!splitFlightMatch) {
                        return null;
                    }
                    airlineNumber = splitFlightMatch[1];
                    cabinClass = splitFlightMatch[2] ?? null;
                } else {
                    const airlineToken = parts[cursor++];
                    const flightMatch = airlineToken.match(/^([A-Z]{2})(\d+)([A-Z])?$/);
                    if (!flightMatch) {
                        return null;
                    }
                    airlineCode = flightMatch[1];
                    airlineNumber = flightMatch[2];
                    cabinClass = flightMatch[3] ?? null;
                }

                const departureDateToken = parts[cursor++];
                if (/^[A-Z]$/.test(parts[cursor] ?? '') && !/^[A-Z]{6}$/.test(parts[cursor] ?? '')) {
                    cabinClass = parts[cursor];
                    cursor += 1;
                }

                const routeToken = parts[cursor++];
                const departureTimeToken = parts[cursor++];
                const arrivalTimeToken = parts[cursor++];
                const arrivalDateToken =
                    parts[cursor] && /^\d{2}[A-Z]{3}(\d{2})?$/.test(parts[cursor]) ? parts[cursor++] : null;

                const routeMatch = routeToken.match(/^([A-Z]{3})([A-Z]{3})$/);
                if (!routeMatch || !cabinClass) {
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
                    airline_code: airlineCode,
                    airline_number: airlineNumber,
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

            const passengerTitleOptions = @json(\App\Models\FolderPassenger::titles());
            const passengerTypeOptions = @json(\App\Models\FolderPassenger::passengerTypes());

            const nameFields = [
                ['first_name', 'text', 'w-28', null],
                ['middle_name', 'text', 'w-28', null],
                ['last_name', 'text', 'w-28', null],
            ];
            const afterPassengerTypeFields = [
                ['email', 'email', 'w-40', null],
                ['phone', 'text', 'w-32', null],
                ['date_of_birth', 'date', 'w-36', null],
                ['passport_details', 'text', 'w-40', null],
            ];

            function inputClass(sizeClass) {
                return `${sizeClass} rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm`;
            }

            function makeTitleSelect(index) {
                const sel = document.createElement('select');
                sel.name = `passengers[${index}][title]`;
                sel.required = true;
                sel.className =
                    `${inputClass('min-w-[8rem] w-full max-w-[10rem]')} focus:border-concierge-accent focus:outline-none focus:ring-2 focus:ring-concierge-accent/20`;
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.disabled = true;
                placeholder.selected = true;
                placeholder.textContent = 'Select title';
                sel.appendChild(placeholder);
                passengerTitleOptions.forEach((t) => {
                    const opt = document.createElement('option');
                    opt.value = t;
                    opt.textContent = t;
                    sel.appendChild(opt);
                });
                return sel;
            }

            function makePassengerTypeSelect(index) {
                const sel = document.createElement('select');
                sel.name = `passengers[${index}][passenger_type]`;
                sel.required = true;
                sel.className =
                    `${inputClass('min-w-[10rem] w-full max-w-[12rem]')} focus:border-concierge-accent focus:outline-none focus:ring-2 focus:ring-concierge-accent/20`;
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.disabled = true;
                placeholder.selected = true;
                placeholder.textContent = 'Select passenger type';
                sel.appendChild(placeholder);
                passengerTypeOptions.forEach((t) => {
                    const opt = document.createElement('option');
                    opt.value = t;
                    opt.textContent = t;
                    sel.appendChild(opt);
                });
                return sel;
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

            function appendFieldCells(row, index, fieldDefs) {
                fieldDefs.forEach(([field, type, sizeClass, minValue]) => {
                    const cell = document.createElement('td');
                    cell.className = 'border border-slate-200 px-2 py-2';
                    cell.appendChild(makeInput(index, field, type, sizeClass, minValue));
                    row.appendChild(cell);
                });
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

                const titleCell = document.createElement('td');
                titleCell.className = 'border border-slate-200 px-2 py-2';
                titleCell.appendChild(makeTitleSelect(index));
                row.appendChild(titleCell);

                appendFieldCells(row, index, nameFields);

                const passengerTypeCell = document.createElement('td');
                passengerTypeCell.className = 'border border-slate-200 px-2 py-2';
                passengerTypeCell.appendChild(makePassengerTypeSelect(index));
                row.appendChild(passengerTypeCell);

                appendFieldCells(row, index, afterPassengerTypeFields);

                return row;
            }

            function renumberRows() {
                [...tableBody.querySelectorAll('.passenger-row')].forEach((row, idx) => {
                    row.querySelectorAll('input[name^="passengers["], select[name^="passengers["]').forEach((field) => {
                        field.name = field.name.replace(/passengers\[\d+\]/, `passengers[${idx}]`);
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
                ['margin', 'number', 'w-20', null, '0.01'],
                ['sell', 'number', 'w-20', '0', '0.01'],
                ['supplier', 'text', 'w-24', null, null],
                ['pnr', 'text', 'w-24', null, null],
            ];

            function inputClass(sizeClass) {
                return `${sizeClass} rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm`;
            }

            function makeInput(index, field, type, sizeClass, minValue, stepValue) {
                const input = document.createElement('input');
                setFolderNumericInputType(input, type, stepValue);
                input.name = `package_costs[${index}][${field}]`;
                input.className = inputClass(sizeClass);
                if (field === 'margin') {
                    input.readOnly = true;
                    input.tabIndex = -1;
                    input.className =
                        `${inputClass(sizeClass)} cursor-not-allowed bg-slate-50/80 text-slate-700`;
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
                document.dispatchEvent(new CustomEvent('folder-margin-recalc'));
            }

            addButton.addEventListener('click', () => {
                const nextIndex = tableBody.querySelectorAll('.package-cost-row').length;
                tableBody.appendChild(createRow(nextIndex));
                document.dispatchEvent(new CustomEvent('folder-margin-recalc'));
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

            const fieldsBeforeStatus = [
                ['sr_no', 'number', 'w-12', '1', null],
                ['supplier', 'text', 'w-24', null, null],
                ['hotel_name', 'text', 'w-32', null, null],
                ['guest_name', 'text', 'w-32', null, null],
                ['rooms', 'number', 'w-16', '0', null],
                ['type', 'text', 'w-24', null, null],
                ['meals', 'select', 'w-36', null, null],
                ['date_in', 'date', 'w-36', null, null],
                ['date_out', 'date', 'w-36', null, null],
                ['nights', 'number', 'w-16', '0', null],
                ['supplier_ref', 'text', 'w-24', null, null],
            ];

            const fieldsAfterStatus = [
                ['cost', 'number', 'w-20', '0', '0.01'],
                ['margin', 'number', 'w-20', null, '0.01'],
                ['sell', 'number', 'w-20', '0', '0.01'],
            ];

            const statusSelectTemplate = document.getElementById('hotel-detail-status-select-skeleton');
            const citySelectTemplate = document.getElementById('hotel-detail-city-select-skeleton');
            const mealsSelectTemplate = document.getElementById('hotel-detail-meals-select-skeleton');

            function makeStatusSelect(index) {
                const select = statusSelectTemplate?.content?.querySelector('select')?.cloneNode(true);
                if (!select) {
                    const fallback = document.createElement('select');
                    fallback.name = `hotel_details[${index}][status]`;
                    fallback.className = inputClass('w-36');
                    fallback.innerHTML =
                        '<option value="issued">Issued</option><option value="reserved">Reserved</option><option value="issue_later" selected>Issue later</option>';
                    return fallback;
                }
                select.name = `hotel_details[${index}][status]`;
                select.value = 'issue_later';
                return select;
            }

            function makeHotelCitySelect(index) {
                const select = citySelectTemplate?.content?.querySelector('select')?.cloneNode(true);
                if (!select) {
                    const fallback = document.createElement('select');
                    fallback.name = `hotel_details[${index}][hotel_city]`;
                    fallback.className =
                        'w-36 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm';
                    fallback.innerHTML =
                        '<option value="" selected>Select city</option>';
                    return fallback;
                }
                select.name = `hotel_details[${index}][hotel_city]`;
                select.value = '';
                return select;
            }

            function makeMealsSelect(index) {
                const select = mealsSelectTemplate?.content?.querySelector('select')?.cloneNode(true);
                if (!select) {
                    const fallback = document.createElement('select');
                    fallback.name = `hotel_details[${index}][meals]`;
                    fallback.className =
                        'w-36 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm';
                    fallback.innerHTML =
                        '<option value="" selected>Select meals</option>' +
                        '<option value="Room Only">Room Only</option>' +
                        '<option value="Breakfast">Breakfast</option>' +
                        '<option value="Half-Board">Half-Board</option>' +
                        '<option value="Full-Board">Full-Board</option>' +
                        '<option value="Dinner">Dinner</option>' +
                        '<option value="Lunch">Lunch</option>';
                    return fallback;
                }
                select.name = `hotel_details[${index}][meals]`;
                select.value = '';
                return select;
            }

            function inputClass(sizeClass) {
                return `${sizeClass} rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm`;
            }

            function makeInput(index, field, type, sizeClass, minValue, stepValue) {
                const input = document.createElement('input');
                setFolderNumericInputType(input, type, stepValue);
                input.name = `hotel_details[${index}][${field}]`;
                input.className = inputClass(sizeClass);
                if (field === 'margin') {
                    input.readOnly = true;
                    input.tabIndex = -1;
                    input.className =
                        `${inputClass(sizeClass)} cursor-not-allowed bg-slate-50/80 text-slate-700`;
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

                fieldsBeforeStatus.forEach(([field, type, sizeClass, minValue, stepValue]) => {
                    const cell = document.createElement('td');
                    cell.className = 'border border-slate-200 px-2 py-2';
                    if (field === 'meals') {
                        cell.appendChild(makeMealsSelect(index));
                    } else {
                        cell.appendChild(makeInput(index, field, type, sizeClass, minValue, stepValue));
                    }
                    row.appendChild(cell);
                });

                const statusCell = document.createElement('td');
                statusCell.className = 'border border-slate-200 px-2 py-2';
                statusCell.appendChild(makeStatusSelect(index));
                row.appendChild(statusCell);

                fieldsAfterStatus.forEach(([field, type, sizeClass, minValue, stepValue]) => {
                    const cell = document.createElement('td');
                    cell.className = 'border border-slate-200 px-2 py-2';
                    cell.appendChild(makeInput(index, field, type, sizeClass, minValue, stepValue));
                    row.appendChild(cell);
                });

                const cityCell = document.createElement('td');
                cityCell.className = 'border border-slate-200 px-2 py-2';
                cityCell.appendChild(makeHotelCitySelect(index));
                row.appendChild(cityCell);

                return row;
            }

            function renumberRows() {
                [...tableBody.querySelectorAll('.hotel-detail-row')].forEach((row, idx) => {
                    row.querySelectorAll('input[name^="hotel_details["], select[name^="hotel_details["]').forEach((input) => {
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
                document.dispatchEvent(new CustomEvent('folder-margin-recalc'));
            }

            addButton.addEventListener('click', () => {
                const nextIndex = tableBody.querySelectorAll('.hotel-detail-row').length;
                tableBody.appendChild(createRow(nextIndex));
                document.dispatchEvent(new CustomEvent('folder-margin-recalc'));
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
            const tableBody = document.getElementById('transport-detail-rows');
            const addButton = document.getElementById('add-transport-detail-row');
            if (!tableBody || !addButton) {
                return;
            }

            const fields = [
                ['supplier', 'text', 'w-28', null, null],
                ['description', 'text', 'w-40', null, null],
                ['origin', 'text', 'w-32', null, null],
                ['destination', 'text', 'w-32', null, null],
                ['service_date', 'date', 'w-36', null, null],
                ['pickup_time', 'time', 'w-28', null, null],
                ['vehicle_type', 'select', 'w-28', null, null],
                ['cost', 'number', 'w-20', '0', '0.01'],
                ['margin', 'number', 'w-20', null, '0.01'],
                ['sell', 'number', 'w-20', '0', '0.01'],
                ['sar', 'number', 'w-20', '0', '0.01'],
            ];

            const vehicleTypeSelectTemplate = document.getElementById(
                'transport-detail-vehicle-type-select-skeleton');

            function makeVehicleTypeSelect(index) {
                const select = vehicleTypeSelectTemplate?.content?.querySelector('select')?.cloneNode(true);
                if (!select) {
                    const fallback = document.createElement('select');
                    fallback.name = `transport_details[${index}][vehicle_type]`;
                    fallback.className =
                        'w-28 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm';
                    fallback.innerHTML =
                        '<option value="" selected>Select vehicle</option>' +
                        '<option value="Car">Car</option>' +
                        '<option value="H1">H1</option>' +
                        '<option value="HiAce">HiAce</option>' +
                        '<option value="Bus">Bus</option>' +
                        '<option value="GMC">GMC</option>';
                    return fallback;
                }
                select.name = `transport_details[${index}][vehicle_type]`;
                select.value = '';
                return select;
            }

            function inputClass(sizeClass) {
                return `${sizeClass} rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm`;
            }

            function makeInput(index, field, type, sizeClass, minValue, stepValue) {
                const input = document.createElement('input');
                setFolderNumericInputType(input, type, stepValue);
                input.name = `transport_details[${index}][${field}]`;
                if (field === 'margin') {
                    input.readOnly = true;
                    input.tabIndex = -1;
                    input.className =
                        `${inputClass(sizeClass)} cursor-not-allowed bg-slate-50/80 text-slate-700`;
                } else if (field === 'sell') {
                    input.className = `${inputClass(sizeClass)} transport-detail-sell-input`;
                } else {
                    input.className = inputClass(sizeClass);
                }
                return input;
            }

            function createRow(index) {
                const row = document.createElement('tr');
                row.className = 'transport-detail-row bg-white';

                const actionCell = document.createElement('td');
                actionCell.className = 'border border-slate-200 px-2 py-2 align-top';
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className =
                    'remove-transport-detail-row inline-flex cursor-pointer items-center justify-center rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50';
                removeBtn.textContent = 'X';
                actionCell.appendChild(removeBtn);
                row.appendChild(actionCell);

                fields.forEach(([field, type, sizeClass, minValue, stepValue]) => {
                    const cell = document.createElement('td');
                    cell.className = 'border border-slate-200 px-2 py-2';
                    if (field === 'vehicle_type') {
                        cell.appendChild(makeVehicleTypeSelect(index));
                    } else {
                        cell.appendChild(makeInput(index, field, type, sizeClass, minValue, stepValue));
                    }
                    row.appendChild(cell);
                });

                return row;
            }

            function renumberRows() {
                [...tableBody.querySelectorAll('.transport-detail-row')].forEach((row, idx) => {
                    row.querySelectorAll(
                        'input[name^="transport_details["], select[name^="transport_details["]'
                    ).forEach((el) => {
                        el.name = el.name.replace(/transport_details\[\d+\]/,
                            `transport_details[${idx}]`);
                    });
                });
            }

            function ensureOneRow() {
                if (tableBody.querySelectorAll('.transport-detail-row').length === 0) {
                    tableBody.appendChild(createRow(0));
                }
                renumberRows();
                document.dispatchEvent(new CustomEvent('folder-margin-recalc'));
            }

            addButton.addEventListener('click', () => {
                const nextIndex = tableBody.querySelectorAll('.transport-detail-row').length;
                tableBody.appendChild(createRow(nextIndex));
                document.dispatchEvent(new CustomEvent('folder-margin-recalc'));
            });

            tableBody.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }
                const removeBtn = target.closest('.remove-transport-detail-row');
                if (!removeBtn) {
                    return;
                }
                removeBtn.closest('.transport-detail-row')?.remove();
                ensureOneRow();
            });

            ensureOneRow();
        })();

        (() => {
            const tableBody = document.getElementById('visa-detail-rows');
            const addButton = document.getElementById('add-visa-detail-row');
            if (!tableBody || !addButton) {
                return;
            }

            const fields = [
                ['supplier', 'text', 'w-full', null, null],
                ['description', 'text', 'w-full', null, null],
                ['cost', 'number', 'w-full', '0', '0.01'],
                ['margin', 'number', 'w-full', null, '0.01'],
                ['sell', 'number', 'w-full', '0', '0.01'],
            ];

            function inputClass(sizeClass) {
                return `${sizeClass} rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm`;
            }

            function makeInput(index, field, type, sizeClass, minValue, stepValue) {
                const input = document.createElement('input');
                setFolderNumericInputType(input, type, stepValue);
                input.name = `visa_details[${index}][${field}]`;
                if (field === 'margin') {
                    input.readOnly = true;
                    input.tabIndex = -1;
                    input.className =
                        `${inputClass(sizeClass)} cursor-not-allowed bg-slate-50/80 text-slate-700`;
                } else if (field === 'sell') {
                    input.className = `${inputClass(sizeClass)} visa-detail-sell-input`;
                } else {
                    input.className = inputClass(sizeClass);
                }
                return input;
            }

            function createRow(index) {
                const row = document.createElement('tr');
                row.className = 'visa-detail-row bg-white';

                const actionCell = document.createElement('td');
                actionCell.className = 'border border-slate-200 px-2 py-2 align-top';
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className =
                    'remove-visa-detail-row inline-flex cursor-pointer items-center justify-center rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50';
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
                [...tableBody.querySelectorAll('.visa-detail-row')].forEach((row, idx) => {
                    row.querySelectorAll('input[name^="visa_details["]').forEach((input) => {
                        input.name = input.name.replace(/visa_details\[\d+\]/,
                            `visa_details[${idx}]`);
                    });
                });
            }

            function ensureOneRow() {
                if (tableBody.querySelectorAll('.visa-detail-row').length === 0) {
                    tableBody.appendChild(createRow(0));
                }
                renumberRows();
                document.dispatchEvent(new CustomEvent('folder-margin-recalc'));
            }

            addButton.addEventListener('click', () => {
                const nextIndex = tableBody.querySelectorAll('.visa-detail-row').length;
                tableBody.appendChild(createRow(nextIndex));
                document.dispatchEvent(new CustomEvent('folder-margin-recalc'));
            });

            tableBody.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }
                const removeBtn = target.closest('.remove-visa-detail-row');
                if (!removeBtn) {
                    return;
                }
                removeBtn.closest('.visa-detail-row')?.remove();
                ensureOneRow();
            });

            ensureOneRow();
        })();

        (() => {
            const tableBody = document.getElementById('other-detail-rows');
            const addButton = document.getElementById('add-other-detail-row');
            if (!tableBody || !addButton) {
                return;
            }

            const fields = [
                ['supplier', 'text', 'w-full', null, null],
                ['description', 'text', 'w-full', null, null],
                ['cost', 'number', 'w-full', '0', '0.01'],
                ['margin', 'number', 'w-full', null, '0.01'],
                ['sell', 'number', 'w-full', '0', '0.01'],
            ];

            function inputClass(sizeClass) {
                return `${sizeClass} rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm`;
            }

            function makeInput(index, field, type, sizeClass, minValue, stepValue) {
                const input = document.createElement('input');
                setFolderNumericInputType(input, type, stepValue);
                input.name = `other_details[${index}][${field}]`;
                if (field === 'margin') {
                    input.readOnly = true;
                    input.tabIndex = -1;
                    input.className =
                        `${inputClass(sizeClass)} cursor-not-allowed bg-slate-50/80 text-slate-700`;
                } else if (field === 'sell') {
                    input.className = `${inputClass(sizeClass)} other-detail-sell-input`;
                } else {
                    input.className = inputClass(sizeClass);
                }
                return input;
            }

            function createRow(index) {
                const row = document.createElement('tr');
                row.className = 'other-detail-row bg-white';

                const actionCell = document.createElement('td');
                actionCell.className = 'border border-slate-200 px-2 py-2 align-top';
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className =
                    'remove-other-detail-row inline-flex cursor-pointer items-center justify-center rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50';
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
                [...tableBody.querySelectorAll('.other-detail-row')].forEach((row, idx) => {
                    row.querySelectorAll('input[name^="other_details["]').forEach((input) => {
                        input.name = input.name.replace(/other_details\[\d+\]/,
                            `other_details[${idx}]`);
                    });
                });
            }

            addButton.addEventListener('click', () => {
                const nextIndex = tableBody.querySelectorAll('.other-detail-row').length;
                tableBody.appendChild(createRow(nextIndex));
                document.dispatchEvent(new CustomEvent('folder-margin-recalc'));
            });

            tableBody.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }
                const removeBtn = target.closest('.remove-other-detail-row');
                if (!removeBtn) {
                    return;
                }
                removeBtn.closest('.other-detail-row')?.remove();
                renumberRows();
                document.dispatchEvent(new CustomEvent('folder-margin-recalc'));
            });

            document.dispatchEvent(new CustomEvent('folder-margin-recalc'));
        })();

        (() => {
            const tableBody = document.getElementById('payment-rows');
            const addButton = document.getElementById('add-payment-row');
            if (!tableBody || !addButton) {
                return;
            }

            const paymentModes = @json($paymentModes);
            const bankOptions = @json($banksForForm->map(fn ($b) => ['id' => $b->id, 'name' => $b->name])->values());
            const showPaymentStatusColumn = @json($showPaymentStatusColumn);

            function selectFieldClass() {
                return 'w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm';
            }

            function syncBankFieldForRow(row) {
                if (!row) {
                    return;
                }
                const modeEl = row.querySelector('.payment-mode-select');
                const bankEl = row.querySelector('.payment-bank-select');
                if (!modeEl || !bankEl) {
                    return;
                }
                const mode = String(modeEl.value || '');
                const isCash = mode === 'Cash in office';
                bankEl.disabled = isCash;
                if (isCash) {
                    bankEl.value = '';
                }
            }

            function makeModeSelect(index) {
                const sel = document.createElement('select');
                sel.name = `payments[${index}][mode_of_payment]`;
                sel.className = `payment-mode-select ${selectFieldClass()}`;
                const ph = document.createElement('option');
                ph.value = '';
                ph.disabled = true;
                ph.selected = true;
                ph.textContent = 'Select mode';
                sel.appendChild(ph);
                paymentModes.forEach((m) => {
                    const opt = document.createElement('option');
                    opt.value = m;
                    opt.textContent = m;
                    sel.appendChild(opt);
                });
                sel.addEventListener('change', () => syncBankFieldForRow(sel.closest('tr')));
                return sel;
            }

            function makeBankSelect(index) {
                const sel = document.createElement('select');
                sel.name = `payments[${index}][bank_id]`;
                sel.className = `payment-bank-select ${selectFieldClass()}`;
                const empty = document.createElement('option');
                empty.value = '';
                empty.textContent = '—';
                sel.appendChild(empty);
                bankOptions.forEach((b) => {
                    const opt = document.createElement('option');
                    opt.value = String(b.id);
                    opt.textContent = b.name;
                    sel.appendChild(opt);
                });
                return sel;
            }

            function createRow(index) {
                const row = document.createElement('tr');
                row.className = 'payment-row bg-white';

                const actionCell = document.createElement('td');
                actionCell.className = 'border border-slate-200 px-2 py-2 align-top';
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className =
                    'remove-payment-row inline-flex cursor-pointer items-center justify-center rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50';
                removeBtn.textContent = 'X';
                actionCell.appendChild(removeBtn);
                row.appendChild(actionCell);

                const amountCell = document.createElement('td');
                amountCell.className = 'border border-slate-200 px-2 py-2';
                const amountIn = document.createElement('input');
                setFolderNumericInputType(amountIn, 'number', '0.01');
                amountIn.name = `payments[${index}][amount]`;
                amountIn.className = selectFieldClass();
                amountCell.appendChild(amountIn);
                row.appendChild(amountCell);

                const refCell = document.createElement('td');
                refCell.className = 'border border-slate-200 px-2 py-2';
                const refIn = document.createElement('input');
                refIn.type = 'text';
                refIn.name = `payments[${index}][reference_no]`;
                refIn.maxLength = 100;
                refIn.className = selectFieldClass();
                refCell.appendChild(refIn);
                row.appendChild(refCell);

                const dateCell = document.createElement('td');
                dateCell.className = 'border border-slate-200 px-2 py-2';
                const dateIn = document.createElement('input');
                dateIn.type = 'date';
                dateIn.name = `payments[${index}][payment_date]`;
                dateIn.className = selectFieldClass();
                dateCell.appendChild(dateIn);
                row.appendChild(dateCell);

                const modeCell = document.createElement('td');
                modeCell.className = 'border border-slate-200 px-2 py-2';
                modeCell.appendChild(makeModeSelect(index));
                row.appendChild(modeCell);

                const bankCell = document.createElement('td');
                bankCell.className = 'border border-slate-200 px-2 py-2';
                bankCell.appendChild(makeBankSelect(index));
                row.appendChild(bankCell);

                const imageCell = document.createElement('td');
                imageCell.className = 'border border-slate-200 px-2 py-2 align-top';
                imageCell.innerHTML = `
                    <div class="payment-image-field min-w-[8.5rem] space-y-1.5">
                        <input type="hidden" name="payments[${index}][form_index]" value="${index}" data-payment-form-index>
                        <input type="file" name="payments[${index}][image]" accept="image/jpeg,image/png,image/gif,image/webp"
                            class="block w-full max-w-[10rem] cursor-pointer text-xs text-concierge-muted file:mr-2 file:cursor-pointer file:rounded-lg file:border-0 file:bg-concierge-navy file:px-2 file:py-1 file:text-xs file:font-medium file:text-white hover:file:bg-concierge-navy-deep">
                    </div>
                `;
                row.appendChild(imageCell);

                if (showPaymentStatusColumn) {
                    const statusCell = document.createElement('td');
                    statusCell.className = 'border border-slate-200 px-2 py-2 align-top';
                    statusCell.innerHTML =
                        '<div class="flex flex-col gap-1"><span class="text-sm font-medium text-amber-700">Pending</span></div>';
                    row.appendChild(statusCell);
                }

                syncBankFieldForRow(row);
                return row;
            }

            function renumberRows() {
                [...tableBody.querySelectorAll('.payment-row')].forEach((row, idx) => {
                    row.querySelectorAll(
                        'input[name^="payments["], select[name^="payments["], input[type="file"][name^="payments["]'
                    ).forEach((field) => {
                        field.name = field.name.replace(/payments\[\d+\]/, `payments[${idx}]`);
                    });
                    const formIndexEl = row.querySelector('[data-payment-form-index]');
                    if (formIndexEl instanceof HTMLInputElement) {
                        formIndexEl.value = String(idx);
                    }
                    syncBankFieldForRow(row);
                });
            }

            function ensureOneRow() {
                if (tableBody.querySelectorAll('.payment-row').length === 0) {
                    tableBody.appendChild(createRow(0));
                }
                renumberRows();
            }

            addButton.addEventListener('click', () => {
                const nextIndex = tableBody.querySelectorAll('.payment-row').length;
                tableBody.appendChild(createRow(nextIndex));
                renumberRows();
            });

            tableBody.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }
                const removeBtn = target.closest('.remove-payment-row');
                if (!removeBtn) {
                    return;
                }
                const row = removeBtn.closest('.payment-row');
                if (row?.dataset.locked === '1') {
                    return;
                }
                row?.remove();
                ensureOneRow();
            });

            tableBody.addEventListener('change', (event) => {
                const t = event.target;
                if (t instanceof HTMLSelectElement && t.classList.contains('payment-mode-select')) {
                    syncBankFieldForRow(t.closest('tr'));
                }
            });

            [...tableBody.querySelectorAll('.payment-row')].forEach(syncBankFieldForRow);
        })();

        (() => {
            const form = document.getElementById('lead-create-form') || document.getElementById('lead-edit-form');
            if (!form) {
                return;
            }
            const sectionSaveUrlTemplate = @json(route(($leadRoutePrefix ?? 'agent') . '.folders.sections.save', ['section' => '__SECTION__']));
            const csrfToken = form.querySelector('input[name="_token"]')?.value ?? '';

            (function initAutoMargin() {
                function parseMoneyInput(el) {
                    if (!el) {
                        return null;
                    }
                    const raw = String(el.value ?? '').trim().replace(/,/g, '');
                    if (raw === '') {
                        return null;
                    }
                    const n = parseFloat(raw);
                    return Number.isFinite(n) ? n : null;
                }

                function formatMarginValue(n) {
                    if (!Number.isFinite(n)) {
                        return '';
                    }
                    const rounded = Math.round(n * 100) / 100;
                    if (Object.is(rounded, -0)) {
                        return '0';
                    }
                    return String(rounded);
                }

                function syncPackageCostTotal(row) {
                    if (!row) {
                        return;
                    }
                    const fareIn = row.querySelector('input[name$="[fare]"]');
                    const taxIn = row.querySelector('input[name$="[tax]"]');
                    const totalCostIn = row.querySelector('input[name$="[total_cost]"]');
                    if (!totalCostIn) {
                        return;
                    }

                    const fare = parseMoneyInput(fareIn);
                    const tax = parseMoneyInput(taxIn);
                    if (fare === null && tax === null) {
                        totalCostIn.value = '';
                        return;
                    }

                    totalCostIn.value = formatMarginValue((fare ?? 0) + (tax ?? 0));
                }

                function syncPackageCostMargin(row) {
                    if (!row) {
                        return;
                    }
                    const costIn = row.querySelector('input[name$="[total_cost]"]');
                    const sellIn = row.querySelector('input[name$="[sell]"]');
                    const marginIn = row.querySelector('input[name$="[margin]"]');
                    if (!marginIn) {
                        return;
                    }
                    const c = parseMoneyInput(costIn);
                    const s = parseMoneyInput(sellIn);
                    if (c === null && s === null) {
                        marginIn.value = '';
                        return;
                    }
                    marginIn.value = formatMarginValue((s ?? 0) - (c ?? 0));
                }

                function syncHotelDetailMargin(row) {
                    if (!row) {
                        return;
                    }
                    const costIn = row.querySelector('input[name$="[cost]"]');
                    const sellIn = row.querySelector('input[name$="[sell]"]');
                    const marginIn = row.querySelector('input[name$="[margin]"]');
                    if (!marginIn) {
                        return;
                    }
                    const c = parseMoneyInput(costIn);
                    const s = parseMoneyInput(sellIn);
                    if (c === null && s === null) {
                        marginIn.value = '';
                        return;
                    }
                    marginIn.value = formatMarginValue((s ?? 0) - (c ?? 0));
                }

                function syncTransportDetailMargin(row) {
                    if (!row) {
                        return;
                    }
                    const costIn = row.querySelector('input[name$="[cost]"]');
                    const sellIn = row.querySelector('input[name$="[sell]"]');
                    const marginIn = row.querySelector('input[name$="[margin]"]');
                    if (!marginIn) {
                        return;
                    }
                    const c = parseMoneyInput(costIn);
                    const s = parseMoneyInput(sellIn);
                    if (c === null && s === null) {
                        marginIn.value = '';
                        return;
                    }
                    marginIn.value = formatMarginValue((s ?? 0) - (c ?? 0));
                }

                function syncVisaDetailMargin(row) {
                    if (!row) {
                        return;
                    }
                    const costIn = row.querySelector('input[name$="[cost]"]');
                    const sellIn = row.querySelector('input[name$="[sell]"]');
                    const marginIn = row.querySelector('input[name$="[margin]"]');
                    if (!marginIn) {
                        return;
                    }
                    const c = parseMoneyInput(costIn);
                    const s = parseMoneyInput(sellIn);
                    if (c === null && s === null) {
                        marginIn.value = '';
                        return;
                    }
                    marginIn.value = formatMarginValue((s ?? 0) - (c ?? 0));
                }

                function syncOtherDetailMargin(row) {
                    if (!row) {
                        return;
                    }
                    const costIn = row.querySelector('input[name$="[cost]"]');
                    const sellIn = row.querySelector('input[name$="[sell]"]');
                    const marginIn = row.querySelector('input[name$="[margin]"]');
                    if (!marginIn) {
                        return;
                    }
                    const c = parseMoneyInput(costIn);
                    const s = parseMoneyInput(sellIn);
                    if (c === null && s === null) {
                        marginIn.value = '';
                        return;
                    }
                    marginIn.value = formatMarginValue((s ?? 0) - (c ?? 0));
                }

                function syncAllAutoMargins() {
                    form.querySelectorAll('.package-cost-row').forEach((row) => {
                        syncPackageCostTotal(row);
                        syncPackageCostMargin(row);
                    });
                    form.querySelectorAll('.hotel-detail-row').forEach(syncHotelDetailMargin);
                    form.querySelectorAll('.transport-detail-row').forEach(syncTransportDetailMargin);
                    form.querySelectorAll('.visa-detail-row').forEach(syncVisaDetailMargin);
                    form.querySelectorAll('.other-detail-row').forEach(syncOtherDetailMargin);
                    syncFolderCostSummary();
                }

                function sumMoneyInputs(selector) {
                    let total = 0;
                    form.querySelectorAll(selector).forEach((el) => {
                        const v = parseMoneyInput(el);
                        if (v !== null) {
                            total += v;
                        }
                    });
                    return total;
                }

                function formatSummaryCell(n) {
                    if (!Number.isFinite(n)) {
                        return '—';
                    }
                    const rounded = Math.round(n * 100) / 100;
                    return rounded.toLocaleString('en-US', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2,
                    });
                }

                function applyFolderSummaryCellStyle(cell, key, numericValue) {
                    if (!cell) {
                        return;
                    }
                    const base = 'border border-slate-200 px-3 py-2 text-sm tabular-nums';
                    if (key === 'total-sale') {
                        cell.className = `${base} font-semibold folder-cost-text-emerald-600`;
                        return;
                    }
                    if (key === 'summary-margin') {
                        const positive = Number.isFinite(numericValue) && numericValue > 0;
                        cell.className = `${base} font-semibold ${positive ? 'folder-cost-text-emerald-600' : 'text-rose-600'}`;
                        return;
                    }
                    cell.className = `${base} font-medium text-rose-600`;
                }

                function syncFolderCostSummary() {
                    const totalSale = sumMoneyInputs('input[name^="package_costs["][name$="[sell]"]');
                    const flightCost = sumMoneyInputs('input[name^="package_costs["][name$="[total_cost]"]');
                    const hotelCost = sumMoneyInputs('input[name^="hotel_details["][name$="[cost]"]');
                    const transportCost = sumMoneyInputs('input[name^="transport_details["][name$="[cost]"]');
                    const visaCost = sumMoneyInputs('input[name^="visa_details["][name$="[cost]"]');
                    const othersCost = sumMoneyInputs('input[name^="other_details["][name$="[cost]"]');
                    const summaryMargin = totalSale - flightCost - hotelCost - transportCost - visaCost - othersCost;

                    const cells = {
                        'total-sale': totalSale,
                        'flight-cost': flightCost,
                        'hotel-cost': hotelCost,
                        'transport-cost': transportCost,
                        'visa-cost': visaCost,
                        'others-cost': othersCost,
                        'summary-margin': summaryMargin,
                    };
                    Object.entries(cells).forEach(([key, value]) => {
                        const cell = form.querySelector(`[data-folder-summary="${key}"]`);
                        if (cell) {
                            cell.textContent = formatSummaryCell(value);
                            applyFolderSummaryCellStyle(cell, key, value);
                        }
                    });
                }

                function onCostOrSellInput(ev) {
                    const t = ev.target;
                    if (!(t instanceof HTMLInputElement)) {
                        return;
                    }
                    const n = t.name;
                    if (n.startsWith('package_costs[') && (n.endsWith('[fare]') || n.endsWith('[tax]') || n
                            .endsWith('[total_cost]') || n.endsWith('[sell]'))) {
                        const row = t.closest('.package-cost-row');
                        syncPackageCostTotal(row);
                        syncPackageCostMargin(row);
                    } else if (n.startsWith('hotel_details[') && (n.endsWith('[cost]') || n.endsWith('[sell]'))) {
                        syncHotelDetailMargin(t.closest('.hotel-detail-row'));
                    } else if (n.startsWith('transport_details[') && (n.endsWith('[cost]') || n.endsWith('[sell]'))) {
                        syncTransportDetailMargin(t.closest('.transport-detail-row'));
                    } else if (n.startsWith('visa_details[') && (n.endsWith('[cost]') || n.endsWith('[sell]'))) {
                        syncVisaDetailMargin(t.closest('.visa-detail-row'));
                    } else if (n.startsWith('other_details[') && (n.endsWith('[cost]') || n.endsWith('[sell]'))) {
                        syncOtherDetailMargin(t.closest('.other-detail-row'));
                    }
                    syncFolderCostSummary();
                }

                form.addEventListener('input', onCostOrSellInput);
                form.addEventListener('change', onCostOrSellInput);
                document.addEventListener('folder-margin-recalc', syncAllAutoMargins);
                syncAllAutoMargins();
            })();

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

            const passengerRequiredFields = [
                'title',
                'first_name',
                'last_name',
                'passenger_type',
                'email',
                'phone',
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

            function isLockedPaymentRow(row) {
                return row instanceof HTMLElement && row.dataset.locked === '1';
            }

            function validateRequiredRowFields(rowSelector, requiredFields, inputPrefix) {
                clearRowErrors(rowSelector);
                const rows = [...document.querySelectorAll(rowSelector)];
                let firstInvalidField = null;

                rows.forEach((row) => {
                    if (isLockedPaymentRow(row)) {
                        return;
                    }

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

            function rowHasAnyValue(row) {
                return [...row.querySelectorAll('input, select, textarea')].some((field) => {
                    const value = field.value;
                    return typeof value === 'string' && value.trim() !== '';
                });
            }

            function validateOptionalSectionRows(rowSelector, requiredFields, inputPrefix) {
                clearRowErrors(rowSelector);
                const rows = [...document.querySelectorAll(rowSelector)];
                let firstInvalidField = null;

                rows.forEach((row) => {
                    if (isLockedPaymentRow(row)) {
                        return;
                    }

                    if (!rowHasAnyValue(row)) {
                        return;
                    }

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
                return [...document.querySelectorAll(rowSelector)]
                    .filter((row) => !(sectionName === 'payments' && isLockedPaymentRow(row)))
                    .map((row) => {
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
                            @if ($isEditMode)
                                folder_id: {{ (int) $lead->id }},
                            @endif
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

            function validateHeaderDateField(inputId, label) {
                const input = document.getElementById(inputId);
                if (!(input instanceof HTMLInputElement)) {
                    return true;
                }

                if (input.value.trim() !== '') {
                    return true;
                }

                const altInput = input._flatpickr?.altInput;
                if (altInput instanceof HTMLInputElement) {
                    altInput.focus();
                    altInput.reportValidity?.();
                } else {
                    input.focus();
                    input.reportValidity?.();
                }

                showError(`${label} is required.`);
                return false;
            }

            form.addEventListener('submit', (event) => {
                if (!validateHeaderDateField('lead_travel_date', 'Travel date')) {
                    event.preventDefault();
                    return;
                }

                if (!validateHeaderDateField('lead_balance_due_date', 'Balance due date')) {
                    event.preventDefault();
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
            });

            function clearSectionFieldError(event) {
                const target = event.target;
                if (!(target instanceof HTMLInputElement) && !(target instanceof HTMLSelectElement) &&
                    !(target instanceof HTMLTextAreaElement)) {
                    return;
                }
                if (!target.name.startsWith('itineraries[') &&
                    !target.name.startsWith('passengers[') &&
                    !target.name.startsWith('package_costs[') &&
                    !target.name.startsWith('hotel_details[') &&
                    !target.name.startsWith('transport_details[') &&
                    !target.name.startsWith('visa_details[') &&
                    !target.name.startsWith('other_details[') &&
                    !target.name.startsWith('payments[')) {
                    return;
                }
                removeFieldError(target);
            }

            form.addEventListener('input', clearSectionFieldError);
            form.addEventListener('change', clearSectionFieldError);

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

            document.getElementById('save-transport-details-section')?.addEventListener('click', () => {
                saveSectionDraft('transport_details', '#transport-detail-rows .transport-detail-row', document
                    .getElementById('save-transport-details-section'));
            });

            document.getElementById('save-visa-details-section')?.addEventListener('click', () => {
                saveSectionDraft('visa_details', '#visa-detail-rows .visa-detail-row', document
                    .getElementById('save-visa-details-section'));
            });

            document.getElementById('save-other-details-section')?.addEventListener('click', () => {
                saveSectionDraft('other_details', '#other-detail-rows .other-detail-row', document
                    .getElementById('save-other-details-section'));
            });

            document.getElementById('save-payments-section')?.addEventListener('click', () => {
                saveSectionDraft('payments', '#payment-rows .payment-row', document.getElementById(
                    'save-payments-section'));
            });
        })();
    </script>
@endpush
