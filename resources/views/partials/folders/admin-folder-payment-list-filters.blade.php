<form method="GET" action="{{ route('admin.folder-payments.index') }}" class="mt-6">
    @if ($filterFolderId ?? null)
        <input type="hidden" name="folder_id" value="{{ $filterFolderId }}">
    @endif
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
        <div>
            <label for="payment-agent-filter" class="block text-sm font-medium text-concierge-navy">Agent</label>
            <select id="payment-agent-filter" name="agent_id"
                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                <option value="">All agents</option>
                @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}" @selected((string) ($selectedAgentId ?? '') === (string) $agent->id)>
                        {{ $agent->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="payment-date-filter" class="block text-sm font-medium text-concierge-navy">Date</label>
            <div class="relative">
                <input id="payment-date-filter" name="payment_date" type="text"
                    value="{{ $selectedPaymentDate ?? '' }}" placeholder="Select date" autocomplete="off"
                    data-payment-date-picker="true"
                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 pr-10 text-sm text-slate-800 placeholder:text-slate-400 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-concierge-muted"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7V3m8 4V3m-9 8h10m-13 9h16a1 1 0 001-1V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a1 1 0 001 1z" />
                </svg>
            </div>
        </div>
        <div>
            <label for="payment-status-filter" class="block text-sm font-medium text-concierge-navy">Status</label>
            <select id="payment-status-filter" name="status"
                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                <option value="">All statuses</option>
                @foreach ($statuses as $statusKey => $statusLabel)
                    <option value="{{ $statusKey }}" @selected(($selectedStatus ?? '') === $statusKey)>
                        {{ $statusLabel }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="mt-3 flex flex-col gap-2 sm:flex-row">
        <input id="payment-search" name="search" type="search"
            placeholder="Search by customer name or reference number" value="{{ $search ?? '' }}"
            class="w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
        <div class="flex shrink-0 gap-2">
            <button type="submit"
                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-concierge-navy-deep">
                Apply
            </button>
            @if (
                ($search ?? '') !== '' ||
                    ($selectedAgentId ?? null) ||
                    ($selectedPaymentDate ?? '') !== '' ||
                    ($selectedStatus ?? '') !== '' ||
                    ($filterFolderId ?? null))
                <a href="{{ route('admin.folder-payments.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-concierge-navy transition hover:bg-slate-50">
                    Clear
                </a>
            @endif
        </div>
    </div>
</form>
