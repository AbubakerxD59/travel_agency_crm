<?php

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait ResolvesClosedLeadsChart
{
    protected function closedLeadsChartResponse(Request $request, ?int $scopedAgentId = null): JsonResponse
    {
        $range = (string) $request->query('chart_date_range', 'year');
        $startDate = trim((string) $request->query('chart_start_date', ''));
        $endDate = trim((string) $request->query('chart_end_date', ''));
        $source = trim((string) $request->query('chart_source', ''));

        $customStart = $startDate !== '' ? Carbon::parse($startDate)->startOfDay() : null;
        $customEnd = $endDate !== '' ? Carbon::parse($endDate)->endOfDay() : null;

        [$start, $end, $groupByMonth] = performanceChartDateRange($range, $customStart, $customEnd);

        $agentId = $scopedAgentId;
        if ($agentId === null && user_is_staff_portal($request->user())) {
            $agentId = $request->integer('chart_agent_id') ?: null;
        }

        $companyId = resolve_staff_company_filter(
            $request->user(),
            user_is_staff_portal($request->user())
                ? ($request->integer('chart_company_id') ?: null)
                : null,
        );

        if ($agentId !== null
            && $request->user()?->hasRole(\App\Models\User::ROLE_MANAGER)
            && ! \App\Models\User::recordAssigneesVisibleTo($request->user())->whereKey($agentId)->exists()) {
            $agentId = null;
        }

        $sourceOptions = $scopedAgentId !== null ? getAgentLeadSources() : null;
        if ($source !== '' && $sourceOptions !== null && ! array_key_exists($source, $sourceOptions)) {
            $source = '';
        }

        return response()->json(
            buildClosedLeadsChartData(
                $start,
                $end,
                $groupByMonth,
                $source !== '' ? $source : null,
                $agentId,
                $companyId,
                $sourceOptions,
            ),
        );
    }

}
