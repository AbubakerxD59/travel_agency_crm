@extends('layouts.admin')

@section('title', 'Abbreviations')

@section('content')
    <div id="js-abbreviations-config" class="hidden"
        data-url-base="{{ portal_route('abbreviations.index') }}"
        data-can-manage="{{ $canManageAbbreviations ? '1' : '0' }}"
        data-actions-colspan="{{ $canManageAbbreviations ? '4' : '3' }}"></div>

    <div class="mx-auto max-w-8xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Abbreviations</h1>
                <p class="mt-1 text-sm text-concierge-muted">
                    Manage codes and their full forms. Codes are expanded automatically on invoices when a match exists.
                </p>
            </div>
            <button type="button" id="open-abbreviation-modal"
                class="inline-flex shrink-0 cursor-pointer items-center justify-center gap-2 rounded-xl bg-concierge-navy px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 transition hover:bg-concierge-navy-deep">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add new
            </button>
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                role="status">{{ session('status') }}</div>
        @endif

        <div class="mt-6">
            <label for="abbreviation-list-filter" class="block text-sm font-medium text-concierge-navy">Search
                abbreviations</label>
            <input id="abbreviation-list-filter" type="search" placeholder="Filter by code or full form…"
                autocomplete="off"
                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
        </div>

        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-6">
            <div class="overflow-x-auto">
                <table id="abbreviations-index-table" class="min-w-full text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-slate-100 bg-slate-50/80 text-xs font-semibold uppercase tracking-wide text-concierge-muted">
                            <th class="px-6 py-4">Code</th>
                            <th class="px-6 py-4">Full form</th>
                            <th class="px-6 py-4">Added</th>
                            @if ($canManageAbbreviations)
                                <th class="px-6 py-4 text-right">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($abbreviations as $abbreviation)
                            <tr class="hover:bg-slate-50/50" data-abbreviation-id="{{ $abbreviation->id }}"
                                data-search-text="{{ e(mb_strtolower($abbreviation->code.' '.$abbreviation->full_form, 'UTF-8')) }}">
                                <td class="px-6 py-4 font-mono font-semibold text-concierge-navy">{{ $abbreviation->code }}
                                </td>
                                <td class="px-6 py-4 text-concierge-navy">{{ $abbreviation->full_form }}</td>
                                <td class="px-6 py-4 text-concierge-muted">
                                    {{ $abbreviation->created_at?->format('M j, Y') }}</td>
                                @if ($canManageAbbreviations)
                                    <td class="px-6 py-4 text-right">
                                        <div class="inline-flex items-center justify-end gap-1">
                                            <button type="button"
                                                class="abbreviation-row-action cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-concierge-navy"
                                                data-edit-abbreviation="{{ $abbreviation->id }}" title="Edit"
                                                aria-label="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                                    aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                                </svg>
                                            </button>
                                            <button type="button"
                                                class="abbreviation-row-action cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-rose-600"
                                                data-delete-abbreviation="{{ $abbreviation->id }}" title="Delete"
                                                aria-label="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                                    aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr class="abbreviations-index-empty">
                                <td colspan="{{ $canManageAbbreviations ? 4 : 3 }}"
                                    class="px-6 py-10 text-center text-sm text-concierge-muted">
                                    No abbreviations yet. Use “Add new” to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p id="abbreviations-filter-no-results" class="hidden px-6 py-8 text-center text-sm text-concierge-muted"
                role="status">
                No abbreviations match your search.
            </p>
        </div>
    </div>

    <div id="abbreviation-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4"
        aria-hidden="true" role="dialog" aria-labelledby="abbreviation-modal-title">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h2 id="abbreviation-modal-title" class="text-lg font-semibold text-concierge-navy">Add abbreviation</h2>
                <button type="button" data-close-abbreviation-modal
                    class="rounded-lg p-2 text-concierge-muted hover:bg-slate-100 hover:text-concierge-navy"
                    aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="store-abbreviation-form" method="POST" action="{{ portal_route('abbreviations.store') }}"
                class="space-y-4 px-6 py-5">
                @csrf
                <div>
                    <label for="modal_abbreviation_code" class="block text-sm font-medium text-concierge-navy">Code</label>
                    <input id="modal_abbreviation_code" name="code" type="text" required maxlength="30"
                        autocomplete="off" placeholder="e.g. JED"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm uppercase focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                </div>
                <div>
                    <label for="modal_abbreviation_full_form" class="block text-sm font-medium text-concierge-navy">Full
                        form</label>
                    <input id="modal_abbreviation_full_form" name="full_form" type="text" required maxlength="255"
                        autocomplete="off" placeholder="e.g. Jeddah - King Abdulaziz Int'l"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" data-close-abbreviation-modal
                        class="flex-1 cursor-pointer rounded-xl border border-slate-200 py-2.5 text-sm font-medium text-concierge-navy hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 cursor-pointer rounded-xl bg-concierge-navy py-2.5 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 hover:bg-concierge-navy-deep">
                        Create abbreviation
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($canManageAbbreviations)
        <div id="edit-abbreviation-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4"
            aria-hidden="true" role="dialog" aria-labelledby="edit-abbreviation-modal-title">
            <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h2 id="edit-abbreviation-modal-title" class="text-lg font-semibold text-concierge-navy">Edit
                        abbreviation</h2>
                    <button type="button" data-close-edit-abbreviation-modal
                        class="rounded-lg p-2 text-concierge-muted hover:bg-slate-100 hover:text-concierge-navy"
                        aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="edit-abbreviation-form" method="POST" class="space-y-4 px-6 py-5">
                    @csrf
                    <input type="hidden" name="_method" value="PATCH">
                    <div>
                        <label for="edit_modal_abbreviation_code"
                            class="block text-sm font-medium text-concierge-navy">Code</label>
                        <input id="edit_modal_abbreviation_code" name="code" type="text" required maxlength="30"
                            autocomplete="off"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm uppercase focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                    </div>
                    <div>
                        <label for="edit_modal_abbreviation_full_form"
                            class="block text-sm font-medium text-concierge-navy">Full form</label>
                        <input id="edit_modal_abbreviation_full_form" name="full_form" type="text" required
                            maxlength="255" autocomplete="off"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" data-close-edit-abbreviation-modal
                            class="flex-1 cursor-pointer rounded-xl border border-slate-200 py-2.5 text-sm font-medium text-concierge-navy hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 cursor-pointer rounded-xl bg-concierge-navy py-2.5 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 hover:bg-concierge-navy-deep">
                            Save changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    @vite(['resources/js/abbreviations.js'])
@endpush
