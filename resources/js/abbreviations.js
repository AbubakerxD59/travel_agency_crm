import toastr from 'toastr';

import 'toastr/build/toastr.min.css';

toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    timeOut: 5000,
    extendedTimeOut: 2000,
};

const cfg = document.getElementById('js-abbreviations-config');
const baseUrl = (cfg?.dataset.urlBase ?? '').replace(/\/$/, '');
const canManage = cfg?.dataset.canManage === '1';
const actionsColspan = Number.parseInt(cfg?.dataset.actionsColspan ?? '3', 10) || 3;

const modal = document.getElementById('abbreviation-modal');
const form = document.getElementById('store-abbreviation-form');
const editModal = document.getElementById('edit-abbreviation-modal');
const editForm = document.getElementById('edit-abbreviation-form');
const filterInput = document.getElementById('abbreviation-list-filter');
const filterNoResults = document.getElementById('abbreviations-filter-no-results');
const table = document.getElementById('abbreviations-index-table');

let editingId = null;

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function jsonHeaders(extra = {}) {
    return {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken(),
        ...extra,
    };
}

function abbreviationUrl(id) {
    return `${baseUrl}/${id}`;
}

function tableBody() {
    return table?.querySelector('tbody') ?? null;
}

function openModal(element) {
    if (!element) {
        return;
    }

    element.classList.remove('hidden');
    element.classList.add('flex');
    element.setAttribute('aria-hidden', 'false');
}

function closeModal(element) {
    if (!element) {
        return;
    }

    element.classList.add('hidden');
    element.classList.remove('flex');
    element.setAttribute('aria-hidden', 'true');
}

function removeEmptyRow() {
    tableBody()?.querySelector('tr.abbreviations-index-empty')?.remove();
}

function ensureEmptyRow() {
    const tbody = tableBody();
    if (!tbody || tbody.querySelector('tr[data-abbreviation-id]')) {
        return;
    }

    if (tbody.querySelector('tr.abbreviations-index-empty')) {
        return;
    }

    const tr = document.createElement('tr');
    tr.className = 'abbreviations-index-empty';
    tr.innerHTML = `<td colspan="${actionsColspan}" class="px-6 py-10 text-center text-sm text-concierge-muted">No abbreviations yet. Use "Add new" to create one.</td>`;
    tbody.appendChild(tr);
}

function searchText(item) {
    return `${item.code ?? ''} ${item.full_form ?? ''}`.trim().toLowerCase();
}

function rowHtml(item) {
    const manageActions = canManage
        ? `<td class="px-6 py-4 text-right">
            <div class="inline-flex items-center justify-end gap-1">
                <button type="button" class="abbreviation-row-action cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-concierge-navy" data-edit-abbreviation="${item.id}" title="Edit" aria-label="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                </button>
                <button type="button" class="abbreviation-row-action cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-rose-600" data-delete-abbreviation="${item.id}" title="Delete" aria-label="Delete">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                </button>
            </div>
        </td>`
        : '';

    return `<tr class="hover:bg-slate-50/50" data-abbreviation-id="${item.id}" data-search-text="${searchText(item).replace(/"/g, '&quot;')}">
        <td class="px-6 py-4 font-mono font-semibold text-concierge-navy">${item.code ?? ''}</td>
        <td class="px-6 py-4 text-concierge-navy">${item.full_form ?? ''}</td>
        <td class="px-6 py-4 text-concierge-muted">${item.created_at ?? ''}</td>
        ${manageActions}
    </tr>`;
}

function appendRow(item) {
    removeEmptyRow();
    tableBody()?.insertAdjacentHTML('beforeend', rowHtml(item));
    applyFilter();
}

function updateRow(item) {
    const row = tableBody()?.querySelector(`tr[data-abbreviation-id="${item.id}"]`);
    if (!row) {
        return;
    }

    row.outerHTML = rowHtml(item);
    applyFilter();
}

function removeRow(id) {
    tableBody()?.querySelector(`tr[data-abbreviation-id="${id}"]`)?.remove();
    ensureEmptyRow();
    applyFilter();
}

function applyFilter() {
    const query = (filterInput?.value ?? '').trim().toLowerCase();
    let visibleCount = 0;

    tableBody()?.querySelectorAll('tr[data-abbreviation-id]').forEach((row) => {
        const haystack = row.getAttribute('data-search-text') ?? '';
        const visible = query === '' || haystack.includes(query);
        row.classList.toggle('hidden', !visible);
        if (visible) {
            visibleCount += 1;
        }
    });

    if (!filterNoResults) {
        return;
    }

    const hasRows = Boolean(tableBody()?.querySelector('tr[data-abbreviation-id]'));
    filterNoResults.classList.toggle('hidden', !hasRows || visibleCount > 0 || query === '');
}

async function parseJsonResponse(response) {
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
        const message = payload?.message || Object.values(payload?.errors ?? {})?.flat()?.[0] || 'Request failed.';
        throw new Error(message);
    }

    return payload;
}

document.getElementById('open-abbreviation-modal')?.addEventListener('click', () => {
    form?.reset();
    openModal(modal);
    document.getElementById('modal_abbreviation_code')?.focus();
});

document.querySelectorAll('[data-close-abbreviation-modal]').forEach((button) => {
    button.addEventListener('click', () => closeModal(modal));
});

document.querySelectorAll('[data-close-edit-abbreviation-modal]').forEach((button) => {
    button.addEventListener('click', () => {
        editingId = null;
        closeModal(editModal);
    });
});

modal?.addEventListener('click', (event) => {
    if (event.target === modal) {
        closeModal(modal);
    }
});

editModal?.addEventListener('click', (event) => {
    if (event.target === editModal) {
        editingId = null;
        closeModal(editModal);
    }
});

form?.addEventListener('submit', async (event) => {
    event.preventDefault();

    const code = document.getElementById('modal_abbreviation_code')?.value ?? '';
    const fullForm = document.getElementById('modal_abbreviation_full_form')?.value ?? '';

    try {
        const payload = await parseJsonResponse(
            await fetch(form.action, {
                method: 'POST',
                headers: jsonHeaders(),
                body: JSON.stringify({ code, full_form: fullForm }),
            }),
        );

        appendRow(payload.abbreviation);
        toastr.success(payload.message ?? 'Abbreviation created.');
        closeModal(modal);
        form.reset();
    } catch (error) {
        toastr.error(error instanceof Error ? error.message : 'Could not create abbreviation.');
    }
});

editForm?.addEventListener('submit', async (event) => {
    event.preventDefault();

    if (!editingId) {
        return;
    }

    const code = document.getElementById('edit_modal_abbreviation_code')?.value ?? '';
    const fullForm = document.getElementById('edit_modal_abbreviation_full_form')?.value ?? '';

    try {
        const payload = await parseJsonResponse(
            await fetch(abbreviationUrl(editingId), {
                method: 'PATCH',
                headers: jsonHeaders(),
                body: JSON.stringify({ code, full_form: fullForm }),
            }),
        );

        updateRow(payload.abbreviation);
        toastr.success(payload.message ?? 'Abbreviation updated.');
        editingId = null;
        closeModal(editModal);
    } catch (error) {
        toastr.error(error instanceof Error ? error.message : 'Could not update abbreviation.');
    }
});

table?.addEventListener('click', async (event) => {
    const editButton = event.target.closest('[data-edit-abbreviation]');
    const deleteButton = event.target.closest('[data-delete-abbreviation]');

    if (editButton && canManage) {
        const id = editButton.getAttribute('data-edit-abbreviation');
        if (!id) {
            return;
        }

        try {
            const payload = await parseJsonResponse(await fetch(abbreviationUrl(id), { headers: jsonHeaders() }));
            editingId = id;
            document.getElementById('edit_modal_abbreviation_code').value = payload.abbreviation.code ?? '';
            document.getElementById('edit_modal_abbreviation_full_form').value = payload.abbreviation.full_form ?? '';
            openModal(editModal);
        } catch (error) {
            toastr.error(error instanceof Error ? error.message : 'Could not load abbreviation.');
        }

        return;
    }

    if (deleteButton && canManage) {
        const id = deleteButton.getAttribute('data-delete-abbreviation');
        if (!id || !window.confirm('Delete this abbreviation?')) {
            return;
        }

        try {
            const payload = await parseJsonResponse(
                await fetch(abbreviationUrl(id), {
                    method: 'DELETE',
                    headers: jsonHeaders(),
                }),
            );

            removeRow(id);
            toastr.success(payload.message ?? 'Abbreviation deleted.');
        } catch (error) {
            toastr.error(error instanceof Error ? error.message : 'Could not delete abbreviation.');
        }
    }
});

filterInput?.addEventListener('input', applyFilter);

applyFilter();
