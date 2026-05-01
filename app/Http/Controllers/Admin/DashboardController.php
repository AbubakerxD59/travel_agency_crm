<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalLeads = Lead::query()->count();
        $totalClosed = Lead::query()->where('status', Lead::STATUS_SALE_DONE)->count();
        $totalFailed = Lead::query()->where('status', Lead::STATUS_NOT_CONVERTED)->count();
        $totalPending = max(0, $totalLeads - $totalClosed - $totalFailed);

        $agents = User::role('agent')->orderBy('name')->get(['id', 'name']);
        $totalAgents = $agents->count();
        $totalFolders = Folder::query()->count();

        $leadsSuccessRatePercent = $totalLeads > 0
            ? min(100, (int) round(($totalClosed / $totalLeads) * 100))
            : 0;

        $dashboardAgentChart = $this->buildAgentChartData($agents, 'year');

        return view('admin.dashboard', compact(
            'totalLeads',
            'totalClosed',
            'totalPending',
            'totalFailed',
            'totalAgents',
            'totalFolders',
            'leadsSuccessRatePercent',
            'dashboardAgentChart',
        ));
    }

    public function agentPerformanceData(Request $request): JsonResponse
    {
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

        $agents = User::role('agent')->orderBy('name')->get(['id', 'name']);

        return response()->json(
            $this->buildAgentChartData($agents, $range, $customStart, $customEnd)
        );
    }

    /**
     * @return array{labels: list<string>, agents: list<array{name: string, color: string, data: list<int>}>}
     */
    private function buildAgentChartData(
        Collection $agents,
        string $range,
        ?Carbon $customStart = null,
        ?Carbon $customEnd = null,
    ): array {
        $now = now();
        $safeRange = in_array($range, ['week', 'month', 'year', 'custom'], true) ? $range : 'year';

        $groupByMonth = false;
        $start = null;
        $end = null;

        if ($safeRange === 'week') {
            $start = $now->copy()->startOfWeek();
            $end = $now->copy()->endOfWeek();
        } elseif ($safeRange === 'month') {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
        } elseif ($safeRange === 'year') {
            $groupByMonth = true;
            $start = $now->copy()->startOfYear();
            $end = $now->copy()->endOfYear();
        } else {
            if ($customStart instanceof Carbon && $customEnd instanceof Carbon && $customStart->lte($customEnd)) {
                $start = $customStart->copy()->startOfDay();
                $end = $customEnd->copy()->endOfDay();
                $daysDiff = $start->diffInDays($end);
                $groupByMonth = $daysDiff > 62;
            } else {
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
            }
        }

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

        $rowsByAgent = Lead::query()
            ->selectRaw("agent_id, {$bucketSql} as bucket, COUNT(*) as total")
            ->whereNotNull('agent_id')
            ->whereIn('agent_id', $agents->pluck('id'))
            ->where('status', Lead::STATUS_SALE_DONE)
            ->whereBetween('created_at', [$start, $end])
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

        $chartAgents = $agents->values()->map(function (User $agent, int $index) use ($rowsByAgent, $bucketKeys, $agentColors): array {
            $totalsByBucket = collect($rowsByAgent->get($agent->id, []))
                ->pluck('total', 'bucket')
                ->map(fn ($total): int => (int) $total);

            return [
                'name' => $agent->name,
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
