const LEAD_ALERT_RING_COUNT = 3;
const LEAD_ALERT_RING_GAP_MS = 350;
const ALERTED_IDS_STORAGE_KEY = 'agent_lead_alerted_notification_ids';
const ADMIN_PAYMENT_ALERTED_IDS_STORAGE_KEY = 'admin_payment_alerted_notification_ids';
const MAX_STORED_ALERTED_IDS = 100;

/**
 * Reliable lead-assignment alert audio for agent dashboards.
 * Uses Web Audio (when unlocked) plus HTML5 Audio fallback so playback continues
 * when the tab is in the background after the agent has interacted with the page once.
 */
export class LeadAlertAudio {
    /** @param {string} soundUrl */
    constructor(soundUrl) {
        this.soundUrl = soundUrl;
        /** @type {AudioContext | null} */
        this.audioContext = null;
        /** @type {AudioBuffer | null} */
        this.audioBuffer = null;
        /** @type {Promise<void>} */
        this.playbackQueue = Promise.resolve();
        this.unlocked = false;
        this.isPlaying = false;
        /** @type {HTMLAudioElement[]} */
        this.htmlAudioPool = [];

        this.preloadHtmlAudio();
        this.bindUnlockListeners();
    }

    preloadHtmlAudio() {
        this.htmlAudioPool = Array.from({ length: LEAD_ALERT_RING_COUNT }, () => {
            const audio = new Audio(this.soundUrl);
            audio.preload = 'auto';
            audio.volume = 1;
            audio.load();
            return audio;
        });
    }

    bindUnlockListeners() {
        const unlock = () => {
            void this.unlock();
        };

        document.addEventListener('pointerdown', unlock, { once: true, capture: true });
        document.addEventListener('keydown', unlock, { once: true, capture: true });
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && this.audioContext?.state === 'suspended') {
                void this.audioContext.resume();
            }
        });
    }

    async unlock() {
        if (this.unlocked) {
            return;
        }

        this.unlocked = true;

        const AudioContextCtor = window.AudioContext || window.webkitAudioContext;
        if (AudioContextCtor) {
            try {
                this.audioContext = new AudioContextCtor();
                if (this.audioContext.state === 'suspended') {
                    await this.audioContext.resume();
                }

                const response = await fetch(this.soundUrl);
                if (response.ok) {
                    const arrayBuffer = await response.arrayBuffer();
                    this.audioBuffer = await this.audioContext.decodeAudioData(arrayBuffer);
                }
            } catch {
                this.audioBuffer = null;
            }
        }

        await Promise.all(
            this.htmlAudioPool.map(
                (audio) =>
                    new Promise((resolve) => {
                        const playAttempt = audio.play();
                        if (playAttempt && typeof playAttempt.then === 'function') {
                            playAttempt.then(() => {
                                audio.pause();
                                audio.currentTime = 0;
                                resolve();
                            }).catch(resolve);
                            return;
                        }
                        resolve();
                    }),
            ),
        );
    }

    /**
     * @param {Array<{ id?: string, title?: string, message?: string, customer_name?: string, type?: string }>} items
     */
    async alertForNewLeadNotifications(items) {
        return this.alertForTypedNotifications(items, {
            types: ['lead_assigned', 'lead_reassigned'],
            storageKey: ALERTED_IDS_STORAGE_KEY,
        });
    }

    /**
     * @param {Array<{ id?: string, title?: string, message?: string, type?: string }>} items
     */
    async alertForNewPaymentNotifications(items) {
        return this.alertForTypedNotifications(items, {
            types: ['folder_payment_pending'],
            storageKey: ADMIN_PAYMENT_ALERTED_IDS_STORAGE_KEY,
        });
    }

    /**
     * @param {Array<{ id?: string, title?: string, message?: string, customer_name?: string, type?: string }>} items
     * @param {{ types: string[], storageKey: string }} options
     */
    async alertForTypedNotifications(items, { types, storageKey }) {
        const allowedTypes = new Set(types);
        const matched = (Array.isArray(items) ? items : []).filter((item) =>
            allowedTypes.has(item?.type ?? ''),
        );
        const pending = this.filterUnalerted(matched, storageKey);
        if (!pending.length) {
            return;
        }

        this.markAsAlerted(
            pending.map((item) => String(item.id)),
            storageKey,
        );

        await this.ensureNotificationPermission();

        if (!document.hidden) {
            pending.forEach((item) => this.showSystemNotification(item));
        }

        await this.playRing();
    }

    /**
     * @param {Array<{ id?: string }>} items
     * @param {string} [storageKey]
     */
    filterUnalerted(items, storageKey = ALERTED_IDS_STORAGE_KEY) {
        if (!Array.isArray(items) || !items.length) {
            return [];
        }

        const alerted = this.loadAlertedIds(storageKey);

        return items.filter((item) => {
            const id = item?.id != null ? String(item.id) : '';
            return id !== '' && !alerted.has(id);
        });
    }

    /**
     * @param {string[]} ids
     * @param {string} [storageKey]
     */
    markAsAlerted(ids, storageKey = ALERTED_IDS_STORAGE_KEY) {
        const alerted = this.loadAlertedIds(storageKey);
        ids.forEach((id) => alerted.add(id));

        const trimmed = [...alerted].slice(-MAX_STORED_ALERTED_IDS);
        try {
            sessionStorage.setItem(storageKey, JSON.stringify(trimmed));
        } catch {
            // Ignore storage errors (private mode, quota, etc.).
        }
    }

    /**
     * @param {string} [storageKey]
     */
    loadAlertedIds(storageKey = ALERTED_IDS_STORAGE_KEY) {
        try {
            const raw = sessionStorage.getItem(storageKey);
            if (!raw) {
                return new Set();
            }

            const parsed = JSON.parse(raw);
            if (!Array.isArray(parsed)) {
                return new Set();
            }

            return new Set(parsed.map((id) => String(id)));
        } catch {
            return new Set();
        }
    }

    async ensureNotificationPermission() {
        if (!('Notification' in window) || Notification.permission !== 'default') {
            return;
        }

        try {
            await Notification.requestPermission();
        } catch {
            // Permission prompt dismissed or blocked.
        }
    }

    /**
     * @param {{ title?: string, message?: string, customer_name?: string, type?: string }} item
     */
    showSystemNotification(item) {
        if (!('Notification' in window) || Notification.permission !== 'granted') {
            return;
        }

        const customerName = item.customer_name || 'Customer';
        const title = item.title || 'New lead assigned';
        let body = item.message || '';

        if (item.type === 'lead_reassigned') {
            body = `Lead for ${customerName} has been reassigned to you.`;
        } else if (item.type === 'lead_assigned' || !body) {
            body = `A new lead for ${customerName} has been assigned to you.`;
        } else if (item.type === 'folder_payment_pending' && !body) {
            body = 'New folder payment(s) are pending your approval.';
        }

        try {
            const notification = new Notification(title, {
                body,
                tag: `lead-assigned-${item.id ?? Date.now()}`,
                silent: false,
                requireInteraction: false,
            });

            notification.onclick = () => {
                window.focus();
                notification.close();
            };
        } catch {
            // Notification constructor blocked in some contexts.
        }
    }

    playRing() {
        this.playbackQueue = this.playbackQueue.then(() => this.playRingInternal());

        return this.playbackQueue;
    }

    async playRingInternal() {
        if (this.isPlaying) {
            return;
        }

        this.isPlaying = true;

        try {
            if (!this.unlocked) {
                await this.unlock();
            }

            for (let index = 0; index < LEAD_ALERT_RING_COUNT; index += 1) {
                await this.playOnce(index);
                if (index < LEAD_ALERT_RING_COUNT - 1) {
                    await this.sleep(LEAD_ALERT_RING_GAP_MS);
                }
            }
        } finally {
            this.isPlaying = false;
        }
    }

    /**
     * @param {number} poolIndex
     */
    async playOnce(poolIndex) {
        const playedWithWebAudio = await this.playWithWebAudio();
        if (playedWithWebAudio) {
            const durationMs = this.audioBuffer
                ? Math.ceil(this.audioBuffer.duration * 1000)
                : 600;
            await this.sleep(durationMs);
            return;
        }

        await this.playWithHtmlAudio(poolIndex);
    }

    async playWithWebAudio() {
        if (!this.audioContext || !this.audioBuffer) {
            return false;
        }

        try {
            if (this.audioContext.state === 'suspended') {
                await this.audioContext.resume();
            }

            const source = this.audioContext.createBufferSource();
            const gain = this.audioContext.createGain();
            gain.gain.value = 1;
            source.buffer = this.audioBuffer;
            source.connect(gain);
            gain.connect(this.audioContext.destination);

            await new Promise((resolve) => {
                source.addEventListener('ended', resolve, { once: true });
                source.addEventListener('error', resolve, { once: true });
                source.start(0);
            });

            return true;
        } catch {
            return false;
        }
    }

    /**
     * @param {number} poolIndex
     */
    playWithHtmlAudio(poolIndex) {
        return new Promise((resolve) => {
            const audio = this.htmlAudioPool[poolIndex % this.htmlAudioPool.length] ?? new Audio(this.soundUrl);
            audio.volume = 1;
            audio.currentTime = 0;

            const finish = () => resolve();

            audio.addEventListener('ended', finish, { once: true });
            audio.addEventListener('error', finish, { once: true });

            const playAttempt = audio.play();
            if (playAttempt && typeof playAttempt.then === 'function') {
                playAttempt.catch(finish);
                return;
            }

            finish();
        });
    }

    /**
     * @param {number} ms
     */
    sleep(ms) {
        return new Promise((resolve) => {
            window.setTimeout(resolve, ms);
        });
    }
}
