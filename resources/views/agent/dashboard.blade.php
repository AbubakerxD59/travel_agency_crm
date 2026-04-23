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
            aria-labelledby="dashboard-agent-performance-heading">
            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="dashboard-agent-performance-heading"
                        class="text-lg font-semibold text-concierge-navy md:text-xl">
                        Agent performance
                    </h2>
                    <p class="mt-0.5 text-sm text-concierge-muted">Current monthly trend: Total leads, Closed Leads, and
                        Failed Leads.</p>
                </div>
            </div>
            <div class="relative h-72 w-full min-w-0 md:h-80">
                <canvas id="dashboard-agent-performance-chart"
                    aria-label="Line chart of agent performance over months"></canvas>
            </div>
        </section>

        <script type="application/json" id="dashboard-agent-chart-config">@json($dashboardAgentChart)</script>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/dashboard.js'])
@endpush
