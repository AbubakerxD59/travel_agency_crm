import toastr from 'toastr';

import 'toastr/build/toastr.min.css';
import { initImageDropzone } from './image-dropzone';

toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    timeOut: 5000,
    extendedTimeOut: 2000,
};

const uploadRoot = document.querySelector('[data-folder-payment-image-upload]');
if (uploadRoot) {
    const existingUrl = uploadRoot.dataset.existingImageUrl || null;
    initImageDropzone(uploadRoot, {
        existingUrl: existingUrl || null,
        existingAlt: 'Payment receipt',
        onError: (message) => toastr.error(message),
    });
}

const form = document.getElementById('folder-payment-image-form');
form?.addEventListener('submit', (e) => {
    const input = form.querySelector('[data-company-image-input]');
    if (!input?.files?.[0]) {
        e.preventDefault();
        toastr.error('Please choose an image to upload.');
    }
});
