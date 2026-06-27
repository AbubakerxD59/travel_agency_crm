import {
    applyImageFileToDropzone,
    getImageFileFromClipboard,
    initImageDropzone,
} from './image-dropzone';

function showPaymentImageError(message) {
    if (window.toastr) {
        window.toastr.error(message);
        return;
    }

    window.alert(message);
}

/**
 * @param {Element} wrapper
 */
function initPaymentReceiptUpload(wrapper) {
    if (!(wrapper instanceof Element) || wrapper.dataset.paymentReceiptInitialized === '1') {
        return;
    }

    const uploadRoot = wrapper.querySelector('[data-company-image-upload]');
    if (!(uploadRoot instanceof Element)) {
        return;
    }

    wrapper.dataset.paymentReceiptInitialized = '1';

    const removeCheckbox = wrapper.querySelector('[data-payment-remove-image]');
    const existingUrl = wrapper.dataset.existingImageUrl || null;

    initImageDropzone(uploadRoot, {
        existingUrl,
        existingAlt: 'Payment receipt',
        onError: showPaymentImageError,
        removeExistingInput: removeCheckbox instanceof HTMLInputElement ? removeCheckbox : null,
        onNewFile: () => {
            if (removeCheckbox instanceof HTMLInputElement) {
                removeCheckbox.checked = false;
            }
        },
    });
}

/**
 * @param {ParentNode} container
 */
export function initPaymentReceiptUploads(container = document) {
    container.querySelectorAll('[data-payment-receipt-upload]').forEach((wrapper) => {
        initPaymentReceiptUpload(wrapper);
    });
}

function initFolderFormPaymentImages() {
    const form = document.getElementById('lead-create-form');
    const paymentRows = document.getElementById('payment-rows');

    if (!form || !paymentRows) {
        return;
    }

    initPaymentReceiptUploads(paymentRows);

    new MutationObserver(() => {
        initPaymentReceiptUploads(paymentRows);
    }).observe(paymentRows, {
        childList: true,
        subtree: true,
    });

    document.addEventListener('paste', (event) => {
        if (!(event.target instanceof Element)) {
            return;
        }

        const row = event.target.closest('.payment-row');
        if (!(row instanceof HTMLElement) || row.dataset.locked === '1') {
            return;
        }

        const file = getImageFileFromClipboard(event.clipboardData);
        if (!file) {
            return;
        }

        const wrapper = row.querySelector('[data-payment-receipt-upload]');
        const uploadRoot = wrapper?.querySelector('[data-company-image-upload]');
        if (!(uploadRoot instanceof Element)) {
            return;
        }

        event.preventDefault();
        applyImageFileToDropzone(uploadRoot, file, showPaymentImageError);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFolderFormPaymentImages);
} else {
    initFolderFormPaymentImages();
}
