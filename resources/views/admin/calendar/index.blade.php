@extends('layouts.admin')

@section('title', 'Travel Calendar')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Travel calendar</h1>
            <p class="mt-1 text-concierge-muted">Browse all folders by travel date across any month.</p>
        </div>

        <section
            class="min-w-0 rounded-xl border border-slate-200/80 bg-white p-5 shadow-[0_1px_3px_rgba(21,44,73,0.08)] md:p-6">
            @include('partials.admin.dashboard-folder-calendar', [
                'folderCalendar' => $folderCalendar,
                'embedded' => false,
                'inlineFolderDetails' => true,
                'allowMonthNavigation' => true,
            ])
        </section>

        <section id="dash-folder-calendar-details-outer"
            class="mt-6 hidden min-w-0 rounded-xl border border-slate-200/80 bg-white shadow-[0_1px_3px_rgba(21,44,73,0.08)]">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between md:px-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-concierge-muted">Folder details</p>
                    <p id="dash-folder-calendar-details-title" class="mt-0.5 text-base font-semibold text-concierge-navy">
                        Select a folder
                    </p>
                </div>
                <a id="dash-folder-calendar-details-full-link" href="{{ route('admin.folders.index') }}"
                    class="hidden inline-flex items-center gap-1.5 text-sm font-medium text-concierge-accent transition hover:text-concierge-navy">
                    Open full folder
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
            <div id="dash-folder-calendar-details-loading"
                class="hidden flex justify-center px-5 py-10 md:px-6">
                @include('partials.travel-loader', [
                    'size' => 'md',
                    'message' => 'Loading folder details…',
                ])
            </div>
            <div id="dash-folder-calendar-details" class="p-5 md:p-6"></div>
        </section>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin-dashboard-folder-calendar.js'])
@endpush
