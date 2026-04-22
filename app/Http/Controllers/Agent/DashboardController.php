<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $agentId = (int) request()->user()->id;
        $baseQuery = Lead::query()->where('agent_id', $agentId);
        $totalLeads = (clone $baseQuery)->count();
        $leadsSuccessful = (clone $baseQuery)->where('status', Lead::STATUS_SALE_DONE)->count();
        $leadsFailed = (clone $baseQuery)->where('status', Lead::STATUS_NOT_CONVERTED)->count();

        $leadsSuccessRatePercent = $totalLeads > 0
            ? min(100, (int) round(($leadsSuccessful / $totalLeads) * 100))
            : 0;

        $startMonth = now()->startOfMonth()->subMonths(5);
        $months = collect(range(0, 5))->map(
            fn (int $offset) => (clone $startMonth)->addMonths($offset)
        );

        $labels = $months->map(fn (Carbon $month) => $month->format('M'))->values()->all();
        $monthKeys = $months->map(fn (Carbon $month) => $month->format('Y-m'))->values()->all();

        $totalByMonth = (clone $baseQuery)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->where('created_at', '>=', $startMonth)
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $closedByMonth = (clone $baseQuery)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->where('created_at', '>=', $startMonth)
            ->where('status', Lead::STATUS_SALE_DONE)
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $failedByMonth = (clone $baseQuery)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->where('created_at', '>=', $startMonth)
            ->where('status', Lead::STATUS_NOT_CONVERTED)
            ->groupBy('ym')
            ->pluck('total', 'ym');

        /** @var array{labels: list<string>, agents: list<array{name: string, color: string, data: list<int>}>} */
        $dashboardAgentChart = [
            'labels' => $labels,
            'agents' => [
                [
                    'name' => 'Total leads',
                    'color' => '#2d5a8c',
                    'data' => collect($monthKeys)->map(fn (string $key) => (int) ($totalByMonth[$key] ?? 0))->all(),
                ],
                [
                    'name' => 'Closed Leads',
                    'color' => '#059669',
                    'data' => collect($monthKeys)->map(fn (string $key) => (int) ($closedByMonth[$key] ?? 0))->all(),
                ],
                [
                    'name' => 'Failed Leads',
                    'color' => '#dc2626',
                    'data' => collect($monthKeys)->map(fn (string $key) => (int) ($failedByMonth[$key] ?? 0))->all(),
                ],
            ],
        ];

        return view('agent.dashboard', compact(
            'totalLeads',
            'leadsSuccessful',
            'leadsFailed',
            'leadsSuccessRatePercent',
            'dashboardAgentChart',
        ));
    }
}
