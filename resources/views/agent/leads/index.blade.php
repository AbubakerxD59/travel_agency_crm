@extends('layouts.agent')

@section('title', 'Lead Management')

@section('content')
    <div class="mx-auto max-w-8xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Lead Management</h1>
                <p class="mt-1 max-w-2xl text-sm text-concierge-muted">Umrah and Hajj bookings: assignment, invoice number,
                    travel dates, and ziarat options.</p>
            </div>
            @if ($canCreateLeads)
                <button type="button" id="open-new-lead-modal"
                    class="inline-flex shrink-0 cursor-pointer items-center justify-center gap-2 rounded-xl bg-concierge-navy px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 transition hover:bg-concierge-navy-deep">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New Lead
                </button>
            @endif
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        @include('partials.leads.closed-leads-chart', [
            'chartEndpoint' => route('agent.leads.chart.closed'),
            'closedLeadsChart' => $closedLeadsChart,
            'chartDateLabel' => $chartDateLabel,
            'chartDateRange' => $chartDateRange,
            'chartStartDate' => $chartStartDate,
            'chartEndDate' => $chartEndDate,
            'chartSource' => $chartSource,
            'sourceOptions' => $agentSourceOptions,
        ])

        <form method="GET" action="{{ route('agent.leads.index') }}" class="mt-6">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                    <label for="lead-status-filter" class="block text-sm font-medium text-concierge-navy">Status</label>
                    <select id="lead-status-filter" name="status"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $statusKey => $statusLabel)
                            <option value="{{ $statusKey }}" @selected($selectedStatus === $statusKey)>
                                {{ $statusLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-1.5 flex flex-col gap-2 sm:flex-row">
                <input id="lead-search" name="search" type="search"
                    placeholder="Search by customer name, phone number, or email" value="{{ $search }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                <div class="flex shrink-0 gap-2">
                    <button type="submit"
                        class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-concierge-navy-deep">
                        Apply
                    </button>
                    @if ($search !== '' || $selectedStatus !== '')
                        <a href="{{ route('agent.leads.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-concierge-navy transition hover:bg-slate-50">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-slate-100 bg-slate-50/80 text-xs font-semibold uppercase tracking-wide text-concierge-muted">
                            <th class="px-4 py-4 lg:px-6">Customer name</th>
                            <th class="px-4 py-4 lg:px-6">Phone number</th>
                            <th class="px-4 py-4 lg:px-6">Company name</th>
                            <th class="px-4 py-4 lg:px-6">City</th>
                            <th class="px-4 py-4 lg:px-6">Travel date</th>
                            <th class="px-4 py-4 lg:px-6">Status</th>
                            <th class="px-4 py-4 text-right lg:px-6">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($leads as $lead)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-4 text-concierge-navy lg:px-6">{{ $lead->customer_name ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-muted lg:px-6">{{ $lead->phone_number ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-navy lg:px-6">{{ $lead->company?->name ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-muted lg:px-6">{{ $lead->city ?? '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-concierge-muted lg:px-6">
                                    {{ $lead->travel_date?->format('M j, Y') }}</td>
                                <td class="whitespace-nowrap px-4 py-4 lg:px-6">
                                    <span data-lead-status-pill="{{ $lead->id }}"
                                        class="concierge-pill concierge-pill-{{ $lead->statusPillClass() }}">{{ $lead->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-4 text-right lg:px-6">
                                    <div class="inline-flex items-center justify-end gap-1 whitespace-nowrap">
                                        <button type="button"
                                            data-status-toggle
                                            data-lead-id="{{ $lead->id }}"
                                            data-current-status="{{ $lead->status }}"
                                            data-status-url="{{ route('agent.leads.status', $lead) }}"
                                            class="lead-row-action inline-flex cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-concierge-navy"
                                            title="Update status" aria-label="Update status"
                                            aria-haspopup="true" aria-expanded="false"
                                            aria-controls="lead-status-dropdown">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                                aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                            </svg>
                                        </button>
                                        <a href="{{ route('agent.leads.show', $lead) }}"
                                            class="lead-row-action inline-flex cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-concierge-accent"
                                            title="View" aria-label="View">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                                aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-concierge-muted">
                                    No leads to show.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($leads->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $leads->links() }}
                </div>
            @endif
        </div>

        <div id="lead-status-dropdown"
            class="lead-status-dropdown-portal fixed z-[60] hidden w-44 rounded-xl border border-slate-200 bg-white p-2 text-left shadow-xl ring-1 ring-black/5"
            role="menu" aria-labelledby="lead-status-dropdown-title" hidden>
            <p id="lead-status-dropdown-title"
                class="px-2 pb-2 text-xs font-semibold uppercase tracking-wide text-concierge-muted">
                Update status
            </p>
            <div id="lead-status-dropdown-options"></div>
        </div>

        <div id="not-converted-modal"
            class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/40 p-4"
            aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="not-converted-modal-title">
            <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-xl">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 id="not-converted-modal-title" class="text-lg font-semibold text-concierge-navy">Not converted
                    </h2>
                    <p class="mt-1 text-sm text-concierge-muted">Please provide a reason. This is saved with the lead.
                    </p>
                </div>
                <div class="space-y-4 px-6 py-5">
                    <div>
                        <label for="not-converted-reason-input" class="block text-sm font-medium text-concierge-navy">
                            Reason <span class="text-rose-600">*</span>
                        </label>
                        <textarea id="not-converted-reason-input" rows="4" maxlength="1000"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-concierge-navy placeholder:text-slate-400 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20"
                            placeholder="e.g. Customer chose another agency, budget constraints…"></textarea>
                    </div>
                    <p id="not-converted-modal-error" class="hidden text-sm text-rose-600" role="alert"></p>
                </div>
                <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-6 py-4 sm:flex-row sm:justify-end">
                    <button type="button" id="not-converted-modal-cancel"
                        class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-concierge-navy transition hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="button" id="not-converted-modal-submit"
                        class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-concierge-navy-deep">
                        Update status
                    </button>
                </div>
            </div>
        </div>

        @if ($canCreateLeads)
            <div id="new-lead-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4"
                aria-hidden="true" role="dialog" aria-labelledby="new-lead-modal-title">
                <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-xl">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                        <h2 id="new-lead-modal-title" class="text-lg font-semibold text-concierge-navy">New Lead</h2>
                        <button type="button" data-close-new-lead-modal
                            class="rounded-lg p-2 text-concierge-muted hover:bg-slate-100 hover:text-concierge-navy"
                            aria-label="Close">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form id="new-lead-form" method="POST" action="{{ route('agent.leads.store') }}"
                        class="space-y-4 px-6 py-5">
                        @csrf
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="new_lead_customer_name"
                                    class="block text-sm font-medium text-concierge-navy">Customer Name <span
                                        class="text-rose-600">*</span></label>
                                <input id="new_lead_customer_name" name="customer_name" type="text" required
                                    value="{{ old('customer_name') }}"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                            </div>
                            <div>
                                <label for="new_lead_phone_number" class="block text-sm font-medium text-concierge-navy">Phone
                                    Number <span class="text-rose-600">*</span></label>
                                <input id="new_lead_phone_number" name="phone_number" type="text" required
                                    value="{{ old('phone_number') }}"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                            </div>
                            <div>
                                <label for="new_lead_email" class="block text-sm font-medium text-concierge-navy">Email</label>
                                <input id="new_lead_email" name="email" type="email" value="{{ old('email') }}"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                            </div>
                            <div>
                                <label for="new_lead_company_id" class="block text-sm font-medium text-concierge-navy">Company
                                    Name</label>
                                <select id="new_lead_company_id" name="company_id"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                                    <option value="">Select company</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}" @selected((string) old('company_id') === (string) $company->id)>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="new_lead_city" class="block text-sm font-medium text-concierge-navy">City</label>
                                <input id="new_lead_city" name="city" type="text" value="{{ old('city') }}"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                            </div>
                            <div>
                                <label for="new_lead_total_passengers" class="block text-sm font-medium text-concierge-navy">Total
                                    Passengers</label>
                                <input id="new_lead_total_passengers" name="total_passengers" type="number" min="1" max="500"
                                    step="1" value="{{ old('total_passengers') }}" placeholder="e.g. 4"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                            </div>
                            <div>
                                <label for="new_lead_source" class="block text-sm font-medium text-concierge-navy">Source</label>
                                <select id="new_lead_source" name="source"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                                    <option value="">Select source</option>
                                    @foreach (getAgentLeadSources() as $sourceKey => $sourceLabelOption)
                                        <option value="{{ $sourceKey }}" @selected(old('source') === $sourceKey)>
                                            {{ $sourceLabelOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="new_lead_notes" class="block text-sm font-medium text-concierge-navy">Notes</label>
                            <textarea id="new_lead_notes" name="notes" rows="4"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">{{ old('notes') }}</textarea>
                        </div>

                        @if ($errors->any())
                            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <div class="flex gap-3 pt-2">
                            <button type="button" data-close-new-lead-modal
                                class="flex-1 cursor-pointer rounded-xl border border-slate-200 py-2.5 text-sm font-medium text-concierge-navy hover:bg-slate-50">
                                Cancel
                            </button>
                            <button type="submit" id="new-lead-submit-btn"
                                class="flex-1 cursor-pointer rounded-xl bg-concierge-navy py-2.5 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 hover:bg-concierge-navy-deep">
                                Send Lead
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/leads-closed-chart.js'])
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        (() => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!document.getElementById('toastr-css-cdn')) {
                const link = document.createElement('link');
                link.id = 'toastr-css-cdn';
                link.rel = 'stylesheet';
                link.href = 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css';
                document.head.appendChild(link);
            }

            if (window.toastr) {
                window.toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 3500,
                    extendedTimeOut: 1500,
                };
            }

            function toastSuccess(message) {
                if (window.toastr) {
                    window.toastr.success(message);
                    return;
                }

                alert(message);
            }

            function toastError(message) {
                if (window.toastr) {
                    window.toastr.error(message);
                    return;
                }

                alert(message);
            }

            const STATUS_NOT_CONVERTED = 'not_converted';
            const notConvertedModal = document.getElementById('not-converted-modal');
            const notConvertedReasonInput = document.getElementById('not-converted-reason-input');
            const notConvertedModalError = document.getElementById('not-converted-modal-error');
            const notConvertedModalCancel = document.getElementById('not-converted-modal-cancel');
            const notConvertedModalSubmit = document.getElementById('not-converted-modal-submit');

            let notConvertedPendingUrl = '';
            let notConvertedPendingLeadId = '';

            const statusDropdown = document.getElementById('lead-status-dropdown');
            const statusDropdownOptions = document.getElementById('lead-status-dropdown-options');
            const statusLabels = @json($statuses);
            let activeStatusToggle = null;

            function closeStatusDropdown() {
                if (!statusDropdown) {
                    return;
                }
                statusDropdown.classList.add('hidden');
                statusDropdown.hidden = true;
                activeStatusToggle?.setAttribute('aria-expanded', 'false');
                activeStatusToggle = null;
            }

            function renderStatusOptions(leadId, currentStatus) {
                if (!statusDropdownOptions) {
                    return;
                }

                statusDropdownOptions.replaceChildren();
                Object.entries(statusLabels).forEach(([statusKey, statusLabel]) => {
                    const option = document.createElement('button');
                    option.type = 'button';
                    option.dataset.statusOption = '';
                    option.dataset.leadId = String(leadId);
                    option.dataset.status = statusKey;
                    option.className =
                        'mb-1 block w-full cursor-pointer rounded-lg px-2 py-1.5 text-left text-sm text-concierge-navy hover:bg-slate-100';
                    option.textContent = statusLabel;
                    if (statusKey === currentStatus) {
                        option.classList.add('bg-slate-100', 'font-semibold');
                    }
                    statusDropdownOptions.appendChild(option);
                });
            }

            function positionStatusDropdown(anchor) {
                if (!statusDropdown || !anchor) {
                    return;
                }

                statusDropdown.classList.remove('hidden');
                statusDropdown.hidden = false;

                const rect = anchor.getBoundingClientRect();
                const dropdownWidth = statusDropdown.offsetWidth;
                const dropdownHeight = statusDropdown.offsetHeight;
                const margin = 8;
                const viewportPadding = 8;

                let left = rect.right - dropdownWidth;
                left = Math.max(viewportPadding, Math.min(left, window.innerWidth - dropdownWidth - viewportPadding));

                const spaceBelow = window.innerHeight - rect.bottom;
                const openAbove = spaceBelow < dropdownHeight + margin && rect.top > dropdownHeight + margin;
                const top = openAbove ? rect.top - dropdownHeight - margin : rect.bottom + margin;

                statusDropdown.style.left = `${left}px`;
                statusDropdown.style.top = `${Math.max(viewportPadding, top)}px`;
            }

            function openStatusDropdown(toggle) {
                if (!statusDropdown) {
                    return;
                }

                const leadId = toggle.getAttribute('data-lead-id');
                const currentStatus = toggle.getAttribute('data-current-status') ?? '';
                if (!leadId) {
                    return;
                }

                renderStatusOptions(leadId, currentStatus);
                activeStatusToggle = toggle;
                toggle.setAttribute('aria-expanded', 'true');
                positionStatusDropdown(toggle);
                requestAnimationFrame(() => positionStatusDropdown(toggle));
            }

            function hideNotConvertedModalError() {
                if (!notConvertedModalError) {
                    return;
                }
                notConvertedModalError.classList.add('hidden');
                notConvertedModalError.textContent = '';
            }

            function showNotConvertedModalError(message) {
                if (!notConvertedModalError) {
                    return;
                }
                notConvertedModalError.textContent = message;
                notConvertedModalError.classList.remove('hidden');
            }

            function openNotConvertedModal(url, leadId) {
                if (!notConvertedModal || !notConvertedReasonInput) {
                    return;
                }
                notConvertedPendingUrl = url;
                notConvertedPendingLeadId = leadId;
                notConvertedReasonInput.value = '';
                hideNotConvertedModalError();
                notConvertedModal.classList.remove('hidden');
                notConvertedModal.classList.add('flex');
                notConvertedModal.setAttribute('aria-hidden', 'false');
                notConvertedReasonInput.focus();
            }

            function closeNotConvertedModal() {
                if (!notConvertedModal || !notConvertedReasonInput) {
                    return;
                }
                notConvertedModal.classList.add('hidden');
                notConvertedModal.classList.remove('flex');
                notConvertedModal.setAttribute('aria-hidden', 'true');
                notConvertedPendingUrl = '';
                notConvertedPendingLeadId = '';
                notConvertedReasonInput.value = '';
                hideNotConvertedModalError();
            }

            async function updateLeadStatus(url, status, notConvertedReason = null) {
                const body = {
                    status,
                };
                if (status === STATUS_NOT_CONVERTED) {
                    body.not_converted_reason = notConvertedReason;
                }

                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken ?? '',
                    },
                    body: JSON.stringify(body),
                });

                if (!response.ok) {
                    let message = 'Failed to update lead status.';
                    try {
                        const errorPayload = await response.json();
                        if (errorPayload?.errors?.not_converted_reason?.[0]) {
                            message = errorPayload.errors.not_converted_reason[0];
                        } else if (errorPayload?.message) {
                            message = errorPayload.message;
                        }
                    } catch (error) {
                        console.log(error);
                    }
                    throw new Error(message);
                }

                return response.json();
            }

            function applyStatusUpdatePayload(leadId, payload, status) {
                const statusPill = document.querySelector(`[data-lead-status-pill="${leadId}"]`);
                if (statusPill) {
                    statusPill.textContent = payload.status_label ?? status;
                    statusPill.className = `concierge-pill concierge-pill-${payload.status_pill_class ?? 'meta'}`;
                }
                const toggle = document.querySelector(`[data-status-toggle][data-lead-id="${leadId}"]`);
                if (toggle) {
                    toggle.setAttribute('data-current-status', payload.status ?? status);
                }
                toastSuccess(payload.message ?? 'Lead status updated successfully.');
            }

            notConvertedModalCancel?.addEventListener('click', () => {
                closeNotConvertedModal();
            });

            notConvertedReasonInput?.addEventListener('input', () => {
                hideNotConvertedModalError();
            });

            notConvertedModalSubmit?.addEventListener('click', async () => {
                const reason = (notConvertedReasonInput?.value ?? '').trim();
                if (!reason) {
                    showNotConvertedModalError('Please enter a reason.');
                    return;
                }
                hideNotConvertedModalError();
                const url = notConvertedPendingUrl;
                const leadId = notConvertedPendingLeadId;
                if (!url || !leadId) {
                    showNotConvertedModalError('Something went wrong. Please try again.');
                    return;
                }

                notConvertedModalSubmit.disabled = true;
                try {
                    const payload = await updateLeadStatus(url, STATUS_NOT_CONVERTED, reason);
                    applyStatusUpdatePayload(leadId, payload, STATUS_NOT_CONVERTED);
                    closeNotConvertedModal();
                } catch (error) {
                    console.log(error);
                    showNotConvertedModalError(
                        error instanceof Error ? error.message : 'Could not update status. Please try again.');
                } finally {
                    notConvertedModalSubmit.disabled = false;
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') {
                    return;
                }
                if (notConvertedModal && !notConvertedModal.classList.contains('hidden')) {
                    closeNotConvertedModal();
                    return;
                }
                closeStatusDropdown();
            });

            window.addEventListener('resize', closeStatusDropdown);
            window.addEventListener('scroll', closeStatusDropdown, true);

            document.addEventListener('click', async (event) => {
                const toggleButton = event.target.closest('[data-status-toggle]');
                const statusOptionButton = event.target.closest('[data-status-option]');

                if (statusOptionButton) {
                    const leadId = statusOptionButton.getAttribute('data-lead-id');
                    const status = statusOptionButton.getAttribute('data-status');
                    const toggle = document.querySelector(`[data-status-toggle][data-lead-id="${leadId}"]`);
                    const url = toggle?.getAttribute('data-status-url');
                    if (!leadId || !status || !url) {
                        closeStatusDropdown();
                        return;
                    }

                    if (status === STATUS_NOT_CONVERTED) {
                        closeStatusDropdown();
                        openNotConvertedModal(url, leadId);
                        return;
                    }

                    statusOptionButton.disabled = true;
                    statusOptionButton.classList.add('opacity-60');

                    try {
                        const payload = await updateLeadStatus(url, status);
                        applyStatusUpdatePayload(leadId, payload, status);
                    } catch (error) {
                        console.log(error);
                        toastError(
                            error instanceof Error ? error.message : 'Could not update status. Please try again.',
                        );
                    } finally {
                        statusOptionButton.disabled = false;
                        statusOptionButton.classList.remove('opacity-60');
                        closeStatusDropdown();
                    }

                    return;
                }

                if (toggleButton) {
                    if (notConvertedModal && !notConvertedModal.classList.contains('hidden')) {
                        closeNotConvertedModal();
                    }

                    const isSameToggle = activeStatusToggle === toggleButton;
                    const isOpen = statusDropdown && !statusDropdown.classList.contains('hidden');
                    if (isOpen && isSameToggle) {
                        closeStatusDropdown();
                        return;
                    }

                    closeStatusDropdown();
                    openStatusDropdown(toggleButton);
                    return;
                }

                if (statusDropdown?.contains(event.target)) {
                    return;
                }

                if (notConvertedModal && !notConvertedModal.classList.contains('hidden')
                    && notConvertedModal.contains(event.target)) {
                    return;
                }

                closeStatusDropdown();
            });
        })();
    </script>
    @if ($canCreateLeads)
        <script>
            const newLeadModal = document.getElementById('new-lead-modal');
            const openNewLeadModalBtn = document.getElementById('open-new-lead-modal');
            const newLeadForm = document.getElementById('new-lead-form');
            const newLeadSubmitBtn = document.getElementById('new-lead-submit-btn');

            function setNewLeadButtonLoading(button, isLoading) {
                if (!(button instanceof HTMLButtonElement)) {
                    return;
                }

                if (isLoading) {
                    if (button.dataset.loading === '1') {
                        return;
                    }
                    button.dataset.loading = '1';
                    button.dataset.originalHtml = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML =
                        '<span class="inline-flex items-center justify-center gap-1" aria-hidden="true"><span class="loading-dot"></span><span class="loading-dot"></span><span class="loading-dot"></span></span>';
                    return;
                }

                if (button.dataset.originalHtml) {
                    button.innerHTML = button.dataset.originalHtml;
                }
                delete button.dataset.originalHtml;
                delete button.dataset.loading;
                button.disabled = false;
            }

            function openNewLeadModal() {
                if (!newLeadModal) {
                    return;
                }
                newLeadModal.classList.remove('hidden');
                newLeadModal.classList.add('flex');
                newLeadModal.setAttribute('aria-hidden', 'false');
            }

            function closeNewLeadModal() {
                if (!newLeadModal) {
                    return;
                }
                newLeadModal.classList.add('hidden');
                newLeadModal.classList.remove('flex');
                newLeadModal.setAttribute('aria-hidden', 'true');
                setNewLeadButtonLoading(newLeadSubmitBtn, false);
            }

            openNewLeadModalBtn?.addEventListener('click', () => {
                newLeadForm?.reset();
                setNewLeadButtonLoading(newLeadSubmitBtn, false);
                openNewLeadModal();
            });

            const shouldOpenNewLeadFromQuery = new URLSearchParams(window.location.search).get('openNewLead') === '1';
            if (shouldOpenNewLeadFromQuery) {
                openNewLeadModal();
                const cleanUrl = new URL(window.location.href);
                cleanUrl.searchParams.delete('openNewLead');
                window.history.replaceState({}, '', cleanUrl.toString());
            }

            document.querySelectorAll('[data-close-new-lead-modal]').forEach((btn) => {
                btn.addEventListener('click', closeNewLeadModal);
            });

            @if ($errors->any())
                openNewLeadModal();
            @endif

            newLeadForm?.addEventListener('submit', () => {
                setNewLeadButtonLoading(newLeadSubmitBtn, true);
            });
        </script>
    @endif
@endpush
