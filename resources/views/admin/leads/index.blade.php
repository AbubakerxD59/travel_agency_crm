@extends('layouts.admin')

@section('title', 'Lead Management')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Lead Management</h1>
            </div>
            @if ($canCreateLeads)
                <button type="button" id="open-assign-lead-modal"
                    class="inline-flex shrink-0 cursor-pointer items-center justify-center gap-2 rounded-xl bg-concierge-navy px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 transition hover:bg-concierge-navy-deep">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Assign Lead
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

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-slate-100 bg-slate-50/80 text-xs font-semibold uppercase tracking-wide text-concierge-muted">
                            <th class="px-4 py-4 lg:px-6">Agent</th>
                            <th class="px-4 py-4 lg:px-6">Customer Name</th>
                            <th class="px-4 py-4 lg:px-6">Phone Number</th>
                            <th class="px-4 py-4 lg:px-6">Email</th>
                            <th class="px-4 py-4 lg:px-6">Company Name</th>
                            <th class="px-4 py-4 lg:px-6">City</th>
                            <th class="px-4 py-4 lg:px-6">Source</th>
                            <th class="px-4 py-4 lg:px-6">Notes</th>
                            <th class="px-4 py-4 lg:px-6">Status</th>
                            <th class="px-4 py-4 text-right lg:px-6">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($leads as $lead)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-4 text-concierge-navy lg:px-6">{{ $lead->agent?->name ?? 'Unassigned' }}
                                </td>
                                <td class="px-4 py-4 text-concierge-navy lg:px-6">{{ $lead->customer_name ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-muted lg:px-6">{{ $lead->phone_number ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-muted lg:px-6">{{ $lead->email ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-navy lg:px-6">{{ $lead->company?->name ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-muted lg:px-6">{{ $lead->city ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-muted lg:px-6">{{ $lead->source ?? '—' }}</td>
                                <td class="max-w-xs truncate px-4 py-4 text-concierge-muted lg:px-6"
                                    title="{{ $lead->notes }}">
                                    {{ $lead->notes ?? '—' }}
                                </td>
                                <td class="px-4 py-4 lg:px-6">
                                    <span
                                        class="concierge-pill concierge-pill-{{ $lead->statusPillClass() }}">{{ $lead->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-4 text-right lg:px-6">
                                    <div class="inline-flex items-center justify-end gap-1 whitespace-nowrap">
                                        @if ($canCreateLeads)
                                            <button type="button"
                                                class="js-edit-assigned-lead lead-row-action inline-flex cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-concierge-navy"
                                                data-lead-id="{{ $lead->id }}"
                                                data-agent-id="{{ $lead->agent_id ?? '' }}"
                                                data-customer-name="{{ $lead->customer_name ?? '' }}"
                                                data-phone-number="{{ $lead->phone_number ?? '' }}"
                                                data-email="{{ $lead->email ?? '' }}"
                                                data-company-id="{{ $lead->company_id ?? '' }}"
                                                data-city="{{ $lead->city ?? '' }}"
                                                data-source="{{ $lead->source ?? '' }}"
                                                data-notes="{{ $lead->notes ?? '' }}" title="Edit" aria-label="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                                    aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                </svg>
                                            </button>
                                        @else
                                            <button type="button"
                                                class="lead-row-action cursor-not-allowed rounded-lg p-2 text-concierge-muted opacity-40"
                                                title="Edit" aria-label="Edit" disabled>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                                    aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                </svg>
                                            </button>
                                        @endif
                                        <a href="{{ route('admin.leads.show', $lead) }}"
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
                                        @if ($canCreateLeads)
                                            <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}"
                                                class="js-lead-delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="lead-row-action cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-rose-600"
                                                    title="Delete" aria-label="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="1.5" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-10 text-center text-sm text-concierge-muted">
                                    @if ($canCreateLeads)
                                        No leads yet. Use “Assign Lead” to create one.
                                    @else
                                        No leads to show.
                                    @endif
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

    @if ($canCreateLeads)
        <div id="assign-lead-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4"
            aria-hidden="true" role="dialog" aria-labelledby="assign-lead-modal-title">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h2 id="assign-lead-modal-title" class="text-lg font-semibold text-concierge-navy">Assign Lead</h2>
                    <button type="button" data-close-assign-lead-modal
                        class="rounded-lg p-2 text-concierge-muted hover:bg-slate-100 hover:text-concierge-navy"
                        aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="assign-lead-form" method="POST" action="{{ route('admin.leads.assign') }}"
                    class="space-y-4 px-6 py-5">
                    @csrf
                    <input type="hidden" name="_method" id="assign_lead_form_method" value="">
                    <div>
                        <label for="assign_agent_id" class="block text-sm font-medium text-concierge-navy">Agent</label>
                        <select id="assign_agent_id" name="agent_id"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                            <option value="">Select agent</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}" @selected((string) old('agent_id') === (string) $agent->id)>{{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="assign_customer_name"
                                class="block text-sm font-medium text-concierge-navy">Customer Name <span
                                    class="text-rose-600">*</span></label>
                            <input id="assign_customer_name" name="customer_name" type="text" required
                                value="{{ old('customer_name') }}"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        </div>
                        <div>
                            <label for="assign_phone_number" class="block text-sm font-medium text-concierge-navy">Phone
                                Number <span class="text-rose-600">*</span></label>
                            <input id="assign_phone_number" name="phone_number" type="text" required
                                value="{{ old('phone_number') }}"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        </div>
                        <div>
                            <label for="assign_email" class="block text-sm font-medium text-concierge-navy">Email</label>
                            <input id="assign_email" name="email" type="email" value="{{ old('email') }}"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        </div>
                        <div>
                            <label for="assign_company_id" class="block text-sm font-medium text-concierge-navy">Company
                                Name</label>
                            <select id="assign_company_id" name="company_id"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                                <option value="">Select company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}" @selected((string) old('company_id') === (string) $company->id)>{{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="assign_city" class="block text-sm font-medium text-concierge-navy">City</label>
                            <input id="assign_city" name="city" type="text" value="{{ old('city') }}"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        </div>
                        <div>
                            <label for="assign_source"
                                class="block text-sm font-medium text-concierge-navy">Source</label>
                            <select id="assign_source" name="source"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                                <option value="">Select source</option>
                                @foreach (['meta', 'google', 'whatsapp', 'referral'] as $source)
                                    <option value="{{ $source }}" @selected(old('source') === $source)>
                                        {{ ucfirst($source) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="assign_notes" class="block text-sm font-medium text-concierge-navy">Notes</label>
                        <textarea id="assign_notes" name="notes" rows="4"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">{{ old('notes') }}</textarea>
                    </div>

                    @if ($errors->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="flex gap-3 pt-2">
                        <button type="button" data-close-assign-lead-modal
                            class="flex-1 cursor-pointer rounded-xl border border-slate-200 py-2.5 text-sm font-medium text-concierge-navy hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" id="assign-lead-submit-btn"
                            class="flex-1 cursor-pointer rounded-xl bg-concierge-navy py-2.5 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 hover:bg-concierge-navy-deep">
                            Save Lead
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        const assignLeadModal = document.getElementById('assign-lead-modal');
        const openAssignLeadModalBtn = document.getElementById('open-assign-lead-modal');
        const assignLeadForm = document.getElementById('assign-lead-form');
        const assignLeadModalTitle = document.getElementById('assign-lead-modal-title');
        const assignLeadFormMethod = document.getElementById('assign_lead_form_method');
        const assignLeadSubmitBtn = document.getElementById('assign-lead-submit-btn');
        const assignLeadUpdateUrlTemplate = "{{ url('/admin/leads') }}/__LEAD_ID__/assign";

        function setButtonLoading(button, isLoading) {
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

        function resetAssignLeadModalToCreate() {
            if (!assignLeadForm) {
                return;
            }
            assignLeadForm.action = "{{ route('admin.leads.assign') }}";
            if (assignLeadFormMethod) {
                assignLeadFormMethod.value = '';
            }
            if (assignLeadModalTitle) {
                assignLeadModalTitle.textContent = 'Assign Lead';
            }
            if (assignLeadSubmitBtn) {
                assignLeadSubmitBtn.textContent = 'Save Lead';
                setButtonLoading(assignLeadSubmitBtn, false);
            }
            assignLeadForm.reset();
        }

        function openAssignLeadModal() {
            if (!assignLeadModal) {
                return;
            }
            assignLeadModal.classList.remove('hidden');
            assignLeadModal.classList.add('flex');
            assignLeadModal.setAttribute('aria-hidden', 'false');
        }

        function closeAssignLeadModal() {
            if (!assignLeadModal) {
                return;
            }
            assignLeadModal.classList.add('hidden');
            assignLeadModal.classList.remove('flex');
            assignLeadModal.setAttribute('aria-hidden', 'true');
        }

        openAssignLeadModalBtn?.addEventListener('click', () => {
            resetAssignLeadModalToCreate();
            openAssignLeadModal();
        });

        document.querySelectorAll('.js-edit-assigned-lead').forEach((button) => {
            button.addEventListener('click', () => {
                if (!assignLeadForm) {
                    return;
                }

                const leadId = button.dataset.leadId ?? '';
                assignLeadForm.action = assignLeadUpdateUrlTemplate.replace('__LEAD_ID__', leadId);
                if (assignLeadFormMethod) {
                    assignLeadFormMethod.value = 'PATCH';
                }
                if (assignLeadModalTitle) {
                    assignLeadModalTitle.textContent = 'Edit Lead';
                }
                if (assignLeadSubmitBtn) {
                    assignLeadSubmitBtn.textContent = 'Update Lead';
                    setButtonLoading(assignLeadSubmitBtn, false);
                }

                document.getElementById('assign_agent_id').value = button.dataset.agentId ?? '';
                document.getElementById('assign_customer_name').value = button.dataset.customerName ?? '';
                document.getElementById('assign_phone_number').value = button.dataset.phoneNumber ?? '';
                document.getElementById('assign_email').value = button.dataset.email ?? '';
                document.getElementById('assign_company_id').value = button.dataset.companyId ?? '';
                document.getElementById('assign_city').value = button.dataset.city ?? '';
                document.getElementById('assign_source').value = button.dataset.source ?? '';
                document.getElementById('assign_notes').value = button.dataset.notes ?? '';

                openAssignLeadModal();
            });
        });
        document.querySelectorAll('[data-close-assign-lead-modal]').forEach((btn) => {
            btn.addEventListener('click', closeAssignLeadModal);
        });
        assignLeadModal?.addEventListener('click', (event) => {
            if (event.target === assignLeadModal) {
                closeAssignLeadModal();
            }
        });

        @if ($errors->any())
            openAssignLeadModal();
        @endif

        assignLeadForm?.addEventListener('submit', () => {
            setButtonLoading(assignLeadSubmitBtn, true);
        });

        document.addEventListener('submit', async function(event) {
            const form = event.target.closest('.js-lead-delete-form');
            if (!form || form.dataset.confirmed === '1') {
                return;
            }

            event.preventDefault();

            if (typeof window.Swal === 'undefined') {
                return;
            }

            const result = await window.Swal.fire({
                title: 'Delete this lead?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
            });

            if (result.isConfirmed) {
                form.dataset.confirmed = '1';
                form.submit();
            }
        });
    </script>
@endpush
