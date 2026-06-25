@extends('layouts.agent')

@section('title', 'Folder Management')

@section('content')
    <div class="mx-auto max-w-8xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Folder Management</h1>
            </div>
            @if ($canEditFolders ?? auth()->user()?->can('folders.edit'))
            <a href="{{ route('agent.folders.create') }}"
                class="inline-flex shrink-0 cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 transition hover:bg-concierge-navy-deep">
                New Folder
            </a>
            @endif
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @include('partials.folders.agent-folder-list-filters', [
            'formAction' => route('agent.folders.index'),
            'clearUrl' => route('agent.folders.index'),
        ])

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-slate-100 bg-slate-50/80 text-xs font-semibold uppercase tracking-wide text-concierge-muted">
                            <th class="px-4 py-4 lg:px-6">Customer Name</th>
                            <th class="px-4 py-4 lg:px-6">Order type</th>
                            <th class="px-4 py-4 lg:px-6">Invoice Number</th>
                            <th class="px-4 py-4 lg:px-6">Company</th>
                            <th class="px-4 py-4 lg:px-6">Travel date</th>
                            <th class="px-4 py-4 lg:px-6">Destination</th>
                            <th class="px-4 py-4 lg:px-6">Folder Info</th>
                            <th class="px-4 py-4 text-right lg:px-6">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($folders as $folder)
                            <tr class="{{ folder_list_row_class($folder) }}">
                                <td class="px-4 py-4 text-concierge-navy lg:px-6">{{ $folder->customer_name ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-navy lg:px-6">{{ $folder->order_type ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-muted lg:px-6">{{ $folder->vendor_reference ?? '—' }}
                                </td>
                                <td class="px-4 py-4 text-concierge-navy lg:px-6">{{ $folder->company?->name ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-muted lg:px-6">
                                    @php
                                        $departureItinerary = $folder->itineraries->sortBy('sr_no')->first();
                                        $arrivalItinerary = $folder->itineraries->sortByDesc('sr_no')->first();
                                        $departureDateTime = null;
                                        $arrivalDateTime = null;

                                        if (
                                            $departureItinerary?->departure_date &&
                                            $departureItinerary?->departure_time
                                        ) {
                                            $departureDateTime = \Illuminate\Support\Carbon::parse(
                                                $departureItinerary->departure_date->format('Y-m-d') .
                                                    ' ' .
                                                    $departureItinerary->departure_time,
                                            );
                                        }

                                        if ($arrivalItinerary?->arrival_date && $arrivalItinerary?->arrival_time) {
                                            $arrivalDateTime = \Illuminate\Support\Carbon::parse(
                                                $arrivalItinerary->arrival_date->format('Y-m-d') .
                                                    ' ' .
                                                    $arrivalItinerary->arrival_time,
                                            );
                                        }
                                    @endphp

                                    <div class="space-y-1 whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5" title="Departure datetime">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-plane-takeoff-icon swap-icon tw-shrink-0 tw-w-5 lg:tw-w-6 tw-text-text-white"
                                                data-v-5a94d2d6="">
                                                <path d="M2 22h20"></path>
                                                <path
                                                    d="M6.36 17.4 4 17l-2-4 1.1-.55a2 2 0 0 1 1.8 0l.17.1a2 2 0 0 0 1.8 0L8 12 5 6l.9-.45a2 2 0 0 1 2.09.2l4.02 3a2 2 0 0 0 2.1.2l4.19-2.06a2.41 2.41 0 0 1 1.73-.17L21 7a1.4 1.4 0 0 1 .87 1.99l-.38.76c-.23.46-.6.84-1.07 1.08L7.58 17.2a2 2 0 0 1-1.22.18Z">
                                                </path>
                                            </svg>
                                            <span>{{ $departureDateTime?->format('jS M, y h:i A') ?? '—' }}</span>
                                        </div>
                                        <br>
                                        <div class="inline-flex items-center gap-1.5" title="Arrival datetime">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-plane-landing-icon swap-icon tw-shrink-0 tw-w-5 lg:tw-w-6 tw-text-text-white"
                                                data-v-5a94d2d6="">
                                                <path d="M2 22h20"></path>
                                                <path
                                                    d="M3.77 10.77 2 9l2-4.5 1.1.55c.55.28.9.84.9 1.45s.35 1.17.9 1.45L8 8.5l3-6 1.05.53a2 2 0 0 1 1.09 1.52l.72 5.4a2 2 0 0 0 1.09 1.52l4.4 2.2c.42.22.78.55 1.01.96l.6 1.03c.49.88-.06 1.98-1.06 2.1l-1.18.15c-.47.06-.95-.02-1.37-.24L4.29 11.15a2 2 0 0 1-.52-.38Z">
                                                </path>
                                            </svg>
                                            <span>{{ $arrivalDateTime?->format('jS M, y h:i A') ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-concierge-muted lg:px-6">{{ $folder->destination?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-4 text-concierge-muted lg:px-6">
                                    <div class="space-y-1 text-xs">
                                        <p><span class="font-semibold text-concierge-navy">ID:</span> #{{ $folder->id }}</p>
                                        <p><span class="font-semibold text-concierge-navy">Passengers:</span>
                                            {{ $folder->passengers_count ?? 0 }}</p>
                                        <p><span class="font-semibold text-concierge-navy">Balance Due:</span>
                                            {{ $folder->balance_due_date?->format('j M, Y') ?? '—' }}</p>
                                        <p><span class="font-semibold text-concierge-navy">Ziarat:</span>
                                            @if ($folder->makkah_ziarat && $folder->madinah_ziarat)
                                                Makkah, Madinah
                                            @elseif($folder->makkah_ziarat)
                                                Makkah
                                            @elseif($folder->madinah_ziarat)
                                                Madinah
                                            @else
                                                —
                                            @endif
                                        </p>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-right lg:px-6">
                                    <div class="inline-flex items-center justify-end gap-1 whitespace-nowrap">
                                        @include('partials.folders.folder-lock-action', ['folder' => $folder])
                                        @if (user_can_edit_folder(auth()->user(), $folder))
                                        <a href="{{ route('agent.folders.edit', $folder) }}"
                                            class="lead-row-action inline-flex cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-concierge-navy"
                                            title="Edit" aria-label="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                                aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                            </svg>
                                        </a>
                                        @endif
                                        @include('partials.folders.invoice-action', [
                                            'folder' => $folder,
                                            'routeName' => 'agent.folders.invoice',
                                        ])
                                        @include('partials.folders.transportation-voucher-action', [
                                            'folder' => $folder,
                                            'routeName' => 'agent.folders.transportation-voucher',
                                        ])
                                        <a href="{{ route('agent.folders.show', $folder) }}"
                                            class="lead-row-action inline-flex cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-concierge-accent"
                                            title="View" aria-label="View">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                                aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-sm text-concierge-muted">
                                    No folders to show.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($folders->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $folders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
