<?php

declare(strict_types=1);

use Illuminate\Validation\ValidationException;

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
            foreach (['amount', 'payment_date', 'mode_of_payment', 'bank_id'] as $field) {
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
 * @return list<array{amount: mixed, payment_date: mixed, mode_of_payment: string, bank_id: int|null}>
 */
function folder_normalized_payments_for_storage(array $rows): array
{
    return collect($rows)->map(function (array $row): array {
        $mode = (string) ($row['mode_of_payment'] ?? '');
        $bankId = $row['bank_id'] ?? null;
        if ($mode === 'Cash in office') {
            $bankId = null;
        } else {
            $bankId = $bankId !== '' && $bankId !== null ? (int) $bankId : null;
        }

        return [
            'amount' => $row['amount'],
            'payment_date' => $row['payment_date'],
            'mode_of_payment' => $mode,
            'bank_id' => $bankId,
        ];
    })->all();
}
