import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

function initFolderFormDatePickers() {
    const form = document.getElementById('lead-create-form');
    if (!form) {
        return;
    }

    const initialized = new WeakSet();

    function setupPicker(input) {
        if (!(input instanceof HTMLInputElement) || initialized.has(input)) {
            return;
        }

        const originalValue = input.value;
        input.type = 'text';
        input.autocomplete = 'off';
        input.placeholder = input.placeholder || 'Select date';

        flatpickr(input, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',
            allowInput: false,
            defaultDate: originalValue || null,
            position: 'auto left',
        });

        initialized.add(input);
    }

    function setupAll() {
        form.querySelectorAll('input[type="date"]').forEach(setupPicker);
    }

    setupAll();

    const observer = new MutationObserver(() => {
        setupAll();
    });

    observer.observe(form, {
        childList: true,
        subtree: true,
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFolderFormDatePickers);
} else {
    initFolderFormDatePickers();
}
