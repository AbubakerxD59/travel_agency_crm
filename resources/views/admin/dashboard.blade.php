@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="mb-8 grid min-w-0 grid-cols-2 gap-4 md:grid-cols-4 md:gap-6 md:items-end">
            <div class="col-span-2 md:col-span-2">
                <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Dashboard</h1>
                <p class="mt-1 text-concierge-muted">Overview of your concierge operations.</p>
            </div>
            <form id="dashboard-filters-form" method="GET" action="{{ route('admin.dashboard') }}"
                class="col-span-2 flex w-full flex-col gap-3 sm:flex-row sm:items-end sm:justify-end md:col-span-2 md:col-start-3">
                <input type="hidden" id="dashboard-date-range-input" name="date_range" value="{{ $selectedDateRange }}">
                <input type="hidden" id="dashboard-start-date-input" name="start_date" value="{{ $selectedStartDate }}">
                <input type="hidden" id="dashboard-end-date-input" name="end_date" value="{{ $selectedEndDate }}">

                <div class="min-w-0 w-full sm:w-auto sm:min-w-[12rem]">
                    <label for="dashboard-company-filter"
                        class="block text-sm font-medium text-concierge-navy">Company</label>
                    <select id="dashboard-company-filter" name="company_id"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:border-concierge-accent focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        <option value="">All companies</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected($selectedCompanyId === $company->id)>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="relative min-w-0 w-full sm:w-auto">
                    <span class="block text-sm font-medium text-concierge-navy">Date</span>
                    <button type="button" id="dashboard-date-filter-button"
                        class="mt-1.5 inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-medium text-concierge-navy transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-concierge-accent/25 sm:w-auto"
                        aria-haspopup="true" aria-expanded="false" aria-controls="dashboard-date-filter-menu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 4.5h18m-15 6h12m-9 6h6" />
                        </svg>
                        <span id="dashboard-date-filter-label">{{ $selectedDateFilterLabel }}</span>
                    </button>
                    <div id="dashboard-date-filter-menu"
                        class="absolute right-0 z-20 mt-2 hidden min-w-40 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
                        role="menu" aria-labelledby="dashboard-date-filter-button">
                        @include('partials.date-range-filter-menu', ['optionClass' => 'dashboard-date-filter-option'])
                    </div>
                </div>
            </form>
        </div>

        <div class="mb-6 grid min-w-0 grid-cols-2 gap-4 md:grid-cols-4 md:gap-6">
            <div class="dash-stat-card dash-stat-card--accent-leads">
                <p class="dash-stat-card__label">Total Leads</p>
                <p class="dash-stat-card__value">{{ number_format($totalLeads) }}</p>
            </div>
            <div class="dash-stat-card dash-stat-card--accent-leads">
                <p class="dash-stat-card__label">Follow ups</p>
                <p class="dash-stat-card__value">{{ number_format($totalFollowUps) }}</p>
            </div>
            <div class="dash-stat-card dash-stat-card--accent-success">
                <p class="dash-stat-card__label">Sales done</p>
                <p class="dash-stat-card__value dash-stat-card__value--success">{{ number_format($totalSalesDone) }}</p>
            </div>
            <div class="dash-stat-card dash-stat-card--accent-fail">
                <p class="dash-stat-card__label">Not Converted</p>
                <p class="dash-stat-card__value dash-stat-card__value--fail">{{ number_format($totalNotConverted) }}</p>
            </div>
        </div>

        <section
            class="mb-6 min-w-0 rounded-xl border border-slate-200/80 bg-white p-5 shadow-[0_1px_3px_rgba(21,44,73,0.08)] md:p-6"
            aria-labelledby="dashboard-leads-by-source-heading">
            <h2 id="dashboard-leads-by-source-heading" class="text-lg font-semibold text-concierge-navy md:text-xl">
                Leads by source
            </h2>
            <p class="mt-0.5 text-sm text-concierge-muted">Follow-up, sale done, and not converted leads in the selected
                period.</p>
            <ul class="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-200/80">
                @forelse ($leadsBySource as $sourceStat)
                    <li
                        class="flex items-center justify-between gap-4 px-4 py-3.5 first:rounded-t-xl last:rounded-b-xl sm:px-5">
                        <span class="text-sm font-medium text-concierge-navy">{{ $sourceStat['label'] }}</span>
                        <span class="shrink-0 text-lg font-bold tabular-nums text-concierge-navy">
                            {{ number_format($sourceStat['count']) }}
                        </span>
                    </li>
                @empty
                    <li class="px-4 py-6 text-center text-sm text-concierge-muted sm:px-5">No leads to show for these
                        filters.</li>
                @endforelse
            </ul>
        </section>

        <section
            class="mt-2 min-w-0 rounded-xl border border-slate-200/80 bg-white p-5 shadow-[0_1px_3px_rgba(21,44,73,0.08)] md:p-6"
            aria-labelledby="dashboard-agent-performance-heading">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="dashboard-agent-performance-heading"
                        class="text-lg font-semibold text-concierge-navy md:text-xl">
                        Agent performance
                    </h2>
                    <p class="mt-0.5 text-sm text-concierge-muted">Successful leads by agent for the selected company and
                        date.</p>
                </div>
                <div id="admin-agent-chart-agent-filter-wrap" class="min-w-0 sm:min-w-[14rem]"></div>
            </div>
            <div class="relative h-72 w-full min-w-0 md:h-80">
                <canvas id="dashboard-agent-performance-chart"
                    aria-label="Line chart of agent performance over time"></canvas>
                <p id="dashboard-agent-performance-empty"
                    class="@if (!empty($dashboardAgentChart['agents'])) hidden @endif absolute inset-0 flex items-center justify-center px-4 text-center text-sm text-concierge-muted">
                    No agents match the selected company and date filters.
                </p>
            </div>
        </section>

        <script type="application/json" id="dashboard-agent-chart-config"
            data-chart-endpoint="{{ route('admin.dashboard.agent-performance') }}"
            data-highlight-top-performer="true">@json($dashboardAgentChart)</script>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin-dashboard-filters.js', 'resources/js/dashboard.js'])
@endpush
