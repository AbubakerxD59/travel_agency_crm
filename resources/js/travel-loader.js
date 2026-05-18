const LOADER_SCENE_HTML = `
    <div class="travel-loader__scene" aria-hidden="true">
        <div class="travel-loader__cloud travel-loader__cloud--1"></div>
        <div class="travel-loader__cloud travel-loader__cloud--2"></div>
        <div class="travel-loader__globe">
            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="2" opacity="0.35"></circle>
                <ellipse cx="24" cy="24" rx="8" ry="20" stroke="currentColor" stroke-width="1.5" opacity="0.5"></ellipse>
                <path d="M4 24h40" stroke="currentColor" stroke-width="1.5" opacity="0.45"></path>
                <path d="M8 14c8 4 24 4 32 0M8 34c8-4 24-4 32 0" stroke="currentColor" stroke-width="1.5" opacity="0.35" stroke-linecap="round"></path>
            </svg>
        </div>
        <div class="travel-loader__orbit">
            <svg class="travel-loader__plane" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20.5 11.5L4 6.5l2.5 5.5-2 2 3.5 1 2 5.5 2-1.5 2 1.5 2-5.5 3.5-1-2-2 2.5-5.5z" fill="currentColor"></path>
            </svg>
        </div>
    </div>`;

function escapeHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

/**
 * @param {'sm'|'md'|'lg'} size
 * @param {string} [message]
 */
export function loaderHtml(size = 'md', message = '') {
    const sizeClass = size === 'sm' || size === 'lg' ? `travel-loader--${size}` : 'travel-loader--md';
    const ariaLabel = message || 'Loading';
    const messageHtml =
        message && size !== 'sm'
            ? `<p class="travel-loader__message">${escapeHtml(message)}</p>`
            : '';

    return `<div class="travel-loader ${sizeClass}" role="status" aria-live="polite" aria-busy="true" aria-label="${escapeHtml(ariaLabel)}">${LOADER_SCENE_HTML}${messageHtml}</div>`;
}

/**
 * @param {HTMLButtonElement|null|undefined} button
 * @param {boolean} isLoading
 */
export function setButtonLoading(button, isLoading) {
    if (!(button instanceof HTMLButtonElement)) {
        return;
    }

    if (isLoading) {
        if (button.dataset.loading === '1') {
            return;
        }
        button.dataset.loading = '1';
        button.dataset.originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = loaderHtml('sm');
        return;
    }

    if (button.dataset.originalHtml) {
        button.innerHTML = button.dataset.originalHtml;
    }
    delete button.dataset.originalHtml;
    delete button.dataset.loading;
    button.disabled = false;
}

const OVERLAY_ID = 'travel-loader-overlay';
let overlayShowCount = 0;

function getOverlay() {
    return document.getElementById(OVERLAY_ID);
}

/**
 * @param {string} [message]
 */
export function showOverlay(message = 'Loading…') {
    const overlay = getOverlay();
    if (!overlay) {
        return;
    }

    const messageEl = overlay.querySelector('.travel-loader__message');
    if (messageEl) {
        messageEl.textContent = message;
    }

    overlayShowCount += 1;
    overlay.classList.remove('pointer-events-none', 'invisible', 'opacity-0');
    overlay.classList.add('pointer-events-auto', 'visible', 'opacity-100');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
}

export function hideOverlay() {
    const overlay = getOverlay();
    if (!overlay) {
        return;
    }

    overlayShowCount = Math.max(0, overlayShowCount - 1);
    if (overlayShowCount > 0) {
        return;
    }

    overlay.classList.add('pointer-events-none', 'invisible', 'opacity-0');
    overlay.classList.remove('pointer-events-auto', 'visible', 'opacity-100');
    overlay.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');
}

/**
 * @param {HTMLElement|null|undefined} container
 * @param {string} [message]
 * @param {'sm'|'md'|'lg'} [size]
 */
export function showInContainer(container, message = 'Loading…', size = 'md') {
    if (!(container instanceof HTMLElement)) {
        return;
    }

    container.classList.add('travel-loader-host--active');
    let layer = container.querySelector(':scope > .travel-loader-host__layer');

    if (!layer) {
        layer = document.createElement('div');
        layer.className = 'travel-loader-host__layer';
        container.appendChild(layer);
    }

    layer.innerHTML = loaderHtml(size, message);
    layer.classList.remove('hidden');
    layer.setAttribute('aria-hidden', 'false');
    container.setAttribute('aria-busy', 'true');
}

/**
 * @param {HTMLElement|null|undefined} container
 */
export function hideFromContainer(container) {
    if (!(container instanceof HTMLElement)) {
        return;
    }

    const layer = container.querySelector(':scope > .travel-loader-host__layer');
    layer?.classList.add('hidden');
    layer?.setAttribute('aria-hidden', 'true');
    container.classList.remove('travel-loader-host--active');
    container.removeAttribute('aria-busy');
}

/**
 * @param {HTMLElement|null|undefined} element
 * @param {'sm'|'md'|'lg'} [size]
 * @param {string} [message]
 */
export function renderInto(element, size = 'md', message = '') {
    if (!(element instanceof HTMLElement)) {
        return;
    }

    element.innerHTML = loaderHtml(size, message);
    element.classList.remove('hidden');
    element.setAttribute('aria-busy', 'true');
}

export const TravelLoader = {
    loaderHtml,
    setButtonLoading,
    showOverlay,
    hideOverlay,
    showInContainer,
    hideFromContainer,
    renderInto,
};
