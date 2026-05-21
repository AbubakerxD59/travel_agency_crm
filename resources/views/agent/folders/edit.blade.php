@extends($leadLayout ?? 'layouts.admin')

@section('title', 'Edit folder')

@section('content')
    <div class="mx-auto max-w-8xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Edit folder #{{ $lead->id }}</h1>
                <p class="mt-1 text-sm text-concierge-muted">Update folder details.</p>
            </div>
            <a href="{{ route(($leadRoutePrefix ?? 'admin') . '.'.($leadRouteResource ?? 'leads').'.index') }}"
                class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-concierge-navy shadow-sm transition hover:bg-slate-50">
                Back to folders
            </a>
        </div>

        <div class="mt-8 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8">
            <form id="lead-edit-form" method="POST"
                action="{{ route(($leadRoutePrefix ?? 'admin') . '.'.($leadRouteResource ?? 'leads').'.update', $lead) }}" class="space-y-4">
                @csrf
                @method('PATCH')

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
                    $itineraryRows = old(
                        'itineraries',
                        $lead->itineraries
                            ->map(
                                fn($itinerary) => [
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
                                ],
                            )
                            ->toArray(),
                    );
                    $passengerRows = old(
                        'passengers',
                        $lead->passengers
                            ->map(
                                fn($passenger) => [
                                    'title' => $passenger->title,
                                    'first_name' => $passenger->first_name,
                                    'middle_name' => $passenger->middle_name,
                                    'last_name' => $passenger->last_name,
                                    'passenger_type' => $passenger->passenger_type,
                                    'email' => $passenger->email,
                                    'phone' => $passenger->phone,
                                    'date_of_birth' => optional($passenger->date_of_birth)->format('Y-m-d'),
                                    'passport_details' => $passenger->passport_details,
                                ],
                            )
                            ->toArray(),
                    );
                    $packageCostRows = old(
                        'package_costs',
                        $lead->packageCosts
                            ->map(
                                fn($cost) => [
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
                                ],
                            )
                            ->toArray(),
                    );
                    if (!is_array($itineraryRows) || count($itineraryRows) === 0) {
                        $itineraryRows = [[]];
                    }
                    if (!is_array($passengerRows) || count($passengerRows) === 0) {
                        $passengerRows = [[]];
                    }
                    if (!is_array($packageCostRows) || count($packageCostRows) === 0) {
                        $packageCostRows = [[]];
                    }
                    $passengerTitles = \App\Models\FolderPassenger::titles();
                    $passengerTypes = \App\Models\FolderPassenger::passengerTypes();
                @endphp

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div class="min-w-0">
                        <label for="lead_order_type" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Order type</label>
                        <select id="lead_order_type" name="order_type" required class="{{ $fieldClass }}">
                            <option value="" disabled @selected(!old('order_type', $lead->order_type))>Select order type</option>
                            @foreach (\App\Models\Folder::orderTypes() as $orderType)
                                <option value="{{ $orderType }}" @selected(old('order_type', $lead->order_type) === $orderType)>
                                    {{ $orderType }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-0">
                        <label for="lead_vendor_reference" class="block text-sm font-medium text-concierge-navy">Vendor
                            reference <span class="font-normal text-concierge-muted">(optional)</span></label>
                        <input id="lead_vendor_reference" name="vendor_reference" type="text"
                            value="{{ old('vendor_reference', $lead->vendor_reference) }}" class="{{ $fieldClass }}">
                    </div>
                    <div class="min-w-0">
                        <label for="lead_company_id" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Company</label>
                        <select id="lead_company_id" name="company_id" required class="{{ $fieldClass }}">
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected(old('company_id', $lead->company_id) == $company->id)>{{ $company->name }}
                                    ({{ $company->country?->name ?? '—' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="min-w-0">
                        <label for="lead_destination_id" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Destination</label>
                        <select id="lead_destination_id" name="destination_id" required class="{{ $fieldClass }}">
                            @foreach ($destinations as $destination)
                                <option value="{{ $destination->id }}" @selected(old('destination_id', $lead->destination_id) == $destination->id)>
                                    {{ $destination->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-0">
                        <label for="lead_travel_date" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Travel date</label>
                        <input id="lead_travel_date" name="travel_date" type="date" required
                            value="{{ old('travel_date', optional($lead->travel_date)->format('Y-m-d')) }}"
                            class="{{ $fieldClass }}">
                    </div>
                    <div class="min-w-0">
                        <label for="lead_booking_date" class="block text-sm font-medium text-concierge-navy"><span
                                class="text-rose-600">*</span> Booking date</label>
                        <input id="lead_booking_date" name="booking_date" type="date" required
                            value="{{ old('booking_date', optional($lead->booking_date)->format('Y-m-d') ?: now()->format('Y-m-d')) }}"
                            class="{{ $fieldClass }}">
                    </div>
                    <div class="min-w-0">
                        <label for="lead_balance_due_date" class="block text-sm font-medium text-concierge-navy">Balance due
                            date <span class="font-normal text-concierge-muted">(optional)</span></label>
                        <input id="lead_balance_due_date" name="balance_due_date" type="date"
                            value="{{ old('balance_due_date', optional($lead->balance_due_date)->format('Y-m-d')) }}"
                            class="{{ $fieldClass }}">
                    </div>
                    <div class="min-w-0">
                        <span class="block text-sm font-medium text-concierge-navy">Ziarat</span>
                        <div
                            class="mt-1.5 flex min-h-[2.625rem] flex-row flex-nowrap items-center gap-6 rounded-xl border border-slate-200/80 bg-slate-50/50 px-4 py-2.5">
                            <label class="flex cursor-pointer items-center gap-2 text-sm text-concierge-navy">
                                <input type="checkbox" name="ziarat_makkah" value="1"
                                    class="rounded border-slate-300 text-concierge-accent focus:ring-concierge-accent/30"
                                    @checked(old('ziarat_makkah', $lead->ziarat_makkah))>
                                Makkah
                            </label>
                            <label class="flex cursor-pointer items-center gap-2 text-sm text-concierge-navy">
                                <input type="checkbox" name="ziarat_madinah" value="1"
                                    class="rounded border-slate-300 text-concierge-accent focus:ring-concierge-accent/30"
                                    @checked(old('ziarat_madinah', $lead->ziarat_madinah))>
                                Madinah
                            </label>
                        </div>
                    </div>
                </div>

                <div class="min-w-0">
                    <label for="lead_flight_itinerary" class="block text-sm font-medium text-concierge-navy">Flight
                        itinerary <span class="font-normal text-concierge-muted">(optional)</span></label>
                    <textarea id="lead_flight_itinerary" name="flight_itinerary" rows="4" class="{{ $fieldClass }}">{{ old('flight_itinerary', $lead->flight_itinerary) }}</textarea>
                </div>

                <div class="space-y-3 rounded-2xl border border-slate-200/80 bg-slate-50/40 p-4 sm:p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-base font-semibold text-concierge-navy"><span class="text-rose-600">*</span>
                            Itineraries</h2>
                        <button type="button" id="add-itinerary-row"
                            class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2 text-xs font-semibold text-white hover:bg-concierge-navy-deep">
                            Add itinerary
                        </button>
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
                        <h2 class="text-base font-semibold text-concierge-navy"><span class="text-rose-600">*</span>
                            Passenger details</h2>
                        <button type="button" id="add-passenger-row"
                            class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2 text-xs font-semibold text-white hover:bg-concierge-navy-deep">
                            Add new passenger
                        </button>
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
                        <h2 class="text-base font-semibold text-concierge-navy"><span class="text-rose-600">*</span>
                            Hotel/Package cost</h2>
                        <button type="button" id="add-package-cost-row"
                            class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2 text-xs font-semibold text-white hover:bg-concierge-navy-deep">
                            Add new package cost
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-[1400px] w-full border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-slate-100 text-left text-concierge-muted">
                                    <th class="border border-slate-200 px-2 py-2">Action</th>
                                    <th class="border border-slate-200 px-2 py-2">Ticket no</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        Ticket date</th>
                                    <th class="border border-slate-200 px-2 py-2">Airline from</th>
                                    <th class="border border-slate-200 px-2 py-2">Airline to</th>
                                    <th class="border border-slate-200 px-2 py-2">Fare</th>
                                    <th class="border border-slate-200 px-2 py-2">Tax</th>
                                    <th class="border border-slate-200 px-2 py-2">Total cost</th>
                                    <th class="border border-slate-200 px-2 py-2">Margin <span
                                            class="block font-normal normal-case text-slate-500">(total cost −
                                            sell)</span></th>
                                    <th class="border border-slate-200 px-2 py-2">Sell</th>
                                    <th class="border border-slate-200 px-2 py-2">Supplier</th>
                                    <th class="border border-slate-200 px-2 py-2"><span class="text-rose-600">*</span>
                                        PNR</th>
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
                                                value="{{ data_get($row, 'ticket_date') }}" required
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
                                            <input type="number" step="0.01" readonly tabindex="-1"
                                                title="Margin = total cost − sell (calculated automatically)"
                                                name="package_costs[{{ $i }}][margin]"
                                                value="{{ data_get($row, 'margin') }}"
                                                class="w-20 cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50/80 px-2 py-1.5 text-sm text-slate-700">
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
                    <p class="text-xs text-concierge-muted">At least one hotel/package cost row is required.</p>
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

                <div class="flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route(($leadRoutePrefix ?? 'admin') . '.'.($leadRouteResource ?? 'leads').'.index') }}"
                        class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-200 py-2.5 text-center text-sm font-medium text-concierge-navy hover:bg-slate-50 sm:px-6">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy py-2.5 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 hover:bg-concierge-navy-deep sm:px-6">
                        Save folder
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

            addButton.addEventListener('click', () => {
                const nextIndex = tableBody.querySelectorAll('.itinerary-row').length;
                tableBody.appendChild(createRow(nextIndex));
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
                input.type = type;
                input.name = `package_costs[${index}][${field}]`;
                input.className = inputClass(sizeClass);
                if (field === 'margin') {
                    input.readOnly = true;
                    input.tabIndex = -1;
                    input.title = 'Margin = total cost − sell (calculated automatically)';
                    input.className =
                        `${inputClass(sizeClass)} cursor-not-allowed bg-slate-50/80 text-slate-700`;
                }
                if (minValue != null && field !== 'margin') {
                    input.min = minValue;
                }
                if (stepValue != null) {
                    input.step = stepValue;
                }
                if (field === 'ticket_date') {
                    input.required = true;
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
            const form = document.getElementById('lead-edit-form');
            if (!form) {
                return;
            }

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

                function syncAllAutoMargins() {
                    form.querySelectorAll('.package-cost-row').forEach(syncPackageCostMargin);
                    form.querySelectorAll('.hotel-detail-row').forEach(syncHotelDetailMargin);
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
                    if (n.startsWith('package_costs[') && (n.endsWith('[total_cost]') || n.endsWith(
                            '[sell]'))) {
                        syncPackageCostMargin(t.closest('.package-cost-row'));
                    } else if (n.startsWith('hotel_details[') && (n.endsWith('[cost]') || n.endsWith('[sell]'))) {
                        syncHotelDetailMargin(t.closest('.hotel-detail-row'));
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

            function sectionHasAtLeastOneFilledRow(selector) {
                const rows = [...document.querySelectorAll(selector)];
                return rows.some((row) => [...row.querySelectorAll('input, select, textarea')].some((field) => {
                    const value = field.value;
                    return typeof value === 'string' && value.trim() !== '';
                }));
            }

            form.addEventListener('submit', (event) => {
                if (!sectionHasAtLeastOneFilledRow('#itinerary-rows .itinerary-row')) {
                    event.preventDefault();
                    showError('Please fill at least one itinerary row.');
                    return;
                }

                if (!sectionHasAtLeastOneFilledRow('#passenger-rows .passenger-row')) {
                    event.preventDefault();
                    showError('Please fill at least one passenger row.');
                    return;
                }

                if (!sectionHasAtLeastOneFilledRow('#package-cost-rows .package-cost-row')) {
                    event.preventDefault();
                    showError('Please fill at least one hotel/package cost row.');
                    return;
                }
            });
        })();
    </script>
@endpush
