<?php

namespace App\Support;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LeadListingQuery
{
    /**
     * @return array{
     *     query: Builder<Lead>,
     *     search: string,
     *     agent_id: int|null,
     *     company_id: int|null,
     *     source: string,
     *     status: string,
     *     date_range: string,
     *     start_date: string,
     *     end_date: string,
     *     start_bound: \Carbon\Carbon|null,
     *     end_bound: \Carbon\Carbon|null,
     *     date_label: string,
     * }
     */
    public static function adminFilters(Request $request): array
    {
        $search = trim((string) $request->query('search', ''));
        $agentId = $request->integer('agent_id') ?: null;
        $companyId = resolve_staff_company_filter(
            $request->user(),
            $request->integer('company_id') ?: null,
        );
        $source = trim((string) $request->query('source', ''));
        if ($request->user()?->hasRole(User::ROLE_MANAGER)) {
            $source = '';
        }
        $status = trim((string) $request->query('status', ''));
        $dateFilter = resolveLeadDateRangeFilter(
            (string) $request->query('date_range', ''),
            (string) $request->query('start_date', ''),
            (string) $request->query('end_date', ''),
            'year',
        );

        $query = Lead::query()
            ->with(['agent', 'company', 'destination'])
            ->latest();

        apply_staff_company_records_scope($query, $request->user(), 'leads');

        self::applyAdminFilters(
            $query,
            $search,
            $agentId,
            $companyId,
            $source,
            $status,
            $dateFilter['start'],
            $dateFilter['end'],
        );

        return [
            'query' => $query,
            'search' => $search,
            'agent_id' => $agentId,
            'company_id' => $companyId,
            'source' => $source,
            'status' => $status,
            'date_range' => $dateFilter['range'],
            'start_date' => $dateFilter['startDate'],
            'end_date' => $dateFilter['endDate'],
            'start_bound' => $dateFilter['start'],
            'end_bound' => $dateFilter['end'],
            'date_label' => $dateFilter['label'],
        ];
    }

    /**
     * @return array{
     *     query: Builder<Lead>,
     *     search: string,
     *     source: string,
     *     status: string,
     * }
     */
    public static function agentFilters(Request $request, int $agentId): array
    {
        $search = trim((string) $request->query('search', ''));
        $source = trim((string) $request->query('source', ''));
        $status = trim((string) $request->query('status', ''));

        $query = Lead::query()
            ->with(['agent', 'company', 'destination'])
            ->where('agent_id', $agentId)
            ->latest();

        self::applyAgentFilters($query, $search, $source, $status);

        return [
            'query' => $query,
            'search' => $search,
            'source' => $source,
            'status' => $status,
        ];
    }

    public static function adminStatsQuery(
        string $search,
        ?int $agentId,
        ?int $companyId,
        string $source,
        string $status,
        ?\Carbon\Carbon $startBound,
        ?\Carbon\Carbon $endBound,
        ?\App\Models\User $viewer = null,
    ): Builder {
        $statsQuery = Lead::query();
        apply_staff_company_records_scope($statsQuery, $viewer, 'leads');
        self::applyAdminFilters(
            $statsQuery,
            $search,
            $agentId,
            $companyId,
            $source,
            $status,
            $startBound,
            $endBound,
        );

        return $statsQuery;
    }

    private static function applyAdminFilters(
        Builder $query,
        string $search,
        ?int $agentId,
        ?int $companyId,
        string $source,
        string $status,
        ?\Carbon\Carbon $startBound,
        ?\Carbon\Carbon $endBound,
    ): void {
        if ($agentId !== null) {
            $query->where('agent_id', $agentId);
        }

        if ($companyId !== null) {
            $query->where('company_id', $companyId);
        }

        if ($source !== '') {
            $query->where('source', $source);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($startBound !== null && $endBound !== null) {
            $query->whereBetween('created_at', [$startBound, $endBound]);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('customer_name', 'like', '%'.$search.'%')
                    ->orWhere('phone_number', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }
    }

    private static function applyAgentFilters(
        Builder $query,
        string $search,
        string $source,
        string $status,
    ): void {
        if ($source !== '') {
            $query->where('source', $source);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('customer_name', 'like', '%'.$search.'%')
                    ->orWhere('phone_number', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }
    }
}
