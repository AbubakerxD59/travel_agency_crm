@extends('layouts.agent')

@section('title', 'Lead #' . $lead->id)

@section('content')
    <div class="mx-auto max-w-7xl">
        <div
            class="rounded-2xl border border-slate-200/70 bg-gradient-to-r from-white via-slate-50/60 to-white p-6 shadow-sm lg:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-concierge-muted">Lead Details</p>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Lead #{{ $lead->id }}</h1>
                        <span data-lead-status-pill="{{ $lead->id }}"
                            class="concierge-pill concierge-pill-{{ $lead->statusPillClass() }}">{{ $lead->statusLabel() }}</span>
                    </div>
                    <p class="mt-1 text-sm text-concierge-muted">Contact and assignment details for this lead.</p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <a href="{{ route('agent.leads.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-concierge-navy shadow-sm transition hover:bg-slate-50">
                        Back to leads
                    </a>
                    <div class="relative">
                        <button type="button" data-status-toggle data-lead-id="{{ $lead->id }}"
                            data-current-status="{{ $lead->status }}"
                            data-status-url="{{ route('agent.leads.status', $lead) }}"
                            class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-concierge-navy-deep"
                            aria-haspopup="true" aria-expanded="false" aria-controls="lead-status-dropdown">
                            Update status
                        </button>
                        <div id="lead-status-dropdown"
                            class="absolute right-0 top-full z-20 mt-2 hidden w-44 rounded-xl border border-slate-200 bg-white p-2 text-left shadow-xl view-update-status-dropdown">
                            <p class="px-2 pb-2 text-xs font-semibold uppercase tracking-wide text-concierge-muted">
                                Update status
                            </p>
                            @foreach ($statuses as $statusKey => $statusLabel)
                                <button type="button" data-status-option data-lead-id="{{ $lead->id }}"
                                    data-status="{{ $statusKey }}"
                                    class="mb-1 block w-full rounded-lg px-2 py-1.5 text-left text-sm text-concierge-navy hover:bg-slate-100 {{ $lead->status === $statusKey ? 'bg-slate-100 font-semibold' : '' }}">
                                    {{ $statusLabel }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-concierge-navy">Overview</h2>
            <dl class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Agent</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->agent?->name ?? 'Unassigned' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Customer name</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->customer_name ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Phone number</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->phone_number ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Email</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->email ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Company</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->company?->name ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">City</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->city ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Source</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->source ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Status</dt>
                    <dd class="mt-1">
                        <span data-lead-status-pill="{{ $lead->id }}"
                            class="concierge-pill concierge-pill-{{ $lead->statusPillClass() }}">{{ $lead->statusLabel() }}</span>
                    </dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Created</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">
                        {{ $lead->created_at?->format('M j, Y g:i A') ?? '—' }}
                    </dd>
                </div>
            </dl>

            <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                <dt class="text-xs uppercase tracking-wide text-concierge-muted">Notes</dt>
                <dd class="mt-1 whitespace-pre-wrap text-sm font-medium text-concierge-navy">{{ $lead->notes ?? '—' }}</dd>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
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

            const leadId = "{{ $lead->id }}";
            const dropdown = document.getElementById('lead-status-dropdown');
            const toggleButton = document.querySelector('[data-status-toggle]');
            if (!dropdown || !toggleButton) {
                return;
            }

            function closeDropdown() {
                dropdown.classList.add('hidden');
                toggleButton.setAttribute('aria-expanded', 'false');
            }

            function openDropdown() {
                dropdown.classList.remove('hidden');
                toggleButton.setAttribute('aria-expanded', 'true');
            }

            function setStatusSelection(status) {
                dropdown.querySelectorAll('[data-status-option]').forEach((button) => {
                    const isSelected = button.getAttribute('data-status') === status;
                    button.classList.toggle('bg-slate-100', isSelected);
                    button.classList.toggle('font-semibold', isSelected);
                });
            }

            async function updateLeadStatus(url, status) {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken ?? '',
                    },
                    body: JSON.stringify({
                        status,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Failed to update lead status.');
                }

                return response.json();
            }

            document.addEventListener('click', async (event) => {
                const clickedToggle = event.target.closest('[data-status-toggle]');
                const statusOptionButton = event.target.closest('[data-status-option]');

                if (clickedToggle) {
                    if (dropdown.classList.contains('hidden')) {
                        openDropdown();
                    } else {
                        closeDropdown();
                    }
                    return;
                }

                if (!statusOptionButton) {
                    if (!dropdown.classList.contains('hidden') && !dropdown.contains(event.target)) {
                        closeDropdown();
                    }
                    return;
                }

                const status = statusOptionButton.getAttribute('data-status');
                const url = toggleButton.getAttribute('data-status-url');
                if (!status || !url) {
                    closeDropdown();
                    return;
                }

                statusOptionButton.disabled = true;
                statusOptionButton.classList.add('opacity-60');

                try {
                    const payload = await updateLeadStatus(url, status);
                    document.querySelectorAll(`[data-lead-status-pill="${leadId}"]`).forEach((
                    statusPill) => {
                        statusPill.textContent = payload.status_label ?? status;
                        statusPill.className =
                            `concierge-pill concierge-pill-${payload.status_pill_class ?? 'meta'}`;
                    });
                    toggleButton.setAttribute('data-current-status', payload.status ?? status);
                    setStatusSelection(payload.status ?? status);
                    toastSuccess(payload.message ?? 'Lead status updated successfully.');
                } catch (error) {
                    console.log(error);
                    alert('Could not update status. Please try again.');
                } finally {
                    statusOptionButton.disabled = false;
                    statusOptionButton.classList.remove('opacity-60');
                    closeDropdown();
                }
            });
        })();
    </script>
@endpush
