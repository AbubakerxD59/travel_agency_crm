import Chart from 'chart.js/auto';

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

    new Chart(canvas, {
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
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAgentPerformanceChart);
} else {
    initAgentPerformanceChart();
}
