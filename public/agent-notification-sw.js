/* eslint-disable no-restricted-globals */

const DEFAULT_ICON = '/favicon.ico';
const POLL_INTERVAL_MS = 1500;
const MAX_ALERTED_IDS = 100;

let pollUrl = '';
/** @type {Set<string>} */
let alertTypes = new Set(['lead_assigned', 'lead_reassigned', 'folder_payment_pending']);
/** @type {Set<string>} */
const alertedIds = new Set();
/** @type {ReturnType<typeof setInterval> | null} */
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
        silent: true,
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

    if (data.type === 'CONFIGURE') {
        if (typeof data.pollUrl === 'string' && data.pollUrl !== '') {
            pollUrl = data.pollUrl;
        }

        if (Array.isArray(data.alertTypes) && data.alertTypes.length > 0) {
            alertTypes = new Set(data.alertTypes.map((type) => String(type)));
        }

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

/**
 * @param {Array<{ id?: string, type?: string }>} items
 */
function filterAlertable(items) {
    return items.filter((item) => {
        const id = item?.id != null ? String(item.id) : '';
        const type = String(item?.type ?? '');

        return id !== '' && alertTypes.has(type) && !alertedIds.has(id);
    });
}

/**
 * @param {Array<{ id?: string }>} items
 */
function markAlerted(items) {
    items.forEach((item) => {
        alertedIds.add(String(item.id));
    });

    while (alertedIds.size > MAX_ALERTED_IDS) {
        const oldest = alertedIds.values().next().value;
        alertedIds.delete(oldest);
    }
}

/**
 * @param {{ id?: string, title?: string, message?: string, customer_name?: string, type?: string, url?: string }} item
 */
function buildNotificationContent(item) {
    const customerName = item.customer_name || 'Customer';
    const title = item.title || 'Notification';
    let body = item.message || '';

    if (item.type === 'lead_reassigned') {
        body = `Lead for ${customerName} has been reassigned to you.`;
    } else if (item.type === 'lead_assigned' && !body) {
        body = `A new lead for ${customerName} has been assigned to you.`;
    } else if (item.type === 'folder_payment_pending' && !body) {
        body = 'New folder payment(s) are pending your approval.';
    }

    return {
        title,
        body,
        tag: `notification-${item.id ?? Date.now()}`,
        url: item.url || '/',
    };
}

/**
 * @param {{ id?: string, title?: string, message?: string, customer_name?: string, type?: string, url?: string }} item
 */
async function showAlertNotification(item) {
    const content = buildNotificationContent(item);

    await self.registration.showNotification(content.title, {
        body: content.body,
        icon: DEFAULT_ICON,
        badge: DEFAULT_ICON,
        tag: content.tag,
        renotify: true,
        silent: !['folder_payment_pending', 'lead_assigned', 'lead_reassigned'].includes(
            String(item.type ?? ''),
        ),
        requireInteraction: false,
        data: {
            url: content.url,
        },
    });
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
        const newItems = Array.isArray(payload.new_notifications) ? payload.new_notifications : [];
        const pending = filterAlertable(newItems);

        const clients = await self.clients.matchAll({
            type: 'window',
            includeUncontrolled: true,
        });
        const hasVisibleClient = clients.some((client) => client.visibilityState === 'visible');

        if (pending.length > 0) {
            markAlerted(pending);

            if (!hasVisibleClient) {
                for (const item of pending) {
                    await showAlertNotification(item);
                }
            }
        }

        clients.forEach((client) => {
            client.postMessage({
                type: 'NOTIFICATION_POLL',
                payload,
            });
        });
    } catch {
        // Retry on the next interval.
    }
}
