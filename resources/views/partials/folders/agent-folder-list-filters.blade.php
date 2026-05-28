@php
    $showTravelDateFilter = $showTravelDateFilter ?? true;
@endphp
<form method="GET" action="{{ $formAction }}" class="mt-6">
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 {{ $showTravelDateFilter ? 'xl:grid-cols-4' : 'xl:grid-cols-3' }}">
        <div>
            <label for="folder-destination-filter"
                class="block text-sm font-medium text-concierge-navy">Destination</label>
            <select id="folder-destination-filter" name="destination_id"
                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                <option value="">All destinations</option>
                @foreach ($destinations as $destination)
                    <option value="{{ $destination->id }}" @selected((string) ($selectedDestinationId ?? '') === (string) $destination->id)>
                        {{ $destination->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="folder-order-type-filter" class="block text-sm font-medium text-concierge-navy">Order
                type</label>
            <select id="folder-order-type-filter" name="order_type"
                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                <option value="">All order types</option>
                @foreach (\App\Models\Folder::orderTypes() as $typeOption)
                    <option value="{{ $typeOption }}" @selected(($selectedOrderType ?? '') === $typeOption)>
                        {{ $typeOption }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="folder-booking-status-filter" class="block text-sm font-medium text-concierge-navy">Booking
                status</label>
            <select id="folder-booking-status-filter" name="booking_status"
                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                <option value="">All bookings</option>
                @foreach (folder_booking_status_filter_options() as $bookingValue => $bookingLabel)
                    <option value="{{ $bookingValue }}" @selected(($selectedBookingStatus ?? '') === $bookingValue)>
                        {{ $bookingLabel }}
                    </option>
                @endforeach
            </select>
        </div>
        @if ($showTravelDateFilter)
            <div>
                <label for="folder-travel-arrival-from-filter" class="block text-sm font-medium text-concierge-navy">
                    Date
                </label>
                <div class="relative">
                    <input id="folder-travel-arrival-range-filter" data-folder-date-range-picker="true"
                        data-folder-date-range-display-format="d M Y"
                        value="{{ ($selectedTravelArrivalFrom ?? '') !== '' && ($selectedTravelArrivalTo ?? '') !== '' ? $selectedTravelArrivalFrom . ' to ' . $selectedTravelArrivalTo : '' }}"
                        placeholder="Select date range" autocomplete="off"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 pr-10 text-sm text-slate-800 placeholder:text-slate-400 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                    <input id="folder-travel-arrival-from-filter" name="travel_arrival_from" type="hidden"
                        value="{{ $selectedTravelArrivalFrom ?? '' }}">
                    <input id="folder-travel-arrival-to-filter" name="travel_arrival_to" type="hidden"
                        value="{{ $selectedTravelArrivalTo ?? '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-concierge-muted"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10m-13 9h16a1 1 0 001-1V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a1 1 0 001 1z" />
                    </svg>
                </div>
            </div>
        @endif
    </div>

    <div class="mt-1.5 flex flex-col gap-2 sm:flex-row">
        <input id="folder-search-agent" name="search" type="search"
            placeholder="Search by customer name, order type, or invoice number" value="{{ $search ?? '' }}"
            class="w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
        <div class="flex shrink-0 gap-2">
            <button type="submit"
                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-concierge-navy-deep">
                Apply
            </button>
            @if (
                ($search ?? '') !== '' ||
                    ($selectedDestinationId ?? null) ||
                    ($showTravelDateFilter &&
                        (($selectedTravelArrivalFrom ?? '') !== '' || ($selectedTravelArrivalTo ?? '') !== '')) ||
                    ($selectedOrderType ?? '') !== '' ||
                    ($selectedBookingStatus ?? '') !== '')
                <a href="{{ $clearUrl }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-concierge-navy transition hover:bg-slate-50">
                    Clear
                </a>
            @endif
        </div>
    </div>
</form>
