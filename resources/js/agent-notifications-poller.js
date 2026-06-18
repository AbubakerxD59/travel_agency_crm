import { LeadAlertAudio } from './lead-alert-audio';
import { registerAppServiceWorker } from './service-worker-register';

function initAgentNotificationPoller() {
    const notificationIcon = document.getElementById('agent-notification-icon');
    const notificationDot = document.getElementById('agent-notification-dot');
    const leadsUnreadBadge = document.getElementById('agent-leads-unread-badge');
    const notificationDropdown = document.getElementById('agent-notification-dropdown');
    const notificationList = document.getElementById('agent-notification-dropdown-list');

    if (!notificationIcon || !notificationDot || !leadsUnreadBadge || !notificationDropdown || !notificationList) {
        return;
    }

    const pollUrl = notificationIcon.dataset.pollUrl || '';
    const alertSoundUrl =
        notificationIcon.dataset.alertSoundUrl || '/sounds/mixkit-confirmation-tone-2867.wav';
    const serviceWorkerUrl =
        notificationIcon.dataset.serviceWorkerUrl || '/agent-notification-sw.js';

    if (!pollUrl) {
        return;
    }

    const leadAlertAudio = new LeadAlertAudio(alertSoundUrl);
    let isPolling = false;

    function setUnreadUI(unreadCount) {
        const count = Number(unreadCount || 0);
        if (count > 0) {
            notificationDot.classList.remove('hidden');
            notificationDot.classList.add('inline-flex');
            leadsUnreadBadge.classList.remove('hidden');
            leadsUnreadBadge.classList.add('inline-flex');
            leadsUnreadBadge.textContent = count > 99 ? '99+' : String(count);
            return;
        }

        notificationDot.classList.add('hidden');
        notificationDot.classList.remove('inline-flex');
        leadsUnreadBadge.classList.add('hidden');
        leadsUnreadBadge.classList.remove('inline-flex');
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function renderNotifications(items) {
        if (!Array.isArray(items) || items.length === 0) {
            notificationList.innerHTML =
                '<p class="px-4 py-6 text-center text-sm text-concierge-muted">No unread notifications.</p>';
            return;
        }

        notificationList.innerHTML = items
            .map((item) => {
                const title = item?.title || 'Notification';
                const message = item?.message || '';
                const type = item?.type || '';
                const customerName = item?.customer_name || '';
                const url = item?.url || '#';
                const time = item?.created_at_human || '';
                const isRead = Boolean(item?.is_read);
                const unreadDot = isRead
                    ? ''
                    : '<span class="inline-flex h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500" aria-hidden="true"></span>';
                const description = customerName
                    ? type === 'lead_reassigned'
                        ? `Lead for <span class="font-semibold text-concierge-navy">${escapeHtml(customerName)}</span> has been reassigned to you.`
                        : `A new lead for <span class="font-semibold text-concierge-navy">${escapeHtml(customerName)}</span> has been assigned to you.`
                    : escapeHtml(message);

                return `<a href="${escapeHtml(url)}" class="block border-b border-slate-100 px-4 py-3 transition hover:bg-slate-50 last:border-b-0">
                    <p class="inline-flex items-center gap-2 text-sm font-semibold text-concierge-navy">${unreadDot}${escapeHtml(title)}</p>
                    <p class="mt-1 text-sm text-concierge-muted">${description}</p>
                    <p class="mt-1 text-xs text-slate-400">${escapeHtml(time)}</p>
                </a>`;
            })
            .join('');
    }

    function openDropdown() {
        notificationDropdown.classList.remove('hidden');
        notificationIcon.setAttribute('aria-expanded', 'true');
    }

    function closeDropdown() {
        notificationDropdown.classList.add('hidden');
        notificationIcon.setAttribute('aria-expanded', 'false');
    }

    notificationIcon.addEventListener('click', () => {
        void leadAlertAudio.unlock();
        void leadAlertAudio.ensureNotificationPermission();

        if (notificationDropdown.classList.contains('hidden')) {
            openDropdown();
            return;
        }
        closeDropdown();
    });

    document.addEventListener('click', (event) => {
        if (
            notificationDropdown.classList.contains('hidden') ||
            notificationIcon.contains(event.target) ||
            notificationDropdown.contains(event.target)
        ) {
            return;
        }
        closeDropdown();
    });

    /**
     * @param {object} payload
     */
    async function handlePollPayload(payload) {
        setUnreadUI(payload.unread_count ?? 0);
        renderNotifications(payload.notifications ?? []);

        const newItems = Array.isArray(payload.new_notifications) ? payload.new_notifications : [];
        if (newItems.length > 0) {
            await leadAlertAudio.alertForNewLeadNotifications(newItems);
        }
    }

    async function pollFromPage() {
        if (isPolling) {
            return;
        }

        isPolling = true;

        try {
            const response = await fetch(pollUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                cache: 'no-store',
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            await handlePollPayload(payload);
        } catch {
            // Keep polling on the next interval.
        } finally {
            isPolling = false;
        }
    }

    /** @type {number | null} */
    let pagePollIntervalId = null;

    function startPagePolling() {
        if (pagePollIntervalId !== null) {
            return;
        }

        const intervalMs = document.hidden ? 1500 : 3000;
        pagePollIntervalId = window.setInterval(pollFromPage, intervalMs);
    }

    function stopPagePolling() {
        if (pagePollIntervalId === null) {
            return;
        }

        window.clearInterval(pagePollIntervalId);
        pagePollIntervalId = null;
    }

    function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            return Promise.resolve(false);
        }

        return registerAppServiceWorker(serviceWorkerUrl)
            .then((registration) => {
                const sendConfigure = (worker) => {
                    worker?.postMessage({
                        type: 'CONFIGURE',
                        pollUrl,
                        alertTypes: ['lead_assigned', 'lead_reassigned'],
                    });
                };

                if (registration.active) {
                    sendConfigure(registration.active);
                }

                registration.addEventListener('updatefound', () => {
                    const installing = registration.installing;
                    installing?.addEventListener('statechange', () => {
                        if (installing.state === 'activated') {
                            sendConfigure(registration.active);
                        }
                    });
                });

                return navigator.serviceWorker.ready.then(() => {
                    sendConfigure(registration.active);
                    return true;
                });
            })
            .catch(() => false);
    }

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data?.type !== 'NOTIFICATION_POLL') {
                return;
            }

            void handlePollPayload(event.data.payload ?? {});
        });
    }

    void pollFromPage();

    void registerServiceWorker().then((registered) => {
        if (registered) {
            stopPagePolling();
            return;
        }

        startPagePolling();
    });

    document.addEventListener('visibilitychange', () => {
        if (pagePollIntervalId === null) {
            return;
        }

        stopPagePolling();
        startPagePolling();

        if (!document.hidden) {
            void pollFromPage();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAgentNotificationPoller);
} else {
    initAgentNotificationPoller();
}
