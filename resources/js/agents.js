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

const cfg = document.getElementById('js-agents-config');
const agentBaseUrl = (
    cfg?.dataset.urlBase ??
    cfg?.getAttribute('data-url-base') ??
    ''
)
    .replace(/\/$/, '');

const modal = document.getElementById('agent-modal');
const form = document.getElementById('store-agent-form');
const editModal = document.getElementById('edit-agent-modal');
const editForm = document.getElementById('edit-agent-form');
const permissionsModal = document.getElementById('permissions-modal');
const permissionsForm = document.getElementById('permissions-form');
const permissionsCheckboxes = document.getElementById('permissions-checkboxes');
const permissionsAgentLabel = document.getElementById('permissions-agent-label');
const agentListFilterInput = document.getElementById('agent-list-filter');
const agentsFilterNoResults = document.getElementById('agents-filter-no-results');
const agentsIndexTable = document.getElementById('agents-index-table');

let editingAgentId = null;
let permissionsAgentId = null;

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

function agentUrl(id) {
    return `${agentBaseUrl}/${id}`;
}

function agentOverviewUrl(id) {
    return `${agentUrl(id)}/overview`;
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

function permissionsUrl(id) {
    return `${agentBaseUrl}/${id}/permissions`;
}

function canManageAgents() {
    return cfg?.dataset.canManage === '1';
}

function currentUserId() {
    return cfg?.dataset.currentUserId ?? '';
}

function agentsTableActionsColspan() {
    const n = parseInt(cfg?.dataset.actionsColspan ?? '5', 10);
    return Number.isFinite(n) && n > 0 ? n : 5;
}

function agentSearchTextFromPayload(agent) {
    const phone = agent.phone_number != null ? String(agent.phone_number) : '';
    return `${agent.name ?? ''} ${agent.email ?? ''} ${phone}`.trim().toLowerCase();
}

function agentsTableBody() {
    return agentsIndexTable?.querySelector('tbody') ?? null;
}

function removeEmptyStateRow() {
    agentsTableBody()?.querySelector('tr.agents-index-empty')?.remove();
}

function ensureEmptyStateRowVisible() {
    const tb = agentsTableBody();
    if (!tb || tb.querySelector('tr[data-agent-id]')) {
        return;
    }
    if (tb.querySelector('tr.agents-index-empty')) {
        return;
    }
    const tr = document.createElement('tr');
    tr.className = 'agents-index-empty';
    const td = document.createElement('td');
    td.colSpan = agentsTableActionsColspan();
    td.className = 'px-6 py-10 text-center text-sm text-concierge-muted';
    td.textContent = 'No agents yet. Use "Add new" to create one.';
    tr.appendChild(td);
    tb.appendChild(tr);
}

function agentActionButtonsInnerHtml(agentId) {
    const id = String(agentId);
    const isSelf = id === String(currentUserId());
    const delDisabled = isSelf ? ' disabled' : '';
    const delTitle = isSelf ? 'You cannot delete your own account' : 'Delete';
    return `<div class="inline-flex flex-wrap items-center justify-end gap-1">
        <button type="button" class="agent-row-action cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-concierge-navy" data-edit-agent="${id}" title="Edit">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
        </button>
        <button type="button" class="agent-row-action cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-concierge-accent" data-permissions-agent="${id}" title="Permissions">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H3.75v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
        </button>
        <button type="button" class="agent-row-action cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-rose-600 disabled:cursor-not-allowed disabled:opacity-40" data-delete-agent="${id}" title="${delTitle}"${delDisabled}>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
        </button>
    </div>`;
}

function buildAgentRow(agent) {
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-50/50';
    tr.dataset.agentId = String(agent.id);
    tr.dataset.searchText = agentSearchTextFromPayload(agent);

    const tdName = document.createElement('td');
    tdName.className = 'px-6 py-4 font-medium text-concierge-navy';
    tdName.innerHTML = `<a href="${agentOverviewUrl(agent.id)}" class="hover:underline">${escapeHtml(agent.name ?? '')}${
        agent.agent_cnic
            ? `<br><span class="text-xs text-concierge-muted">CNIC: ${escapeHtml(agent.agent_cnic)}</span>`
            : ''
    }</a>`;

    const tdEmail = document.createElement('td');
    tdEmail.className = 'px-6 py-4 font-medium text-concierge-navy';
    tdEmail.innerHTML = `<a href="${agentOverviewUrl(agent.id)}" class="hover:underline">${escapeHtml(agent.email ?? '')}${
        agent.phone_number
            ? `<br><span class="text-xs text-concierge-muted">Phone: ${escapeHtml(agent.phone_number)}</span>`
            : ''
    }</a>`;

    const tdGuardian = document.createElement('td');
    if (agent.guardian_name) {
        tdGuardian.className = 'px-6 py-4 font-medium text-concierge-navy';
        tdGuardian.innerHTML = `<a href="${agentOverviewUrl(agent.id)}" class="hover:underline">${escapeHtml(agent.guardian_name)}${
            agent.guardian_phone_number
                ? `<br><span class="text-xs text-concierge-muted">Phone: ${escapeHtml(agent.guardian_phone_number)}</span>`
                : ''
        }</a>`;
    } else {
        tdGuardian.className = 'px-6 py-4 font-medium text-concierge-navy text-center';
        tdGuardian.textContent = '-';
    }

    const tdRole = document.createElement('td');
    tdRole.className = 'px-6 py-4';
    const pill = document.createElement('span');
    pill.className = 'concierge-pill concierge-pill-contacted';
    pill.textContent = agent.role ?? 'agent';
    tdRole.appendChild(pill);

    const tdAdded = document.createElement('td');
    tdAdded.className = 'px-6 py-4 text-sm text-concierge-muted';
    tdAdded.textContent = agent.created_at ?? '';

    tr.append(tdName, tdEmail, tdGuardian, tdRole, tdAdded);

    if (canManageAgents()) {
        const tdAct = document.createElement('td');
        tdAct.className = 'px-6 py-4 text-right';
        tdAct.innerHTML = agentActionButtonsInnerHtml(agent.id);
        tr.appendChild(tdAct);
    }

    return tr;
}

function findAgentRow(id) {
    return (
        agentsIndexTable?.querySelector(`tbody tr[data-agent-id="${CSS.escape(String(id))}"]`) ?? null
    );
}

function updateAgentRowFromPayload(agent) {
    const tr = findAgentRow(agent.id);
    if (!tr) {
        return;
    }
    tr.dataset.searchText = agentSearchTextFromPayload(agent);
    const cells = tr.querySelectorAll('td');
    if (cells.length < 5) {
        return;
    }
    cells[0].innerHTML = `<a href="${agentOverviewUrl(agent.id)}" class="hover:underline">${escapeHtml(agent.name ?? '')}${
        agent.agent_cnic
            ? `<br><span class="text-xs text-concierge-muted">CNIC: ${escapeHtml(agent.agent_cnic)}</span>`
            : ''
    }</a>`;
    cells[1].innerHTML = `<a href="${agentOverviewUrl(agent.id)}" class="hover:underline">${escapeHtml(agent.email ?? '')}${
        agent.phone_number
            ? `<br><span class="text-xs text-concierge-muted">Phone: ${escapeHtml(agent.phone_number)}</span>`
            : ''
    }</a>`;
    if (agent.guardian_name) {
        cells[2].className = 'px-6 py-4 font-medium text-concierge-navy';
        cells[2].innerHTML = `<a href="${agentOverviewUrl(agent.id)}" class="hover:underline">${escapeHtml(agent.guardian_name)}${
            agent.guardian_phone_number
                ? `<br><span class="text-xs text-concierge-muted">Phone: ${escapeHtml(agent.guardian_phone_number)}</span>`
                : ''
        }</a>`;
    } else {
        cells[2].className = 'px-6 py-4 font-medium text-concierge-navy text-center';
        cells[2].textContent = '-';
    }
    const pill = cells[3].querySelector('.concierge-pill');
    if (pill) {
        pill.textContent = agent.role ?? 'agent';
    }
    cells[4].textContent = agent.created_at ?? '';
}

function appendAgentRowFromPayload(agent) {
    const tb = agentsTableBody();
    if (!tb) {
        return;
    }
    removeEmptyStateRow();
    tb.appendChild(buildAgentRow(agent));
    applyAgentListFilter();
}

function removeAgentRowById(id) {
    findAgentRow(id)?.remove();
    applyAgentListFilter();
    ensureEmptyStateRowVisible();
}

async function confirmDeleteAgent() {
    if (typeof window.Swal === 'undefined') {
        toastr.error('Confirmation dialog is unavailable. Please refresh and try again.');
        return false;
    }

    const result = await window.Swal.fire({
        title: 'Delete this agent?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc2626',
    });

    return Boolean(result.isConfirmed);
}

function applyAgentListFilter() {
    const q = (agentListFilterInput?.value ?? '').trim().toLowerCase();
    const rows = agentsIndexTable?.querySelectorAll('tbody tr[data-agent-id]') ?? [];

    let visible = 0;
    rows.forEach((row) => {
        const haystack = row.getAttribute('data-search-text') ?? '';
        const match = q === '' || haystack.includes(q);
        row.classList.toggle('hidden', !match);
        if (match) {
            visible += 1;
        }
    });

    const hasAgents = rows.length > 0;
    const showNoResults = hasAgents && q !== '' && visible === 0;
    agentsFilterNoResults?.classList.toggle('hidden', !showNoResults);
}

function openAgentModal() {
    if (!modal) {
        return;
    }
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
}

function closeAgentModal() {
    if (!modal) {
        return;
    }
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('aria-hidden', 'true');
}

function openEditAgentModal() {
    if (!editModal) {
        return;
    }
    editModal.classList.remove('hidden');
    editModal.classList.add('flex');
    editModal.setAttribute('aria-hidden', 'false');
}

function closeEditAgentModal() {
    if (!editModal) {
        return;
    }
    editModal.classList.add('hidden');
    editModal.classList.remove('flex');
    editModal.setAttribute('aria-hidden', 'true');
    editingAgentId = null;
    editForm?.reset();
    resetPasswordVisibility();
}

function openPermissionsModal() {
    if (!permissionsModal) {
        return;
    }
    permissionsModal.classList.remove('hidden');
    permissionsModal.classList.add('flex');
    permissionsModal.setAttribute('aria-hidden', 'false');
}

function closePermissionsModal() {
    if (!permissionsModal) {
        return;
    }
    permissionsModal.classList.add('hidden');
    permissionsModal.classList.remove('flex');
    permissionsModal.setAttribute('aria-hidden', 'true');
    permissionsAgentId = null;
    if (permissionsCheckboxes) {
        permissionsCheckboxes.innerHTML = '';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text == null ? '' : String(text);
    return div.innerHTML;
}

function syncPasswordToggleButton(btn, input) {
    const visible = input.type === 'text';
    btn.setAttribute('aria-pressed', visible ? 'true' : 'false');
    btn.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
    btn.querySelector('[data-icon="show"]')?.classList.toggle('hidden', visible);
    btn.querySelector('[data-icon="hide"]')?.classList.toggle('hidden', !visible);
}

function resetPasswordVisibility() {
    document.querySelectorAll('[data-password-toggle]').forEach((btn) => {
        const id = btn.getAttribute('data-password-toggle');
        const input = id ? document.getElementById(id) : null;
        if (!input) {
            return;
        }
        input.type = 'password';
        syncPasswordToggleButton(btn, input);
    });
}

document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-password-toggle]');
    if (!btn) {
        return;
    }
    const id = btn.getAttribute('data-password-toggle');
    const input = id ? document.getElementById(id) : null;
    if (!input) {
        return;
    }
    input.type = input.type === 'password' ? 'text' : 'password';
    syncPasswordToggleButton(btn, input);
});

function toastValidationErrors(data) {
    if (data.errors) {
        const msgs = Object.values(data.errors).flat();
        if (msgs.length) {
            msgs.forEach((m) => toastr.error(m));
            return true;
        }
    }
    if (data.message) {
        toastr.error(data.message);
        return true;
    }
    return false;
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

agentListFilterInput?.addEventListener('input', applyAgentListFilter);
agentListFilterInput?.addEventListener('search', applyAgentListFilter);

document.getElementById('open-agent-modal')?.addEventListener('click', openAgentModal);

document.querySelectorAll('[data-close-agent-modal]').forEach((btn) => {
    btn.addEventListener('click', closeAgentModal);
});

document.querySelectorAll('[data-close-edit-agent-modal]').forEach((btn) => {
    btn.addEventListener('click', closeEditAgentModal);
});

document.querySelectorAll('[data-close-permissions-modal]').forEach((btn) => {
    btn.addEventListener('click', closePermissionsModal);
});

modal?.addEventListener('click', (e) => {
    if (e.target === modal) {
        closeAgentModal();
    }
});

editModal?.addEventListener('click', (e) => {
    if (e.target === editModal) {
        closeEditAgentModal();
    }
});

permissionsModal?.addEventListener('click', (e) => {
    if (e.target === permissionsModal) {
        closePermissionsModal();
    }
});

document.addEventListener('click', async (e) => {
    const root = elementFromClickTarget(e.target);
    if (!root?.closest('#agents-index-table')) {
        return;
    }

    const editBtn = root.closest('[data-edit-agent]');
    const delBtn = root.closest('[data-delete-agent]');
    const permBtn = root.closest('[data-permissions-agent]');

    if (editBtn) {
        if (editBtn.dataset.loading === '1') {
            return;
        }
        const id = editBtn.getAttribute('data-edit-agent');
        if (!id || !editForm) {
            return;
        }
        editingAgentId = id;
        setButtonLoading(editBtn, true);
        try {
            const res = await fetch(agentUrl(id), { headers: jsonHeaders() });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                toastr.error(data.message ?? 'Could not load agent.');
                return;
            }
            const a = data.agent;
            if (!a) {
                toastr.error('Invalid response.');
                return;
            }
            editForm.action = agentUrl(id);
            document.getElementById('edit_modal_name').value = a.name ?? '';
            document.getElementById('edit_modal_email').value = a.email ?? '';
            document.getElementById('edit_modal_phone_number').value = a.phone_number ?? '';
            document.getElementById('edit_modal_agent_cnic').value = a.agent_cnic ?? '';
            document.getElementById('edit_modal_home_address').value = a.home_address ?? '';
            document.getElementById('edit_modal_guardian_name').value = a.guardian_name ?? '';
            document.getElementById('edit_modal_guardian_phone_number').value =
                a.guardian_phone_number ?? '';
            document.getElementById('edit_modal_guardian_cnic').value = a.guardian_cnic ?? '';
            document.getElementById('edit_modal_password').value = '';
            document.getElementById('edit_modal_confirm_password').value = '';
            resetPasswordVisibility();
            openEditAgentModal();
        } catch {
            toastr.error('Network error.');
        } finally {
            setButtonLoading(editBtn, false);
        }
        return;
    }

    if (permBtn) {
        if (permBtn.dataset.loading === '1') {
            return;
        }
        const id = permBtn.getAttribute('data-permissions-agent');
        if (!id || !permissionsCheckboxes) {
            return;
        }
        permissionsAgentId = id;
        setButtonLoading(permBtn, true);
        try {
            const res = await fetch(permissionsUrl(id), { headers: jsonHeaders() });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                toastr.error(data.message ?? 'Could not load permissions.');
                return;
            }
            permissionsAgentLabel.textContent = data.agent?.name
                ? `Agent: ${data.agent.name}`
                : '';
            const assigned = new Set(data.assigned ?? []);
            permissionsCheckboxes.innerHTML = (data.assignable ?? [])
                .map((item) => {
                    const name = escapeHtml(item.name);
                    const label = escapeHtml(item.label ?? item.name);
                    const checked = assigned.has(item.name) ? ' checked' : '';
                    return `<label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3 hover:bg-slate-50">
                        <input type="checkbox" name="permissions[]" value="${name}" class="mt-1 rounded border-slate-300 text-concierge-accent focus:ring-concierge-accent/30"${checked}>
                        <span class="text-sm text-concierge-navy">${label}</span>
                    </label>`;
                })
                .join('');
            openPermissionsModal();
        } catch {
            toastr.error('Network error.');
        } finally {
            setButtonLoading(permBtn, false);
        }
        return;
    }

    if (delBtn) {
        if (delBtn.dataset.loading === '1') {
            return;
        }
        if (delBtn.disabled) {
            return;
        }
        const id = delBtn.getAttribute('data-delete-agent');
        if (!id) {
            return;
        }
        const confirmed = await confirmDeleteAgent();
        if (!confirmed) {
            return;
        }
        setButtonLoading(delBtn, true);
        try {
            const res = await fetch(agentUrl(id), {
                method: 'DELETE',
                headers: jsonHeaders(),
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok) {
                toastr.success(data.message ?? 'Agent deleted.');
                removeAgentRowById(id);
            } else {
                toastr.error(data.message ?? 'Could not delete agent.');
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
    if (!editingAgentId) {
        return;
    }

    const submitBtn = editForm.querySelector('button[type="submit"]');
    setButtonLoading(submitBtn, true);

    const fd = new FormData(editForm);

    try {
        const res = await fetch(agentUrl(editingAgentId), {
            method: 'POST',
            headers: jsonHeaders(),
            body: fd,
        });

        const data = await res.json().catch(() => ({}));

        if (res.ok) {
            toastr.success(data.message ?? 'Agent updated.');
            closeEditAgentModal();
            if (data.agent) {
                updateAgentRowFromPayload(data.agent);
                applyAgentListFilter();
            }
        } else if (res.status === 422) {
            if (!toastValidationErrors(data)) {
                toastr.error('Validation failed.');
            }
        } else {
            toastr.error(data.message ?? 'Could not update agent.');
        }
    } catch {
        toastr.error('Network error.');
    } finally {
        setButtonLoading(submitBtn, false);
    }
});

permissionsForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!permissionsAgentId) {
        return;
    }

    const submitBtn = permissionsForm.querySelector('button[type="submit"]');
    setButtonLoading(submitBtn, true);

    const boxes = permissionsCheckboxes?.querySelectorAll('input[type="checkbox"][name="permissions[]"]') ?? [];
    const permissions = Array.from(boxes)
        .filter((c) => c.checked)
        .map((c) => c.value);

    try {
        const res = await fetch(permissionsUrl(permissionsAgentId), {
            method: 'PUT',
            headers: jsonHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ permissions }),
        });

        const data = await res.json().catch(() => ({}));

        if (res.ok) {
            toastr.success(data.message ?? 'Permissions updated.');
            closePermissionsModal();
        } else if (res.status === 422) {
            if (!toastValidationErrors(data)) {
                toastr.error('Validation failed.');
            }
        } else {
            toastr.error(data.message ?? 'Could not update permissions.');
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
            toastr.success(data.message ?? 'Agent created successfully.');
            form.reset();
            resetPasswordVisibility();
            closeAgentModal();
            if (data.agent) {
                appendAgentRowFromPayload(data.agent);
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
