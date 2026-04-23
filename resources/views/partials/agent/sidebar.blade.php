@php
    $route = request()->route()?->getName();
@endphp

<aside
    id="admin-sidebar"
    class="fixed inset-y-0 left-0 z-[110] flex h-full w-64 max-w-[85vw] -translate-x-full flex-col overflow-y-auto border-r border-slate-200/80 bg-concierge-sidebar px-4 py-6 shadow-2xl shadow-slate-900/20 transition-transform duration-300 ease-out lg:static lg:z-auto lg:h-auto lg:max-w-none lg:w-72 lg:translate-x-0 lg:shadow-none"
    aria-label="Main navigation"
>
    <div class="mb-10 flex items-start justify-between gap-2 px-2">
        <div class="min-w-0">
            <p class="text-lg font-bold tracking-tight text-concierge-navy">NAZIRSONS</p>
            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-widest text-concierge-muted">Agent Panel</p>
        </div>
        <button
            type="button"
            class="admin-sidebar-close -mr-1 -mt-1 flex shrink-0 cursor-pointer items-center justify-center rounded-lg border border-slate-200/80 bg-white/90 p-2 text-concierge-navy shadow-sm transition hover:bg-white hover:text-concierge-navy-deep lg:hidden"
            aria-label="Close menu"
        >
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex flex-1 flex-col gap-1">
        <a href="{{ route('agent.dashboard') }}"
            class="concierge-sidebar-link {{ $route === 'agent.dashboard' ? 'concierge-sidebar-link--active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75A2.25 2.25 0 0115.75 13.5H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25zM13.5 6A2.25 2.25 0 0115.75 3.75H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25A2.25 2.25 0 0113.5 8.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25A2.25 2.25 0 0110.5 18v2.25A2.25 2.25 0 018 20.25H6a2.25 2.25 0 01-2.25-2.25V15.75z" />
            </svg>
            Dashboard
        </a>

        <a href="{{ route('agent.leads.index') }}"
            class="concierge-sidebar-link {{ str_starts_with((string) $route, 'agent.leads.') ? 'concierge-sidebar-link--active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            Leads
        </a>
    </nav>
</aside>
