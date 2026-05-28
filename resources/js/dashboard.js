import Chart from 'chart.js/auto';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

const CONFETTI_ICON = '🎉';

/**
 * @param {string} name
 * @param {{ salesDoneTotal?: number, performanceRate?: number, isTopPerformer?: boolean }} [options]
 */
function formatAgentLabel(name, options = {}) {
    const { salesDoneTotal, performanceRate, isTopPerformer = false } = options;
    let label = name;

    if (performanceRate != null) {
        label = `${name} (${formatPerformanceRate(performanceRate)})`;
    } else if (salesDoneTotal != null) {
        label = `${name} (${salesDoneTotal})`;
    }

    return isTopPerformer ? `${CONFETTI_ICON} ${label}` : label;
}

/**
 * @param {number} rate
 */
function formatPerformanceRate(rate) {
    const value = Number(rate);
    if (!Number.isFinite(value)) {
        return '0%';
    }

    return Number.isInteger(value) ? `${value}%` : `${value.toFixed(1)}%`;
}

/**
 * @param {object} config
 * @returns {Set<string>}
 */
function parseTopPerformerAgentIds(config) {
    const raw = config?.topPerformerAgentIds;
    if (Array.isArray(raw) && raw.length > 0) {
        return new Set(raw.map((id) => String(id)));
    }

    if (config?.topPerformerAgentId != null) {
        return new Set([String(config.topPerformerAgentId)]);
    }

    return new Set();
}

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
    const highlightTopPerformer = cfgEl.dataset.highlightTopPerformer === 'true';
    const highestPerformanceButton = document.getElementById('admin-agent-chart-highest-performance-button');
    const highestPerformanceMenu = document.getElementById('admin-agent-chart-highest-performance-menu');
    let sortByHighestPerformance = Boolean(config.sortByHighestPerformance);
    const topPerformerState = {
        ids: highlightTopPerformer ? parseTopPerformerAgentIds(config) : new Set(),
    };
    const useGlobalDashboardFilters = Boolean(document.getElementById('dashboard-filters-form'));
    const chartEmptyEl = document.getElementById('dashboard-agent-performance-empty');

    if (!labels.length) {
        return;
    }

    if (!agents.length && !useGlobalDashboardFilters) {
        return;
    }

    let chartFilterState = { range: 'year', start: '', end: '' };

    const rootStyles = getComputedStyle(document.documentElement);
    const navy = rootStyles.getPropertyValue('--color-concierge-navy').trim() || '#152c49';
    const muted = rootStyles.getPropertyValue('--color-concierge-muted').trim() || '#64748b';

    /**
     * @param {Array<{ id?: number, name: string, salesDoneTotal?: number, performanceRate?: number, color: string, data: number[] }>} chartAgents
     */
    function mapChartDatasets(chartAgents) {
        return chartAgents.map((a) => {
            const agentId = a.id != null ? String(a.id) : '';
            const isTopPerformer = highlightTopPerformer && topPerformerState.ids.has(agentId);
            const showPerformanceRate = sortByHighestPerformance && a.performanceRate != null;
            const showSalesTotal = highlightTopPerformer && !showPerformanceRate && a.salesDoneTotal != null;

            return {
                label: formatAgentLabel(a.name, {
                    salesDoneTotal: showSalesTotal ? a.salesDoneTotal : undefined,
                    performanceRate: showPerformanceRate ? a.performanceRate : undefined,
                    isTopPerformer,
                }),
                agentId,
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
            };
        });
    }

    function setChartEmptyVisible(isEmpty) {
        if (chartEmptyEl) {
            chartEmptyEl.classList.toggle('hidden', !isEmpty);
            canvas.classList.toggle('hidden', isEmpty);
        }
    }

    setChartEmptyVisible(agents.length === 0);

    const chart = new Chart(canvas, {
        type: 'line',
        data: { labels, datasets: agents.length ? mapChartDatasets(agents) : [] },
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

    if (!useGlobalDashboardFilters && (!filterButton || !filterMenu || !filterLabel)) {
        return;
    }

    if (agentOptions.length && agentFilterWrap) {
        agentCombobox = createSearchableAgentCombobox(agentFilterWrap, agentOptions, {
            highlightTopPerformer,
            topPerformerState,
            getSortByHighestPerformance: () => sortByHighestPerformance,
        });
    }

    function dashboardGlobalFilterParams() {
        const form = document.getElementById('dashboard-filters-form');
        if (!form) {
            return new URLSearchParams();
        }

        return new URLSearchParams(new FormData(form));
    }

    function applyTopPerformerFromConfig(nextConfig) {
        if (!highlightTopPerformer) {
            return;
        }

        topPerformerState.ids = parseTopPerformerAgentIds(nextConfig);
        agentCombobox?.refreshTopPerformerDisplay?.();
    }

    function updateChartData(nextConfig) {
        const nextLabels = Array.isArray(nextConfig?.labels) ? nextConfig.labels : [];
        const nextAgents = Array.isArray(nextConfig?.agents) ? nextConfig.agents : [];
        if (!nextLabels.length) {
            return;
        }

        sortByHighestPerformance = Boolean(nextConfig?.sortByHighestPerformance);
        applyTopPerformerFromConfig(nextConfig);
        if (Array.isArray(nextConfig?.agentOptions)) {
            agentCombobox?.setAgentOptions?.(nextConfig.agentOptions);
        }

        const isEmpty = nextAgents.length === 0;
        setChartEmptyVisible(isEmpty);
        chart.data.labels = nextLabels;
        chart.data.datasets = isEmpty ? [] : mapChartDatasets(nextAgents);
        chart.update();
    }

    function currentAgentIdParam() {
        return agentCombobox?.getAgentId() ?? '';
    }

    async function fetchAndApplyChartData(range = 'year', startDate = '', endDate = '') {
        if (!chartEndpoint) {
            return false;
        }

        const params = useGlobalDashboardFilters
            ? dashboardGlobalFilterParams()
            : new URLSearchParams({ range });

        if (!useGlobalDashboardFilters) {
            if (startDate) {
                params.set('start_date', startDate);
            }
            if (endDate) {
                params.set('end_date', endDate);
            }
        }

        const agentId = currentAgentIdParam();
        if (agentId) {
            params.set('agent_id', agentId);
        }

        if (sortByHighestPerformance) {
            params.set('highest_performance', '1');
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
        if (!filterButton || !filterLabel) {
            return false;
        }

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
        if (customDatePicker || !filterButton) {
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
            onClose: async (selectedDates, _dateStr, instance) => {
                if (!Array.isArray(selectedDates) || selectedDates.length !== 2) {
                    return;
                }

                const start = instance.formatDate(selectedDates[0], 'Y-m-d');
                const end = instance.formatDate(selectedDates[1], 'Y-m-d');
                await runFilterFetch('custom', `${start} - ${end}`, start, end);
            },
        });

        return customDatePicker;
    }

    if (highestPerformanceButton && highestPerformanceMenu) {
        initHighestPerformanceDropdown({
            button: highestPerformanceButton,
            menu: highestPerformanceMenu,
            chartEndpoint,
            getDashboardParams: dashboardGlobalFilterParams,
            onAgentSelect: async (agentId) => {
                sortByHighestPerformance = true;
                agentCombobox?.selectAgentById?.(agentId);
                agentCombobox?.setDisabled(true);
                await fetchAndApplyChartData();
                agentCombobox?.setDisabled(false);
            },
            formatPerformanceRate,
            highlightTopPerformer,
            topPerformerState,
        });
    }

    agentCombobox?.onSelectionCommit(async () => {
        agentCombobox?.setDisabled(true);

        if (useGlobalDashboardFilters) {
            await fetchAndApplyChartData();
        } else if (filterButton && filterLabel) {
            const { range, start, end } = chartFilterState;
            const labelBefore = filterLabel.textContent;
            filterButton.disabled = true;
            filterLabel.textContent = 'Loading...';
            await fetchAndApplyChartData(range, start, end);
            filterButton.disabled = false;
            filterLabel.textContent = labelBefore;
        }

        agentCombobox?.setDisabled(false);
    });

    if (!useGlobalDashboardFilters && filterButton && filterMenu && filterLabel) {
        function closeMenu() {
            filterMenu.classList.add('hidden');
            filterButton.setAttribute('aria-expanded', 'false');
        }

        function openMenu() {
            filterMenu.classList.remove('hidden');
            filterButton.setAttribute('aria-expanded', 'true');
        }

        closeMenu();

        filterButton.addEventListener('click', () => {
            if (filterMenu.classList.contains('hidden')) {
                openMenu();
                return;
            }
            closeMenu();
        });

        filterMenu.querySelectorAll('.admin-agent-chart-filter-option').forEach((option) => {
            option.addEventListener('click', async () => {
                const filterKey = option.dataset.filter || 'year';
                const displayLabel = option.dataset.filterLabel || option.textContent?.trim() || 'Filter';
                if (filterKey === 'custom') {
                    const picker = ensureCustomDatePicker();
                    closeMenu();
                    picker?.open();
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
}

/**
 * Searchable agent filter (combobox pattern) for the admin performance chart.
 *
 * @param {HTMLElement} mountEl
 * @param {Array<{ id: number, name: string, salesDoneTotal?: number }>} agents
 * @param {{ highlightTopPerformer?: boolean, topPerformerState?: { ids: Set<string> }, getSortByHighestPerformance?: () => boolean }} [options]
 */
function createSearchableAgentCombobox(mountEl, agents, options = {}) {
    const highlightTopPerformer = Boolean(options.highlightTopPerformer);
    const topPerformerState = options.topPerformerState ?? { ids: new Set() };
    const getSortByHighestPerformance = options.getSortByHighestPerformance ?? (() => false);

    function displayAgentName(choice) {
        if (choice.id === '') {
            return choice.name;
        }

        const isTop = highlightTopPerformer && topPerformerState.ids.has(choice.id);
        const showPerformanceRate = getSortByHighestPerformance() && choice.performanceRate != null;
        const showSalesTotal = highlightTopPerformer && !showPerformanceRate && choice.salesDoneTotal != null;

        return formatAgentLabel(choice.name, {
            salesDoneTotal: showSalesTotal ? choice.salesDoneTotal : undefined,
            performanceRate: showPerformanceRate ? choice.performanceRate : undefined,
            isTopPerformer: isTop,
        });
    }
    const inputClass =
        'w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-2 pr-9 text-sm font-medium text-concierge-navy placeholder:text-concierge-muted transition hover:bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/25';

    /** @type {string} numeric id as string or '' */
    let selectedAgentId = '';
    let selectedAgentName = '';
    let listOpen = false;
    /** @type {(() => Promise<void>) | null} */
    let onCommitCallback = null;

    function buildAllChoices(agentsList) {
        return [
            { id: '', name: 'All agents' },
            ...agentsList.map((a) => ({
                id: String(a.id),
                name: String(a.name),
                salesDoneTotal: a.salesDoneTotal ?? 0,
                performanceRate: a.performanceRate ?? 0,
            })),
        ];
    }

    let allChoices = buildAllChoices(agents);

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
        if (selectedAgentId === '') {
            input.value = '';
        } else {
            const choice = allChoices.find((c) => c.id === selectedAgentId);
            input.value = choice ? displayAgentName(choice) : selectedAgentName;
        }
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
            li.textContent = displayAgentName(choice);
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
        /** @param {string | number} agentId */
        selectAgentById(agentId) {
            const id = agentId === '' || agentId == null ? '' : String(agentId);
            const choice = allChoices.find((c) => c.id === id);
            if (!choice && id !== '') {
                return;
            }

            selectedAgentId = id;
            selectedAgentName = choice?.name ?? '';
            syncInputToSelection();
            if (listOpen) {
                renderOptions();
            }
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
        refreshTopPerformerDisplay() {
            syncInputToSelection();
            if (listOpen) {
                renderOptions();
            }
        },
        /** @param {Array<{ id: number, name: string, salesDoneTotal?: number }>} nextAgents */
        setAgentOptions(nextAgents) {
            allChoices = buildAllChoices(nextAgents);
            if (selectedAgentId !== '' && !allChoices.some((c) => c.id === selectedAgentId)) {
                selectedAgentId = '';
                selectedAgentName = '';
            }
            syncInputToSelection();
            if (listOpen) {
                renderOptions();
            }
        },
    };
}

/**
 * @param {{
 *   button: HTMLButtonElement,
 *   menu: HTMLElement,
 *   chartEndpoint: string,
 *   getDashboardParams: () => URLSearchParams,
 *   onAgentSelect: (agentId: string) => Promise<void>,
 *   formatPerformanceRate: (rate: number) => string,
 *   highlightTopPerformer: boolean,
 *   topPerformerState: { ids: Set<string> },
 * }} options
 */
function initHighestPerformanceDropdown(options) {
    const {
        button,
        menu,
        chartEndpoint,
        getDashboardParams,
        onAgentSelect,
        formatPerformanceRate,
        highlightTopPerformer,
        topPerformerState,
    } = options;

    let isLoading = false;

    function closeMenu() {
        menu.classList.add('hidden');
        button.setAttribute('aria-expanded', 'false');
    }

    function openMenu() {
        menu.classList.remove('hidden');
        button.setAttribute('aria-expanded', 'true');
    }

    function renderLoading() {
        menu.replaceChildren();
        const loading = document.createElement('p');
        loading.className = 'px-4 py-3 text-sm text-concierge-muted';
        loading.textContent = 'Loading rankings...';
        menu.appendChild(loading);
    }

    function renderEmpty() {
        menu.replaceChildren();
        const empty = document.createElement('p');
        empty.className = 'px-4 py-3 text-sm text-concierge-muted';
        empty.textContent = 'No agents match the selected filters.';
        menu.appendChild(empty);
    }

    /**
     * @param {Array<{ id: number, name: string, performanceRate?: number, salesDoneTotal?: number }>} agents
     */
    function renderAgentList(agents) {
        menu.replaceChildren();

        if (!agents.length) {
            renderEmpty();
            return;
        }

        const sorted = [...agents].sort(
            (a, b) => (Number(b.performanceRate) || 0) - (Number(a.performanceRate) || 0),
        );

        sorted.forEach((agent, index) => {
            const agentId = String(agent.id);
            const isTopPerformer = highlightTopPerformer && topPerformerState.ids.has(agentId);
            const item = document.createElement('button');
            item.type = 'button';
            item.role = 'menuitem';
            item.className =
                'flex w-full cursor-pointer items-center justify-between gap-3 px-4 py-2.5 text-left text-sm text-concierge-navy transition hover:bg-slate-50';
            item.dataset.agentId = agentId;

            const nameWrap = document.createElement('span');
            nameWrap.className = 'min-w-0 flex items-center gap-2';
            const rank = document.createElement('span');
            rank.className = 'w-5 shrink-0 text-xs font-semibold tabular-nums text-concierge-muted';
            rank.textContent = String(index + 1);
            const name = document.createElement('span');
            name.className = 'truncate font-medium';
            name.textContent = isTopPerformer ? `${CONFETTI_ICON} ${agent.name}` : agent.name;
            nameWrap.appendChild(rank);
            nameWrap.appendChild(name);

            const rate = document.createElement('span');
            rate.className = 'shrink-0 font-semibold tabular-nums text-concierge-navy';
            rate.textContent = formatPerformanceRate(Number(agent.performanceRate) || 0);

            item.appendChild(nameWrap);
            item.appendChild(rate);

            item.addEventListener('click', async () => {
                if (isLoading) {
                    return;
                }
                isLoading = true;
                item.disabled = true;
                try {
                    await onAgentSelect(agentId);
                } finally {
                    isLoading = false;
                    item.disabled = false;
                    closeMenu();
                }
            });

            menu.appendChild(item);
        });
    }

    async function loadRankings() {
        if (!chartEndpoint) {
            renderEmpty();
            return;
        }

        renderLoading();
        isLoading = true;
        button.disabled = true;

        const params = getDashboardParams();
        params.set('highest_performance', '1');

        try {
            const response = await fetch(`${chartEndpoint}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                renderEmpty();
                return;
            }

            const payload = await response.json();
            if (highlightTopPerformer && Array.isArray(payload?.topPerformerAgentIds)) {
                topPerformerState.ids = new Set(payload.topPerformerAgentIds.map((id) => String(id)));
            }

            const agentOptions = Array.isArray(payload?.agentOptions) ? payload.agentOptions : [];
            renderAgentList(agentOptions);
        } catch {
            renderEmpty();
        } finally {
            isLoading = false;
            button.disabled = false;
        }
    }

    button.addEventListener('click', async () => {
        if (isLoading) {
            return;
        }

        if (!menu.classList.contains('hidden')) {
            closeMenu();
            return;
        }

        openMenu();
        await loadRankings();
    });

    document.addEventListener('click', (event) => {
        if (menu.classList.contains('hidden')) {
            return;
        }
        if (!(event.target instanceof Node)) {
            return;
        }
        if (menu.contains(event.target) || button.contains(event.target)) {
            return;
        }
        closeMenu();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAgentPerformanceChart);
} else {
    initAgentPerformanceChart();
}
