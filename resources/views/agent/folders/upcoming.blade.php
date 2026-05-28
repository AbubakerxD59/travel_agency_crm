@extends('layouts.agent')

@section('title', 'Upcoming Folders')

@section('content')
    <div class="mx-auto max-w-8xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Upcoming Folders</h1>
            </div>
            <a href="{{ route('agent.folders.index') }}"
                class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-concierge-navy shadow-sm transition hover:bg-slate-50">
                All folders
            </a>
        </div>

        @include('partials.folders.agent-folder-list-filters', [
            'formAction' => route('agent.folders.upcoming'),
            'clearUrl' => route('agent.folders.upcoming'),
            'showTravelDateFilter' => false,
        ])

        @include('partials.folders.upcoming-table', [
            'folders' => $folders,
            'routePrefix' => 'agent',
        ])
    </div>
@endsection
