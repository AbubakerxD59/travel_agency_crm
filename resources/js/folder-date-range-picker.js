import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

function initFolderDateRangePicker() {
    const rangeInputs = document.querySelectorAll('[data-folder-date-range-picker="true"]');
    if (rangeInputs.length === 0) {
        return;
    }

    rangeInputs.forEach((rangeInput) => {
        const container = rangeInput.parentElement;
        if (!container) {
            return;
        }

        const fromInput = container.querySelector('input[name="travel_arrival_from"]');
        const toInput = container.querySelector('input[name="travel_arrival_to"]');
        if (!(fromInput instanceof HTMLInputElement) || !(toInput instanceof HTMLInputElement)) {
            return;
        }

        const initialDates = [];
        if (fromInput.value) {
            initialDates.push(fromInput.value);
        }
        if (toInput.value) {
            initialDates.push(toInput.value);
        }

        flatpickr(rangeInput, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            altInput: false,
            allowInput: false,
            defaultDate: initialDates,
            position: 'auto left',
            onChange: (selectedDates, dateStr, instance) => {
                if (selectedDates.length === 0) {
                    fromInput.value = '';
                    toInput.value = '';
                    rangeInput.value = '';
                    return;
                }

                if (selectedDates.length >= 1) {
                    fromInput.value = instance.formatDate(selectedDates[0], 'Y-m-d');
                }

                if (selectedDates.length >= 2) {
                    toInput.value = instance.formatDate(selectedDates[1], 'Y-m-d');
                    rangeInput.value = `${instance.formatDate(selectedDates[0], 'd M Y')} to ${instance.formatDate(selectedDates[1], 'd M Y')}`;

                    return;
                }

                const singleDate = instance.formatDate(selectedDates[0], 'Y-m-d');
                fromInput.value = singleDate;
                toInput.value = singleDate;
                rangeInput.value = instance.formatDate(selectedDates[0], 'd M Y');
            },
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFolderDateRangePicker);
} else {
    initFolderDateRangePicker();
}
