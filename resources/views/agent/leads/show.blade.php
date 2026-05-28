@extends('layouts.agent')

@section('title', 'Lead #' . $lead->id)

@section('content')
    <div class="mx-auto max-w-8xl">
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
                            class="absolute right-0 top-full z-20 mt-2 hidden w-44 rounded-xl border border-slate-200 bg-white p-2 text-left shadow-xl">
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
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ lead_agent_display_name($lead) }}</dd>
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
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Total passengers</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">
                        {{ $lead->total_passengers !== null ? number_format((int) $lead->total_passengers) : '—' }}
                    </dd>
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

            @if ($lead->status === \App\Models\Lead::STATUS_NOT_CONVERTED)
                <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Not converted reason</dt>
                    <dd class="mt-1 whitespace-pre-wrap text-sm font-medium text-concierge-navy" data-not-converted-reason>{{ $lead->not_converted_reason ?? '—' }}</dd>
                </div>
            @endif
        </div>

        <div id="not-converted-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/40 p-4"
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

            function toastError(message) {
                if (window.toastr) {
                    window.toastr.error(message);
                    return;
                }
                alert(message);
            }

            const STATUS_NOT_CONVERTED = 'not_converted';
            const leadId = "{{ $lead->id }}";
            const dropdown = document.getElementById('lead-status-dropdown');
            const toggleButton = document.querySelector('[data-status-toggle]');
            const notConvertedModal = document.getElementById('not-converted-modal');
            const notConvertedReasonInput = document.getElementById('not-converted-reason-input');
            const notConvertedModalError = document.getElementById('not-converted-modal-error');
            const notConvertedModalCancel = document.getElementById('not-converted-modal-cancel');
            const notConvertedModalSubmit = document.getElementById('not-converted-modal-submit');

            let notConvertedPendingUrl = '';

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

            function openNotConvertedModal(url) {
                if (!notConvertedModal || !notConvertedReasonInput) {
                    return;
                }
                notConvertedPendingUrl = url;
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

            function applyStatusUpdatePayload(payload, status) {
                document.querySelectorAll(`[data-lead-status-pill="${leadId}"]`).forEach((statusPill) => {
                    statusPill.textContent = payload.status_label ?? status;
                    statusPill.className =
                        `concierge-pill concierge-pill-${payload.status_pill_class ?? 'meta'}`;
                });
                toggleButton.setAttribute('data-current-status', payload.status ?? status);
                setStatusSelection(payload.status ?? status);
                const notConvertedReasonElement = document.querySelector('[data-not-converted-reason]');
                if (notConvertedReasonElement) {
                    notConvertedReasonElement.textContent = payload.status === STATUS_NOT_CONVERTED ?
                        (payload.not_converted_reason || '—') :
                        '—';
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
                if (!url) {
                    showNotConvertedModalError('Something went wrong. Please try again.');
                    return;
                }

                notConvertedModalSubmit.disabled = true;
                try {
                    const payload = await updateLeadStatus(url, STATUS_NOT_CONVERTED, reason);
                    applyStatusUpdatePayload(payload, STATUS_NOT_CONVERTED);
                    closeNotConvertedModal();
                } catch (error) {
                    console.log(error);
                    showNotConvertedModalError(
                        error instanceof Error ? error.message :
                        'Could not update status. Please try again.');
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
                }
            });

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

                if (status === STATUS_NOT_CONVERTED) {
                    closeDropdown();
                    openNotConvertedModal(url);
                    return;
                }

                statusOptionButton.disabled = true;
                statusOptionButton.classList.add('opacity-60');

                try {
                    const payload = await updateLeadStatus(url, status);
                    applyStatusUpdatePayload(payload, status);
                } catch (error) {
                    console.log(error);
                    toastError(error instanceof Error ? error.message :
                        'Could not update status. Please try again.');
                } finally {
                    statusOptionButton.disabled = false;
                    statusOptionButton.classList.remove('opacity-60');
                    closeDropdown();
                }
            });
        })();
    </script>
@endpush
