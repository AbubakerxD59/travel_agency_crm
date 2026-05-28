import { LeadAlertAudio } from './lead-alert-audio';

function initAdminNotificationPoller() {
    const notificationIcon = document.getElementById('admin-notification-icon');
    const notificationDot = document.getElementById('admin-notification-dot');
    const notificationDropdown = document.getElementById('admin-notification-dropdown');
    const notificationList = document.getElementById('admin-notification-dropdown-list');

    if (!notificationIcon || !notificationDot || !notificationDropdown || !notificationList) {
        return;
    }

    const pollUrl = notificationIcon.dataset.pollUrl || '';
    const alertSoundUrl =
        notificationIcon.dataset.alertSoundUrl || '/sounds/mixkit-confirmation-tone-2867.wav';

    if (!pollUrl) {
        return;
    }

    const paymentAlertAudio = new LeadAlertAudio(alertSoundUrl);
    let isPolling = false;

    function setUnreadUI(unreadCount) {
        const count = Number(unreadCount || 0);
        if (count > 0) {
            notificationDot.classList.remove('hidden');
            notificationDot.classList.add('inline-flex');
            return;
        }

        notificationDot.classList.add('hidden');
        notificationDot.classList.remove('inline-flex');
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
                const url = item?.url || '#';
                const time = item?.created_at_human || '';
                const isRead = Boolean(item?.is_read);
                const unreadDot = isRead
                    ? ''
                    : '<span class="inline-flex h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500" aria-hidden="true"></span>';

                return `<a href="${escapeHtml(url)}" class="block border-b border-slate-100 px-4 py-3 transition hover:bg-slate-50 last:border-b-0">
                    <p class="inline-flex items-center gap-2 text-sm font-semibold text-concierge-navy">${unreadDot}${escapeHtml(title)}</p>
                    <p class="mt-1 text-sm text-concierge-muted">${escapeHtml(message)}</p>
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
        void paymentAlertAudio.unlock();

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
            await paymentAlertAudio.alertForNewPaymentNotifications(newItems);
        }
    }

    async function poll() {
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
            // Keep polling on next interval.
        } finally {
            isPolling = false;
        }
    }

    void poll();
    window.setInterval(poll, 3000);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminNotificationPoller);
} else {
    initAdminNotificationPoller();
}
