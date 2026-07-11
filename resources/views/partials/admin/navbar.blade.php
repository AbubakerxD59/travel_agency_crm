<header class="sticky top-0 z-40 border-b border-slate-300/80 bg-slate-100/95 px-4 py-4 backdrop-blur-md sm:px-6 lg:z-10 lg:px-8">
    <div class="flex w-full items-center gap-4">
        <button
            type="button"
            id="admin-sidebar-toggle"
            class="inline-flex shrink-0 cursor-pointer items-center justify-center rounded-xl border border-slate-300/80 bg-slate-100 p-2.5 text-concierge-navy shadow-sm transition hover:bg-slate-200 lg:hidden"
            aria-expanded="false"
            aria-controls="admin-sidebar"
        >
            <span class="sr-only">Open menu</span>
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        <div class="flex min-w-0 flex-1 items-center justify-end gap-3">
            @if (auth()->user()?->hasRole('agent'))
                @php
                    $unreadCount = auth()->user()->unreadNotifications()->count();
                @endphp
                <div class="relative">
                    <button
                        type="button"
                    id="agent-notification-icon"
                    data-poll-url="{{ route('agent.notifications.poll') }}"
                    data-alert-sound-url="{{ asset('sounds/mixkit-confirmation-tone-2867.wav') }}"
                    data-service-worker-url="{{ agent_notification_sw_url() }}"
                    class="relative inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-300/80 bg-slate-100 text-concierge-navy shadow-sm transition hover:bg-slate-200"
                    aria-label="Notifications"
                    aria-expanded="false"
                    aria-controls="agent-notification-dropdown"
                    title="Notifications"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.08 5.454 1.31m5.715 0a24.255 24.255 0 01-5.715 0m5.715 0a3 3 0 11-5.715 0" />
                        </svg>
                        <span id="agent-notification-dot"
                            class="{{ $unreadCount > 0 ? 'inline-flex' : 'hidden' }} absolute -right-1 -top-1 h-3.5 w-3.5 rounded-full border-2 border-white bg-emerald-500"
                            aria-hidden="true"></span>
                    </button>
                    <div id="agent-notification-dropdown"
                        class="absolute right-0 z-20 mt-2 hidden w-65 max-w-[90vw] overflow-hidden rounded-2xl border border-slate-300 bg-slate-100 shadow-xl">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="text-base font-semibold text-concierge-navy">Notifications</p>
                        </div>
                        <div id="agent-notification-dropdown-list" class="max-h-80 overflow-y-auto">
                            <p class="px-4 py-6 text-center text-sm text-concierge-muted">No notifications yet.</p>
                        </div>
                    </div>
                </div>
            @endif
            @if (user_is_staff_portal(auth()->user()))
                @php
                    $adminUnreadCount = auth()->user()->unreadNotifications()->count();
                @endphp
                <div class="relative">
                    <button type="button" id="admin-notification-icon"
                        data-poll-url="{{ portal_route('notifications.poll') }}"
                        data-alert-sound-url="{{ asset('sounds/mixkit-confirmation-tone-2867.wav') }}"
                        data-service-worker-url="{{ agent_notification_sw_url() }}"
                        class="relative inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-300/80 bg-slate-100 text-concierge-navy shadow-sm transition hover:bg-slate-200"
                        aria-label="Notifications" aria-expanded="false" aria-controls="admin-notification-dropdown"
                        title="Notifications">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.08 5.454 1.31m5.715 0a24.255 24.255 0 01-5.715 0m5.715 0a3 3 0 11-5.715 0" />
                        </svg>
                        <span id="admin-notification-dot"
                            class="{{ $adminUnreadCount > 0 ? 'inline-flex' : 'hidden' }} absolute -right-1 -top-1 h-3.5 w-3.5 rounded-full border-2 border-white bg-emerald-500"
                            aria-hidden="true"></span>
                    </button>
                    <div id="admin-notification-dropdown"
                        class="absolute right-0 z-20 mt-2 hidden w-80 max-w-[90vw] overflow-hidden rounded-2xl border border-slate-300 bg-slate-100 shadow-xl">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="text-base font-semibold text-concierge-navy">Notifications</p>
                        </div>
                        <div id="admin-notification-dropdown-list" class="max-h-80 overflow-y-auto">
                            <p class="px-4 py-6 text-center text-sm text-concierge-muted">No notifications yet.</p>
                        </div>
                    </div>
                </div>
            @endif
            <div class="hidden text-right text-sm sm:block">
                <p class="font-semibold text-concierge-navy">{{ auth()->user()->name }}</p>
                <p class="text-xs text-concierge-muted">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</p>
            </div>
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-concierge-navy text-xs font-bold text-white">
                {{ strtoupper(collect(preg_split('/\s+/', trim(auth()->user()->name)))->take(2)->map(fn ($w) => $w[0] ?? '')->implode('')) }}
            </div>
            <form method="POST" action="{{ route('logout') }}" class="inline shrink-0">
                @csrf
                <button type="submit" class="rounded-lg px-3 py-2 text-xs font-medium text-concierge-muted hover:bg-slate-200 hover:text-concierge-navy">
                    Log out
                </button>
            </form>
        </div>
    </div>
</header>
