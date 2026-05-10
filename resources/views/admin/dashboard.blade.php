@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Dashboard</h1>
            <p class="mt-1 text-concierge-muted">Overview of your concierge operations.</p>
        </div>

        <div class="mb-6 grid min-w-0 gap-4 md:grid-cols-2 grid-cols-1 md:gap-6">
            <div class="dash-stat-card dash-stat-card--accent-navy">
                <p class="dash-stat-card__label">Total Agents</p>
                <p class="dash-stat-card__value">{{ number_format($totalAgents) }}</p>
                <p class="dash-stat-card__trend">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span>Active concierge accounts</span>
                </p>
            </div>
            <div class="dash-stat-card dash-stat-card--accent-leads">
                <p class="dash-stat-card__label">Total Folders</p>
                <p class="dash-stat-card__value">{{ number_format($totalFolders) }}</p>
                <p class="dash-stat-card__hint">Folders created across all agents</p>
            </div>
        </div>

        <section
            class="mt-2 min-w-0 rounded-xl border border-slate-200/80 bg-white p-5 shadow-[0_1px_3px_rgba(21,44,73,0.08)] md:p-6"
            aria-labelledby="dashboard-agent-performance-heading">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="dashboard-agent-performance-heading"
                        class="text-lg font-semibold text-concierge-navy md:text-xl">
                        Agent performance
                    </h2>
                    <p class="mt-0.5 text-sm text-concierge-muted">Monthly successful leads by agent.</p>
                </div>
                <div class="flex flex-col gap-2 self-start sm:flex-row sm:items-end sm:gap-3 sm:self-auto">
                    <div id="admin-agent-chart-agent-filter-wrap" class="min-w-0 sm:min-w-[14rem]"></div>
                    <div class="relative">
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
                            <button type="button" class="admin-agent-chart-filter-option block w-full px-4 py-2.5 text-left text-sm text-concierge-navy transition hover:bg-slate-50"
                                role="menuitem" data-filter="week" data-filter-label="This week">This week</button>
                            <button type="button" class="admin-agent-chart-filter-option block w-full px-4 py-2.5 text-left text-sm text-concierge-navy transition hover:bg-slate-50"
                                role="menuitem" data-filter="month" data-filter-label="This month">This month</button>
                            <button type="button" class="admin-agent-chart-filter-option block w-full px-4 py-2.5 text-left text-sm text-concierge-navy transition hover:bg-slate-50"
                                role="menuitem" data-filter="year" data-filter-label="This year">This year</button>
                            <button type="button" class="admin-agent-chart-filter-option block w-full px-4 py-2.5 text-left text-sm text-concierge-navy transition hover:bg-slate-50"
                                role="menuitem" data-filter="custom" data-filter-label="Custom date">Custom date</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative h-72 w-full min-w-0 md:h-80">
                <canvas id="dashboard-agent-performance-chart"
                    aria-label="Line chart of agent performance over months"></canvas>
            </div>
        </section>

        <script type="application/json" id="dashboard-agent-chart-config"
            data-chart-endpoint="{{ route('admin.dashboard.agent-performance') }}">@json($dashboardAgentChart)</script>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/dashboard.js'])
@endpush
