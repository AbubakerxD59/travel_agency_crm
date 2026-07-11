@extends('layouts.admin')

@section('title', 'Upcoming Folders')

@section('content')
    <div class="mx-auto max-w-8xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Upcoming Folders</h1>
            </div>
            <a href="{{ portal_route('folders.index') }}"
                class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-concierge-navy shadow-sm transition hover:bg-slate-50">
                All folders
            </a>
        </div>

        @include('partials.folders.admin-folder-list-filters', [
            'formAction' => portal_route('folders.upcoming'),
            'clearUrl' => portal_route('folders.upcoming'),
            'showTravelDateFilter' => false,
        ])

        @include('partials.folders.upcoming-table', [
            'folders' => $folders,
            'canManageFolders' => $canManageFolders ?? false,
            'routePrefix' => portal_route_prefix(),
        ])
    </div>
@endsection
