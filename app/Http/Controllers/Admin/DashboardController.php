<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;
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

        $startMonth = now()->startOfMonth()->subMonths(5);
        $months = collect(range(0, 5))->map(
            fn (int $offset) => (clone $startMonth)->addMonths($offset)
        );

        $labels = $months->map(fn (Carbon $month) => $month->format('M'))->values()->all();
        $monthKeys = $months->map(fn (Carbon $month) => $month->format('Y-m'))->values()->all();

        $monthlyAgentCounts = Lead::query()
            ->selectRaw("agent_id, DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->whereNotNull('agent_id')
            ->whereIn('agent_id', $agents->pluck('id'))
            ->where('status', Lead::STATUS_SALE_DONE)
            ->where('created_at', '>=', $startMonth)
            ->groupBy('agent_id', 'ym')
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

        /** @var array{labels: list<string>, agents: list<array{name: string, color: string, data: list<int>}>} */
        $dashboardAgentChart = [
            'labels' => $labels,
            'agents' => $agents->values()->map(function (User $agent, int $index) use ($monthlyAgentCounts, $monthKeys, $agentColors): array {
                $rowsForAgent = collect($monthlyAgentCounts->get($agent->id, []));
                $totalsByMonth = $rowsForAgent
                    ->pluck('total', 'ym')
                    ->map(fn ($total): int => (int) $total);

                return [
                    'name' => $agent->name,
                    'color' => $agentColors[$index % count($agentColors)],
                    'data' => collect($monthKeys)->map(
                        fn (string $key): int => (int) ($totalsByMonth[$key] ?? 0)
                    )->all(),
                ];
            })->all(),
        ];

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
}
