<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Folder;
use App\Models\FolderPayment;
use App\Models\Lead;
use App\Models\User;
use App\Support\AbbreviationResolver;
use App\Support\FolderPaymentImageStorage;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Route name prefix for the current staff/agent portal request (admin|manager|agent).
 */
function portal_route_prefix(?Request $request = null): string
{
    $name = ($request ?? request())->route()?->getName() ?? '';

    if (str_starts_with($name, 'manager.')) {
        return 'manager';
    }

    if (str_starts_with($name, 'agent.')) {
        return 'agent';
    }

    return 'admin';
}

/**
 * Generate a named route under the current portal prefix.
 *
 * @param  mixed  $parameters
 */
function portal_route(string $name, mixed $parameters = [], bool $absolute = true): string
{
    return route(portal_route_prefix().'.'.$name, $parameters, $absolute);
}

function user_is_staff_portal(?User $user): bool
{
    return (bool) $user?->hasAnyRole(['super-admin', User::ROLE_MANAGER]);
}

function staff_can_delete_records(?User $user): bool
{
    return (bool) $user?->hasRole('super-admin');
}

/**
 * Company id forced for managers; null means "no force" (super-admin / others).
 */
function staff_forced_company_id(?User $viewer): ?int
{
    if (! $viewer?->hasRole(User::ROLE_MANAGER)) {
        return null;
    }

    return $viewer->company_id !== null ? (int) $viewer->company_id : null;
}

/**
 * Resolve company filter for staff portal lists.
 * Managers are always locked to their own company (or empty when unassigned).
 */
function resolve_staff_company_filter(?User $viewer, ?int $requestedCompanyId): ?int
{
    if ($viewer?->hasRole(User::ROLE_MANAGER)) {
        return staff_forced_company_id($viewer);
    }

    return $requestedCompanyId;
}

/**
 * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
 */
function apply_staff_company_scope(Builder $query, ?User $viewer, string $column = 'company_id'): void
{
    if (! $viewer?->hasRole(User::ROLE_MANAGER)) {
        return;
    }

    $companyId = staff_forced_company_id($viewer);
    if ($companyId === null) {
        $query->whereRaw('0 = 1');

        return;
    }

    $query->where($column, $companyId);
}

/**
 * Scope lead/folder listings for managers to their own records and their company agents'.
 *
 * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
 */
function apply_staff_company_records_scope(Builder $query, ?User $viewer, string $table): void
{
    if (! $viewer?->hasRole(User::ROLE_MANAGER)) {
        return;
    }

    $companyId = staff_forced_company_id($viewer);
    if ($companyId === null) {
        $query->whereRaw('0 = 1');

        return;
    }

    $query
        ->where("{$table}.company_id", $companyId)
        ->whereIn(
            "{$table}.agent_id",
            User::recordAssigneesVisibleTo($viewer)->select('users.id'),
        );
}

/**
 * Whether a lead/folder company is visible to the staff viewer.
 */
function staff_can_access_company_record(?User $viewer, mixed $companyId): bool
{
    if (! $viewer?->hasRole(User::ROLE_MANAGER)) {
        return true;
    }

    $forced = staff_forced_company_id($viewer);

    return $forced !== null && (int) $companyId === $forced;
}

/**
 * Whether a lead/folder assigned to an agent (or the manager themself) is visible.
 */
function staff_can_access_agent_record(?User $viewer, mixed $agentId, mixed $companyId = null): bool
{
    if (! $viewer?->hasRole(User::ROLE_MANAGER)) {
        return true;
    }

    if ($companyId !== null && ! staff_can_access_company_record($viewer, $companyId)) {
        return false;
    }

    if ($agentId === null || $agentId === '') {
        return false;
    }

    return User::recordAssigneesVisibleTo($viewer)->whereKey((int) $agentId)->exists();
}

/**
 * Companies visible in staff portal filters/forms.
 *
 * @return Builder<Company>
 */
function companies_visible_to_staff(?User $viewer): Builder
{
    $query = Company::query()->orderBy('name');

    if ($viewer?->hasRole(User::ROLE_MANAGER)) {
        $companyId = staff_forced_company_id($viewer);
        if ($companyId === null) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereKey($companyId);
    }

    return $query;
}

/**
 * Fail validation when a manager tries to use another company's id.
 */
function assert_staff_company_allowed(?User $viewer, mixed $companyId, \Closure $fail): void
{
    if (! $viewer?->hasRole(User::ROLE_MANAGER)) {
        return;
    }

    $forced = staff_forced_company_id($viewer);
    if ($forced === null || (int) $companyId !== $forced) {
        $fail(__('You can only use your assigned company.'));
    }
}

/**
 * Fail validation when a manager selects an agent outside their company.
 */
function assert_staff_agent_allowed(?User $viewer, mixed $agentId, \Closure $fail): void
{
    if ($agentId === null || $agentId === '') {
        return;
    }

    if (! User::recordAssigneesVisibleTo($viewer)->whereKey((int) $agentId)->exists()) {
        $fail(__('The selected agent is invalid.'));
    }
}

/**
 * Public URL for a file on the `public` disk (e.g. company logos under storage/app/public).
 */
function public_storage_url(?string $path): ?string
{
    if ($path === null || trim($path) === '') {
        return null;
    }

    if (Str::startsWith($path, ['http://', 'https://'])) {
        return $path;
    }

    $normalized = str_replace('\\', '/', trim($path));
    $normalized = ltrim($normalized, '/');

    if (Str::startsWith($normalized, 'storage/')) {
        return asset($normalized);
    }

    if (! Storage::disk('public')->exists($normalized)) {
        return null;
    }

    return asset('storage/'.$normalized);
}

/**
 * Root-relative URL for the agent notification service worker.
 * Avoid asset() here: misconfigured APP_URL values can produce /public/... paths
 * that break service worker scope on production.
 */
function agent_notification_sw_url(): string
{
    return '/agent-notification-sw.js';
}

/**
 * Public URL for the static transportation voucher PDF.
 */
function transportation_voucher_pdf_url(): string
{
    return asset('TRANSPORTATION VOUCHER TERMS & CONDITIONS.pdf');
}

/**
 * Suggested download filename for the transportation voucher PDF.
 */
function transportation_voucher_pdf_filename(): string
{
    return 'TRANSPORTATION VOUCHER TERMS & CONDITIONS.pdf';
}

/**
 * Absolute path to the invoice terms & conditions PDF in public/.
 */
function invoice_terms_and_conditions_pdf_path(): string
{
    return public_path('Terms & Conditions for Invoice.pdf');
}

/**
 * Public URL for the invoice terms & conditions PDF.
 */
function invoice_terms_and_conditions_pdf_url(): string
{
    return asset('Terms & Conditions for Invoice.pdf');
}

/**
 * Display an abbreviation code as its full form when configured.
 */
function abbreviation_display(?string $code): string
{
    return app(AbbreviationResolver::class)->display($code);
}

/**
 * Expand known abbreviation codes inside free-form invoice text.
 */
function abbreviation_expand_text(?string $text): string
{
    return app(AbbreviationResolver::class)->expandText($text);
}

/**
 * Lead source options for dropdowns: stored key => display label.
 *
 * @return array<string, string>
 */
function getSources(): array
{
    return [
        'google' => 'Google',
        'meta' => 'Meta',
        'seo' => 'SEO',
        'direct_whatsapp_chat' => 'Direct WhatsApp Chat',
        'direct_call' => 'Direct Call',
        'referral' => 'Referral',
    ];
}

/**
 * Lead source options for agent-created leads (modal dropdown).
 *
 * @return array<string, string>
 */
function getAgentLeadSources(): array
{
    return [
        'whatsapp_chat' => 'WhatsApp Chat',
        'direct_call' => 'Direct Call',
        'referral' => 'Referral',
    ];
}

/**
 * Display label for a stored lead source key (handles legacy slugs).
 */
function getSourceLabel(?string $key): string
{
    if ($key === null || $key === '') {
        return '';
    }

    $sources = getSources() + getAgentLeadSources();

    return $sources[$key] ?? match ($key) {
        'whatsapp' => 'Direct WhatsApp Chat',
        default => $key,
    };
}

/**
 * All lead source keys for filters and charts (admin + agent options).
 *
 * @return array<string, string>
 */
function getAllLeadSourceOptions(): array
{
    return getSources() + getAgentLeadSources();
}

/**
 * Chart color for a lead source key.
 */
function closedLeadsChartSourceColor(string $sourceKey): string
{
    return match ($sourceKey) {
        'google' => '#F97316',
        'meta' => '#0284C7',
        'seo' => '#7C3AED',
        'direct_whatsapp_chat', 'whatsapp_chat' => '#22C55E',
        'direct_call' => '#0D9488',
        'referral' => '#A855F7',
        '' => '#94A3B8',
        default => '#64748B',
    };
}

/**
 * Closed (sale done) leads over time, optionally split by source.
 * Source datasets are ordered highest → lowest by closed lead count.
 *
 * @return array{
 *     labels: list<string>,
 *     datasets: list<array{key: string, label: string, color: string, data: list<int>, totalLeads: int}>,
 *     totalClosed: int,
 *     range: string
 * }
 */
function buildClosedLeadsChartData(
    Carbon $start,
    Carbon $end,
    bool $groupByMonth,
    ?string $sourceFilter = null,
    ?int $agentId = null,
    ?int $companyId = null,
    ?array $sourceOptions = null,
): array {
    /** @var array<string, string> $sourceCatalog */
    $sourceCatalog = $sourceOptions ?? getAllLeadSourceOptions();
    $restrictToCatalog = $sourceOptions !== null;
    $bucketKeys = [];
    $labels = [];
    $cursor = $start->copy();

    while ($cursor->lte($end)) {
        if ($groupByMonth) {
            $bucketKeys[] = $cursor->format('Y-m');
            $labels[] = $cursor->format('M Y');
        } else {
            $bucketKeys[] = $cursor->format('Y-m-d');
            $labels[] = $cursor->format('d M');
        }

        if ($groupByMonth) {
            $cursor->addMonthNoOverflow();
        } else {
            $cursor->addDay();
        }
    }

    $query = Lead::query()
        ->where('status', Lead::STATUS_SALE_DONE)
        ->whereBetween('created_at', [$start, $end]);

    $totalLeadsQuery = Lead::query()
        ->whereBetween('created_at', [$start, $end]);

    if ($sourceFilter !== null && $sourceFilter !== '') {
        $query->where('source', $sourceFilter);
        $totalLeadsQuery->where('source', $sourceFilter);
    } elseif ($restrictToCatalog) {
        $query->whereIn('source', array_keys($sourceCatalog));
        $totalLeadsQuery->whereIn('source', array_keys($sourceCatalog));
    }

    if ($agentId !== null) {
        $query->where('agent_id', $agentId);
        $totalLeadsQuery->where('agent_id', $agentId);
    }

    if ($companyId !== null) {
        $query->where('company_id', $companyId);
        $totalLeadsQuery->where('company_id', $companyId);
    }

    $totalClosed = (int) (clone $query)->count();

    $bucketExpression = $groupByMonth
        ? "DATE_FORMAT(created_at, '%Y-%m')"
        : "DATE_FORMAT(created_at, '%Y-%m-%d')";

    $rows = (clone $query)
        ->selectRaw("{$bucketExpression} as bucket, COALESCE(source, '') as source_key, COUNT(*) as total")
        ->groupBy('bucket', 'source_key')
        ->orderBy('bucket')
        ->get();

    /** @var \Illuminate\Support\Collection<string, int> $totalLeadsBySource */
    $totalLeadsBySource = (clone $totalLeadsQuery)
        ->selectRaw("COALESCE(source, '') as source_key, COUNT(*) as total")
        ->groupBy('source_key')
        ->pluck('total', 'source_key');

    /** @var array<string, array<string, int>> $countsByBucket */
    $countsByBucket = [];
    foreach ($rows as $row) {
        $countsByBucket[(string) $row->bucket][(string) $row->source_key] = (int) $row->total;
    }

    $sourceDefinitions = [];

    if ($sourceFilter !== null && $sourceFilter !== '') {
        $sourceDefinitions[$sourceFilter] = getSourceLabel($sourceFilter) ?: $sourceFilter;
    } else {
        $seenKeys = [];
        foreach ($rows as $row) {
            $seenKeys[(string) $row->source_key] = true;
        }

        foreach ($sourceCatalog as $key => $label) {
            if (! isset($seenKeys[$key])) {
                continue;
            }

            $hasData = false;
            foreach ($bucketKeys as $bucketKey) {
                if (($countsByBucket[$bucketKey][$key] ?? 0) > 0) {
                    $hasData = true;
                    break;
                }
            }

            if ($hasData) {
                $sourceDefinitions[$key] = $label;
            }
        }

        if (! $restrictToCatalog && isset($seenKeys[''])) {
            $sourceDefinitions[''] = 'Not specified';
        }
    }

    $datasets = [];
    foreach ($sourceDefinitions as $key => $label) {
        $data = [];
        foreach ($bucketKeys as $bucketKey) {
            $data[] = $countsByBucket[$bucketKey][$key] ?? 0;
        }

        $datasets[] = [
            'key' => $key,
            'label' => $label,
            'color' => closedLeadsChartSourceColor($key),
            'data' => $data,
            'totalLeads' => (int) ($totalLeadsBySource[$key] ?? 0),
            'totalClosed' => array_sum($data),
        ];
    }

    usort($datasets, static function (array $left, array $right): int {
        $closedCmp = ($right['totalClosed'] ?? 0) <=> ($left['totalClosed'] ?? 0);
        if ($closedCmp !== 0) {
            return $closedCmp;
        }

        return ($right['totalLeads'] ?? 0) <=> ($left['totalLeads'] ?? 0);
    });

    $datasets = array_map(static function (array $dataset): array {
        unset($dataset['totalClosed']);

        return $dataset;
    }, $datasets);

    return [
        'labels' => $labels,
        'datasets' => array_values($datasets),
        'totalClosed' => $totalClosed,
        'range' => $groupByMonth ? 'monthly' : 'daily',
    ];
}

/**
 * Resolve lead list date filter bounds from range key and optional custom dates.
 *
 * @return array{
 *     range: string,
 *     label: string,
 *     start: ?Carbon,
 *     end: ?Carbon,
 *     startDate: string,
 *     endDate: string,
 * }
 */
function resolveLeadDateRangeFilter(
    string $dateRange = '',
    string $startDate = '',
    string $endDate = '',
    ?string $defaultRange = null,
): array {
    $dateRange = trim($dateRange);
    $startDate = trim($startDate);
    $endDate = trim($endDate);
    $label = 'Date filter';
    $startBound = null;
    $endBound = null;

    if ($dateRange === '' && in_array($defaultRange, ['today', 'week', 'month', 'year'], true)) {
        $dateRange = $defaultRange;
    }

    if ($dateRange === 'today') {
        $startBound = now()->startOfDay();
        $endBound = now()->endOfDay();
        $label = 'Today';
    } elseif ($dateRange === 'week') {
        $startBound = now()->startOfWeek();
        $endBound = now()->endOfWeek();
        $label = 'This week';
    } elseif ($dateRange === 'month') {
        $startBound = now()->startOfMonth();
        $endBound = now()->endOfMonth();
        $label = 'This month';
    } elseif ($dateRange === 'year') {
        $startBound = now()->startOfYear();
        $endBound = now()->endOfYear();
        $label = 'This year';
    } elseif ($dateRange === 'custom' && $startDate !== '' && $endDate !== '') {
        try {
            $startBound = now()->parse($startDate)->startOfDay();
            $endBound = now()->parse($endDate)->endOfDay();
            if ($startBound->gt($endBound)) {
                $startBound = null;
                $endBound = null;
                $dateRange = '';
                $startDate = '';
                $endDate = '';
            } else {
                $label = $startBound->format('Y-m-d').' - '.$endBound->format('Y-m-d');
            }
        } catch (Throwable) {
            $startBound = null;
            $endBound = null;
            $dateRange = '';
            $startDate = '';
            $endDate = '';
        }
    } else {
        $dateRange = '';
        $startDate = '';
        $endDate = '';
    }

    return [
        'range' => $dateRange,
        'label' => $label,
        'start' => $startBound,
        'end' => $endBound,
        'startDate' => $startDate,
        'endDate' => $endDate,
    ];
}

/**
 * Resolve start/end bounds for performance chart date filters.
 *
 * @return array{0: Carbon, 1: Carbon, 2: bool, 3: string}
 */
function performanceChartDateRange(
    string $range,
    ?Carbon $customStart = null,
    ?Carbon $customEnd = null,
): array {
    $now = now();
    $safeRange = in_array($range, ['today', 'week', 'month', 'year', 'custom'], true) ? $range : 'today';

    if ($safeRange === 'today') {
        return [$now->copy()->startOfDay(), $now->copy()->endOfDay(), false, $safeRange];
    }

    if ($safeRange === 'week') {
        return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), false, $safeRange];
    }

    if ($safeRange === 'month') {
        return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), false, $safeRange];
    }

    if ($safeRange === 'year') {
        return [$now->copy()->startOfYear(), $now->copy()->endOfYear(), true, $safeRange];
    }

    if ($customStart instanceof Carbon && $customEnd instanceof Carbon && $customStart->lte($customEnd)) {
        $start = $customStart->copy()->startOfDay();
        $end = $customEnd->copy()->endOfDay();

        return [$start, $end, $start->diffInDays($end) > 62, $safeRange];
    }

    return [$now->copy()->startOfDay(), $now->copy()->endOfDay(), false, 'today'];
}

/**
 * Allowed folder order type values stored in `folders.order_type`.
 *
 * @return list<string>
 */
function folder_order_types(): array
{
    return [
        'Flight',
        'Umrah Package',
        'Hajj package',
        'Holiday',
        'Visa',
        'Other',
    ];
}

/**
 * Allowed passenger title values for folder passenger rows.
 *
 * @return list<string>
 */
function folder_passenger_titles(): array
{
    return [
        'Mr',
        'Mrs',
        'Ms',
        'Miss',
        'Master',
    ];
}

/**
 * Allowed passenger type values for folder passenger rows.
 *
 * @return list<string>
 */
function folder_passenger_types(): array
{
    return [
        'Adult',
        'Youth',
        'Child',
        'Infant',
    ];
}

/**
 * Allowed folder payment mode labels (stored as-is on `folder_payments.mode_of_payment`).
 *
 * @return list<string>
 */
function folder_payment_modes(): array
{
    return [
        'Cash in office',
        'Bank Transfer',
        'Card Payment',
    ];
}

/**
 * Hotel detail row status options for `folder_hotel_details.status`: stored key => display label.
 *
 * @return array<string, string>
 */
function folder_hotel_detail_statuses(): array
{
    return [
        'issued' => 'Issued',
        'reserved' => 'Reserved',
        'issue_later' => 'Issue later',
    ];
}

/**
 * Meals options for folder hotel detail rows (`folder_hotel_details.meals`): stored value = label.
 *
 * @return list<string>
 */
function folder_hotel_meals_options(): array
{
    return [
        'Room Only',
        'Breakfast',
        'Half-Board',
        'Full-Board',
        'Dinner',
        'Lunch',
    ];
}

/**
 * Vehicle type options for folder transport detail rows (`folder_transport_details.vehicle_type`): stored value = label.
 *
 * @return list<string>
 */
function folder_transport_vehicle_types(): array
{
    return [
        'Car',
        'H1',
        'HiAce',
        'Bus',
        'GMC',
    ];
}

/**
 * Hotel city options for folder hotel detail rows (`folder_hotel_details.hotel_city`): stored value = label.
 *
 * @return list<string>
 */
function folder_hotel_cities(): array
{
    return [
        'Makkah',
        'Madinah',
        'Jeddah',
        'Istanbul',
        'Cairo',
        'Doha',
        'Dubai',
        'Morocco',
        'Abu Dhabi',
        'Riyadh',
        'Singapore',
        'Maldives',
        'Bangkok',
        'Kuala Lumpur',
    ];
}

/**
 * Display label for a stored folder hotel detail status key.
 */
function getFolderHotelDetailStatusLabel(?string $key): string
{
    if ($key === null || $key === '') {
        return '';
    }

    return folder_hotel_detail_statuses()[$key] ?? $key;
}

/**
 * Folder list "booking status" filter: query param value => display label.
 * Incomplete = folder has at least one hotel detail with status {@see folder_hotel_detail_statuses()} key `issue_later`.
 *
 * @return array<string, string>
 */
function folder_booking_status_filter_options(): array
{
    return [
        'successful' => 'Successful Bookings',
        'incomplete' => 'Incomplete Bookings',
    ];
}

/**
 * Restrict folder list queries to folders whose travel date falls within the selected range (inclusive).
 *
 * @param  Builder<Folder>  $query
 */
function apply_folder_travel_date_filter(Builder $query, string $from, string $to): void
{
    if ($from === '' && $to === '') {
        return;
    }

    if ($from !== '' && $to !== '') {
        $query->whereDate('travel_date', '>=', $from)
            ->whereDate('travel_date', '<=', $to);

        return;
    }

    if ($from !== '') {
        $query->whereDate('travel_date', $from);

        return;
    }

    $query->whereDate('travel_date', $to);
}

/**
 * Tailwind classes for a folder list table row (incomplete bookings use error styling).
 */
function folder_list_row_class(Folder $folder): string
{
    $isIncomplete = (bool) ($folder->is_incomplete_booking ?? false);

    return $isIncomplete
        ? 'bg-rose-50 hover:bg-rose-100/70'
        : 'hover:bg-slate-50/50';
}

function folder_agent_display_name(Folder $folder): string
{
    $stored = trim((string) ($folder->agent_name ?? ''));
    if ($stored !== '') {
        return $stored;
    }

    return $folder->agent?->name ?? (string) __('Unassigned');
}

function lead_agent_display_name(Lead $lead): string
{
    $stored = trim((string) ($lead->agent_name ?? ''));
    if ($stored !== '') {
        return $stored;
    }

    return $lead->agent?->name ?? (string) __('Unassigned');
}

function lead_sync_agent_name_from_user(Lead $lead): void
{
    if (! $lead->agent_id) {
        $lead->agent_name = null;

        return;
    }

    $lead->agent_name = User::withTrashed()
        ->whereKey($lead->agent_id)
        ->value('name');
}

function folder_sync_agent_name_from_user(Folder $folder): void
{
    if (! $folder->agent_id) {
        $folder->agent_name = null;

        return;
    }

    $folder->agent_name = User::withTrashed()
        ->whereKey($folder->agent_id)
        ->value('name');
}

/**
 * Validation rules for agent_id that allow soft-deleted team members (historical folders/leads).
 *
 * @return list<string|callable>
 */
function team_member_user_id_validation_rules(?User $viewer = null): array
{
    $viewer ??= request()->user();

    return [
        'nullable',
        'integer',
        function (string $attribute, mixed $value, Closure $fail) use ($viewer): void {
            if ($value === null || $value === '') {
                return;
            }

            if ($viewer?->hasRole(User::ROLE_MANAGER)) {
                if (! User::recordAssigneesVisibleTo($viewer)->withTrashed()->whereKey((int) $value)->exists()) {
                    $fail(__('The selected agent is invalid.'));
                }

                return;
            }

            if (! User::withTrashed()->whereKey((int) $value)->exists()) {
                $fail(__('The selected agent is invalid.'));
            }
        },
    ];
}

/**
 * Locked payments on a folder, keyed by payment id.
 *
 * @return Collection<int, FolderPayment>
 */
function folder_locked_payments_for(Folder $folder)
{
    return $folder->payments()
        ->whereNotNull('locked_at')
        ->get()
        ->keyBy('id');
}

function folder_is_locked(Folder $folder): bool
{
    return $folder->isLocked();
}

function user_can_edit_folder(User $user, Folder $folder): bool
{
    if (user_is_staff_portal($user)) {
        return true;
    }

    if ((int) $folder->agent_id !== (int) $user->id) {
        return false;
    }

    if (! $user->can('folders.edit')) {
        return false;
    }

    if (folder_is_locked($folder) && ! $user->can('folders.edit_locked')) {
        return false;
    }

    return true;
}

function user_can_create_folder(User $user): bool
{
    if (user_is_staff_portal($user)) {
        return true;
    }

    return $user->can('folders.edit');
}

function folder_draft_session_key(?int $userId, ?int $folderId = null): string
{
    $base = 'folder_section_drafts.user.'.(string) ($userId ?? 0);

    return $folderId !== null ? "{$base}.folder.{$folderId}" : "{$base}.new";
}

/**
 * @return array<string, mixed>
 */
function folder_draft_sections_from_session(Request $request, ?int $folderId = null): array
{
    return (array) $request->session()->get(
        folder_draft_session_key($request->user()?->getAuthIdentifier(), $folderId),
        []
    );
}

function folder_forget_draft_sections(Request $request, ?int $folderId = null): void
{
    $request->session()->forget(
        folder_draft_session_key($request->user()?->getAuthIdentifier(), $folderId)
    );
}

/**
 * @param  mixed  $data
 */
function folder_put_draft_section(Request $request, ?int $folderId, string $section, $data): void
{
    $key = folder_draft_session_key($request->user()?->getAuthIdentifier(), $folderId);
    $drafts = (array) $request->session()->get($key, []);
    $drafts[$section] = $data;
    $request->session()->put($key, $drafts);
}

/**
 * Persist a single folder section to the database (replaces that relation only).
 * Used for AJAX section saves while editing an existing folder.
 *
 * @param  list<array<string, mixed>>  $rows
 * @return list<array{id: int}>|null Payment IDs when section is payments; otherwise null.
 */
function folder_persist_section_rows(
    Folder $folder,
    string $section,
    array $rows,
    string $paymentApprovalStatusForNew,
    ?Request $request = null,
): ?array {
    $paymentIds = null;

    DB::transaction(function () use ($folder, $section, $rows, $paymentApprovalStatusForNew, $request, &$paymentIds): void {
        switch ($section) {
            case 'itineraries':
                $folder->itineraries()->forceDelete();
                $folder->itineraries()->createMany($rows);
                break;
            case 'passengers':
                $folder->passengers()->forceDelete();
                $folder->passengers()->createMany($rows);
                break;
            case 'package_costs':
                $folder->packageCosts()->forceDelete();
                $folder->packageCosts()->createMany($rows);
                break;
            case 'hotel_details':
                $folder->hotelDetails()->forceDelete();
                $folder->hotelDetails()->createMany(folder_hotel_details_for_storage($rows));
                break;
            case 'transport_details':
                $folder->transportDetails()->forceDelete();
                $folder->transportDetails()->createMany($rows);
                break;
            case 'visa_details':
                $folder->visaDetails()->forceDelete();
                $folder->visaDetails()->createMany($rows);
                break;
            case 'other_details':
                $folder->otherDetails()->forceDelete();
                $folder->otherDetails()->createMany(folder_other_details_for_storage($rows));
                break;
            case 'payments':
                $paymentIds = folder_sync_folder_payments(
                    $folder,
                    $rows,
                    $paymentApprovalStatusForNew,
                    $request,
                );
                break;
            default:
                throw new \InvalidArgumentException("Unsupported folder section [{$section}].");
        }
    });

    return $paymentIds;
}

/**
 * First itinerary row in form submission order (not sorted by sr_no).
 *
 * @param  list<array<string, mixed>>  $itineraries
 * @return array<string, mixed>|null
 */
function folder_first_itinerary_row(array $itineraries): ?array
{
    foreach ($itineraries as $itinerary) {
        if (is_array($itinerary)) {
            return $itinerary;
        }
    }

    return null;
}

/**
 * Drop submitted payment rows that belong to locked payments (not validated or synced).
 *
 * @param  list<array<string, mixed>>  $rows
 * @return list<array<string, mixed>>
 */
function folder_strip_locked_payment_rows(Folder $folder, array $rows): array
{
    $lockedIds = folder_locked_payments_for($folder)->keys()->all();
    if ($lockedIds === []) {
        return $rows;
    }

    $lockedIdSet = array_flip($lockedIds);

    return array_values(array_filter($rows, function ($row) use ($lockedIdSet): bool {
        if (! is_array($row)) {
            return false;
        }

        $paymentId = isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : 0;

        return $paymentId < 1 || ! isset($lockedIdSet[$paymentId]);
    }));
}

/**
 * Keep only payment rows where at least one field is non-empty (trimmed strings).
 *
 * @param  array<int, mixed>|null  $rows
 * @return list<array<string, mixed>>
 */
function folder_filter_non_empty_payment_rows(?array $rows): array
{
    if (! is_array($rows)) {
        return [];
    }

    return folder_filter_rows_by_fields($rows, ['amount', 'reference_no', 'payment_date', 'mode_of_payment', 'bank_id']);
}

/**
 * @return array{amount: mixed, reference_no: string|null, payment_date: mixed, mode_of_payment: string, bank_id: int|null, approval_status: string, locked_at: null}
 */
function folder_payment_attributes_from_row(array $row, string $approvalStatus): array
{
    $normalized = folder_normalized_payments_for_storage([$row], $approvalStatus)[0] ?? null;
    if ($normalized === null) {
        throw ValidationException::withMessages([
            'payments' => __('Invalid payment row.'),
        ]);
    }

    return array_merge($normalized, ['locked_at' => null]);
}

/**
 * @param  array<string, mixed>  $row
 */
function folder_payment_row_is_storable(array $row): bool
{
    $amount = $row['amount'] ?? null;
    $paymentDate = $row['payment_date'] ?? null;
    $mode = trim((string) ($row['mode_of_payment'] ?? ''));

    return $amount !== null && $amount !== ''
        && $paymentDate !== null && $paymentDate !== ''
        && $mode !== '';
}

/**
 * @return array<string, list<ValidationRule|string>>
 */
function folder_itineraries_validation_rules(): array
{
    return [
        'itineraries' => ['nullable', 'array'],
        'itineraries.*.sr_no' => ['nullable', 'integer', 'min:1'],
        'itineraries.*.airline_code' => ['nullable', 'string', 'max:20'],
        'itineraries.*.airline_number' => ['nullable', 'string', 'max:30'],
        'itineraries.*.class' => ['nullable', 'string', 'max:20'],
        'itineraries.*.departure_date' => ['nullable', 'date'],
        'itineraries.*.departure_airport' => ['nullable', 'string', 'max:30'],
        'itineraries.*.arrival_airport' => ['nullable', 'string', 'max:30'],
        'itineraries.*.departure_time' => ['nullable'],
        'itineraries.*.arrival_time' => ['nullable'],
        'itineraries.*.arrival_date' => ['nullable', 'date'],
    ];
}

/**
 * @return array<string, list<ValidationRule|string>>
 */
function folder_package_costs_validation_rules(): array
{
    return [
        'package_costs' => ['nullable', 'array'],
        'package_costs.*.ticket_no' => ['nullable', 'string', 'max:50'],
        'package_costs.*.ticket_date' => ['nullable', 'date'],
        'package_costs.*.airline_from' => ['nullable', 'string', 'max:30'],
        'package_costs.*.airline_to' => ['nullable', 'string', 'max:30'],
        'package_costs.*.fare' => ['nullable', 'numeric', 'min:0'],
        'package_costs.*.tax' => ['nullable', 'numeric', 'min:0'],
        'package_costs.*.total_cost' => ['nullable', 'numeric', 'min:0'],
        'package_costs.*.margin' => ['nullable', 'numeric', 'min:0'],
        'package_costs.*.sell' => ['nullable', 'numeric', 'min:0'],
        'package_costs.*.supplier' => ['nullable', 'string', 'max:100'],
        'package_costs.*.pnr' => ['nullable', 'string', 'max:50'],
    ];
}

/**
 * @param  array<int, mixed>|null  $rows
 * @return list<array<string, mixed>>
 */
function folder_filter_non_empty_package_cost_rows(?array $rows): array
{
    return folder_filter_rows_by_fields($rows, [
        'ticket_no',
        'ticket_date',
        'airline_from',
        'airline_to',
        'fare',
        'tax',
        'total_cost',
        'margin',
        'sell',
        'supplier',
        'pnr',
    ]);
}

/**
 * @return array<string, list<ValidationRule|string>>
 */
function folder_hotel_details_validation_rules(): array
{
    return [
        'hotel_details' => ['nullable', 'array'],
        'hotel_details.*.sr_no' => ['nullable', 'integer', 'min:1'],
        'hotel_details.*.supplier' => ['nullable', 'string', 'max:100'],
        'hotel_details.*.hotel_name' => ['nullable', 'string', 'max:150'],
        'hotel_details.*.guest_name' => ['nullable', 'string', 'max:150'],
        'hotel_details.*.rooms' => ['nullable', 'integer', 'min:0'],
        'hotel_details.*.type' => ['nullable', 'string', 'max:100'],
        'hotel_details.*.meals' => ['nullable', 'string', Rule::in(folder_hotel_meals_options())],
        'hotel_details.*.date_in' => ['nullable', 'date'],
        'hotel_details.*.date_out' => ['nullable', 'date'],
        'hotel_details.*.nights' => ['nullable', 'integer', 'min:0'],
        'hotel_details.*.supplier_ref' => ['nullable', 'string', 'max:100'],
        'hotel_details.*.status' => ['nullable', 'string', Rule::in(array_keys(folder_hotel_detail_statuses()))],
        'hotel_details.*.cost' => ['nullable', 'numeric', 'min:0'],
        'hotel_details.*.margin' => ['nullable', 'numeric'],
        'hotel_details.*.sell' => ['nullable', 'numeric', 'min:0'],
        'hotel_details.*.hotel_city' => ['nullable', 'string', 'max:100', Rule::in(folder_hotel_cities())],
    ];
}

/**
 * @return array<string, list<ValidationRule|string>>
 */
function folder_transport_details_validation_rules(): array
{
    return [
        'transport_details' => ['nullable', 'array'],
        'transport_details.*.supplier' => ['nullable', 'string', 'max:100'],
        'transport_details.*.description' => ['nullable', 'string', 'max:255'],
        'transport_details.*.origin' => ['nullable', 'string', 'max:150'],
        'transport_details.*.destination' => ['nullable', 'string', 'max:150'],
        'transport_details.*.service_date' => ['nullable', 'date'],
        'transport_details.*.pickup_time' => ['nullable', 'string', 'max:30'],
        'transport_details.*.vehicle_type' => ['nullable', 'string', Rule::in(folder_transport_vehicle_types())],
        'transport_details.*.cost' => ['nullable', 'numeric', 'min:0'],
        'transport_details.*.margin' => ['nullable', 'numeric'],
        'transport_details.*.sell' => ['nullable', 'numeric', 'min:0'],
        'transport_details.*.sar' => ['nullable', 'numeric', 'min:0'],
    ];
}

/**
 * @return array<string, list<ValidationRule|string>>
 */
function folder_visa_details_validation_rules(): array
{
    return [
        'visa_details' => ['nullable', 'array'],
        'visa_details.*.supplier' => ['nullable', 'string', 'max:100'],
        'visa_details.*.description' => ['nullable', 'string', 'max:255'],
        'visa_details.*.cost' => ['nullable', 'numeric', 'min:0'],
        'visa_details.*.margin' => ['nullable', 'numeric'],
        'visa_details.*.sell' => ['nullable', 'numeric', 'min:0'],
    ];
}

/**
 * @return array<string, list<ValidationRule|string>>
 */
function folder_other_details_validation_rules(): array
{
    return [
        'other_details' => ['nullable', 'array'],
        'other_details.*.supplier' => ['nullable', 'string', 'max:100'],
        'other_details.*.description' => ['nullable', 'string', 'max:255'],
        'other_details.*.cost' => ['nullable', 'numeric', 'min:0'],
        'other_details.*.margin' => ['nullable', 'numeric'],
        'other_details.*.sell' => ['nullable', 'numeric', 'min:0'],
    ];
}

/**
 * @return array<string, list<ValidationRule|string>>
 */
function folder_payments_validation_rules(): array
{
    return [
        'payments' => ['nullable', 'array'],
        'payments.*.id' => ['nullable', 'integer', 'exists:folder_payments,id'],
        'payments.*.amount' => ['nullable', 'numeric', 'min:0'],
        'payments.*.reference_no' => ['nullable', 'string', 'max:100'],
        'payments.*.payment_date' => ['nullable', 'date'],
        'payments.*.mode_of_payment' => ['nullable', 'string', Rule::in(folder_payment_modes())],
        'payments.*.bank_id' => ['nullable', 'integer', 'exists:banks,id'],
        ...folder_payment_image_validation_rules(),
    ];
}

/**
 * Validation rules for optional payment receipt uploads on folder forms.
 *
 * @return array<string, list<string>>
 */
function folder_payment_image_validation_rules(): array
{
    return [
        'payments.*.image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        'payments.*.remove_image' => ['sometimes', 'boolean'],
        'payments.*.form_index' => ['nullable', 'integer', 'min:0'],
    ];
}

/**
 * @param  array<string, mixed>  $row
 */
function folder_payment_form_index(array $row, int $fallbackIndex): int
{
    if (isset($row['form_index']) && $row['form_index'] !== '') {
        return (int) $row['form_index'];
    }

    return $fallbackIndex;
}

/**
 * @param  array<string, mixed>  $row
 * @return array{file: ?UploadedFile, remove: bool}
 */
function folder_payment_image_input_from_request(
    Request $request,
    array $row,
    int $fallbackIndex,
): array {
    $formIndex = folder_payment_form_index($row, $fallbackIndex);
    $file = $request->file("payments.{$formIndex}.image");
    $remove = filter_var($row['remove_image'] ?? false, FILTER_VALIDATE_BOOLEAN);

    return ['file' => $file, 'remove' => $remove];
}

/**
 * Apply uploaded receipt image to payment attributes (create/update payloads).
 *
 * @param  array<string, mixed>  $attrs
 * @param  array<string, mixed>  $row
 * @return array<string, mixed>
 */
function folder_payment_merge_image_attributes(
    array $attrs,
    array $row,
    Request $request,
    int $fallbackIndex,
    ?FolderPayment $existing = null,
): array {
    $storage = app(FolderPaymentImageStorage::class);
    ['file' => $file, 'remove' => $remove] = folder_payment_image_input_from_request($request, $row, $fallbackIndex);

    if ($file !== null && $file->isValid()) {
        $attrs['image'] = $storage->store($file);

        return $attrs;
    }

    if ($remove) {
        $attrs['image'] = null;

        return $attrs;
    }

    if ($existing !== null) {
        unset($attrs['image']);
    }

    return $attrs;
}

/**
 * Replace unlocked payments on a folder; locked payments are never deleted or updated.
 *
 * @param  list<array<string, mixed>>  $rows
 * @return list<array{id: int}> Payment IDs in the same order as storable submitted rows.
 */
function folder_sync_folder_payments(
    Folder $folder,
    array $rows,
    string $approvalStatusForNew,
    ?Request $request = null,
): array {
    $storage = app(FolderPaymentImageStorage::class);
    $lockedPayments = folder_locked_payments_for($folder);
    $rows = folder_strip_locked_payment_rows($folder, $rows);
    $rows = folder_filter_non_empty_payment_rows($rows);

    $keptUnlockedIds = [];

    foreach ($rows as $index => $row) {
        if (! is_array($row)) {
            continue;
        }

        if (! folder_payment_row_is_storable($row)) {
            continue;
        }

        $paymentId = isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : 0;
        if ($paymentId > 0 && $lockedPayments->has($paymentId)) {
            continue;
        }

        $attrs = folder_payment_attributes_from_row($row, $approvalStatusForNew);

        if ($paymentId > 0) {
            $payment = $folder->payments()->whereKey($paymentId)->whereNull('locked_at')->first();
            if ($payment === null) {
                continue;
            }

            $attrs['approval_status'] = $payment->approval_status;
            $previousImage = $payment->image;

            if ($request !== null) {
                $attrs = folder_payment_merge_image_attributes($attrs, $row, $request, $index, $payment);
            }

            $payment->update($attrs);

            if (array_key_exists('image', $attrs) && $attrs['image'] !== $previousImage && $previousImage !== null) {
                $storage->delete($previousImage);
            }

            if (array_key_exists('image', $attrs) && $attrs['image'] === null && $previousImage !== null) {
                $storage->delete($previousImage);
            }

            $keptUnlockedIds[] = $payment->id;

            continue;
        }

        if ($request !== null) {
            $attrs = folder_payment_merge_image_attributes($attrs, $row, $request, $index);
        }

        $payment = $folder->payments()->create($attrs);
        $keptUnlockedIds[] = $payment->id;
    }

    if (! auth()->user()?->hasRole(User::ROLE_MANAGER)) {
        $folder->payments()
            ->whereNull('locked_at')
            ->when($keptUnlockedIds !== [], fn ($query) => $query->whereNotIn('id', $keptUnlockedIds))
            ->get()
            ->each(fn (FolderPayment $payment) => $payment->delete());
    }

    return array_map(
        static fn (int $id): array => ['id' => $id],
        $keptUnlockedIds,
    );
}

/**
 * Keep only other-details rows where at least one field is non-empty (trimmed strings or numeric).
 *
 * @param  array<int, mixed>|null  $rows
 * @return list<array<string, mixed>>
 */
function folder_filter_non_empty_other_detail_rows(?array $rows): array
{
    return folder_filter_rows_by_fields($rows, ['supplier', 'description', 'cost', 'margin', 'sell']);
}

/**
 * @param  array<int, mixed>|null  $rows
 * @param  list<string>  $fields
 * @return list<array<string, mixed>>
 */
function folder_filter_rows_by_fields(?array $rows, array $fields): array
{
    if (! is_array($rows)) {
        return [];
    }

    return collect($rows)
        ->filter(function ($row) use ($fields): bool {
            if (! is_array($row)) {
                return false;
            }
            foreach ($fields as $field) {
                $v = $row[$field] ?? null;
                if ($v === null) {
                    continue;
                }
                if (is_string($v) && trim($v) !== '') {
                    return true;
                }
                if (is_numeric($v)) {
                    return true;
                }
            }

            return false;
        })
        ->values()
        ->all();
}

/**
 * @param  array<int, mixed>|null  $rows
 * @return list<array<string, mixed>>
 */
function folder_filter_non_empty_itinerary_rows(?array $rows): array
{
    return folder_filter_rows_by_fields($rows, [
        'sr_no',
        'airline_code',
        'airline_number',
        'class',
        'departure_date',
        'departure_airport',
        'arrival_airport',
        'departure_time',
        'arrival_time',
        'arrival_date',
    ]);
}

/**
 * @param  array<string, mixed>  $row
 */
function folder_hotel_detail_row_is_storable(array $row): bool
{
    foreach ([
        'hotel_name',
        'guest_name',
        'supplier',
        'type',
        'meals',
        'date_in',
        'date_out',
        'supplier_ref',
        'hotel_city',
    ] as $field) {
        if (trim((string) ($row[$field] ?? '')) !== '') {
            return true;
        }
    }

    foreach (['sr_no', 'rooms', 'nights', 'cost', 'sell'] as $field) {
        $value = $row[$field] ?? null;
        if ($value !== null && $value !== '' && is_numeric($value)) {
            return true;
        }
    }

    return false;
}

/**
 * @param  array<int, mixed>|null  $rows
 * @return list<array<string, mixed>>
 */
function folder_filter_non_empty_hotel_detail_rows(?array $rows): array
{
    if (! is_array($rows)) {
        return [];
    }

    return collect($rows)
        ->filter(fn ($row): bool => is_array($row) && folder_hotel_detail_row_is_storable($row))
        ->values()
        ->all();
}

/**
 * Filter empty hotel-detail rows and normalize values for {@see FolderHotelDetail} persistence.
 *
 * @param  array<int, mixed>|null  $rows
 * @return list<array<string, mixed>>
 */
function folder_hotel_details_for_storage(?array $rows): array
{
    return collect(folder_filter_non_empty_hotel_detail_rows($rows))
        ->map(function (array $row): array {
            $numericOrNull = static function (string $key) use ($row): mixed {
                $value = $row[$key] ?? null;
                if ($value === null || $value === '') {
                    return null;
                }

                return is_numeric($value) ? $value + 0 : null;
            };

            $integerOrNull = static function (string $key) use ($row): ?int {
                $value = $row[$key] ?? null;
                if ($value === null || $value === '') {
                    return null;
                }

                return is_numeric($value) ? (int) $value : null;
            };

            $stringOrNull = static function (string $key) use ($row): ?string {
                $value = trim((string) ($row[$key] ?? ''));

                return $value !== '' ? $value : null;
            };

            return [
                'sr_no' => $integerOrNull('sr_no'),
                'supplier' => $stringOrNull('supplier'),
                'hotel_name' => $stringOrNull('hotel_name'),
                'guest_name' => $stringOrNull('guest_name'),
                'rooms' => $integerOrNull('rooms'),
                'type' => $stringOrNull('type'),
                'meals' => $stringOrNull('meals'),
                'date_in' => $stringOrNull('date_in'),
                'date_out' => $stringOrNull('date_out'),
                'nights' => $integerOrNull('nights'),
                'supplier_ref' => $stringOrNull('supplier_ref'),
                'status' => $stringOrNull('status'),
                'cost' => $numericOrNull('cost'),
                'margin' => $numericOrNull('margin'),
                'sell' => $numericOrNull('sell'),
                'hotel_city' => $stringOrNull('hotel_city'),
            ];
        })
        ->all();
}

/**
 * @param  array<int, mixed>|null  $rows
 * @return list<array<string, mixed>>
 */
function folder_filter_non_empty_transport_detail_rows(?array $rows): array
{
    return folder_filter_rows_by_fields($rows, [
        'supplier',
        'description',
        'origin',
        'destination',
        'service_date',
        'pickup_time',
        'vehicle_type',
        'cost',
        'margin',
        'sell',
        'sar',
    ]);
}

/**
 * @param  array<int, mixed>|null  $rows
 * @return list<array<string, mixed>>
 */
function folder_filter_non_empty_visa_detail_rows(?array $rows): array
{
    return folder_filter_rows_by_fields($rows, ['supplier', 'description', 'cost', 'margin', 'sell']);
}

/**
 * Filter empty other-details rows and normalize values for {@see FolderOtherDetail} persistence.
 *
 * @param  array<int, mixed>|null  $rows
 * @return list<array{supplier: string, description: string|null, cost: mixed, margin: mixed, sell: mixed}>
 */
function folder_other_details_for_storage(?array $rows): array
{
    return collect(folder_filter_non_empty_other_detail_rows($rows))
        ->map(function (array $row): array {
            $desc = $row['description'] ?? null;
            $description = null;
            if (is_string($desc) && trim($desc) !== '') {
                $description = $desc;
            }

            $numericOrNull = static function (string $key) use ($row): mixed {
                $v = $row[$key] ?? null;
                if ($v === null || $v === '') {
                    return null;
                }

                return is_numeric($v) ? $v + 0 : null;
            };

            return [
                'supplier' => trim((string) ($row['supplier'] ?? '')),
                'description' => $description,
                'cost' => $numericOrNull('cost'),
                'margin' => $numericOrNull('margin'),
                'sell' => $numericOrNull('sell'),
            ];
        })
        ->all();
}

/**
 * @param  list<array<string, mixed>>  $payments
 */
function folder_assert_payment_rows_bank_when_required(array $payments): void
{
    foreach ($payments as $i => $payment) {
        if (! is_array($payment)) {
            continue;
        }
        $mode = (string) ($payment['mode_of_payment'] ?? '');
        if (in_array($mode, ['Bank Transfer', 'Card Payment'], true) && empty($payment['bank_id'])) {
            throw ValidationException::withMessages([
                "payments.{$i}.bank_id" => __('Please select a bank for this payment mode.'),
            ]);
        }
    }
}

/**
 * @param  list<array<string, mixed>>  $rows
 * @return list<array{amount: mixed, reference_no: string|null, payment_date: mixed, mode_of_payment: string, bank_id: int|null, approval_status: string}>
 */
function folder_normalized_payments_for_storage(array $rows, string $approvalStatus = 'approved'): array
{
    return collect($rows)->map(function (array $row) use ($approvalStatus): array {
        $mode = (string) ($row['mode_of_payment'] ?? '');
        $bankId = $row['bank_id'] ?? null;
        if ($mode === 'Cash in office') {
            $bankId = null;
        } else {
            $bankId = $bankId !== '' && $bankId !== null ? (int) $bankId : null;
        }

        $ref = $row['reference_no'] ?? null;
        $referenceNo = is_string($ref) && trim($ref) !== '' ? trim($ref) : null;

        return [
            'amount' => $row['amount'],
            'reference_no' => $referenceNo,
            'payment_date' => $row['payment_date'],
            'mode_of_payment' => $mode,
            'bank_id' => $bankId,
            'approval_status' => $approvalStatus,
        ];
    })->all();
}

/**
 * Invoice date format, e.g. 6th May, 2026.
 */
function format_invoice_date(mixed $date): string
{
    if ($date === null || $date === '') {
        return '';
    }

    return Illuminate\Support\Carbon::parse($date)->format('jS F, Y');
}

/**
 * Invoice time format, e.g. 06:00 AM.
 */
function format_invoice_time(mixed $time): string
{
    if ($time === null || $time === '') {
        return '';
    }

    $value = trim((string) $time);

    if ($value === '') {
        return '';
    }

    if (preg_match('/^\d{3,4}$/', $value)) {
        $value = str_pad($value, 4, '0', STR_PAD_LEFT);
        $value = substr($value, 0, 2).':'.substr($value, 2, 2);
    }

    return Illuminate\Support\Carbon::parse($value)->format('h:i A');
}

/**
 * Blade view name for invoice terms & conditions, keyed by company name in config.
 * Falls back to Haram Travels when the company is unknown.
 */
function invoice_terms_and_conditions_view(?string $companyName): string
{
    $default = 'invoices.partials.terms-and-conditions-haram-travels';
    $views = config('invoice.terms_views', []);

    if ($companyName === null || trim($companyName) === '' || ! is_array($views)) {
        return $default;
    }

    foreach ($views as $name => $view) {
        if (strcasecmp(trim((string) $name), trim($companyName)) === 0 && is_string($view) && $view !== '') {
            return $view;
        }
    }

    return $default;
}

/**
 * Legal entity name used inside invoice terms & conditions for a company.
 */
function invoice_terms_legal_name(?string $companyName): string
{
    $default = (string) config('invoice.terms_legal_name', 'Bukhari Travel Ltd T/A Haram Travel');
    $names = config('invoice.terms_legal_names', []);

    if ($companyName === null || trim($companyName) === '' || ! is_array($names)) {
        return $default;
    }

    foreach ($names as $name => $legalName) {
        if (strcasecmp(trim((string) $name), trim($companyName)) === 0 && is_string($legalName) && trim($legalName) !== '') {
            return $legalName;
        }
    }

    return $default;
}

/**
 * Payment instructions block for an invoice, keyed by company name in config.
 *
 * @return array{intro: list<string>, bank_details: list<array{label: string, value: string}>}|null
 */
function invoice_company_payment_section(?string $companyName): ?array
{
    if ($companyName === null || trim($companyName) === '') {
        return null;
    }

    $sections = config('invoice.company_payment_sections', []);
    foreach ($sections as $name => $section) {
        if (strcasecmp(trim((string) $name), trim($companyName)) !== 0) {
            continue;
        }

        if (! is_array($section)) {
            return null;
        }

        $intro = array_values(array_filter(
            $section['intro'] ?? [],
            static fn ($line): bool => is_string($line) && trim($line) !== '',
        ));
        $bankDetails = array_values(array_filter(
            $section['bank_details'] ?? [],
            static fn ($row): bool => is_array($row)
                && trim((string) ($row['label'] ?? '')) !== '',
        ));
        $bankDetails = array_map(
            static fn (array $row): array => [
                'label' => trim((string) ($row['label'] ?? '')),
                'value' => trim((string) ($row['value'] ?? '')),
            ],
            $bankDetails,
        );

        if ($intro === [] && $bankDetails === []) {
            return null;
        }

        return [
            'intro' => $intro,
            'bank_details' => $bankDetails,
        ];
    }

    return null;
}
