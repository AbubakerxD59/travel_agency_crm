/* eslint-disable no-restricted-globals */

const DEFAULT_ICON = '/favicon.ico';
const POLL_INTERVAL_MS = 2000;
let pollUrl = '';
let pollTimer = null;

self.addEventListener('install', (event) => {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', (event) => {
    let payload = {
        title: 'New lead assigned',
        body: 'A new lead has been assigned to you.',
        url: '/',
        tag: 'lead-assigned',
    };

    try {
        if (event.data) {
            payload = { ...payload, ...event.data.json() };
        }
    } catch {
        // Use defaults when payload is not JSON.
    }

    const options = {
        body: payload.body,
        icon: DEFAULT_ICON,
        badge: DEFAULT_ICON,
        tag: payload.tag || 'lead-assigned',
        renotify: true,
        silent: false,
        requireInteraction: false,
        data: {
            url: payload.url || '/',
        },
    };

    event.waitUntil(self.registration.showNotification(payload.title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = event.notification?.data?.url || '/';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if ('focus' in client) {
                    return client.focus();
                }
            }

            if (self.clients.openWindow) {
                return self.clients.openWindow(targetUrl);
            }

            return undefined;
        }),
    );
});

self.addEventListener('message', (event) => {
    const data = event.data;
    if (!data || typeof data !== 'object') {
        return;
    }

    if (data.type === 'CONFIGURE' && typeof data.pollUrl === 'string' && data.pollUrl !== '') {
        pollUrl = data.pollUrl;
        startPolling();
    }

    if (data.type === 'STOP') {
        stopPolling();
    }
});

function startPolling() {
    stopPolling();
    pollTimer = setInterval(() => {
        void pollNotifications();
    }, POLL_INTERVAL_MS);
    void pollNotifications();
}

function stopPolling() {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
}

async function pollNotifications() {
    if (!pollUrl) {
        return;
    }

    try {
        const response = await fetch(pollUrl, {
            method: 'GET',
            credentials: 'include',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            cache: 'no-store',
        });

        if (!response.ok) {
            return;
        }

        const payload = await response.json();
        const clients = await self.clients.matchAll({
            type: 'window',
            includeUncontrolled: true,
        });

        clients.forEach((client) => {
            client.postMessage({
                type: 'AGENT_NOTIFICATION_POLL',
                payload,
            });
        });
    } catch {
        // Retry on the next interval.
    }
}
