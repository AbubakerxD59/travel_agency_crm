@extends('layouts.agent')

@section('title', 'Lead Management')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Lead Management</h1>
                <p class="mt-1 max-w-2xl text-sm text-concierge-muted">Umrah and Hajj bookings: assignment, vendor reference,
                    travel dates, and ziarat options.</p>
            </div>

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

        <form method="GET" action="{{ route('agent.leads.index') }}" class="mt-6">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <div>
                    <label for="lead-company-filter" class="block text-sm font-medium text-concierge-navy">Company</label>
                    <select id="lead-company-filter" name="company_id"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        <option value="">All companies</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected((string) $selectedCompanyId === (string) $company->id)>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="lead-source-filter" class="block text-sm font-medium text-concierge-navy">Source</label>
                    <select id="lead-source-filter" name="source"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        <option value="">All sources</option>
                        @foreach ($sources as $sourceOption)
                            <option value="{{ $sourceOption }}" @selected($selectedSource === $sourceOption)>
                                {{ $sourceOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
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
                    @if ($search !== '' || $selectedCompanyId || $selectedSource !== '' || $selectedStatus !== '')
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
                                        <div class="relative inline-flex">
                                            <button type="button"
                                                data-status-toggle
                                                data-lead-id="{{ $lead->id }}"
                                                data-current-status="{{ $lead->status }}"
                                                data-status-url="{{ route('agent.leads.status', $lead) }}"
                                                class="lead-row-action inline-flex cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-concierge-navy"
                                                title="Edit" aria-label="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                                    aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                </svg>
                                            </button>
                                            <div data-status-dropdown="{{ $lead->id }}"
                                                class="absolute right-0 top-full z-20 mt-2 hidden w-44 rounded-xl border border-slate-200 bg-white p-2 text-left shadow-xl">
                                                <p class="px-2 pb-2 text-xs font-semibold uppercase tracking-wide text-concierge-muted">
                                                    Update status
                                                </p>
                                                @foreach ($statuses as $statusKey => $statusLabel)
                                                    <button type="button"
                                                        data-status-option
                                                        data-lead-id="{{ $lead->id }}"
                                                        data-status="{{ $statusKey }}"
                                                        class="mb-1 block w-full rounded-lg px-2 py-1.5 text-left text-sm text-concierge-navy hover:bg-slate-100 {{ $lead->status === $statusKey ? 'bg-slate-100 font-semibold' : '' }}">
                                                        {{ $statusLabel }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
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

            function closeAllDropdowns() {
                document.querySelectorAll('[data-status-dropdown]').forEach((dropdown) => {
                    dropdown.classList.add('hidden');
                });
            }

            function setStatusSelection(leadId, status) {
                document.querySelectorAll(`[data-status-option][data-lead-id="${leadId}"]`).forEach((button) => {
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
                const toggleButton = event.target.closest('[data-status-toggle]');
                const statusOptionButton = event.target.closest('[data-status-option]');

                if (toggleButton) {
                    const leadId = toggleButton.getAttribute('data-lead-id');
                    if (!leadId) {
                        return;
                    }

                    const dropdown = document.querySelector(`[data-status-dropdown="${leadId}"]`);
                    if (!dropdown) {
                        return;
                    }

                    const isHidden = dropdown.classList.contains('hidden');
                    closeAllDropdowns();
                    if (isHidden) {
                        dropdown.classList.remove('hidden');
                    }
                    return;
                }

                if (!statusOptionButton) {
                    closeAllDropdowns();
                    return;
                }

                const leadId = statusOptionButton.getAttribute('data-lead-id');
                const status = statusOptionButton.getAttribute('data-status');
                const toggle = document.querySelector(`[data-status-toggle][data-lead-id="${leadId}"]`);
                const url = toggle?.getAttribute('data-status-url');
                if (!leadId || !status || !url) {
                    closeAllDropdowns();
                    return;
                }

                statusOptionButton.disabled = true;
                statusOptionButton.classList.add('opacity-60');

                try {
                    const payload = await updateLeadStatus(url, status);
                    const statusPill = document.querySelector(`[data-lead-status-pill="${leadId}"]`);
                    if (statusPill) {
                        statusPill.textContent = payload.status_label ?? status;
                        statusPill.className = `concierge-pill concierge-pill-${payload.status_pill_class ?? 'meta'}`;
                    }
                    if (toggle) {
                        toggle.setAttribute('data-current-status', payload.status ?? status);
                    }
                    setStatusSelection(leadId, payload.status ?? status);
                    toastSuccess(payload.message ?? 'Lead status updated successfully.');
                } catch (error) {
                    console.log(error);
                    alert('Could not update status. Please try again.');
                } finally {
                    statusOptionButton.disabled = false;
                    statusOptionButton.classList.remove('opacity-60');
                    closeAllDropdowns();
                }
            });
        })();
    </script>
@endpush
