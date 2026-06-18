function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text == null ? '' : String(text);
    return div.innerHTML;
}

function getConfirmDuplicateInput(form) {
    let input = form.querySelector('input[name="confirm_duplicate"]');
    if (!input) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'confirm_duplicate';
        input.value = '0';
        form.appendChild(input);
    }

    return input;
}

function resetDuplicateConfirmation(form) {
    delete form.dataset.duplicateConfirmed;
    const input = form.querySelector('input[name="confirm_duplicate"]');
    if (input) {
        input.value = '0';
    }
}

function duplicateLeadHtml(lead) {
    const rows = [
        ['Customer', lead.customer_name || '—'],
        ['Phone', lead.phone_number || '—'],
        ['Email', lead.email || '—'],
        ['Status', lead.status_label || '—'],
        ['Agent', lead.agent_name || '—'],
        ['Created', lead.created_at || '—'],
    ];

    return `<div class="text-left text-sm leading-relaxed">
        <p class="mb-3">A lead already exists with the same email and phone number:</p>
        <dl class="space-y-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
            ${rows
                .map(
                    ([label, value]) =>
                        `<div><dt class="inline font-semibold text-concierge-navy">${escapeHtml(label)}:</dt> <dd class="inline text-concierge-muted">${escapeHtml(value)}</dd></div>`,
                )
                .join('')}
        </dl>
    </div>`;
}

async function confirmDuplicateLead(lead) {
    if (typeof window.Swal === 'undefined') {
        return window.confirm(
            'A lead already exists with this email and phone number. Create a new lead anyway?',
        );
    }

    const result = await window.Swal.fire({
        title: 'Duplicate lead found',
        html: duplicateLeadHtml(lead),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Create new lead',
        cancelButtonText: 'Discard',
        reverseButtons: true,
        focusCancel: true,
    });

    return Boolean(result.isConfirmed);
}

async function fetchDuplicateLead(checkUrl, phoneNumber, email, excludeLeadId, csrfToken) {
    const body = new FormData();
    body.append('phone_number', phoneNumber);
    body.append('email', email ?? '');
    if (excludeLeadId) {
        body.append('exclude_lead_id', String(excludeLeadId));
    }

    const response = await fetch(checkUrl, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
        },
        body,
        credentials: 'same-origin',
    });

    if (!response.ok) {
        throw new Error('Could not check for duplicate leads.');
    }

    return response.json();
}

function bindLeadDuplicateCheck(form) {
    const checkUrl = form.dataset.leadDuplicateCheck ?? '';
    if (!checkUrl) {
        return;
    }

    const submitSelector = form.dataset.leadDuplicateSubmit ?? '';
    const submitButton = submitSelector ? document.querySelector(submitSelector) : null;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    form.addEventListener('lead-duplicate-reset', () => {
        resetDuplicateConfirmation(form);
    });

    form.addEventListener('submit', async (event) => {
        if (form.dataset.duplicateConfirmed === '1') {
            return;
        }

        const createOnly = form.dataset.leadDuplicateCreate === '1';
        const isPatch = (form.querySelector('input[name="_method"]')?.value ?? '').toUpperCase() === 'PATCH';
        if (createOnly && isPatch) {
            return;
        }

        const phoneInput = form.querySelector('[name="phone_number"]');
        const emailInput = form.querySelector('[name="email"]');
        const phoneNumber = (phoneInput?.value ?? '').trim();
        const email = (emailInput?.value ?? '').trim();

        if (!phoneNumber) {
            return;
        }

        event.preventDefault();

        if (submitButton instanceof HTMLButtonElement) {
            submitButton.disabled = true;
        }

        try {
            const excludeLeadId = form.dataset.leadDuplicateExcludeLeadId ?? '';
            const payload = await fetchDuplicateLead(
                checkUrl,
                phoneNumber,
                email,
                excludeLeadId,
                csrfToken,
            );

            if (!payload?.duplicate) {
                form.dataset.duplicateConfirmed = '1';
                getConfirmDuplicateInput(form).value = '0';
                form.requestSubmit();
                return;
            }

            const shouldCreate = await confirmDuplicateLead(payload.lead ?? {});
            if (!shouldCreate) {
                return;
            }

            form.dataset.duplicateConfirmed = '1';
            getConfirmDuplicateInput(form).value = '1';
            form.requestSubmit();
        } catch {
            if (typeof window.toastr !== 'undefined') {
                window.toastr.error('Could not check for duplicate leads. Please try again.');
            }
        } finally {
            if (submitButton instanceof HTMLButtonElement && form.dataset.duplicateConfirmed !== '1') {
                submitButton.disabled = false;
            }
        }
    });
}

function initLeadDuplicateChecks() {
    document.querySelectorAll('form[data-lead-duplicate-check]').forEach((form) => {
        bindLeadDuplicateCheck(form);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLeadDuplicateChecks);
} else {
    initLeadDuplicateChecks();
}

export { bindLeadDuplicateCheck, initLeadDuplicateChecks, resetDuplicateConfirmation };
