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
    if (!labels.length || !agents.length) {
        return;
    }

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
    const chartEndpoint = cfgEl.dataset.chartEndpoint;
    let customDatePicker = null;

    if (!filterButton || !filterMenu || !filterLabel) {
        return;
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
        filterLabel.textContent = 'Loading...';
        const loaded = await fetchAndApplyChartData(filterKey, startDate, endDate);
        filterButton.disabled = false;
        filterLabel.textContent = loaded ? displayLabel : originalLabel;
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

            await runFilterFetch(filterKey, displayLabel);
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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAgentPerformanceChart);
} else {
    initAgentPerformanceChart();
}
