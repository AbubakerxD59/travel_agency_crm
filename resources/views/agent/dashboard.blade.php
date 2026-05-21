@extends('layouts.agent')

@section('title', 'Dashboard')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Dashboard</h1>
            <p class="mt-1 text-concierge-muted">Overview of your concierge operations.</p>
        </div>

        <div class="grid min-w-0 grid-cols-2 gap-4 md:grid-cols-4 md:gap-6">
            <div class="dash-stat-card dash-stat-card--accent-leads">
                <p class="dash-stat-card__label">Total Leads</p>
                <p class="dash-stat-card__value">{{ number_format($totalLeads) }}</p>
            </div>

            <div class="dash-stat-card dash-stat-card--accent-success">
                <p class="dash-stat-card__label">Leads Closed</p>
                <p class="dash-stat-card__value dash-stat-card__value--success">{{ number_format($totalClosed) }}</p>
            </div>

            <div class="dash-stat-card dash-stat-card--accent-leads">
                <p class="dash-stat-card__label">Leads Pending</p>
                <p class="dash-stat-card__value">{{ number_format($totalPending) }}</p>
            </div>

            <div class="dash-stat-card dash-stat-card--accent-fail">
                <p class="dash-stat-card__label">Leads Failed</p>
                <p class="dash-stat-card__value dash-stat-card__value--fail">{{ number_format($totalFailed) }}</p>
            </div>
        </div>

        <section
            class="mt-8 min-w-0 rounded-xl border border-slate-200/80 bg-white p-5 shadow-[0_1px_3px_rgba(21,44,73,0.08)] md:p-6"
            aria-labelledby="dashboard-agent-performance-heading">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="dashboard-agent-performance-heading"
                        class="text-lg font-semibold text-concierge-navy md:text-xl">
                        Agent performance
                    </h2>
                    <p class="mt-0.5 text-sm text-concierge-muted">Current monthly trend: Total leads, Closed Leads, and
                        Failed Leads.</p>
                </div>
                <div class="relative self-start sm:self-auto">
                    <button type="button" id="admin-agent-chart-filter-button"
                        class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-concierge-navy transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-concierge-accent/25"
                        aria-haspopup="true" aria-expanded="false" aria-controls="admin-agent-chart-filter-menu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 4.5h18m-15 6h12m-9 6h6" />
                        </svg>
                        <span id="admin-agent-chart-filter-label">This year</span>
                    </button>
                    <div id="admin-agent-chart-filter-menu"
                        class="absolute right-0 z-10 mt-2 hidden min-w-40 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
                        role="menu" aria-labelledby="admin-agent-chart-filter-button">
                        @include('partials.date-range-filter-menu')
                    </div>
                </div>
            </div>
            <div class="relative h-72 w-full min-w-0 md:h-80">
                <canvas id="dashboard-agent-performance-chart"
                    aria-label="Line chart of agent performance over months"></canvas>
            </div>
        </section>

        <script type="application/json" id="dashboard-agent-chart-config"
            data-chart-endpoint="{{ route('agent.dashboard.performance') }}">@json($dashboardAgentChart)</script>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/dashboard.js'])
@endpush
