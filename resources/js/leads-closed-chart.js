import Chart from 'chart.js/auto';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

const NAVY = '#0f2744';
const MUTED = '#64748b';

/**
 * @param {Array<{ label: string, color: string, data: number[], totalLeads?: number }>} datasets
 */
function mapChartDatasets(datasets) {
    return datasets.map((dataset) => ({
        label: dataset.label,
        data: dataset.data,
        totalLeads: dataset.totalLeads ?? 0,
        backgroundColor: hexToRgba(dataset.color, 0.85),
        borderColor: dataset.color,
        borderWidth: 0,
        borderRadius: 6,
        borderSkipped: false,
        hoverBackgroundColor: dataset.color,
        stack: 'closed',
    }));
}

function hexToRgba(hex, alpha) {
    const normalized = hex.replace('#', '');
    const full = normalized.length === 3
        ? normalized.split('').map((c) => c + c).join('')
        : normalized;
    const int = Number.parseInt(full, 16);
    const r = (int >> 16) & 255;
    const g = (int >> 8) & 255;
    const b = int & 255;

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function initClosedLeadsChart() {
    const cfgEl = document.getElementById('closed-leads-chart-config');
    const canvas = document.getElementById('closed-leads-chart-canvas');
    const emptyEl = document.getElementById('closed-leads-chart-empty');
    const totalEl = document.getElementById('closed-leads-chart-total');
    const sourceSelect = document.getElementById('closed-leads-chart-source');
    const dateButton = document.getElementById('closed-leads-chart-date-button');
    const dateMenu = document.getElementById('closed-leads-chart-date-menu');
    const dateLabel = document.getElementById('closed-leads-chart-date-label');
    const dateRangeInput = document.getElementById('closed-leads-chart-date-range');
    const startDateInput = document.getElementById('closed-leads-chart-start-date');
    const endDateInput = document.getElementById('closed-leads-chart-end-date');
    const agentIdInput = document.getElementById('closed-leads-chart-agent-id');
    const companyIdInput = document.getElementById('closed-leads-chart-company-id');

    if (!cfgEl || !canvas) {
        return;
    }

    let initial;
    try {
        initial = JSON.parse(cfgEl.textContent || '{}');
    } catch {
        initial = { labels: [], datasets: [], totalClosed: 0 };
    }

    const chartEndpoint = cfgEl.dataset.chartEndpoint || '';
    let filterState = {
        range: dateRangeInput?.value || 'year',
        start: startDateInput?.value || '',
        end: endDateInput?.value || '',
        source: sourceSelect?.value || '',
    };

    function setEmptyVisible(isEmpty) {
        if (emptyEl) {
            emptyEl.classList.toggle('hidden', !isEmpty);
        }
    }

    function updateTotal(total) {
        if (totalEl) {
            totalEl.textContent = new Intl.NumberFormat().format(total ?? 0);
        }
    }

    setEmptyVisible(!(initial.datasets?.length));
    updateTotal(initial.totalClosed);

    const chart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: initial.labels ?? [],
            datasets: initial.datasets?.length ? mapChartDatasets(initial.datasets) : [],
        },
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
                        color: NAVY,
                        font: { family: "'Inter', system-ui, sans-serif", size: 12 },
                    },
                },
                tooltip: {
                    backgroundColor: NAVY,
                    titleFont: { family: "'Inter', system-ui, sans-serif", size: 13 },
                    bodyFont: { family: "'Inter', system-ui, sans-serif", size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label(context) {
                            const closed = Number(context.parsed.y ?? 0);
                            const totalLeads = Number(context.dataset?.totalLeads ?? 0);
                            const numberFormatter = new Intl.NumberFormat();
                            return `${context.dataset.label}: ${numberFormatter.format(closed)} (${numberFormatter.format(totalLeads)})`;
                        },
                        footer(items) {
                            const sum = items.reduce((acc, item) => acc + (item.parsed.y ?? 0), 0);
                            return sum > 0 ? `Total: ${sum}` : '';
                        },
                    },
                },
            },
            scales: {
                x: {
                    stacked: true,
                    grid: { display: false },
                    ticks: {
                        color: MUTED,
                        font: { family: "'Inter', system-ui, sans-serif", size: 11 },
                        maxRotation: 45,
                        minRotation: 0,
                    },
                    border: { color: 'rgba(148, 163, 184, 0.35)' },
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    grid: { color: 'rgba(148, 163, 184, 0.2)' },
                    ticks: {
                        color: MUTED,
                        font: { family: "'Inter', system-ui, sans-serif", size: 11 },
                        precision: 0,
                    },
                    border: { display: false },
                },
            },
        },
    });

    function applyChartPayload(payload) {
        const datasets = payload?.datasets ?? [];
        const isEmpty = datasets.length === 0;
        setEmptyVisible(isEmpty);
        updateTotal(payload?.totalClosed ?? 0);
        chart.data.labels = payload?.labels ?? [];
        chart.data.datasets = isEmpty ? [] : mapChartDatasets(datasets);
        chart.update();
    }

    async function fetchChartData() {
        if (!chartEndpoint) {
            return;
        }

        const params = new URLSearchParams({
            chart_date_range: filterState.range,
            chart_source: filterState.source,
        });

        if (filterState.start) {
            params.set('chart_start_date', filterState.start);
        }
        if (filterState.end) {
            params.set('chart_end_date', filterState.end);
        }
        if (agentIdInput?.value) {
            params.set('chart_agent_id', agentIdInput.value);
        }
        if (companyIdInput?.value) {
            params.set('chart_company_id', companyIdInput.value);
        }

        const response = await fetch(`${chartEndpoint}?${params.toString()}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) {
            throw new Error('Could not load chart data.');
        }

        applyChartPayload(await response.json());
    }

    if (sourceSelect) {
        sourceSelect.addEventListener('change', () => {
            filterState.source = sourceSelect.value;
            fetchChartData().catch(console.error);
        });
    }

    if (dateButton && dateMenu && dateRangeInput && startDateInput && endDateInput && dateLabel) {
        function closeDateMenu() {
            dateMenu.classList.add('hidden');
            dateButton.setAttribute('aria-expanded', 'false');
        }

        function openDateMenu() {
            dateMenu.classList.remove('hidden');
            dateButton.setAttribute('aria-expanded', 'true');
        }

        dateButton.addEventListener('click', () => {
            if (dateMenu.classList.contains('hidden')) {
                openDateMenu();
                return;
            }
            closeDateMenu();
        });

        document.addEventListener('click', (event) => {
            if (dateMenu.classList.contains('hidden')) {
                return;
            }
            if (dateMenu.contains(event.target) || dateButton.contains(event.target)) {
                return;
            }
            closeDateMenu();
        });

        const pickerInput = document.createElement('input');
        pickerInput.type = 'text';
        pickerInput.className = 'sr-only';
        pickerInput.setAttribute('aria-hidden', 'true');
        document.body.appendChild(pickerInput);

        const picker = flatpickr(pickerInput, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            maxDate: 'today',
            allowInput: false,
            clickOpens: false,
            positionElement: dateButton,
            defaultDate: startDateInput.value && endDateInput.value
                ? [startDateInput.value, endDateInput.value]
                : [],
            onClose: (selectedDates, _dateStr, instance) => {
                if (!Array.isArray(selectedDates) || selectedDates.length !== 2) {
                    return;
                }

                filterState.range = 'custom';
                filterState.start = instance.formatDate(selectedDates[0], 'Y-m-d');
                filterState.end = instance.formatDate(selectedDates[1], 'Y-m-d');
                dateRangeInput.value = 'custom';
                startDateInput.value = filterState.start;
                endDateInput.value = filterState.end;
                dateLabel.textContent = `${filterState.start} - ${filterState.end}`;
                closeDateMenu();
                fetchChartData().catch(console.error);
            },
        });

        dateMenu.querySelectorAll('.closed-leads-chart-date-option').forEach((option) => {
            option.addEventListener('click', () => {
                const filter = option.dataset.filter || '';
                const label = option.dataset.filterLabel || option.textContent?.trim() || 'Date';

                if (filter === 'custom') {
                    closeDateMenu();
                    picker.open();
                    return;
                }

                filterState.range = filter;
                filterState.start = '';
                filterState.end = '';
                dateRangeInput.value = filter;
                startDateInput.value = '';
                endDateInput.value = '';
                dateLabel.textContent = label;
                closeDateMenu();
                fetchChartData().catch(console.error);
            });
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initClosedLeadsChart);
} else {
    initClosedLeadsChart();
}
