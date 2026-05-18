<div class="space-y-6">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-concierge-navy">Overview</h2>
            <dl class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Customer Name</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $folder->customer_name ?? '—' }}</dd>
                </div>
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

        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
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

        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
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

        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
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

        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-concierge-navy">Hotel Details</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-[1500px] w-full border-collapse text-xs sm:text-sm">
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
                            <th class="border border-slate-200 px-2 py-2">Status</th>
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
                                <td class="border border-slate-200 px-2 py-2">{{ getFolderHotelDetailStatusLabel($hotel->status) ?: '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->cost ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->margin ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->sell ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $hotel->hotel_city ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td class="border border-slate-200 px-3 py-4 text-center text-concierge-muted" colspan="16">No hotel details found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-concierge-navy">Transport Details</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-[1100px] w-full border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-100 text-left text-concierge-muted">
                            <th class="border border-slate-200 px-2 py-2">Supplier</th>
                            <th class="border border-slate-200 px-2 py-2">Description</th>
                            <th class="border border-slate-200 px-2 py-2">From</th>
                            <th class="border border-slate-200 px-2 py-2">To</th>
                            <th class="border border-slate-200 px-2 py-2">Date</th>
                            <th class="border border-slate-200 px-2 py-2">Pickup time</th>
                            <th class="border border-slate-200 px-2 py-2">Vehicle type</th>
                            <th class="border border-slate-200 px-2 py-2">Cost</th>
                            <th class="border border-slate-200 px-2 py-2">Margin</th>
                            <th class="border border-slate-200 px-2 py-2">Sell</th>
                            <th class="border border-slate-200 px-2 py-2">SAR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($folder->transportDetails as $t)
                            <tr class="bg-white">
                                <td class="border border-slate-200 px-2 py-2">{{ $t->supplier ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $t->description ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $t->origin ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $t->destination ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $t->service_date?->format('M j, Y') ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $t->pickup_time ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $t->vehicle_type ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $t->cost ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $t->margin ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $t->sell ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $t->sar ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td class="border border-slate-200 px-3 py-4 text-center text-concierge-muted" colspan="11">No transport details found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-concierge-navy">Visa Details</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-[640px] w-full border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-100 text-left text-concierge-muted">
                            <th class="border border-slate-200 px-2 py-2">Supplier</th>
                            <th class="border border-slate-200 px-2 py-2">Description</th>
                            <th class="border border-slate-200 px-2 py-2">Cost</th>
                            <th class="border border-slate-200 px-2 py-2">Margin</th>
                            <th class="border border-slate-200 px-2 py-2">Sell</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($folder->visaDetails as $v)
                            <tr class="bg-white">
                                <td class="border border-slate-200 px-2 py-2">{{ $v->supplier ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $v->description ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $v->cost ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $v->margin ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $v->sell ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td class="border border-slate-200 px-3 py-4 text-center text-concierge-muted" colspan="5">No visa details found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-concierge-navy">Other Details</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-[640px] w-full border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-100 text-left text-concierge-muted">
                            <th class="border border-slate-200 px-2 py-2">Supplier</th>
                            <th class="border border-slate-200 px-2 py-2">Description</th>
                            <th class="border border-slate-200 px-2 py-2">Cost</th>
                            <th class="border border-slate-200 px-2 py-2">Margin</th>
                            <th class="border border-slate-200 px-2 py-2">Sell</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($folder->otherDetails as $o)
                            <tr class="bg-white">
                                <td class="border border-slate-200 px-2 py-2">{{ $o->supplier ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $o->description ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $o->cost ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $o->margin ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $o->sell ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td class="border border-slate-200 px-3 py-4 text-center text-concierge-muted" colspan="5">No other details found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @include('agent.folders._show-cost-summary', ['folder' => $folder])

        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-concierge-navy">Payments</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-[640px] w-full border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-100 text-left text-concierge-muted">
                            <th class="border border-slate-200 px-2 py-2">Amount</th>
                            <th class="border border-slate-200 px-2 py-2">Reference No</th>
                            <th class="border border-slate-200 px-2 py-2">Date of Payment</th>
                            <th class="border border-slate-200 px-2 py-2">Mode of Payment</th>
                            <th class="border border-slate-200 px-2 py-2">Bank</th>
                            <th class="border border-slate-200 px-2 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($folder->payments as $p)
                            <tr class="bg-white">
                                <td class="border border-slate-200 px-2 py-2">{{ $p->amount ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $p->reference_no ?: '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ optional($p->payment_date)->format('Y-m-d') ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $p->mode_of_payment ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">{{ $p->bank?->name ?? '—' }}</td>
                                <td class="border border-slate-200 px-2 py-2">
                                    @if (($p->approval_status ?? 'approved') === 'pending')
                                        <span class="font-medium text-amber-700">Pending</span>
                                    @elseif (($p->approval_status ?? '') === 'rejected')
                                        <span class="font-medium text-rose-700">Rejected</span>
                                    @else
                                        <span class="text-concierge-muted">Approved</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td class="border border-slate-200 px-3 py-4 text-center text-concierge-muted" colspan="6">No payments recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

</div>