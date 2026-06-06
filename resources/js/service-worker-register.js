const DEFAULT_SERVICE_WORKER_URL = '/agent-notification-sw.js';

/**
 * Normalize service worker URL for deployments where asset() incorrectly includes /public/.
 *
 * @param {string | undefined | null} url
 */
export function normalizeServiceWorkerUrl(url) {
    if (!url || typeof url !== 'string') {
        return DEFAULT_SERVICE_WORKER_URL;
    }

    try {
        const parsed = new URL(url, window.location.origin);
        parsed.pathname = parsed.pathname.replace(
            /^\/public\/(agent-notification-sw\.js)$/,
            '/$1',
        );

        return `${parsed.pathname}${parsed.search}${parsed.hash}`;
    } catch {
        return url.replace(/\/public\/(agent-notification-sw\.js)/, '/$1');
    }
}

/**
 * @param {string} scriptUrl
 */
export function registerAppServiceWorker(scriptUrl) {
    const normalizedUrl = normalizeServiceWorkerUrl(scriptUrl);

    return navigator.serviceWorker.register(normalizedUrl, { scope: '/' });
}
