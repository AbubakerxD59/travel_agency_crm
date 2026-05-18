@extends('layouts.admin')

@section('title', 'Folder #' . $folder->id)

@section('content')
    <div class="mx-auto max-w-7xl">
        <div
            class="rounded-2xl border border-slate-200/70 bg-gradient-to-r from-white via-slate-50/60 to-white p-6 shadow-sm lg:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-concierge-muted">Folder Details</p>
                    <h1 class="mt-1 text-2xl font-bold text-concierge-navy lg:text-3xl">Folder #{{ $folder->id }}</h1>
                </div>
                <div>
                    <a href="{{ route('admin.folders.index') }}"
                        class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-concierge-navy shadow-sm transition hover:bg-slate-50">
                        Back to folders
                    </a>
                    @if ($canManageFolders ?? false)
                        <a href="{{ route('admin.folders.edit', $folder) }}"
                            class="inline-flex shrink-0 items-center justify-center rounded-xl bg-concierge-navy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-concierge-navy-deep">
                            Edit folder
                        </a>
                    @endif
                </div>
            </div>
        </div>


        @include('partials.admin.folder-details-content', ['folder' => $folder])

    </div>
@endsection
