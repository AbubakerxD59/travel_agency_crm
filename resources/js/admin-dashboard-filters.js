import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

function initAdminDashboardFilters() {
    const form = document.getElementById('dashboard-filters-form');
    const companySelect = document.getElementById('dashboard-company-filter');
    const filterButton = document.getElementById('dashboard-date-filter-button');
    const filterMenu = document.getElementById('dashboard-date-filter-menu');
    const filterLabel = document.getElementById('dashboard-date-filter-label');
    const dateRangeInput = document.getElementById('dashboard-date-range-input');
    const startDateInput = document.getElementById('dashboard-start-date-input');
    const endDateInput = document.getElementById('dashboard-end-date-input');

    if (
        !form ||
        !companySelect ||
        !filterButton ||
        !filterMenu ||
        !filterLabel ||
        !dateRangeInput ||
        !startDateInput ||
        !endDateInput
    ) {
        return;
    }

    companySelect.addEventListener('change', () => {
        form.submit();
    });

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

    document.addEventListener('click', (event) => {
        if (filterMenu.classList.contains('hidden')) {
            return;
        }
        if (filterMenu.contains(event.target) || filterButton.contains(event.target)) {
            return;
        }
        closeMenu();
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
        positionElement: filterButton,
        defaultDate:
            startDateInput.value && endDateInput.value ? [startDateInput.value, endDateInput.value] : [],
        onClose: (selectedDates, _dateStr, instance) => {
            if (!Array.isArray(selectedDates) || selectedDates.length !== 2) {
                return;
            }

            dateRangeInput.value = 'custom';
            startDateInput.value = instance.formatDate(selectedDates[0], 'Y-m-d');
            endDateInput.value = instance.formatDate(selectedDates[1], 'Y-m-d');
            filterLabel.textContent = `${startDateInput.value} - ${endDateInput.value}`;
            form.submit();
        },
    });

    filterMenu.querySelectorAll('.dashboard-date-filter-option').forEach((option) => {
        option.addEventListener('click', () => {
            const filter = option.dataset.filter || '';
            const label = option.dataset.filterLabel || option.textContent?.trim() || 'Date filter';

            if (filter === 'custom') {
                closeMenu();
                picker.open();
                return;
            }

            dateRangeInput.value = filter;
            startDateInput.value = '';
            endDateInput.value = '';
            filterLabel.textContent = label;
            closeMenu();
            form.submit();
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminDashboardFilters);
} else {
    initAdminDashboardFilters();
}
