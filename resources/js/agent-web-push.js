/**
 * Web Push subscription for agent lead alerts (OS notification sound in background).
 */

import { registerAppServiceWorker } from './service-worker-register';

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; i += 1) {
        outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function initAgentWebPush() {
    const root = document.getElementById('agent-push-alerts-root');
    if (!root) {
        return;
    }

    const vapidUrl = root.dataset.vapidUrl || '';
    const subscribeUrl = root.dataset.subscribeUrl || '';
    const unsubscribeUrl = root.dataset.unsubscribeUrl || '';
    const serviceWorkerUrl = root.dataset.serviceWorkerUrl || '/agent-notification-sw.js';
    const enableButton = document.getElementById('agent-push-enable-button');
    const dismissButton = document.getElementById('agent-push-dismiss-button');
    const banner = document.getElementById('agent-push-alerts-banner');

    if (!vapidUrl || !subscribeUrl || !enableButton) {
        return;
    }

    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
        hideBanner();
        return;
    }

    let vapidPublicKey = null;

    async function fetchVapidPublicKey() {
        const response = await fetch(vapidUrl, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return null;
        }

        const payload = await response.json();
        if (!payload?.configured || !payload?.public_key) {
            return null;
        }

        return payload.public_key;
    }

    async function ensureServiceWorkerRegistration() {
        const registration = await registerAppServiceWorker(serviceWorkerUrl);
        await navigator.serviceWorker.ready;

        return registration;
    }

    async function getExistingSubscription(registration) {
        return registration.pushManager.getSubscription();
    }

    async function subscribeUser(registration) {
        if (!vapidPublicKey) {
            vapidPublicKey = await fetchVapidPublicKey();
        }

        if (!vapidPublicKey) {
            throw new Error('Push notifications are not configured on the server.');
        }

        let subscription = await getExistingSubscription(registration);

        if (!subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
            });
        }

        const response = await fetch(subscribeUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify(subscription.toJSON()),
        });

        if (!response.ok) {
            throw new Error('Could not save push subscription.');
        }

        return subscription;
    }

    async function syncSubscriptionWithServer() {
        try {
            const registration = await ensureServiceWorkerRegistration();
            const subscription = await getExistingSubscription(registration);

            if (!subscription) {
                return false;
            }

            vapidPublicKey = await fetchVapidPublicKey();
            if (!vapidPublicKey) {
                return false;
            }

            await fetch(subscribeUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify(subscription.toJSON()),
            });

            return true;
        } catch {
            return false;
        }
    }

    function hideBanner() {
        banner?.classList.add('hidden');
        root.classList.add('hidden');
    }

    function showBanner() {
        banner?.classList.remove('hidden');
        root.classList.remove('hidden');
    }

    async function refreshBannerVisibility() {
        if (Notification.permission === 'granted') {
            const synced = await syncSubscriptionWithServer();
            if (synced) {
                hideBanner();
                return;
            }
        }

        if (Notification.permission === 'denied') {
            hideBanner();
            return;
        }

        if (sessionStorage.getItem('agent_push_banner_dismissed') === '1') {
            hideBanner();
            return;
        }

        showBanner();
    }

    enableButton.addEventListener('click', async () => {
        enableButton.disabled = true;
        const originalText = enableButton.textContent;
        enableButton.textContent = 'Enabling...';

        try {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                throw new Error('Notification permission was not granted.');
            }

            const registration = await ensureServiceWorkerRegistration();
            await subscribeUser(registration);
            hideBanner();
        } catch (error) {
            enableButton.textContent = originalText;
            enableButton.disabled = false;
            window.alert(
                error instanceof Error
                    ? error.message
                    : 'Could not enable push notifications. Please check browser settings.',
            );
        }
    });

    dismissButton?.addEventListener('click', () => {
        sessionStorage.setItem('agent_push_banner_dismissed', '1');
        hideBanner();
    });

    void refreshBannerVisibility();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAgentWebPush);
} else {
    initAgentWebPush();
}
