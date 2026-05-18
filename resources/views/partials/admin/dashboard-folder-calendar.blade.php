@php
    $weeks = $folderCalendar['weeks'] ?? [];
    $monthFolders = $folderCalendar['upcoming_folders'] ?? collect();
    $embedded = $embedded ?? true;
    $inlineFolderDetails = $inlineFolderDetails ?? false;
    $allowMonthNavigation = $allowMonthNavigation ?? false;
    $upcomingOnly = $folderCalendar['upcoming_only'] ?? true;
    $showAgentOnFolderCards = $showAgentOnFolderCards ?? true;
    $calendarRoutes = $calendarRoutes ?? [
        'index' => 'admin.calendar.index',
        'upcoming' => 'admin.folders.upcoming',
        'folders_index' => 'admin.folders.index',
        'folder_show' => 'admin.folders.show',
        'folder_details_base' => url('/admin/calendar/folders'),
        'folder_show_base' => url('/admin/folders'),
    ];

    $todayIso = $folderCalendar['today_iso'] ?? now()->format('Y-m-d');
    $defaultDayIso = $todayIso;

    if ($monthFolders->isNotEmpty()) {
        if ($allowMonthNavigation && ($folderCalendar['is_current_month'] ?? false)) {
            $todayFolders = $monthFolders->filter(
                fn ($folder) => $folder->travel_date->format('Y-m-d') === $todayIso,
            );
            $defaultDayIso = $todayFolders->isNotEmpty()
                ? $todayIso
                : $monthFolders->first()->travel_date->format('Y-m-d');
        } else {
            $defaultDayIso = $monthFolders->first()->travel_date->format('Y-m-d');
        }
    }

    $foldersCountLabel = $upcomingOnly
        ? $monthFolders->count().' upcoming'
        : $monthFolders->count().' '.\Illuminate\Support\Str::plural('folder', $monthFolders->count());
@endphp

<div @class([
    'dash-folder-calendar',
    'mt-8 border-t border-slate-100 pt-8' => $embedded,
]) id="dash-folder-calendar"
    data-default-day="{{ $defaultDayIso }}"
    data-empty-day="{{ $upcomingOnly ? 'No upcoming folders on this day.' : 'No folders on this day.' }}"
    data-empty-month="{{ $upcomingOnly ? 'No upcoming folders for the rest of this month.' : 'No folders in this month.' }}"
    data-no-travel-label="{{ $upcomingOnly ? 'No upcoming travel this month' : 'No folders this month' }}"
    @if ($inlineFolderDetails) data-inline-folder-details="true" data-folder-details-url="{{ $calendarRoutes['folder_details_base'] }}" data-folder-show-url-base="{{ $calendarRoutes['folder_show_base'] }}" @endif>
    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h3 class="text-base font-semibold text-concierge-navy md:text-lg">
                {{ $allowMonthNavigation ? 'Travel calendar' : 'Upcoming travel calendar' }}
            </h3>
            <p class="mt-0.5 text-sm text-concierge-muted">
                @if ($allowMonthNavigation)
                    {{ $showAgentOnFolderCards ? 'Browse folders by travel date' : 'Browse your folders by travel date' }} — use arrows to change month.
                @else
                    {{ $folderCalendar['month_label'] ?? now()->format('F Y') }} — folders with travel dates after today.
                @endif
            </p>
        </div>
        @if ($upcomingOnly)
            <a href="{{ route($calendarRoutes['upcoming']) }}"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-concierge-accent transition hover:text-concierge-navy">
                View all upcoming
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @else
            <a href="{{ route($calendarRoutes['folders_index']) }}"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-concierge-accent transition hover:text-concierge-navy">
                View all folders
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @endif
    </div>

    <div class="dash-folder-calendar__layout relative flex flex-col gap-6 lg:block">
        <div class="dash-folder-calendar__calendar-slot min-w-0">
        <div class="dash-folder-calendar__grid-wrap overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-b from-slate-50/80 to-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200/80 bg-concierge-navy px-4 py-3.5 text-white md:flex-row md:items-center md:justify-between md:px-5">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/10" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    @if ($allowMonthNavigation)
                        <div class="flex min-w-0 flex-1 items-center gap-1 sm:gap-2">
                            <a href="{{ route($calendarRoutes['index'], $folderCalendar['prev']) }}"
                                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 transition hover:bg-white/20"
                                aria-label="Previous month">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                </svg>
                            </a>
                            <span class="min-w-0 truncate text-sm font-semibold tracking-wide md:text-base">
                                {{ $folderCalendar['month_label'] }}
                            </span>
                            <a href="{{ route($calendarRoutes['index'], $folderCalendar['next']) }}"
                                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 transition hover:bg-white/20"
                                aria-label="Next month">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    @else
                        <span class="text-sm font-semibold tracking-wide md:text-base">
                            {{ $folderCalendar['month_label'] ?? now()->format('F Y') }}
                        </span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($allowMonthNavigation && !($folderCalendar['is_current_month'] ?? true))
                        <a href="{{ route($calendarRoutes['index']) }}"
                            class="rounded-full bg-white/15 px-2.5 py-1 text-xs font-medium transition hover:bg-white/25">
                            Today
                        </a>
                    @endif
                    <span class="rounded-full bg-white/15 px-2.5 py-1 text-xs font-medium tabular-nums">
                        {{ $foldersCountLabel }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-7 border-b border-slate-100 bg-slate-50/60 px-2 py-2 text-center text-[11px] font-semibold uppercase tracking-wider text-concierge-muted md:px-3">
                @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                    <span>{{ $weekday }}</span>
                @endforeach
            </div>

            <div class="grid grid-cols-7 gap-px bg-slate-100/80 p-2 md:p-3" role="grid"
                aria-label="Calendar for {{ $folderCalendar['month_label'] ?? '' }}">
                @foreach ($weeks as $week)
                    @foreach ($week as $cell)
                        @php
                            $hasFolders = $cell['folder_count'] > 0;
                            $cellClasses = 'dash-folder-calendar__day';
                            if (!$cell['in_month']) {
                                $cellClasses .= ' dash-folder-calendar__day--outside';
                            } elseif ($cell['is_past']) {
                                $cellClasses .= ' dash-folder-calendar__day--past';
                            }
                            if ($cell['is_today']) {
                                $cellClasses .= ' dash-folder-calendar__day--today';
                            }
                            if ($hasFolders) {
                                $cellClasses .= ' dash-folder-calendar__day--has-folders';
                            }
                        @endphp
                        <button type="button"
                            class="{{ $cellClasses }}"
                            data-calendar-day="{{ $cell['iso'] }}"
                            data-folder-count="{{ $cell['folder_count'] }}"
                            @disabled(!$hasFolders)
                            aria-label="{{ $cell['day'] }}{{ $hasFolders ? ', ' . $cell['folder_count'] . ' folder' . ($cell['folder_count'] === 1 ? '' : 's') : '' }}"
                            aria-pressed="false">
                            <span class="dash-folder-calendar__day-num">{{ $cell['day'] }}</span>
                            @if ($hasFolders)
                                <span class="dash-folder-calendar__dots" aria-hidden="true">
                                    @for ($d = 0; $d < min(3, $cell['folder_count']); $d++)
                                        <span class="dash-folder-calendar__dot"></span>
                                    @endfor
                                </span>
                                <span class="dash-folder-calendar__count">{{ $cell['folder_count'] }}</span>
                            @endif
                        </button>
                    @endforeach
                @endforeach
            </div>
        </div>
        </div>

        <div class="dash-folder-calendar__panel flex min-h-[16rem] flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="dash-folder-calendar__panel-header shrink-0 border-b border-slate-100 px-4 py-3.5 md:px-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-concierge-muted">Selected day</p>
                <p id="dash-folder-calendar-selected-label" class="mt-0.5 text-base font-semibold text-concierge-navy">
                    —
                </p>
            </div>
            <div id="dash-folder-calendar-day-list"
                class="dash-folder-calendar__panel-body min-h-0 flex-1 overflow-y-auto px-3 py-3 md:px-4" role="list">
                @foreach ($weeks as $week)
                    @foreach ($week as $cell)
                        @if ($cell['folders']->isNotEmpty())
                            <template data-calendar-day-template="{{ $cell['iso'] }}">
                                <ul class="space-y-2">
                                    @foreach ($cell['folders'] as $folder)
                                        <li>
                                            @if ($inlineFolderDetails)
                                                <button type="button" data-calendar-folder-id="{{ $folder->id }}"
                                                    class="dash-folder-calendar__folder-item group block w-full rounded-xl border border-slate-100 bg-slate-50/50 p-3 text-left transition hover:border-concierge-accent/30 hover:bg-white hover:shadow-sm">
                                                    <p class="font-medium text-concierge-navy group-hover:text-concierge-accent">
                                                        {{ $folder->customer_name ?? 'Unnamed folder' }}
                                                    </p>
                                                    <p class="mt-1 text-xs text-concierge-muted">
                                                        {{ $folder->destination?->name ?? '—' }}
                                                        @if ($showAgentOnFolderCards && $folder->agent)
                                                            · {{ $folder->agent->name }}
                                                        @endif
                                                    </p>
                                                    @if ($folder->vendor_reference)
                                                        <p class="mt-1 text-[11px] font-medium text-concierge-accent/80">
                                                            Ref: {{ $folder->vendor_reference }}
                                                        </p>
                                                    @endif
                                                </button>
                                            @else
                                                <a href="{{ route($calendarRoutes['folder_show'], $folder) }}"
                                                    class="group block rounded-xl border border-slate-100 bg-slate-50/50 p-3 transition hover:border-concierge-accent/30 hover:bg-white hover:shadow-sm">
                                                    <p class="font-medium text-concierge-navy group-hover:text-concierge-accent">
                                                        {{ $folder->customer_name ?? 'Unnamed folder' }}
                                                    </p>
                                                    <p class="mt-1 text-xs text-concierge-muted">
                                                        {{ $folder->destination?->name ?? '—' }}
                                                        @if ($showAgentOnFolderCards && $folder->agent)
                                                            · {{ $folder->agent->name }}
                                                        @endif
                                                    </p>
                                                    @if ($folder->vendor_reference)
                                                        <p class="mt-1 text-[11px] font-medium text-concierge-accent/80">
                                                            Ref: {{ $folder->vendor_reference }}
                                                        </p>
                                                    @endif
                                                </a>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </template>
                        @endif
                    @endforeach
                @endforeach
                <p id="dash-folder-calendar-empty" class="hidden px-2 py-6 text-center text-sm text-concierge-muted">
                    {{ $upcomingOnly ? 'No upcoming folders on this day.' : 'No folders on this day.' }}
                </p>
                @if ($monthFolders->isEmpty())
                    <p id="dash-folder-calendar-month-empty" class="px-2 py-8 text-center text-sm text-concierge-muted">
                        {{ $upcomingOnly ? 'No upcoming folders for the rest of this month.' : 'No folders in this month.' }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
