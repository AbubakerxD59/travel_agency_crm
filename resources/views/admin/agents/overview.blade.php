@extends('layouts.admin')

@section('title', 'Agent Overview')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="mb-8 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">{{ $agent->name }}</h1>
                <p class="mt-1 text-concierge-muted">{{ $agent->email }} @if ($agent->phone_number)
                        • {{ $agent->phone_number }}
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.agents.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-concierge-navy transition hover:bg-slate-50">
                Back to agents
            </a>
        </div>

        <div class="grid min-w-0 grid-cols-2 gap-4 md:grid-cols-4 md:gap-6">
            <div class="dash-stat-card dash-stat-card--accent-leads">
                <p class="dash-stat-card__label">Total Leads</p>
                <p class="dash-stat-card__value">{{ number_format($totalLeads) }}</p>
            </div>
            <div class="dash-stat-card dash-stat-card--accent-success">
                <p class="dash-stat-card__label">Total Closed</p>
                <p class="dash-stat-card__value dash-stat-card__value--success">{{ number_format($totalClosed) }}</p>
            </div>
            <div class="dash-stat-card dash-stat-card--accent-leads">
                <p class="dash-stat-card__label">Total Pending</p>
                <p class="dash-stat-card__value">{{ number_format($totalPending) }}</p>
            </div>
            <div class="dash-stat-card dash-stat-card--accent-fail">
                <p class="dash-stat-card__label">Total Failed</p>
                <p class="dash-stat-card__value dash-stat-card__value--fail">{{ number_format($totalFailed) }}</p>
            </div>
        </div>

        <section
            class="mt-8 min-w-0 rounded-xl border border-slate-200/80 bg-white p-5 shadow-[0_1px_3px_rgba(21,44,73,0.08)] md:p-6"
            aria-labelledby="agent-performance-heading">
            <div class="mb-4">
                <h2 id="agent-performance-heading" class="text-lg font-semibold text-concierge-navy md:text-xl">Agent
                    performance</h2>
                <p class="mt-0.5 text-sm text-concierge-muted">Current monthly trend: Total leads, Closed Leads, and Failed
                    Leads.</p>
            </div>
            <div class="relative h-72 w-full min-w-0 md:h-80">
                <canvas id="dashboard-agent-performance-chart" aria-label="Line chart of agent performance over months"></canvas>
            </div>
        </section>

        <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-semibold text-concierge-navy">Assigned Leads</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-slate-100 bg-slate-50/80 text-xs font-semibold uppercase tracking-wide text-concierge-muted">
                            <th class="px-4 py-4 lg:px-6">Customer</th>
                            <th class="px-4 py-4 lg:px-6">Phone</th>
                            <th class="px-4 py-4 lg:px-6">Email</th>
                            <th class="px-4 py-4 lg:px-6">Company</th>
                            <th class="px-4 py-4 lg:px-6">Status</th>
                            <th class="px-4 py-4 lg:px-6">Created</th>
                            <th class="px-4 py-4 text-right lg:px-6">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($leads as $lead)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-4 text-concierge-navy lg:px-6">{{ $lead->customer_name ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-muted lg:px-6">{{ $lead->phone_number ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-muted lg:px-6">{{ $lead->email ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-navy lg:px-6">{{ $lead->company?->name ?? '—' }}</td>
                                <td class="px-4 py-4 lg:px-6">
                                    <span
                                        class="concierge-pill concierge-pill-{{ $lead->statusPillClass() }}">{{ $lead->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-4 text-concierge-muted lg:px-6">{{ $lead->created_at?->format('M j, Y') }}
                                </td>
                                <td class="px-4 py-4 text-right lg:px-6">
                                    <a href="{{ route('admin.leads.show', $lead) }}"
                                        class="lead-row-action inline-flex cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-concierge-accent"
                                        title="View" aria-label="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-concierge-muted">
                                    No leads assigned to this agent.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($leads->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $leads->links() }}
                </div>
            @endif
        </section>

        <script type="application/json" id="dashboard-agent-chart-config">@json($dashboardAgentChart)</script>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/dashboard.js'])
@endpush
