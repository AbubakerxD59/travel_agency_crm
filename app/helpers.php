<?php

declare(strict_types=1);

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
        'direct_whatsapp_chat' => 'Direct WhatsApp Chat',
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

    $sources = getSources();

    return $sources[$key] ?? match ($key) {
        'whatsapp' => 'Direct WhatsApp Chat',
        default => $key,
    };
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

    return collect($rows)
        ->filter(function ($row): bool {
            if (! is_array($row)) {
                return false;
            }
            foreach (['amount', 'reference_no', 'payment_date', 'mode_of_payment', 'bank_id'] as $field) {
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
 * Keep only other-details rows where at least one field is non-empty (trimmed strings or numeric).
 *
 * @param  array<int, mixed>|null  $rows
 * @return list<array<string, mixed>>
 */
function folder_filter_non_empty_other_detail_rows(?array $rows): array
{
    if (! is_array($rows)) {
        return [];
    }

    return collect($rows)
        ->filter(function ($row): bool {
            if (! is_array($row)) {
                return false;
            }
            foreach (['supplier', 'description', 'cost', 'margin', 'sell'] as $field) {
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
