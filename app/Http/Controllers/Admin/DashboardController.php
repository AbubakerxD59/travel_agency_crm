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
        $agentsByPerformance = $this->sortAgentsBySalesDone($agents, $filters['start'], $filters['end'], $filters['companyId']);

        $dashboardAgentChart = array_merge(
            $this->buildAgentChartData($agentsByPerformance, $filters['start'], $filters['end'], $filters['companyId']),
            [
                'agentOptions' => $this->mapAgentOptions($agentsByPerformance, $filters['start'], $filters['end'], $filters['companyId']),
                'topPerformerAgentIds' => $this->resolveTopSalesPerformerAgentIds($agents, $filters['start'], $filters['end'], $filters['companyId']),
            ],
        );

        return view('admin.dashboard', [
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'selectedCompanyId' => $filters['companyId'],
            'selectedDateRange' => $filters['dateRange'],
            'selectedStartDate' => $filters['startDate'],
            'selectedEndDate' => $filters['endDate'],
            'selectedDateFilterLabel' => $filters['dateLabel'],
            'totalLeads' => $stats['totalLeads'],
            'totalFollowUps' => $stats['totalFollowUps'],
            'totalSalesDone' => $stats['totalSalesDone'],
            'totalNotConverted' => $stats['totalNotConverted'],
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

        $agentsByPerformance = $this->sortAgentsBySalesDone($allAgents, $filters['start'], $filters['end'], $filters['companyId']);

        $payload = array_merge(
            $this->buildAgentChartData($chartAgents, $filters['start'], $filters['end'], $filters['companyId']),
            [
                'agentOptions' => $this->mapAgentOptions($agentsByPerformance, $filters['start'], $filters['end'], $filters['companyId']),
                'topPerformerAgentIds' => $this->resolveTopSalesPerformerAgentIds($allAgents, $filters['start'], $filters['end'], $filters['companyId']),
            ],
        );

        return response()->json($payload);
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
     * @return array{totalLeads: int, totalFollowUps: int, totalSalesDone: int, totalNotConverted: int}
     */
    private function buildLeadStats(array $filters): array
    {
        $base = $this->dashboardLeadsQuery($filters);

        $totalFollowUps = (clone $base)->where('status', Lead::STATUS_FOLLOW_UP)->count();
        $totalSalesDone = (clone $base)->where('status', Lead::STATUS_SALE_DONE)->count();
        $totalNotConverted = (clone $base)->where('status', Lead::STATUS_NOT_CONVERTED)->count();

        return [
            'totalLeads' => $totalFollowUps + $totalSalesDone + $totalNotConverted,
            'totalFollowUps' => $totalFollowUps,
            'totalSalesDone' => $totalSalesDone,
            'totalNotConverted' => $totalNotConverted,
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
     * @return list<string>
     */
    private function dashboardPipelineStatuses(): array
    {
        return [
            Lead::STATUS_FOLLOW_UP,
            Lead::STATUS_SALE_DONE,
            Lead::STATUS_NOT_CONVERTED,
        ];
    }

    /**
     * @param  array{companyId: ?int, start: Carbon, end: Carbon}  $filters
     * @return list<array{key: string, label: string, count: int}>
     */
    private function buildLeadsBySource(array $filters): array
    {
        $countsBySource = [];
        $rows = $this->dashboardLeadsQuery($filters)
            ->whereIn('status', $this->dashboardPipelineStatuses())
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
     * @return list<array{id: int, name: string, salesDoneTotal: int}>
     */
    private function mapAgentOptions(Collection $agents, Carbon $start, Carbon $end, ?int $companyId = null): array
    {
        $totals = $this->salesDoneTotalsByAgent($agents, $start, $end, $companyId);

        return $agents
            ->map(fn (User $a): array => [
                'id' => $a->id,
                'name' => $a->name,
                'salesDoneTotal' => $totals[$a->id] ?? 0,
            ])
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
    ): array {
        $agents = $this->sortAgentsBySalesDone($agents, $start, $end, $companyId);
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

        $chartAgents = $agents->values()->map(function (User $agent, int $index) use ($rowsByAgent, $bucketKeys, $agentColors, $salesTotals): array {
            $totalsByBucket = collect($rowsByAgent->get($agent->id, []))
                ->pluck('total', 'bucket')
                ->map(fn ($total): int => (int) $total);

            return [
                'id' => $agent->id,
                'name' => $agent->name,
                'salesDoneTotal' => $salesTotals[$agent->id] ?? 0,
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
