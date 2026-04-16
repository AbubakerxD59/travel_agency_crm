<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 px-4 py-4 backdrop-blur-md sm:px-6 lg:z-10 lg:px-8">
    <div class="flex w-full items-center gap-4">
        <button
            type="button"
            id="admin-sidebar-toggle"
            class="inline-flex shrink-0 cursor-pointer items-center justify-center rounded-xl border border-slate-200/80 bg-white p-2.5 text-concierge-navy shadow-sm transition hover:bg-slate-50 lg:hidden"
            aria-expanded="false"
            aria-controls="admin-sidebar"
        >
            <span class="sr-only">Open menu</span>
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        <div class="flex min-w-0 flex-1 items-center justify-end gap-3">
            <div class="hidden text-right text-sm sm:block">
                <p class="font-semibold text-concierge-navy">{{ auth()->user()->name }}</p>
                <p class="text-xs text-concierge-muted">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</p>
            </div>
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-concierge-navy text-xs font-bold text-white">
                {{ strtoupper(collect(preg_split('/\s+/', trim(auth()->user()->name)))->take(2)->map(fn ($w) => $w[0] ?? '')->implode('')) }}
            </div>
            <form method="POST" action="{{ route('logout') }}" class="inline shrink-0">
                @csrf
                <button type="submit" class="rounded-lg px-3 py-2 text-xs font-medium text-concierge-muted hover:bg-slate-100 hover:text-concierge-navy">
                    Log out
                </button>
            </form>
        </div>
    </div>
</header>
