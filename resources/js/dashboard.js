import Chart from 'chart.js/auto';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

function initAgentPerformanceChart() {
    const cfgEl = document.getElementById('dashboard-agent-chart-config');
    const canvas = document.getElementById('dashboard-agent-performance-chart');
    if (!cfgEl || !canvas) {
        return;
    }

    let config;
    try {
        config = JSON.parse(cfgEl.textContent || '{}');
    } catch {
        return;
    }

    const labels = config.labels ?? [];
    const agents = config.agents ?? [];
    const agentOptions = Array.isArray(config.agentOptions) ? config.agentOptions : [];
    if (!labels.length || !agents.length) {
        return;
    }

    let chartFilterState = { range: 'year', start: '', end: '' };

    const rootStyles = getComputedStyle(document.documentElement);
    const navy = rootStyles.getPropertyValue('--color-concierge-navy').trim() || '#152c49';
    const muted = rootStyles.getPropertyValue('--color-concierge-muted').trim() || '#64748b';

    const datasets = agents.map((a) => ({
        label: a.name,
        data: a.data,
        borderColor: a.color,
        backgroundColor: `${a.color}33`,
        pointBackgroundColor: a.color,
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 5,
        borderWidth: 2.5,
        tension: 0.35,
        fill: false,
    }));

    const chart = new Chart(canvas, {
        type: 'line',
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        boxHeight: 10,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 16,
                        color: navy,
                        font: { family: "'Inter', system-ui, sans-serif", size: 12 },
                    },
                },
                tooltip: {
                    backgroundColor: navy,
                    titleFont: { family: "'Inter', system-ui, sans-serif", size: 13 },
                    bodyFont: { family: "'Inter', system-ui, sans-serif", size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                },
                title: { display: false },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: muted,
                        font: { family: "'Inter', system-ui, sans-serif", size: 11 },
                    },
                    border: { color: 'rgba(148, 163, 184, 0.35)' },
                },
                y: {
                    beginAtZero: true,
                    suggestedMax: undefined,
                    grid: { color: 'rgba(148, 163, 184, 0.2)' },
                    ticks: {
                        color: muted,
                        font: { family: "'Inter', system-ui, sans-serif", size: 11 },
                        precision: 0,
                    },
                    border: { display: false },
                },
            },
        },
    });

    const filterButton = document.getElementById('admin-agent-chart-filter-button');
    const filterMenu = document.getElementById('admin-agent-chart-filter-menu');
    const filterLabel = document.getElementById('admin-agent-chart-filter-label');
    const agentFilterWrap = document.getElementById('admin-agent-chart-agent-filter-wrap');
    const chartEndpoint = cfgEl.dataset.chartEndpoint;
    let customDatePicker = null;

    /**
     * @type {{ getAgentId: () => string, setDisabled: (v: boolean) => void } | null}
     */
    let agentCombobox = null;

    if (!filterButton || !filterMenu || !filterLabel) {
        return;
    }

    if (agentOptions.length && agentFilterWrap) {
        agentCombobox = createSearchableAgentCombobox(agentFilterWrap, agentOptions);
    }

    function closeMenu() {
        filterMenu.classList.add('hidden');
        filterButton.setAttribute('aria-expanded', 'false');
    }

    function openMenu() {
        filterMenu.classList.remove('hidden');
        filterButton.setAttribute('aria-expanded', 'true');
    }

    // Force closed state on initial render.
    closeMenu();

    filterButton.addEventListener('click', () => {
        if (filterMenu.classList.contains('hidden')) {
            openMenu();
            return;
        }
        closeMenu();
    });

    function updateChartData(nextConfig) {
        const nextLabels = Array.isArray(nextConfig?.labels) ? nextConfig.labels : [];
        const nextAgents = Array.isArray(nextConfig?.agents) ? nextConfig.agents : [];
        if (!nextLabels.length || !nextAgents.length) {
            return;
        }

        chart.data.labels = nextLabels;
        chart.data.datasets = nextAgents.map((agent) => ({
            label: agent.name,
            data: agent.data,
            borderColor: agent.color,
            backgroundColor: `${agent.color}33`,
            pointBackgroundColor: agent.color,
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 5,
            borderWidth: 2.5,
            tension: 0.35,
            fill: false,
        }));
        chart.update();
    }

    function currentAgentIdParam() {
        return agentCombobox?.getAgentId() ?? '';
    }

    async function fetchAndApplyChartData(range, startDate, endDate) {
        if (!chartEndpoint) {
            return false;
        }

        const params = new URLSearchParams({ range });
        if (startDate) {
            params.set('start_date', startDate);
        }
        if (endDate) {
            params.set('end_date', endDate);
        }
        const agentId = currentAgentIdParam();
        if (agentId) {
            params.set('agent_id', agentId);
        }

        try {
            const response = await fetch(`${chartEndpoint}?${params.toString()}`, {
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                return false;
            }

            const payload = await response.json();
            updateChartData(payload);
            return true;
        } catch {
            return false;
        }
    }

    async function runFilterFetch(filterKey, displayLabel, startDate = '', endDate = '') {
        const originalLabel = filterLabel.textContent;
        filterButton.disabled = true;
        agentCombobox?.setDisabled(true);
        filterLabel.textContent = 'Loading...';
        const loaded = await fetchAndApplyChartData(filterKey, startDate, endDate);
        filterButton.disabled = false;
        agentCombobox?.setDisabled(false);
        filterLabel.textContent = loaded ? displayLabel : originalLabel;
        if (loaded) {
            chartFilterState = {
                range: filterKey,
                start: startDate || '',
                end: endDate || '',
            };
        }
        return loaded;
    }

    function ensureCustomDatePicker() {
        if (customDatePicker) {
            return customDatePicker;
        }

        const pickerInput = document.createElement('input');
        pickerInput.type = 'text';
        pickerInput.className = 'sr-only';
        pickerInput.setAttribute('aria-hidden', 'true');
        document.body.appendChild(pickerInput);

        customDatePicker = flatpickr(pickerInput, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            maxDate: 'today',
            allowInput: false,
            clickOpens: false,
            positionElement: filterButton,
            onClose: async (selectedDates, dateStr, instance) => {
                if (!Array.isArray(selectedDates) || selectedDates.length !== 2) {
                    return;
                }

                const startDate = instance.formatDate(selectedDates[0], 'Y-m-d');
                const endDate = instance.formatDate(selectedDates[1], 'Y-m-d');
                const loaded = await runFilterFetch('custom', `${startDate} - ${endDate}`, startDate, endDate);
                if (!loaded) {
                    return;
                }
            },
        });

        return customDatePicker;
    }

    agentCombobox?.onSelectionCommit(async () => {
        const { range, start, end } = chartFilterState;
        const labelBefore = filterLabel.textContent;
        filterButton.disabled = true;
        agentCombobox?.setDisabled(true);
        filterLabel.textContent = 'Loading...';
        await fetchAndApplyChartData(range, start, end);
        filterButton.disabled = false;
        agentCombobox?.setDisabled(false);
        filterLabel.textContent = labelBefore;
    });

    filterMenu.querySelectorAll('.admin-agent-chart-filter-option').forEach((option) => {
        option.addEventListener('click', async () => {
            const filterKey = option.dataset.filter || 'month';
            const displayLabel = option.dataset.filterLabel || option.textContent?.trim() || 'Filter';
            if (filterKey === 'custom') {
                const picker = ensureCustomDatePicker();
                closeMenu();
                picker.open();
                return;
            }

            await runFilterFetch(filterKey, displayLabel, '', '');
            closeMenu();
        });
    });

    document.addEventListener('click', (event) => {
        if (filterMenu.classList.contains('hidden')) {
            return;
        }
        if (filterMenu.contains(event.target) || filterButton.contains(event.target)) {
            return;
        }
        closeMenu();
    });
}

/**
 * Searchable agent filter (combobox pattern) for the admin performance chart.
 *
 * @param {HTMLElement} mountEl
 * @param {Array<{ id: number, name: string }>} agents
 */
function createSearchableAgentCombobox(mountEl, agents) {
    const inputClass =
        'w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-2 pr-9 text-sm font-medium text-concierge-navy placeholder:text-concierge-muted transition hover:bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/25';

    /** @type {string} numeric id as string or '' */
    let selectedAgentId = '';
    let selectedAgentName = '';
    let listOpen = false;
    /** @type {(() => Promise<void>) | null} */
    let onCommitCallback = null;

    const normalized = [...agents].sort((a, b) =>
        String(a.name).localeCompare(String(b.name), undefined, {
            sensitivity: 'base',
        }),
    );

    const allChoices = [{ id: '', name: 'All agents' }, ...normalized.map((a) => ({ id: String(a.id), name: String(a.name) }))];

    const labelEl = document.createElement('label');
    labelEl.className = 'mb-1 block text-xs font-medium text-concierge-muted';
    labelEl.setAttribute('for', 'admin-agent-chart-agent-combobox');
    labelEl.textContent = 'Agent';

    const root = document.createElement('div');
    root.className = 'relative w-full min-w-0';

    const inputWrap = document.createElement('div');
    inputWrap.className = 'relative';

    const input = document.createElement('input');
    input.type = 'search';
    input.id = 'admin-agent-chart-agent-combobox';
    input.name = 'admin_agent_performance_agent';
    input.autocomplete = 'off';
    input.autocapitalize = 'off';
    input.spellcheck = false;
    input.placeholder = 'All agents';
    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-expanded', 'false');
    input.setAttribute('aria-controls', 'admin-agent-chart-agent-listbox');
    input.className = inputClass;

    const chevronBtn = document.createElement('button');
    chevronBtn.type = 'button';
    chevronBtn.tabIndex = -1;
    chevronBtn.setAttribute('aria-label', 'Open agent list');
    chevronBtn.className =
        'pointer-events-none absolute right-2 top-1/2 z-[1] -translate-y-1/2 rounded p-1 text-concierge-muted';

    chevronBtn.innerHTML =
        '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>';

    const listEl = document.createElement('ul');
    listEl.id = 'admin-agent-chart-agent-listbox';
    listEl.role = 'listbox';
    listEl.className =
        'absolute z-30 mt-1 max-h-56 w-full overflow-auto rounded-xl border border-slate-200 bg-white py-1 shadow-lg ring-1 ring-black/5';

    /** @type {HTMLLIElement[]} */
    let optionEls = [];
    /** @type {number} */
    let activeIndex = -1;

    inputWrap.appendChild(input);
    inputWrap.appendChild(chevronBtn);
    root.appendChild(inputWrap);
    root.appendChild(listEl);
    mountEl.appendChild(labelEl);
    mountEl.appendChild(root);

    function filteredChoices() {
        const q = input.value.trim().toLowerCase();
        if (!q) {
            return allChoices;
        }
        return allChoices.filter((c) => c.name.toLowerCase().includes(q));
    }

    function closeList(commitDisplay = true) {
        listOpen = false;
        activeIndex = -1;
        listEl.classList.add('hidden');
        chevronBtn.setAttribute('aria-label', 'Open agent list');
        input.setAttribute('aria-expanded', 'false');
        if (commitDisplay) {
            syncInputToSelection();
        }
    }

    function openList() {
        listOpen = true;
        listEl.classList.remove('hidden');
        chevronBtn.setAttribute('aria-label', 'Close agent list');
        input.setAttribute('aria-expanded', 'true');
        const items = filteredChoices();
        if (!items.length) {
            activeIndex = -1;
            renderOptions();
            return;
        }
        const idx = items.findIndex((c) => c.id === selectedAgentId);
        activeIndex = idx >= 0 ? idx : 0;
        renderOptions();
        scrollActiveOptionIntoView();
    }

    function syncInputToSelection() {
        input.value = selectedAgentId === '' ? '' : selectedAgentName;
        input.placeholder = 'All agents';
    }

    function selectChoice(choice) {
        selectedAgentId = choice.id;
        selectedAgentName = choice.id === '' ? '' : choice.name;
        closeList(true);
        void onCommitCallback?.();
    }

    function renderOptions() {
        listEl.replaceChildren();
        optionEls = [];
        const items = filteredChoices();
        items.forEach((choice, idx) => {
            const li = document.createElement('li');
            li.role = 'option';
            li.tabIndex = -1;
            li.dataset.agentId = choice.id;
            li.className =
                'cursor-pointer px-3.5 py-2 text-sm text-concierge-navy transition hover:bg-slate-50 aria-selected:bg-slate-100';
            li.textContent = choice.name;
            const isSel = selectedAgentId === choice.id;
            li.setAttribute('aria-selected', String(isSel));
            if (idx === activeIndex && activeIndex >= 0) {
                li.classList.add('bg-slate-100');
            }
            li.addEventListener('mousedown', (event) => {
                event.preventDefault();
            });
            li.addEventListener('click', () => {
                selectChoice(choice);
            });
            listEl.appendChild(li);
            optionEls.push(li);
        });
        if (items.length === 0) {
            const empty = document.createElement('li');
            empty.className = 'cursor-default px-3.5 py-2 text-sm text-concierge-muted';
            empty.textContent = 'No agents match.';
            empty.setAttribute('role', 'presentation');
            listEl.appendChild(empty);
        }
    }

    function scrollActiveOptionIntoView() {
        const el = optionEls[activeIndex];
        el?.scrollIntoView({ block: 'nearest' });
    }

    input.addEventListener('focus', () => {
        if (!listOpen) {
            input.select();
            openList();
        }
    });

    input.addEventListener('input', () => {
        if (!listOpen) {
            openList();
            return;
        }
        const items = filteredChoices();
        activeIndex = items.length ? 0 : -1;
        renderOptions();
        scrollActiveOptionIntoView();
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            syncInputToSelection();
            closeList(true);
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            if (!listOpen) {
                openList();
                return;
            }
            const items = filteredChoices();
            if (!items.length) {
                return;
            }
            if (activeIndex < 0) {
                activeIndex = 0;
            } else {
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
            }
            renderOptions();
            scrollActiveOptionIntoView();
            return;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            if (!listOpen) {
                openList();
                return;
            }
            const items = filteredChoices();
            if (!items.length) {
                return;
            }
            if (activeIndex < 0) {
                activeIndex = Math.max(0, items.length - 1);
            } else {
                activeIndex = Math.max(0, activeIndex - 1);
            }
            renderOptions();
            scrollActiveOptionIntoView();
            return;
        }

        if (event.key === 'Enter' && listOpen) {
            event.preventDefault();
            const items = filteredChoices();
            if (activeIndex >= 0 && activeIndex < items.length) {
                selectChoice(items[activeIndex]);
            }
            return;
        }

        if (event.key === 'Tab') {
            closeList(true);
        }
    });

    document.addEventListener('pointerdown', (event) => {
        if (!(event.target instanceof Node)) {
            return;
        }
        if (root.contains(event.target)) {
            return;
        }
        if (listOpen) {
            syncInputToSelection();
            closeList(true);
        }
    });

    listEl.classList.add('hidden');
    syncInputToSelection();

    return {
        getAgentId() {
            return selectedAgentId;
        },
        setDisabled(v) {
            input.disabled = v;
            if (v && listOpen) {
                syncInputToSelection();
                closeList(false);
                input.placeholder = 'All agents';
            }
            chevronBtn.classList.toggle('opacity-40', Boolean(v));
        },
        /** @param {() => Promise<void>} cb */
        onSelectionCommit(cb) {
            onCommitCallback = cb;
        },
    };
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAgentPerformanceChart);
} else {
    initAgentPerformanceChart();
}
