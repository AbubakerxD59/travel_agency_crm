import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

function initAdminFolderPaymentFilters() {
    const dateInput = document.getElementById('payment-date-filter');
    if (!(dateInput instanceof HTMLInputElement)) {
        return;
    }

    flatpickr(dateInput, {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd M Y',
        allowInput: false,
        defaultDate: dateInput.value || null,
        position: 'auto left',
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminFolderPaymentFilters);
} else {
    initAdminFolderPaymentFilters();
}
