@props([
    'chartEndpoint',
    'closedLeadsChart',
    'chartDateLabel' => 'This year',
    'chartDateRange' => 'year',
    'chartStartDate' => '',
    'chartEndDate' => '',
    'chartSource' => '',
    'chartAgentId' => null,
    'chartCompanyId' => null,
    'sourceOptions' => null,
])

@php
    $chartSourceOptions = $sourceOptions ?? getAllLeadSourceOptions();
@endphp

<section class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
    aria-labelledby="closed-leads-chart-heading">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between lg:px-6">
        <div>
            <h2 id="closed-leads-chart-heading" class="text-lg font-semibold text-concierge-navy">Closed leads</h2>
            <p class="mt-0.5 text-sm text-concierge-muted">Sale done leads over time by source</p>
        </div>
        <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-[10rem]">
                <label for="closed-leads-chart-source" class="block text-xs font-medium text-concierge-muted">Source</label>
                <select id="closed-leads-chart-source"
                    class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2 text-sm text-concierge-navy focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                    <option value="" @selected($chartSource === '')>All sources</option>
                    @foreach ($chartSourceOptions as $sourceKey => $sourceLabelOption)
                        <option value="{{ $sourceKey }}" @selected($chartSource === $sourceKey)>
                            {{ $sourceLabelOption }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="relative">
                <span class="block text-xs font-medium text-concierge-muted">Date</span>
                <button type="button" id="closed-leads-chart-date-button"
                    class="mt-1 inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-concierge-navy transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-concierge-accent/25"
                    aria-haspopup="true" aria-expanded="false" aria-controls="closed-leads-chart-date-menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18m-15 6h12m-9 6h6" />
                    </svg>
                    <span id="closed-leads-chart-date-label">{{ $chartDateLabel }}</span>
                </button>
                <div id="closed-leads-chart-date-menu"
                    class="absolute right-0 z-20 mt-2 hidden min-w-40 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
                    role="menu" aria-labelledby="closed-leads-chart-date-button">
                    @include('partials.date-range-filter-menu', ['optionClass' => 'closed-leads-chart-date-option'])
                </div>
            </div>
        </div>
    </div>

    <div class="px-5 py-4 lg:px-6">
        <p class="text-sm text-concierge-muted">
            <span class="font-semibold text-concierge-navy" id="closed-leads-chart-total">{{ number_format($closedLeadsChart['totalClosed'] ?? 0) }}</span>
            closed in this period
        </p>
        <div class="relative mt-4 leads-closed-chart-wrap">
            <canvas id="closed-leads-chart-canvas" aria-label="Closed leads over time by source"></canvas>
            <div id="closed-leads-chart-empty"
                class="@if (!empty($closedLeadsChart['datasets'])) hidden @endif absolute inset-0 flex items-center justify-center rounded-xl bg-slate-50/80 px-4 text-center text-sm text-concierge-muted">
                No closed leads in this period for the selected filters.
            </div>
        </div>
    </div>

    <input type="hidden" id="closed-leads-chart-date-range" value="{{ $chartDateRange }}">
    <input type="hidden" id="closed-leads-chart-start-date" value="{{ $chartStartDate }}">
    <input type="hidden" id="closed-leads-chart-end-date" value="{{ $chartEndDate }}">
    @if ($chartAgentId)
        <input type="hidden" id="closed-leads-chart-agent-id" value="{{ $chartAgentId }}">
    @endif
    @if ($chartCompanyId)
        <input type="hidden" id="closed-leads-chart-company-id" value="{{ $chartCompanyId }}">
    @endif

    <script type="application/json" id="closed-leads-chart-config"
        data-chart-endpoint="{{ $chartEndpoint }}">@json($closedLeadsChart)</script>
</section>
