@extends('layouts.admin')

@section('title', 'Folder #' . $folder->id)

@section('content')
    <div class="mx-auto max-w-7xl">
        <div
            class="rounded-2xl border border-slate-200/70 bg-gradient-to-r from-white via-slate-50/60 to-white p-6 shadow-sm lg:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-concierge-muted">Folder Details</p>
                    <h1 class="mt-1 text-2xl font-bold text-concierge-navy lg:text-3xl">Folder #{{ $folder->id }}</h1>
                </div>
                <a href="{{ route('admin.folders.index') }}"
                    class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-concierge-navy shadow-sm transition hover:bg-slate-50">
                    Back to folders
                </a>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-concierge-navy">Overview</h2>
            <dl class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Agent</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $folder->agent?->name ?? 'Unassigned' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Order Type</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $folder->order_type ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Vendor Ref#</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $folder->vendor_reference ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Company</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $folder->company?->name ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Destination</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $folder->destination?->name ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Travel Date</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $folder->travel_date?->format('M j, Y') ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Balance Due Date</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $folder->balance_due_date?->format('M j, Y') ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Makkah Ziarat</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $folder->makkah_ziarat ? 'Yes' : 'No' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Madinah Ziarat</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $folder->madinah_ziarat ? 'Yes' : 'No' }}</dd>
                </div>
            </dl>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-concierge-navy">Itineraries</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-[1000px] w-full border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-100 text-left text-concierge-muted">
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
                    <tbody>
                        @forelse ($folder->itineraries as $itinerary)
                            <tr class="bg-white">
                                <td class="border border-slate-200 px-2 py-2">{{ $itinerary->sr_no ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $itinerary->airline_code ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $itinerary->airline_number ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $itinerary->class ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $itinerary->departure_date?->format('M j, Y') ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $itinerary->departure_airport ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $itinerary->arrival_airport ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $itinerary->departure_time ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $itinerary->arrival_time ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $itinerary->arrival_date?->format('M j, Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td class="border border-slate-200 px-3 py-4 text-center text-concierge-muted" colspan="10">No itineraries found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-concierge-navy">Passenger Details</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-[1100px] w-full border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-100 text-left text-concierge-muted">
                            <th class="border border-slate-200 px-2 py-2">Title</th>
                            <th class="border border-slate-200 px-2 py-2">First name</th>
                            <th class="border border-slate-200 px-2 py-2">Middle name</th>
                            <th class="border border-slate-200 px-2 py-2">Last name</th>
                            <th class="border border-slate-200 px-2 py-2">Passenger type</th>
                            <th class="border border-slate-200 px-2 py-2">Email</th>
                            <th class="border border-slate-200 px-2 py-2">Phone</th>
                            <th class="border border-slate-200 px-2 py-2">Date of birth</th>
                            <th class="border border-slate-200 px-2 py-2">Passport details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($folder->passengers as $passenger)
                            <tr class="bg-white">
                                <td class="border border-slate-200 px-2 py-2">{{ $passenger->title ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $passenger->first_name ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $passenger->middle_name ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $passenger->last_name ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $passenger->passenger_type ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $passenger->email ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $passenger->phone ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $passenger->date_of_birth?->format('M j, Y') ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $passenger->passport_details ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td class="border border-slate-200 px-3 py-4 text-center text-concierge-muted" colspan="9">No passengers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-concierge-navy">Ticket / Package Costs</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-[1200px] w-full border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-100 text-left text-concierge-muted">
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
                    <tbody>
                        @forelse ($folder->packageCosts as $cost)
                            <tr class="bg-white">
                                <td class="border border-slate-200 px-2 py-2">{{ $cost->ticket_no ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $cost->ticket_date?->format('M j, Y') ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $cost->airline_from ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $cost->airline_to ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $cost->fare ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $cost->tax ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $cost->total_cost ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $cost->margin ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $cost->sell ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $cost->supplier ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $cost->pnr ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td class="border border-slate-200 px-3 py-4 text-center text-concierge-muted" colspan="11">No ticket/package costs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-concierge-navy">Hotel Details</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-[1400px] w-full border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-100 text-left text-concierge-muted">
                            <th class="border border-slate-200 px-2 py-2">Sr. No.</th>
                            <th class="border border-slate-200 px-2 py-2">Supplier</th>
                            <th class="border border-slate-200 px-2 py-2">Hotel name</th>
                            <th class="border border-slate-200 px-2 py-2">Guest name</th>
                            <th class="border border-slate-200 px-2 py-2">Rooms</th>
                            <th class="border border-slate-200 px-2 py-2">Type</th>
                            <th class="border border-slate-200 px-2 py-2">Meals</th>
                            <th class="border border-slate-200 px-2 py-2">Date in</th>
                            <th class="border border-slate-200 px-2 py-2">Date out</th>
                            <th class="border border-slate-200 px-2 py-2">Nights</th>
                            <th class="border border-slate-200 px-2 py-2">Supplier ref</th>
                            <th class="border border-slate-200 px-2 py-2">Cost</th>
                            <th class="border border-slate-200 px-2 py-2">Margin</th>
                            <th class="border border-slate-200 px-2 py-2">Sell</th>
                            <th class="border border-slate-200 px-2 py-2">Hotel city</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($folder->hotelDetails as $hotel)
                            <tr class="bg-white">
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->sr_no ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->supplier ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->hotel_name ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->guest_name ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->rooms ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->type ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->meals ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->date_in?->format('M j, Y') ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->date_out?->format('M j, Y') ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->nights ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->supplier_ref ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->cost ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->margin ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->sell ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->hotel_city ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td class="border border-slate-200 px-3 py-4 text-center text-concierge-muted" colspan="15">No hotel details found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
