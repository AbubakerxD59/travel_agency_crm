@php
    $s = $folder->costSummary();
    $fmt = static fn (float $n): string => number_format($n, 2, '.', ',');
@endphp
<div class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
    <h2 class="text-base font-semibold text-concierge-navy">Cost summary</h2>
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-[920px] w-full border-collapse text-xs sm:text-sm">
            <thead>
                <tr class="bg-slate-100 text-left text-concierge-muted">
                    <th class="border border-slate-200 px-3 py-2">Total sale</th>
                    <th class="border border-slate-200 px-3 py-2">Flight cost</th>
                    <th class="border border-slate-200 px-3 py-2">Hotel cost</th>
                    <th class="border border-slate-200 px-3 py-2">Transport cost</th>
                    <th class="border border-slate-200 px-3 py-2">Visa cost</th>
                    <th class="border border-slate-200 px-3 py-2">Others cost</th>
                    <th class="border border-slate-200 px-3 py-2">Margin</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-white">
                    <td class="folder-cost-text-emerald-600 border border-slate-200 px-3 py-2 text-sm font-semibold tabular-nums">
                        {{ $fmt($s['total_sale']) }}</td>
                    <td class="border border-slate-200 px-3 py-2 text-sm font-medium tabular-nums text-rose-600">
                        {{ $fmt($s['flight_cost']) }}</td>
                    <td class="border border-slate-200 px-3 py-2 text-sm font-medium tabular-nums text-rose-600">
                        {{ $fmt($s['hotel_cost']) }}</td>
                    <td class="border border-slate-200 px-3 py-2 text-sm font-medium tabular-nums text-rose-600">
                        {{ $fmt($s['transport_cost']) }}</td>
                    <td class="border border-slate-200 px-3 py-2 text-sm font-medium tabular-nums text-rose-600">
                        {{ $fmt($s['visa_cost']) }}</td>
                    <td class="border border-slate-200 px-3 py-2 text-sm font-medium tabular-nums text-rose-600">
                        {{ $fmt($s['others_cost']) }}</td>
                    <td
                        @class([
                            'border border-slate-200 px-3 py-2 text-sm font-semibold tabular-nums',
                            'folder-cost-text-emerald-600' => $s['margin'] > 0,
                            'text-rose-600' => $s['margin'] <= 0,
                        ])>
                        {{ $fmt($s['margin']) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
