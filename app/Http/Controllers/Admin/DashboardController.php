<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->resolveDashboardFilters($request);
        $stats = $this->buildLeadStats($filters);
        $leadsBySource = $this->buildLeadsBySource($filters);
        $agents = $this->agentsForDashboard($filters);
        $sortByHighestPerformance = $request->boolean('highest_performance');

        $dashboardAgentChart = $this->buildAgentPerformanceChartPayload(
            $agents,
            $agents,
            $filters['start'],
            $filters['end'],
            $filters['companyId'],
            $sortByHighestPerformance,
        );

        return view('admin.dashboard', [
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'selectedCompanyId' => $filters['companyId'],
            'selectedDateRange' => $filters['dateRange'],
            'selectedStartDate' => $filters['startDate'],
            'selectedEndDate' => $filters['endDate'],
            'selectedDateFilterLabel' => $filters['dateLabel'],
            'totalLeads' => $stats['totalLeads'],
            'leadStatusStats' => $stats['leadStatusStats'],
            'leadsBySource' => $leadsBySource,
            'dashboardAgentChart' => $dashboardAgentChart,
        ]);
    }

    public function agentPerformanceData(Request $request): JsonResponse
    {
        $filters = $this->resolveDashboardFilters($request);
        $allAgents = $this->agentsForDashboard($filters);
        $filterAgentId = $request->integer('agent_id');
        $chartAgents = $allAgents;

        if ($filterAgentId > 0 && $allAgents->contains('id', $filterAgentId)) {
            $chartAgents = $allAgents->where('id', $filterAgentId)->values();
        }

        $sortByHighestPerformance = $request->boolean('highest_performance');

        return response()->json(
            $this->buildAgentPerformanceChartPayload(
                $allAgents,
                $chartAgents,
                $filters['start'],
                $filters['end'],
                $filters['companyId'],
                $sortByHighestPerformance,
            ),
        );
    }

    /**
     * @return array{
     *     companyId: ?int,
     *     dateRange: string,
     *     dateLabel: string,
     *     startDate: string,
     *     endDate: string,
     *     start: Carbon,
     *     end: Carbon,
     * }
     */
    private function resolveDashboardFilters(Request $request): array
    {
        $companyId = $request->integer('company_id') ?: null;
        $date = resolveLeadDateRangeFilter(
            (string) $request->query('date_range', ''),
            (string) $request->query('start_date', ''),
            (string) $request->query('end_date', ''),
            'today',
        );

        $start = $date['start'] ?? now()->startOfDay();
        $end = $date['end'] ?? now()->endOfDay();

        return [
            'companyId' => $companyId,
            'dateRange' => $date['range'],
            'dateLabel' => $date['label'],
            'startDate' => $date['startDate'],
            'endDate' => $date['endDate'],
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * @param  array{companyId: ?int, start: Carbon, end: Carbon}  $filters
     */
    private function dashboardLeadsQuery(array $filters): Builder
    {
        $query = Lead::query();

        if ($filters['companyId']) {
            $query->where('company_id', $filters['companyId']);
        }

        return $query->whereBetween('created_at', [$filters['start'], $filters['end']]);
    }

    /**
     * @param  array{companyId: ?int, start: Carbon, end: Carbon}  $filters
     * @return array{totalLeads: int, leadStatusStats: list<array{key: string, label: string, count: int}>}
     */
    private function buildLeadStats(array $filters): array
    {
        $base = $this->dashboardLeadsQuery($filters);
        $totalLeads = (clone $base)->count();

        /** @var Collection<string, int> $countsByStatus */
        $countsByStatus = (clone $base)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total): int => (int) $total);

        $leadStatusStats = [];
        foreach (Lead::statusLabels() as $key => $label) {
            $leadStatusStats[] = [
                'key' => $key,
                'label' => $label,
                'count' => $countsByStatus[$key] ?? 0,
            ];
        }

        return [
            'totalLeads' => $totalLeads,
            'leadStatusStats' => $leadStatusStats,
        ];
    }

    /**
     * @return Collection<int, User>
     */
    private function agentsForDashboard(array $filters): Collection
    {
        $query = User::role(User::ROLE_AGENT);

        if ($filters['companyId']) {
            $query->where('company_id', $filters['companyId']);
        }

        return $query->orderBy('name')->get(['id', 'name']);
    }

    /**
     * @param  array{companyId: ?int, start: Carbon, end: Carbon}  $filters
     * @return list<array{key: string, label: string, count: int}>
     */
    private function buildLeadsBySource(array $filters): array
    {
        $countsBySource = [];
        $rows = $this->dashboardLeadsQuery($filters)
            ->selectRaw('source, COUNT(*) as total')
            ->groupBy('source')
            ->get();

        foreach ($rows as $row) {
            $key = $row->source ?? '';
            $countsBySource[$key] = ($countsBySource[$key] ?? 0) + (int) $row->total;
        }

        $items = [];
        foreach (getSources() as $key => $label) {
            $items[] = [
                'key' => $key,
                'label' => $label,
                'count' => $countsBySource[$key] ?? 0,
            ];
        }

        if (($countsBySource[''] ?? 0) > 0) {
            $items[] = [
                'key' => '',
                'label' => 'Not specified',
                'count' => $countsBySource[''],
            ];
        }

        foreach ($countsBySource as $key => $count) {
            if ($key === '' || array_key_exists($key, getSources())) {
                continue;
            }

            $items[] = [
                'key' => $key,
                'label' => getSourceLabel($key),
                'count' => $count,
            ];
        }

        usort(
            $items,
            fn (array $a, array $b): int => $b['count'] <=> $a['count'] ?: strcasecmp($a['label'], $b['label']),
        );

        return $items;
    }

    private function salesDoneLeadsQuery(Carbon $start, Carbon $end, ?int $companyId = null): Builder
    {
        $query = Lead::query()
            ->where('status', Lead::STATUS_SALE_DONE)
            ->whereBetween('created_at', [$start, $end]);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query;
    }

    private function assignedLeadsQuery(Carbon $start, Carbon $end, ?int $companyId = null): Builder
    {
        $query = Lead::query()
            ->whereNotNull('agent_id')
            ->whereBetween('created_at', [$start, $end]);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query;
    }

    /**
     * @return array{
     *     labels: list<string>,
     *     agents: list<array<string, mixed>>,
     *     agentOptions: list<array<string, mixed>>,
     *     topPerformerAgentIds: list<int>,
     *     sortByHighestPerformance: bool,
     * }
     */
    private function buildAgentPerformanceChartPayload(
        Collection $allAgents,
        Collection $chartAgents,
        Carbon $start,
        Carbon $end,
        ?int $companyId,
        bool $sortByHighestPerformance,
    ): array {
        $metrics = $this->agentPerformanceMetrics($allAgents, $start, $end, $companyId);
        $sortedAgents = $sortByHighestPerformance
            ? $this->sortAgentsByPerformanceRate($allAgents, $metrics)
            : $this->sortAgentsBySalesDone($allAgents, $start, $end, $companyId);

        return array_merge(
            $this->buildAgentChartData($chartAgents, $start, $end, $companyId, $sortByHighestPerformance, $metrics),
            [
                'agentOptions' => $this->mapAgentOptions($sortedAgents, $metrics),
                'topPerformerAgentIds' => $sortByHighestPerformance
                    ? $this->resolveTopPerformanceAgentIds($metrics)
                    : $this->resolveTopSalesPerformerAgentIds($allAgents, $start, $end, $companyId),
                'sortByHighestPerformance' => $sortByHighestPerformance,
            ],
        );
    }

    /**
     * @return Collection<int, array{successful: int, assigned: int, rate: float}>
     */
    private function agentPerformanceMetrics(
        Collection $agents,
        Carbon $start,
        Carbon $end,
        ?int $companyId = null,
    ): Collection {
        if ($agents->isEmpty()) {
            return collect();
        }

        $agentIds = $agents->pluck('id');

        /** @var Collection<int|string, int> $assignedTotals */
        $assignedTotals = $this->assignedLeadsQuery($start, $end, $companyId)
            ->selectRaw('agent_id, COUNT(*) as total')
            ->whereIn('agent_id', $agentIds)
            ->groupBy('agent_id')
            ->pluck('total', 'agent_id')
            ->map(fn ($total): int => (int) $total);

        /** @var Collection<int|string, int> $successfulTotals */
        $successfulTotals = $this->salesDoneTotalsByAgent($agents, $start, $end, $companyId);

        return $agents->mapWithKeys(function (User $agent) use ($assignedTotals, $successfulTotals): array {
            $successful = $successfulTotals[$agent->id] ?? 0;
            $assigned = $assignedTotals[$agent->id] ?? 0;

            return [
                $agent->id => [
                    'successful' => $successful,
                    'assigned' => $assigned,
                    'rate' => $this->calculatePerformanceRate($successful, $assigned),
                ],
            ];
        });
    }

    private function calculatePerformanceRate(int $successful, int $assigned): float
    {
        if ($assigned < 1) {
            return 0.0;
        }

        return round(($successful / $assigned) * 100, 1);
    }

    /**
     * @return Collection<int|string, int>
     */
    private function salesDoneTotalsByAgent(Collection $agents, Carbon $start, Carbon $end, ?int $companyId = null): Collection
    {
        if ($agents->isEmpty()) {
            return collect();
        }

        return $this->salesDoneLeadsQuery($start, $end, $companyId)
            ->selectRaw('agent_id, COUNT(*) as total')
            ->whereNotNull('agent_id')
            ->whereIn('agent_id', $agents->pluck('id'))
            ->groupBy('agent_id')
            ->pluck('total', 'agent_id')
            ->map(fn ($total): int => (int) $total);
    }

    /**
     * @param  Collection<int, array{successful: int, assigned: int, rate: float}>  $metrics
     * @return list<array{id: int, name: string, salesDoneTotal: int, assignedLeadsTotal: int, performanceRate: float}>
     */
    private function mapAgentOptions(Collection $agents, Collection $metrics): array
    {
        return $agents
            ->map(function (User $agent) use ($metrics): array {
                $agentMetrics = $metrics[$agent->id] ?? ['successful' => 0, 'assigned' => 0, 'rate' => 0.0];

                return [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'salesDoneTotal' => $agentMetrics['successful'],
                    'assignedLeadsTotal' => $agentMetrics['assigned'],
                    'performanceRate' => $agentMetrics['rate'],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, array{successful: int, assigned: int, rate: float}>  $metrics
     * @return Collection<int, User>
     */
    private function sortAgentsByPerformanceRate(Collection $agents, Collection $metrics): Collection
    {
        if ($agents->isEmpty()) {
            return $agents;
        }

        return $agents
            ->sort(function (User $a, User $b) use ($metrics): int {
                $metricsA = $metrics[$a->id] ?? ['successful' => 0, 'assigned' => 0, 'rate' => 0.0];
                $metricsB = $metrics[$b->id] ?? ['successful' => 0, 'assigned' => 0, 'rate' => 0.0];

                $byRate = $metricsB['rate'] <=> $metricsA['rate'];
                if ($byRate !== 0) {
                    return $byRate;
                }

                $bySuccessful = $metricsB['successful'] <=> $metricsA['successful'];
                if ($bySuccessful !== 0) {
                    return $bySuccessful;
                }

                return strcasecmp($a->name, $b->name);
            })
            ->values();
    }

    /**
     * @param  Collection<int, array{successful: int, assigned: int, rate: float}>  $metrics
     * @return list<int>
     */
    private function resolveTopPerformanceAgentIds(Collection $metrics): array
    {
        if ($metrics->isEmpty()) {
            return [];
        }

        $maxRate = (float) $metrics->max(fn (array $metric): float => $metric['rate']);
        if ($maxRate <= 0) {
            return [];
        }

        return $metrics
            ->filter(fn (array $metric): bool => $metric['rate'] === $maxRate)
            ->keys()
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, User>
     */
    private function sortAgentsBySalesDone(Collection $agents, Carbon $start, Carbon $end, ?int $companyId = null): Collection
    {
        if ($agents->isEmpty()) {
            return $agents;
        }

        $totals = $this->salesDoneTotalsByAgent($agents, $start, $end, $companyId);

        return $agents
            ->sort(function (User $a, User $b) use ($totals): int {
                $bySales = ($totals[$b->id] ?? 0) <=> ($totals[$a->id] ?? 0);
                if ($bySales !== 0) {
                    return $bySales;
                }

                return strcasecmp($a->name, $b->name);
            })
            ->values();
    }

    /**
     * @return list<int>
     */
    private function resolveTopSalesPerformerAgentIds(Collection $agents, Carbon $start, Carbon $end, ?int $companyId = null): array
    {
        if ($agents->isEmpty()) {
            return [];
        }

        $rows = $this->salesDoneLeadsQuery($start, $end, $companyId)
            ->selectRaw('agent_id, COUNT(*) as total')
            ->whereNotNull('agent_id')
            ->whereIn('agent_id', $agents->pluck('id'))
            ->groupBy('agent_id')
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $maxTotal = (int) $rows->max('total');
        if ($maxTotal < 1) {
            return [];
        }

        return $rows
            ->filter(fn ($row) => (int) $row->total === $maxTotal)
            ->pluck('agent_id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     labels: list<string>,
     *     agents: list<array{id: int, name: string, salesDoneTotal: int, color: string, data: list<int>}>,
     * }
     */
    private function buildAgentChartData(
        Collection $agents,
        Carbon $start,
        Carbon $end,
        ?int $companyId = null,
        bool $sortByHighestPerformance = false,
        ?Collection $metrics = null,
    ): array {
        $metrics ??= $this->agentPerformanceMetrics($agents, $start, $end, $companyId);
        $agents = $sortByHighestPerformance
            ? $this->sortAgentsByPerformanceRate($agents, $metrics)
            : $this->sortAgentsBySalesDone($agents, $start, $end, $companyId);
        $salesTotals = $this->salesDoneTotalsByAgent($agents, $start, $end, $companyId);
        $groupByMonth = $start->diffInDays($end) > 62;

        $bucketKeys = [];
        $labels = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if ($groupByMonth) {
                $bucketKeys[] = $cursor->format('Y-m');
                $labels[] = $cursor->format('M');
                $cursor->addMonthNoOverflow();
            } else {
                $bucketKeys[] = $cursor->format('Y-m-d');
                $labels[] = $cursor->format('d M');
                $cursor->addDay();
            }
        }

        if ($agents->isEmpty()) {
            return [
                'labels' => $labels,
                'agents' => [],
            ];
        }

        $bucketSql = $groupByMonth
            ? "DATE_FORMAT(created_at, '%Y-%m')"
            : "DATE_FORMAT(created_at, '%Y-%m-%d')";

        $rowsByAgent = $this->salesDoneLeadsQuery($start, $end, $companyId)
            ->selectRaw("agent_id, {$bucketSql} as bucket, COUNT(*) as total")
            ->whereNotNull('agent_id')
            ->whereIn('agent_id', $agents->pluck('id'))
            ->groupBy('agent_id', 'bucket')
            ->get()
            ->groupBy('agent_id');

        $agentColors = [
            '#2d5a8c',
            '#0ea5e9',
            '#059669',
            '#d97706',
            '#7c3aed',
            '#dc2626',
            '#0f766e',
            '#7c2d12',
        ];

        $chartAgents = $agents->values()->map(function (User $agent, int $index) use (
            $rowsByAgent,
            $bucketKeys,
            $agentColors,
            $salesTotals,
            $metrics,
        ): array {
            $totalsByBucket = collect($rowsByAgent->get($agent->id, []))
                ->pluck('total', 'bucket')
                ->map(fn ($total): int => (int) $total);
            $agentMetrics = $metrics[$agent->id] ?? ['successful' => 0, 'assigned' => 0, 'rate' => 0.0];

            return [
                'id' => $agent->id,
                'name' => $agent->name,
                'salesDoneTotal' => $salesTotals[$agent->id] ?? 0,
                'assignedLeadsTotal' => $agentMetrics['assigned'],
                'performanceRate' => $agentMetrics['rate'],
                'color' => $agentColors[$index % count($agentColors)],
                'data' => collect($bucketKeys)->map(
                    fn (string $key): int => (int) ($totalsByBucket[$key] ?? 0)
                )->all(),
            ];
        })->all();

        return [
            'labels' => $labels,
            'agents' => $chartAgents,
        ];
    }
}
