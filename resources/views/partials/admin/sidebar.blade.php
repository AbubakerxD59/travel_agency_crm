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
        @can('dashboard.access')
            <a href="{{ route('admin.dashboard') }}"
               class="concierge-sidebar-link {{ $route === 'admin.dashboard' ? 'concierge-sidebar-link--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75A2.25 2.25 0 0115.75 13.5H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25zM13.5 6A2.25 2.25 0 0115.75 3.75H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25A2.25 2.25 0 0113.5 8.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25A2.25 2.25 0 0110.5 18v2.25A2.25 2.25 0 018 20.25H6a2.25 2.25 0 01-2.25-2.25V15.75z" />
                </svg>
                Dashboard
            </a>
        @endcan

        @can('agents.create')
            <a href="{{ route('admin.agents.index') }}"
               class="concierge-sidebar-link {{ $route === 'admin.agents.index' ? 'concierge-sidebar-link--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                </svg>
                Add Agents
            </a>
        @endcan

        @can('leads.access')
            <a href="{{ route('admin.leads.index') }}"
               class="concierge-sidebar-link {{ str_starts_with((string) $route, 'admin.leads.') ? 'concierge-sidebar-link--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                Leads
            </a>
        @endcan

        @can('companies.create')
            <a href="{{ route('admin.companies.index') }}"
               class="concierge-sidebar-link {{ str_starts_with((string) $route, 'admin.companies.') ? 'concierge-sidebar-link--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008H15.75v-.008zm0 3h.008v.008H15.75V18zm0 3h.008v.008H15.75v-.008z" />
                </svg>
                Companies
            </a>
        @endcan
    </nav>

    @role('super-admin')
        <a href="{{ route('admin.leads.index', ['openAssignLead' => 1]) }}"
           class="mt-4 flex items-center justify-center gap-2 rounded-xl bg-concierge-navy px-4 py-3 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 transition hover:bg-concierge-navy-deep">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Assign Lead
        </a>
    @endrole
</aside>
