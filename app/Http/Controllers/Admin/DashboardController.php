<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Replace with database aggregates when the Lead model exists.
        $totalLeads = 124;
        $leadsSuccessful = 51;
        $leadsFailed = 28;

        $totalAgents = User::role('agent')->count();

        $leadsSuccessRatePercent = $totalLeads > 0
            ? min(100, (int) round(($leadsSuccessful / $totalLeads) * 100))
            : 0;

        /** @var array{labels: list<string>, agents: list<array{name: string, color: string, data: list<int>}>} */
        $dashboardAgentChart = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'agents' => [
                ['name' => 'Alex Morgan', 'color' => '#2d5a8c', 'data' => [14, 18, 16, 22, 20, 26]],
                ['name' => 'Jordan Lee', 'color' => '#0ea5e9', 'data' => [10, 12, 15, 14, 19, 21]],
                ['name' => 'Sam Rivera', 'color' => '#059669', 'data' => [8, 11, 13, 17, 16, 20]],
                ['name' => 'Casey Nguyen', 'color' => '#d97706', 'data' => [16, 14, 12, 15, 18, 17]],
                ['name' => 'Riley Patel', 'color' => '#7c3aed', 'data' => [11, 15, 19, 18, 22, 25]],
            ],
        ];

        return view('admin.dashboard', compact(
            'totalLeads',
            'leadsSuccessful',
            'leadsFailed',
            'totalAgents',
            'leadsSuccessRatePercent',
            'dashboardAgentChart',
        ));
    }
}
