const MAX_IMAGE_BYTES = 2 * 1024 * 1024;
const IMAGE_TYPES = new Set(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

/** @type {WeakMap<Element, { objectUrl: string | null, existingUrl: string | null, hasNewFile: boolean }>} */
const imageUploadState = new WeakMap();

/** @type {WeakMap<Element, { removeExistingInput?: HTMLInputElement | null, onNewFile?: () => void }>} */
const imageDropzoneOptions = new WeakMap();

/**
 * @param {DataTransfer | ClipboardEvent['clipboardData']} clipboardData
 * @returns {File | null}
 */
export function getImageFileFromClipboard(clipboardData) {
    if (!clipboardData) {
        return null;
    }

    for (const item of clipboardData.items) {
        if (item.type.startsWith('image/')) {
            return item.getAsFile();
        }
    }

    return null;
}

/** @param {Element | null | undefined} root */
function imageUploadParts(root) {
    if (!(root instanceof Element)) {
        return null;
    }

    return {
        root,
        dropzone: root.querySelector('[data-company-image-dropzone]'),
        input: root.querySelector('[data-company-image-input]'),
        empty: root.querySelector('[data-company-image-empty]'),
        preview: root.querySelector('[data-company-image-preview]'),
        previewImg: root.querySelector('[data-company-image-preview-img]'),
        removeBtn: root.querySelector('[data-company-image-remove]'),
    };
}

/** @param {{ root: Element }} parts */
function revokeImageObjectUrl(parts) {
    const state = imageUploadState.get(parts.root);
    if (state?.objectUrl) {
        URL.revokeObjectURL(state.objectUrl);
        state.objectUrl = null;
    }
}

/** @param {File} file */
function validateImageFile(file) {
    if (!IMAGE_TYPES.has(file.type)) {
        return 'Please choose a JPEG, PNG, GIF, or WebP image.';
    }
    if (file.size > MAX_IMAGE_BYTES) {
        return 'Image must be 2 MB or smaller.';
    }
    return null;
}

/**
 * @param {NonNullable<ReturnType<typeof imageUploadParts>>} parts
 * @param {{ showPreview: boolean, src?: string, alt?: string }} opts
 */
function renderImageUpload(parts, { showPreview, src = '', alt = '' }) {
    const { dropzone, empty, preview, previewImg } = parts;
    if (!dropzone || !empty || !preview || !previewImg) {
        return;
    }

    if (showPreview && src) {
        previewImg.src = src;
        previewImg.alt = alt;
        preview.classList.remove('hidden');
        empty.classList.add('hidden');
        dropzone.classList.add('company-image-dropzone--has-preview');
        return;
    }

    previewImg.removeAttribute('src');
    previewImg.alt = '';
    preview.classList.add('hidden');
    empty.classList.remove('hidden');
    dropzone.classList.remove('company-image-dropzone--has-preview');
}

/**
 * @param {Element} root
 * @param {{ existingUrl?: string | null, existingAlt?: string }} opts
 */
export function resetImageDropzone(root, { existingUrl = null, existingAlt = 'Image preview' } = {}) {
    const parts = imageUploadParts(root);
    if (!parts?.input) {
        return;
    }

    revokeImageObjectUrl(parts);
    parts.input.value = '';

    imageUploadState.set(parts.root, {
        objectUrl: null,
        existingUrl: existingUrl || null,
        hasNewFile: false,
    });

    if (existingUrl) {
        renderImageUpload(parts, { showPreview: true, src: existingUrl, alt: existingAlt });
    } else {
        renderImageUpload(parts, { showPreview: false });
    }
}

/**
 * @param {NonNullable<ReturnType<typeof imageUploadParts>>} parts
 * @param {File} file
 * @param {(message: string) => void} onError
 */
function applyFileToImageUpload(parts, file, onError) {
    const validationError = validateImageFile(file);
    if (validationError) {
        onError(validationError);
        return;
    }

    revokeImageObjectUrl(parts);

    const objectUrl = URL.createObjectURL(file);
    const transfer = new DataTransfer();
    transfer.items.add(file);
    parts.input.files = transfer.files;

    const state = imageUploadState.get(parts.root) ?? {
        objectUrl: null,
        existingUrl: null,
        hasNewFile: false,
    };
    state.objectUrl = objectUrl;
    state.hasNewFile = true;
    imageUploadState.set(parts.root, state);

    renderImageUpload(parts, {
        showPreview: true,
        src: objectUrl,
        alt: file.name,
    });

    const opts = imageDropzoneOptions.get(parts.root);
    opts?.onNewFile?.();
}

/**
 * @param {NonNullable<ReturnType<typeof imageUploadParts>>} parts
 * @param {{ keepExisting?: boolean, existingAlt?: string }} opts
 */
function clearImageUpload(parts, { keepExisting = false, existingAlt = 'Image preview' } = {}) {
    revokeImageObjectUrl(parts);
    parts.input.value = '';

    const state = imageUploadState.get(parts.root) ?? {
        objectUrl: null,
        existingUrl: null,
        hasNewFile: false,
    };
    state.hasNewFile = false;
    imageUploadState.set(parts.root, state);

    if (keepExisting && state.existingUrl) {
        renderImageUpload(parts, {
            showPreview: true,
            src: state.existingUrl,
            alt: existingAlt,
        });
        return;
    }

    renderImageUpload(parts, { showPreview: false });
}

/**
 * @param {Element} root
 * @param {File} file
 * @param {(message: string) => void} onError
 */
export function applyImageFileToDropzone(root, file, onError = () => {}) {
    const parts = imageUploadParts(root);
    if (!parts) {
        return;
    }

    applyFileToImageUpload(parts, file, onError);
}

/**
 * @param {Element} root
 * @param {{ existingUrl?: string | null, existingAlt?: string, onError?: (message: string) => void, removeExistingInput?: HTMLInputElement | null, onNewFile?: () => void }} opts
 */
export function initImageDropzone(root, {
    existingUrl = null,
    existingAlt = 'Image preview',
    onError = () => {},
    removeExistingInput = null,
    onNewFile = () => {},
} = {}) {
    const parts = imageUploadParts(root);
    if (!parts?.dropzone || !parts.input) {
        return;
    }

    if (root.dataset.imageDropzoneInitialized === '1') {
        resetImageDropzone(root, { existingUrl, existingAlt });
        imageDropzoneOptions.set(parts.root, { removeExistingInput, onNewFile });
        return;
    }

    root.dataset.imageDropzoneInitialized = '1';
    imageDropzoneOptions.set(parts.root, { removeExistingInput, onNewFile });

    resetImageDropzone(root, { existingUrl, existingAlt });

    let dragDepth = 0;

    const handlePastedFile = (file) => {
        if (file) {
            applyFileToImageUpload(parts, file, onError);
        }
    };

    parts.dropzone.addEventListener('click', (e) => {
        if (e.target instanceof Element && e.target.closest('[data-company-image-remove]')) {
            return;
        }
        parts.input.click();
    });

    parts.dropzone.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            parts.input.click();
        }
    });

    parts.input.addEventListener('change', () => {
        const file = parts.input.files?.[0];
        if (file) {
            applyFileToImageUpload(parts, file, onError);
        }
    });

    parts.removeBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const state = imageUploadState.get(parts.root);
        const opts = imageDropzoneOptions.get(parts.root);

        if (state?.existingUrl && !state.hasNewFile) {
            if (opts?.removeExistingInput instanceof HTMLInputElement) {
                opts.removeExistingInput.checked = true;
            }
            state.existingUrl = null;
            imageUploadState.set(parts.root, state);
            clearImageUpload(parts, { keepExisting: false });
            return;
        }

        clearImageUpload(parts, {
            keepExisting: Boolean(state?.existingUrl && state.hasNewFile),
            existingAlt,
        });
    });

    parts.root.addEventListener('paste', (e) => {
        const file = getImageFileFromClipboard(e.clipboardData);
        if (!file) {
            return;
        }
        e.preventDefault();
        handlePastedFile(file);
    });

    parts.dropzone.addEventListener('dragenter', (e) => {
        e.preventDefault();
        dragDepth += 1;
        parts.dropzone.classList.add('company-image-dropzone--dragover');
    });

    parts.dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
    });

    parts.dropzone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dragDepth -= 1;
        if (dragDepth <= 0) {
            dragDepth = 0;
            parts.dropzone.classList.remove('company-image-dropzone--dragover');
        }
    });

    parts.dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dragDepth = 0;
        parts.dropzone.classList.remove('company-image-dropzone--dragover');
        const file = e.dataTransfer?.files?.[0];
        if (file) {
            applyFileToImageUpload(parts, file, onError);
        }
    });
}
