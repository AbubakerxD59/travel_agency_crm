@php
    $s = $folder->paymentSummary();
    $fmt = static fn (float $n): string => number_format($n, 2, '.', ',');
@endphp
<div class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
    <h2 class="text-base font-semibold text-concierge-navy">Payment summary</h2>
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-[640px] w-full border-collapse text-xs sm:text-sm">
            <thead>
                <tr class="bg-slate-100 text-left text-concierge-muted">
                    <th class="border border-slate-200 px-3 py-2">Total sale</th>
                    <th class="border border-slate-200 px-3 py-2">Amount paid</th>
                    <th class="border border-slate-200 px-3 py-2">Remaining amount</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-white">
                    <td class="folder-cost-text-emerald-600 border border-slate-200 px-3 py-2 text-sm font-semibold tabular-nums">
                        {{ $fmt($s['total_sale']) }}</td>
                    <td class="folder-cost-text-emerald-600 border border-slate-200 px-3 py-2 text-sm font-semibold tabular-nums">
                        {{ $fmt($s['amount_paid']) }}</td>
                    <td
                        @class([
                            'border border-slate-200 px-3 py-2 text-sm font-semibold tabular-nums',
                            'folder-cost-text-emerald-600' => $s['remaining_amount'] <= 0,
                            'text-amber-700' => $s['remaining_amount'] > 0,
                        ])>
                        {{ $fmt($s['remaining_amount']) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @if ($s['approved_payments']->isNotEmpty())
        <p class="mt-4 text-sm font-medium text-concierge-navy">Approved payments</p>
        <ol class="mt-2 list-decimal space-y-1 pl-5 text-sm text-concierge-navy">
            @foreach ($s['approved_payments'] as $payment)
                <li>
                    <span class="font-medium tabular-nums">{{ $fmt((float) ($payment->amount ?? 0)) }}</span>
                    <span class="text-concierge-muted">{{ optional($payment->payment_date)->format('Y-m-d') ?: '—' }}</span>
                </li>
            @endforeach
        </ol>
    @endif
    <p class="mt-3 text-xs text-concierge-muted">Remaining amount is total sale minus approved payments. Pending and
        rejected payments are not deducted.</p>
</div>
