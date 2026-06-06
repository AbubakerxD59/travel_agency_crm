@auth
    @if (auth()->user()?->hasRole('agent'))
        <div id="agent-push-alerts-root"
            data-vapid-url="{{ route('agent.push.vapid-public-key') }}"
            data-subscribe-url="{{ route('agent.push.subscribe') }}"
            data-unsubscribe-url="{{ route('agent.push.unsubscribe') }}"
            data-service-worker-url="{{ agent_notification_sw_url() }}"
            class="hidden px-4 pt-4 sm:px-6 lg:px-8">
            <div id="agent-push-alerts-banner"
                class="flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="font-semibold">Enable lead alert sounds</p>
                    <p class="mt-0.5 text-amber-900/90">
                        Allow notifications to hear a sound when a new lead is assigned, even if this tab is minimized.
                    </p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <button type="button" id="agent-push-enable-button"
                        class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-concierge-navy-deep">
                        Enable alerts
                    </button>
                    <button type="button" id="agent-push-dismiss-button"
                        class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-amber-300/80 bg-white px-3 py-2 text-sm font-medium text-amber-950 transition hover:bg-amber-100/60">
                        Not now
                    </button>
                </div>
            </div>
        </div>
    @endif
@endauth
