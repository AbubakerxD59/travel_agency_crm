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
    if (!pollUrl) {
        return;
    }

    let audioCtx = null;

    function ensureAudioContext() {
        if (audioCtx) {
            return audioCtx;
        }
        const Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) {
            return null;
        }
        audioCtx = new Ctx();
        return audioCtx;
    }

    function playNotificationTone() {
        const ctx = ensureAudioContext();
        if (!ctx) {
            return;
        }
        if (ctx.state === 'suspended') {
            ctx.resume().catch(() => {});
        }
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, ctx.currentTime);
        gain.gain.setValueAtTime(0.0001, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.08, ctx.currentTime + 0.01);
        gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.22);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.24);
    }

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
                    ? (type === 'lead_reassigned'
                        ? `Lead for <span class="font-semibold text-concierge-navy">${escapeHtml(customerName)}</span> has been reassigned to you.`
                        : `A new lead for <span class="font-semibold text-concierge-navy">${escapeHtml(customerName)}</span> has been assigned to you.`)
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

    async function poll() {
        try {
            const response = await fetch(pollUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            setUnreadUI(payload.unread_count ?? 0);
            renderNotifications(payload.notifications ?? []);
            if (Number(payload.new_count || 0) > 0) {
                playNotificationTone();
            }
        } catch {
            // Keep polling on next interval.
        }
    }

    poll();
    window.setInterval(poll, 3000);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAgentNotificationPoller);
} else {
    initAgentNotificationPoller();
}
