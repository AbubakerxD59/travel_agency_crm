function sanitizeInteger(value) {
    return String(value).replace(/\D/g, '');
}

function sanitizeDecimal(value) {
    let cleaned = String(value).replace(/[^\d.]/g, '');
    const dotIndex = cleaned.indexOf('.');

    if (dotIndex !== -1) {
        cleaned = cleaned.slice(0, dotIndex + 1) + cleaned.slice(dotIndex + 1).replace(/\./g, '');
    }

    return cleaned;
}

export function bindFolderNumericInput(input) {
    if (!(input instanceof HTMLInputElement)) {
        return;
    }

    if (input.dataset.folderNumericBound === '1') {
        return;
    }

    const mode = input.dataset.folderNumeric === 'integer' ? 'integer' : 'decimal';
    input.dataset.folderNumericBound = '1';
    input.type = 'text';

    if (input.readOnly) {
        return;
    }

    input.addEventListener('input', () => {
        const next = mode === 'integer'
            ? sanitizeInteger(input.value)
            : sanitizeDecimal(input.value);

        if (next !== input.value) {
            input.value = next;
        }
    });

    input.addEventListener('paste', (event) => {
        event.preventDefault();
        const pasted = event.clipboardData?.getData('text') ?? '';
        const next = mode === 'integer'
            ? sanitizeInteger(pasted)
            : sanitizeDecimal(pasted);
        input.value = next;
    });
}

export function configureFolderNumericInput(input, mode = 'decimal') {
    if (!(input instanceof HTMLInputElement)) {
        return input;
    }

    input.dataset.folderNumeric = mode;
    input.inputMode = mode === 'integer' ? 'numeric' : 'decimal';
    input.autocomplete = 'off';
    bindFolderNumericInput(input);

    return input;
}

export function initFolderNumericInputs(root = document) {
    const scope = root instanceof Document ? root.documentElement : root;

    scope.querySelectorAll('[data-folder-numeric]').forEach(bindFolderNumericInput);

    const form = document.getElementById('lead-create-form');
    if (!(form instanceof HTMLElement) || form.dataset.folderNumericObserver === '1') {
        return;
    }

    form.dataset.folderNumericObserver = '1';

    new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (!(node instanceof HTMLElement)) {
                    return;
                }

                if (node.matches('[data-folder-numeric]')) {
                    bindFolderNumericInput(node);
                }

                node.querySelectorAll('[data-folder-numeric]').forEach(bindFolderNumericInput);
            });
        });
    }).observe(form, { childList: true, subtree: true });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initFolderNumericInputs());
} else {
    initFolderNumericInputs();
}
