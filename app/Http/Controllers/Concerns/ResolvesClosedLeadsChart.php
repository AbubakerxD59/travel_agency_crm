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
        if ($agentId === null && $request->user()?->hasRole('super-admin')) {
            $agentId = $request->integer('chart_agent_id') ?: null;
        }

        $companyId = $request->user()?->hasRole('super-admin')
            ? ($request->integer('chart_company_id') ?: null)
            : null;

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
