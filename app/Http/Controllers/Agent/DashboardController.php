<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $agentId = (int) request()->user()->id;
        $baseQuery = Lead::query()->where('agent_id', $agentId);
        $totalLeads = (clone $baseQuery)->count();
        $totalClosed = (clone $baseQuery)->where('status', Lead::STATUS_SALE_DONE)->count();
        $totalFailed = (clone $baseQuery)->where('status', Lead::STATUS_NOT_CONVERTED)->count();
        $totalPending = max(0, $totalLeads - $totalClosed - $totalFailed);

        $dashboardAgentChart = $this->buildAgentChartData($agentId, 'year');

        return view('agent.dashboard', compact(
            'totalLeads',
            'totalClosed',
            'totalPending',
            'totalFailed',
            'dashboardAgentChart',
        ));
    }

    public function performanceData(Request $request): JsonResponse
    {
        $agentId = (int) $request->user()->id;
        $range = (string) $request->string('range', 'year');
        $customStart = null;
        $customEnd = null;

        try {
            $customStart = $request->filled('start_date') ? Carbon::parse((string) $request->input('start_date')) : null;
            $customEnd = $request->filled('end_date') ? Carbon::parse((string) $request->input('end_date')) : null;
        } catch (\Throwable) {
            $customStart = null;
            $customEnd = null;
        }

        return response()->json(
            $this->buildAgentChartData($agentId, $range, $customStart, $customEnd)
        );
    }

    /**
     * @return array{labels: list<string>, agents: list<array{name: string, color: string, data: list<int>}>}
     */
    private function buildAgentChartData(
        int $agentId,
        string $range,
        ?Carbon $customStart = null,
        ?Carbon $customEnd = null,
    ): array {
        [$start, $end, $groupByMonth] = performanceChartDateRange($range, $customStart, $customEnd);

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

        $bucketSql = $groupByMonth
            ? "DATE_FORMAT(created_at, '%Y-%m')"
            : "DATE_FORMAT(created_at, '%Y-%m-%d')";

        $baseQuery = Lead::query()
            ->where('agent_id', $agentId)
            ->whereBetween('created_at', [$start, $end]);

        /** @var Collection<string, int> $totalByBucket */
        $totalByBucket = (clone $baseQuery)
            ->selectRaw("{$bucketSql} as bucket, COUNT(*) as total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        /** @var Collection<string, int> $closedByBucket */
        $closedByBucket = (clone $baseQuery)
            ->where('status', Lead::STATUS_SALE_DONE)
            ->selectRaw("{$bucketSql} as bucket, COUNT(*) as total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        /** @var Collection<string, int> $failedByBucket */
        $failedByBucket = (clone $baseQuery)
            ->where('status', Lead::STATUS_NOT_CONVERTED)
            ->selectRaw("{$bucketSql} as bucket, COUNT(*) as total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        return [
            'labels' => $labels,
            'agents' => [
                [
                    'name' => 'Total leads',
                    'color' => '#2d5a8c',
                    'data' => collect($bucketKeys)->map(fn (string $key): int => (int) ($totalByBucket[$key] ?? 0))->all(),
                ],
                [
                    'name' => 'Closed Leads',
                    'color' => '#059669',
                    'data' => collect($bucketKeys)->map(fn (string $key): int => (int) ($closedByBucket[$key] ?? 0))->all(),
                ],
                [
                    'name' => 'Failed Leads',
                    'color' => '#dc2626',
                    'data' => collect($bucketKeys)->map(fn (string $key): int => (int) ($failedByBucket[$key] ?? 0))->all(),
                ],
            ],
        ];
    }
}
