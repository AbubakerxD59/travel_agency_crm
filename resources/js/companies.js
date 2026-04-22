import $ from 'jquery';
import toastr from 'toastr';

import 'toastr/build/toastr.min.css';

window.$ = window.jQuery = $;

toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    timeOut: 5000,
    extendedTimeOut: 2000,
};

const cfg = document.getElementById('js-companies-config');
const companyBaseUrl = (
    cfg?.dataset.urlBase ??
    cfg?.getAttribute('data-url-base') ??
    ''
).replace(/\/$/, '');

const modal = document.getElementById('company-modal');
const form = document.getElementById('store-company-form');
const editModal = document.getElementById('edit-company-modal');
const editForm = document.getElementById('edit-company-form');
const companyListFilterInput = document.getElementById('company-list-filter');
const companiesFilterNoResults = document.getElementById('companies-filter-no-results');
const companiesIndexTable = document.getElementById('companies-index-table');

let editingCompanyId = null;

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function jsonHeaders(extra = {}) {
    return {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken(),
        ...extra,
    };
}

function companyUrl(id) {
    return `${companyBaseUrl}/${id}`;
}

function canManageCompanies() {
    return cfg?.dataset.canManage === '1';
}

function companiesTableActionsColspan() {
    const n = parseInt(cfg?.dataset.actionsColspan ?? '3', 10);
    return Number.isFinite(n) && n > 0 ? n : 3;
}

function companySearchTextFromPayload(company) {
    const country = company.country_name != null ? String(company.country_name) : '';
    return `${company.name ?? ''} ${country}`.trim().toLowerCase();
}

function companiesTableBody() {
    return companiesIndexTable?.querySelector('tbody') ?? null;
}

function removeEmptyStateRow() {
    companiesTableBody()?.querySelector('tr.companies-index-empty')?.remove();
}

function ensureEmptyStateRowVisible() {
    const tb = companiesTableBody();
    if (!tb || tb.querySelector('tr[data-company-id]')) {
        return;
    }
    if (tb.querySelector('tr.companies-index-empty')) {
        return;
    }
    const tr = document.createElement('tr');
    tr.className = 'companies-index-empty';
    const td = document.createElement('td');
    td.colSpan = companiesTableActionsColspan();
    td.className = 'px-6 py-10 text-center text-sm text-concierge-muted';
    td.textContent = 'No companies yet. Use "Add new" to create one.';
    tr.appendChild(td);
    tb.appendChild(tr);
}

function companyActionButtonsInnerHtml(companyId) {
    const id = String(companyId);
    return `<div class="inline-flex flex-wrap items-center justify-end gap-1">
        <button type="button" class="company-row-action cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-concierge-navy" data-edit-company="${id}" title="Edit">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
        </button>
        <button type="button" class="company-row-action cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-rose-600" data-delete-company="${id}" title="Delete">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
        </button>
    </div>`;
}

function buildCompanyRow(company) {
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-50/50';
    tr.dataset.companyId = String(company.id);
    tr.dataset.searchText = companySearchTextFromPayload(company);

    const tdName = document.createElement('td');
    tdName.className = 'px-6 py-4 font-medium text-concierge-navy';
    tdName.textContent = company.name ?? '';

    const tdCountry = document.createElement('td');
    tdCountry.className = 'px-6 py-4';
    const pill = document.createElement('span');
    pill.className = 'concierge-pill concierge-pill-meta';
    pill.textContent = company.country_name ?? '—';
    tdCountry.appendChild(pill);

    const tdAdded = document.createElement('td');
    tdAdded.className = 'px-6 py-4 text-sm text-concierge-muted';
    tdAdded.textContent = company.created_at ?? '';

    tr.append(tdName, tdCountry, tdAdded);

    if (canManageCompanies()) {
        const tdAct = document.createElement('td');
        tdAct.className = 'px-6 py-4 text-right';
        tdAct.innerHTML = companyActionButtonsInnerHtml(company.id);
        tr.appendChild(tdAct);
    }

    return tr;
}

function findCompanyRow(id) {
    return (
        companiesIndexTable?.querySelector(`tbody tr[data-company-id="${CSS.escape(String(id))}"]`) ?? null
    );
}

function updateCompanyRowFromPayload(company) {
    const tr = findCompanyRow(company.id);
    if (!tr) {
        return;
    }
    tr.dataset.searchText = companySearchTextFromPayload(company);
    const cells = tr.querySelectorAll('td');
    if (cells.length < 3) {
        return;
    }
    cells[0].textContent = company.name ?? '';
    const pill = cells[1].querySelector('.concierge-pill');
    if (pill) {
        pill.textContent = company.country_name ?? '—';
    }
    cells[2].textContent = company.created_at ?? '';
}

function appendCompanyRowFromPayload(company) {
    const tb = companiesTableBody();
    if (!tb) {
        return;
    }
    removeEmptyStateRow();
    tb.appendChild(buildCompanyRow(company));
    applyCompanyListFilter();
}

function removeCompanyRowById(id) {
    findCompanyRow(id)?.remove();
    applyCompanyListFilter();
    ensureEmptyStateRowVisible();
}

async function confirmDeleteCompany() {
    if (typeof window.Swal === 'undefined') {
        toastr.error('Confirmation dialog is unavailable. Please refresh and try again.');
        return false;
    }

    const result = await window.Swal.fire({
        title: 'Delete this company?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc2626',
    });

    return Boolean(result.isConfirmed);
}

function applyCompanyListFilter() {
    const q = (companyListFilterInput?.value ?? '').trim().toLowerCase();
    const rows = companiesIndexTable?.querySelectorAll('tbody tr[data-company-id]') ?? [];

    let visible = 0;
    rows.forEach((row) => {
        const haystack = row.getAttribute('data-search-text') ?? '';
        const match = q === '' || haystack.includes(q);
        row.classList.toggle('hidden', !match);
        if (match) {
            visible += 1;
        }
    });

    const hasCompanies = rows.length > 0;
    const showNoResults = hasCompanies && q !== '' && visible === 0;
    companiesFilterNoResults?.classList.toggle('hidden', !showNoResults);
}

function openCompanyModal() {
    if (!modal) {
        return;
    }
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
}

function closeCompanyModal() {
    if (!modal) {
        return;
    }
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('aria-hidden', 'true');
}

function openEditCompanyModal() {
    if (!editModal) {
        return;
    }
    editModal.classList.remove('hidden');
    editModal.classList.add('flex');
    editModal.setAttribute('aria-hidden', 'false');
}

function closeEditCompanyModal() {
    if (!editModal) {
        return;
    }
    editModal.classList.add('hidden');
    editModal.classList.remove('flex');
    editModal.setAttribute('aria-hidden', 'true');
    editingCompanyId = null;
    editForm?.reset();
}

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

/** @param {EventTarget|null} target */
function elementFromClickTarget(target) {
    if (target instanceof Element) {
        return target;
    }
    if (target instanceof Node && target.parentElement) {
        return target.parentElement;
    }
    return null;
}

companyListFilterInput?.addEventListener('input', applyCompanyListFilter);
companyListFilterInput?.addEventListener('search', applyCompanyListFilter);

document.getElementById('open-company-modal')?.addEventListener('click', openCompanyModal);

document.querySelectorAll('[data-close-company-modal]').forEach((btn) => {
    btn.addEventListener('click', closeCompanyModal);
});

document.querySelectorAll('[data-close-edit-company-modal]').forEach((btn) => {
    btn.addEventListener('click', closeEditCompanyModal);
});

modal?.addEventListener('click', (e) => {
    if (e.target === modal) {
        closeCompanyModal();
    }
});

editModal?.addEventListener('click', (e) => {
    if (e.target === editModal) {
        closeEditCompanyModal();
    }
});

document.addEventListener('click', async (e) => {
    const root = elementFromClickTarget(e.target);
    if (!root?.closest('#companies-index-table')) {
        return;
    }

    const editBtn = root.closest('[data-edit-company]');
    const delBtn = root.closest('[data-delete-company]');

    if (editBtn) {
        if (editBtn.dataset.loading === '1') {
            return;
        }
        const id = editBtn.getAttribute('data-edit-company');
        if (!id || !editForm) {
            return;
        }
        editingCompanyId = id;
        setButtonLoading(editBtn, true);
        try {
            const res = await fetch(companyUrl(id), { headers: jsonHeaders() });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                toastr.error(data.message ?? 'Could not load company.');
                return;
            }
            const c = data.company;
            if (!c) {
                toastr.error('Invalid response.');
                return;
            }
            editForm.action = companyUrl(id);
            document.getElementById('edit_modal_company_name').value = c.name ?? '';
            const countrySelect = document.getElementById('edit_modal_country_id');
            if (countrySelect) {
                countrySelect.value = String(c.country_id ?? '');
            }
            openEditCompanyModal();
        } catch {
            toastr.error('Network error.');
        } finally {
            setButtonLoading(editBtn, false);
        }
        return;
    }

    if (delBtn) {
        if (delBtn.dataset.loading === '1') {
            return;
        }
        const id = delBtn.getAttribute('data-delete-company');
        if (!id) {
            return;
        }
        const confirmed = await confirmDeleteCompany();
        if (!confirmed) {
            return;
        }
        setButtonLoading(delBtn, true);
        try {
            const res = await fetch(companyUrl(id), {
                method: 'DELETE',
                headers: jsonHeaders(),
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok) {
                toastr.success(data.message ?? 'Company deleted.');
                removeCompanyRowById(id);
            } else {
                toastr.error(data.message ?? 'Could not delete company.');
            }
        } catch {
            toastr.error('Network error.');
        } finally {
            setButtonLoading(delBtn, false);
        }
    }
});

editForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!editingCompanyId) {
        return;
    }

    const submitBtn = editForm.querySelector('button[type="submit"]');
    setButtonLoading(submitBtn, true);

    const fd = new FormData(editForm);

    try {
        const res = await fetch(companyUrl(editingCompanyId), {
            method: 'POST',
            headers: jsonHeaders(),
            body: fd,
        });

        const data = await res.json().catch(() => ({}));

        if (res.ok) {
            toastr.success(data.message ?? 'Company updated.');
            closeEditCompanyModal();
            if (data.company) {
                updateCompanyRowFromPayload(data.company);
                applyCompanyListFilter();
            }
        } else if (res.status === 422) {
            if (data.errors) {
                const msgs = Object.values(data.errors).flat();
                if (msgs.length) {
                    msgs.forEach((m) => toastr.error(m));
                } else if (data.message) {
                    toastr.error(data.message);
                } else {
                    toastr.error('Validation failed.');
                }
            } else if (data.message) {
                toastr.error(data.message);
            } else {
                toastr.error('Validation failed.');
            }
        } else {
            toastr.error(data.message ?? 'Could not update company.');
        }
    } catch {
        toastr.error('Network error.');
    } finally {
        setButtonLoading(submitBtn, false);
    }
});

form?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const submitBtn = form.querySelector('button[type="submit"]');
    setButtonLoading(submitBtn, true);

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: jsonHeaders(),
            body: new FormData(form),
        });

        let data = {};
        try {
            data = await res.json();
        } catch {
            data = {};
        }

        if (res.ok) {
            toastr.success(data.message ?? 'Company created successfully.');
            form.reset();
            const sel = document.getElementById('modal_country_id');
            if (sel) {
                sel.selectedIndex = 0;
            }
            closeCompanyModal();
            if (data.company) {
                appendCompanyRowFromPayload(data.company);
            }
        } else if (res.status === 422 && data.errors) {
            const msgs = Object.values(data.errors).flat();
            if (msgs.length) {
                msgs.forEach((m) => toastr.error(m));
            } else if (data.message) {
                toastr.error(data.message);
            } else {
                toastr.error('Validation failed.');
            }
        } else {
            toastr.error(data.message ?? 'Something went wrong. Please try again.');
        }
    } catch {
        toastr.error('Network error. Please try again.');
    } finally {
        setButtonLoading(submitBtn, false);
    }
});
