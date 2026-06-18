/**
 * Warn before leaving folder create/edit when the form has unsaved changes.
 */
function initFolderFormUnsavedGuard() {
    const form = document.getElementById('lead-create-form');
    if (!form) {
        return;
    }

    let allowLeave = false;
    let initialSnapshot = '';

    const LEAVE_MESSAGE = 'Unsaved changes will be lost.';

    function captureSnapshot() {
        const data = new FormData(form);
        const parts = [];

        for (const [key, value] of data.entries()) {
            if (value instanceof File) {
                continue;
            }
            parts.push(`${key}=${String(value)}`);
        }

        parts.sort();

        return parts.join('\n');
    }

    function hasUnsavedChanges() {
        if (allowLeave) {
            return false;
        }

        return captureSnapshot() !== initialSnapshot;
    }

    function confirmLeavePage() {
        return window.confirm(`${LEAVE_MESSAGE}\n\nLeave this page?`);
    }

    function isSkippableLink(anchor) {
        if (!(anchor instanceof HTMLAnchorElement)) {
            return true;
        }

        if (anchor.target === '_blank' || anchor.hasAttribute('download')) {
            return true;
        }

        if (anchor.dataset.allowNavigation === 'true') {
            return true;
        }

        const href = anchor.getAttribute('href') ?? '';
        if (href === '' || href.startsWith('#') || href.startsWith('javascript:')) {
            return true;
        }

        if (anchor.closest('form') === form) {
            return true;
        }

        try {
            const url = new URL(anchor.href, window.location.origin);
            if (url.origin !== window.location.origin) {
                return false;
            }

            if (url.pathname === window.location.pathname && url.search === window.location.search) {
                return true;
            }
        } catch {
            return true;
        }

        return false;
    }

    function refreshSnapshot() {
        initialSnapshot = captureSnapshot();
    }

    refreshSnapshot();

    form.addEventListener('submit', () => {
        allowLeave = true;
    });

    window.addEventListener('beforeunload', (event) => {
        if (!hasUnsavedChanges()) {
            return;
        }

        event.preventDefault();
        event.returnValue = LEAVE_MESSAGE;
    });

    document.addEventListener(
        'click',
        (event) => {
            if (!hasUnsavedChanges()) {
                return;
            }

            const anchor = event.target instanceof Element ? event.target.closest('a[href]') : null;
            if (!anchor || isSkippableLink(anchor)) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const destination = anchor.href;
            if (!confirmLeavePage()) {
                return;
            }

            allowLeave = true;
            window.location.assign(destination);
        },
        true,
    );

    if (window.history && typeof window.history.pushState === 'function') {
        window.history.pushState({ folderFormGuard: true }, '', window.location.href);

        window.addEventListener('popstate', () => {
            if (!hasUnsavedChanges()) {
                return;
            }

            window.history.pushState({ folderFormGuard: true }, '', window.location.href);

            if (!confirmLeavePage()) {
                return;
            }

            allowLeave = true;

            const folderListUrl = form.dataset.folderListUrl;
            if (folderListUrl) {
                window.location.assign(folderListUrl);
                return;
            }

            history.go(-2);
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFolderFormUnsavedGuard);
} else {
    initFolderFormUnsavedGuard();
}
