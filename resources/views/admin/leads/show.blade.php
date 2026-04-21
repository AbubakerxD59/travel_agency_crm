@extends('layouts.admin')

@section('title', 'Lead #' . $lead->id)

@section('content')
    <div class="mx-auto max-w-7xl">
        <div
            class="rounded-2xl border border-slate-200/70 bg-gradient-to-r from-white via-slate-50/60 to-white p-6 shadow-sm lg:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-concierge-muted">Lead Details</p>
                    <h1 class="mt-1 text-2xl font-bold text-concierge-navy lg:text-3xl">Lead #{{ $lead->id }}</h1>
                    <p class="mt-1 text-sm text-concierge-muted">Booking details, passengers, itinerary, and package costs.
                    </p>
                </div>
                <div class="inline-flex items-center gap-2">
                    <span
                        class="concierge-pill concierge-pill-{{ $lead->statusPillClass() }}">{{ $lead->statusLabel() }}</span>
                    <a href="{{ route('admin.leads.index') }}"
                        class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-concierge-navy shadow-sm transition hover:bg-slate-50">
                        Back to leads
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto">
            <div class="grid min-w-[780px] grid-cols-3 gap-4 md:gap-6">
                <div class="dash-stat-card dash-stat-card--accent-navy">
                    <p class="dash-stat-card__label">Itineraries</p>
                    <p class="dash-stat-card__value">{{ number_format($lead->itineraries->count()) }}</p>
                    <p class="dash-stat-card__hint">Total journey legs added</p>
                </div>
                <div class="dash-stat-card dash-stat-card--accent-success">
                    <p class="dash-stat-card__label">Passengers</p>
                    <p class="dash-stat-card__value">{{ number_format($lead->passengers->count()) }}</p>
                    <p class="dash-stat-card__hint">Travelers linked to this lead</p>
                </div>
                <div class="dash-stat-card dash-stat-card--accent-leads">
                    <p class="dash-stat-card__label">Package costs</p>
                    <p class="dash-stat-card__value">{{ number_format($lead->packageCosts->count()) }}</p>
                    <p class="dash-stat-card__hint">Cost entries for booking</p>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-concierge-navy">Overview</h2>
            <dl class="mt-4 grid grid-cols-1 gap-3 grid-cols-2">
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Order type</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->order_type }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Vendor ref.</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->vendor_reference ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Company</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->company?->name ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Destination</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->destination?->name ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Agent</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->agent?->name ?? 'Unassigned' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Travel date</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">
                        {{ $lead->travel_date?->format('M j, Y') ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Balance due</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">
                        {{ $lead->balance_due_date?->format('M j, Y') ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Ziarat</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">
                        {{ $lead->ziarat_makkah ? 'Makkah' : '' }}{{ $lead->ziarat_makkah && $lead->ziarat_madinah ? ' + ' : '' }}{{ $lead->ziarat_madinah ? 'Madinah' : '' }}{{ !$lead->ziarat_makkah && !$lead->ziarat_madinah ? 'No' : '' }}
                    </dd>
                </div>
            </dl>
            @if ($lead->flight_itinerary)
                <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                    <h3 class="text-sm font-semibold text-concierge-navy">Flight itinerary notes</h3>
                    <p class="mt-2 whitespace-pre-wrap text-sm text-concierge-navy">{{ $lead->flight_itinerary }}</p>
                </div>
            @endif
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-semibold text-concierge-navy">Itinerary Legs</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50/80 text-xs font-semibold uppercase tracking-wide text-concierge-muted">
                        <tr>
                            <th class="px-6 py-3">Route</th>
                            <th class="px-6 py-3">Departure</th>
                            <th class="px-6 py-3">Arrival</th>
                            <th class="px-6 py-3">Airline</th>
                            <th class="px-6 py-3">Class</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($lead->itineraries as $itinerary)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-3 text-concierge-navy">{{ $itinerary->departure_airport ?? '—' }} →
                                    {{ $itinerary->arrival_airport ?? '—' }}</td>
                                <td class="px-6 py-3 text-concierge-muted">
                                    {{ $itinerary->departure_date?->format('M j, Y') ?? '—' }}
                                    {{ $itinerary->departure_time ?? '' }}</td>
                                <td class="px-6 py-3 text-concierge-muted">
                                    {{ $itinerary->arrival_date?->format('M j, Y') ?? '—' }}
                                    {{ $itinerary->arrival_time ?? '' }}</td>
                                <td class="px-6 py-3 text-concierge-muted">{{ $itinerary->airline_code ?? '—' }}
                                    {{ $itinerary->airline_number ?? '' }}</td>
                                <td class="px-6 py-3 text-concierge-muted">{{ $itinerary->class ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-sm text-concierge-muted">No itinerary
                                    records.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-base font-semibold text-concierge-navy">Passengers</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50/80 text-xs font-semibold uppercase tracking-wide text-concierge-muted">
                            <tr>
                                <th class="px-6 py-3">Name</th>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Contact</th>
                                <th class="px-6 py-3">DOB</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($lead->passengers as $passenger)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-6 py-3 text-concierge-navy">
                                        {{ trim(($passenger->title ?? '') . ' ' . ($passenger->first_name ?? '') . ' ' . ($passenger->middle_name ?? '') . ' ' . ($passenger->last_name ?? '')) ?: '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-concierge-muted">{{ $passenger->passenger_type ?? '—' }}</td>
                                    <td class="px-6 py-3 text-concierge-muted">
                                        {{ $passenger->phone ?? ($passenger->email ?? '—') }}</td>
                                    <td class="px-6 py-3 text-concierge-muted">
                                        {{ $passenger->date_of_birth?->format('M j, Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-sm text-concierge-muted">No
                                        passengers added.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-base font-semibold text-concierge-navy">Hotel/Package Costs</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50/80 text-xs font-semibold uppercase tracking-wide text-concierge-muted">
                            <tr>
                                <th class="px-6 py-3">Ticket</th>
                                <th class="px-6 py-3">Route</th>
                                <th class="px-6 py-3">Total</th>
                                <th class="px-6 py-3">Sell</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($lead->packageCosts as $cost)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-6 py-3 text-concierge-navy">{{ $cost->ticket_no ?? '—' }}</td>
                                    <td class="px-6 py-3 text-concierge-muted">{{ $cost->airline_from ?? '—' }} →
                                        {{ $cost->airline_to ?? '—' }}</td>
                                    <td class="px-6 py-3 text-concierge-muted">
                                        {{ $cost->total_cost !== null ? number_format((float) $cost->total_cost, 2) : '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-concierge-muted">
                                        {{ $cost->sell !== null ? number_format((float) $cost->sell, 2) : '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-sm text-concierge-muted">No
                                        package costs added.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
