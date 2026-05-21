<?php

declare(strict_types=1);

use App\Models\Folder;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

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
 *
 * @return array{
 *     labels: list<string>,
 *     datasets: list<array{key: string, label: string, color: string, data: list<int>}>,
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

    if ($sourceFilter !== null && $sourceFilter !== '') {
        $query->where('source', $sourceFilter);
    } elseif ($restrictToCatalog) {
        $query->whereIn('source', array_keys($sourceCatalog));
    }

    if ($agentId !== null) {
        $query->where('agent_id', $agentId);
    }

    if ($companyId !== null) {
        $query->where('company_id', $companyId);
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
        ];
    }

    return [
        'labels' => $labels,
        'datasets' => $datasets,
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
        } catch (\Throwable) {
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
 * Tailwind classes for a folder list table row (incomplete bookings use error styling).
 */
function folder_list_row_class(Folder $folder): string
{
    $isIncomplete = (bool) ($folder->is_incomplete_booking ?? false);

    return $isIncomplete
        ? 'bg-rose-50 hover:bg-rose-100/70'
        : 'hover:bg-slate-50/50';
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
 * @param  array<int, mixed>|null  $rows
 * @return list<array<string, mixed>>
 */
function folder_filter_non_empty_hotel_detail_rows(?array $rows): array
{
    return folder_filter_rows_by_fields($rows, [
        'sr_no',
        'supplier',
        'hotel_name',
        'guest_name',
        'rooms',
        'type',
        'meals',
        'date_in',
        'date_out',
        'nights',
        'supplier_ref',
        'status',
        'cost',
        'margin',
        'sell',
        'hotel_city',
    ]);
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

    return \Illuminate\Support\Carbon::parse($date)->format('jS F, Y');
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

    return \Illuminate\Support\Carbon::parse($value)->format('h:i A');
}
