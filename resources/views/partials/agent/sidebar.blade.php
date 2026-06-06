@php
    use App\Models\Folder;

    $route = request()->route()?->getName();
    $agentUser = auth()->user();
    $agentUser?->loadMissing('company');
    $agentCompany = $agentUser?->company;
    $agentCompanyLogoUrl = $agentCompany?->imageUrl();
    $agentUnreadNotifications = $agentUser?->unreadNotifications()->count() ?? 0;
    $agentUpcomingFoldersCount = Folder::countUpcomingByTravelDate($agentUser?->id);
@endphp

<aside id="admin-sidebar" class="concierge-fixed-sidebar" aria-label="Main navigation">
    <div class="mb-10 flex items-start justify-between gap-2 px-2">
        <div class="min-w-0">
            <p class="text-lg font-bold tracking-tight text-concierge-navy">NAZIRSONS</p>
            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-widest text-concierge-muted">Agent Panel</p>
        </div>
        <button type="button"
            class="admin-sidebar-close -mr-1 -mt-1 flex shrink-0 cursor-pointer items-center justify-center rounded-lg border border-slate-200/80 bg-white/90 p-2 text-concierge-navy shadow-sm transition hover:bg-white hover:text-concierge-navy-deep lg:hidden"
            aria-label="Close menu">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex flex-1 flex-col gap-1">
        @can('dashboard.access')
            <a href="{{ route('agent.dashboard') }}"
                class="concierge-sidebar-link {{ $route === 'agent.dashboard' ? 'concierge-sidebar-link--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75A2.25 2.25 0 0115.75 13.5H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25zM13.5 6A2.25 2.25 0 0115.75 3.75H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25A2.25 2.25 0 0113.5 8.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25A2.25 2.25 0 0110.5 18v2.25A2.25 2.25 0 018 20.25H6a2.25 2.25 0 01-2.25-2.25V15.75z" />
                </svg>
                Dashboard
            </a>
        @endcan

        @can('leads.access')
            <a href="{{ route('agent.leads.index') }}"
                class="concierge-sidebar-link {{ str_starts_with((string) $route, 'agent.leads.') ? 'concierge-sidebar-link--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                <span class="flex min-w-0 flex-1 items-center justify-between gap-2">
                    <span>Leads</span>
                    <span id="agent-leads-unread-badge"
                        class="{{ $agentUnreadNotifications > 0 ? 'inline-flex' : 'hidden' }} min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-bold leading-none text-white">
                        {{ $agentUnreadNotifications > 99 ? '99+' : $agentUnreadNotifications }}
                    </span>
                </span>
            </a>
        @endcan

        @can('folders.access')
            <div class="flex flex-col gap-0.5">
                <a href="{{ route('agent.folders.index') }}"
                    class="concierge-sidebar-link {{ str_starts_with((string) $route, 'agent.folders.') && (string) $route !== 'agent.folders.upcoming' ? 'concierge-sidebar-link--active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 5.25A2.25 2.25 0 016 3h3.879a2.25 2.25 0 011.591.659l1.121 1.121A2.25 2.25 0 0014.182 5.5H18A2.25 2.25 0 0120.25 7.75v10.5A2.25 2.25 0 0118 20.5H6a2.25 2.25 0 01-2.25-2.25V5.25z" />
                    </svg>
                    Folder
                </a>
                <a href="{{ route('agent.folders.upcoming') }}"
                    class="concierge-sidebar-link pl-11 text-[13px] leading-snug {{ $route === 'agent.folders.upcoming' ? 'concierge-sidebar-link--active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 5.25A2.25 2.25 0 016 3h3.879a2.25 2.25 0 011.591.659l1.121 1.121A2.25 2.25 0 0014.182 5.5H18A2.25 2.25 0 0120.25 7.75v10.5A2.25 2.25 0 0118 20.5H6a2.25 2.25 0 01-2.25-2.25V5.25z" />
                    </svg>
                    <span class="flex min-w-0 flex-1 items-center gap-2">
                        <span
                            class="inline-flex min-h-5 min-w-5 shrink-0 items-center justify-center rounded-full bg-concierge-navy/10 px-1.5 text-[10px] font-bold leading-none text-concierge-navy tabular-nums">
                            {{ $agentUpcomingFoldersCount > 99 ? '99+' : $agentUpcomingFoldersCount }}
                        </span>
                        <span>Upcoming Folders</span>
                    </span>
                </a>
            </div>
        @endcan
    </nav>

    @if ($agentCompanyLogoUrl)
        <div class="mt-auto border-t border-slate-200/80 px-2 pt-5">
            <img src="{{ $agentCompanyLogoUrl }}" alt="{{ $agentCompany->name }} logo"
                class="mx-auto max-h-20 w-full max-w-[180px] object-contain object-center">
        </div>
    @endif
</aside>
