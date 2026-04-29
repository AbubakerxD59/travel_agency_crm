@extends('layouts.admin')

@section('title', 'Folders')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Folders</h1>
                <p class="mt-1 text-sm text-concierge-muted">Folder listing across agents.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.folders.index') }}" class="mt-6">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label for="folder-agent-filter" class="block text-sm font-medium text-concierge-navy">Agent</label>
                    <select id="folder-agent-filter" name="agent_id"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        <option value="">All agents</option>
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}" @selected((string) ($selectedAgentId ?? '') === (string) $agent->id)>
                                {{ $agent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="folder-company-filter" class="block text-sm font-medium text-concierge-navy">Company</label>
                    <select id="folder-company-filter" name="company_id"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        <option value="">All companies</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected((string) ($selectedCompanyId ?? '') === (string) $company->id)>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="folder-destination-filter"
                        class="block text-sm font-medium text-concierge-navy">Destination</label>
                    <select id="folder-destination-filter" name="destination_id"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        <option value="">All destinations</option>
                        @foreach ($destinations as $destination)
                            <option value="{{ $destination->id }}" @selected((string) ($selectedDestinationId ?? '') === (string) $destination->id)>
                                {{ $destination->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-1.5 flex flex-col gap-2 sm:flex-row">
                <input id="folder-search-admin" name="search" type="search"
                    placeholder="Search by customer name, order type, or vendor ref#" value="{{ $search ?? '' }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                <div class="flex shrink-0 gap-2">
                    <button type="submit"
                        class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-concierge-navy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-concierge-navy-deep">
                        Apply
                    </button>
                    @if (($search ?? '') !== '' || ($selectedAgentId ?? null) || ($selectedCompanyId ?? null) || ($selectedDestinationId ?? null))
                        <a href="{{ route('admin.folders.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-concierge-navy transition hover:bg-slate-50">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-slate-100 bg-slate-50/80 text-xs font-semibold uppercase tracking-wide text-concierge-muted">
                            <th class="px-4 py-4 lg:px-6">Agent</th>
                            <th class="px-4 py-4 lg:px-6">Customer Name</th>
                            <th class="px-4 py-4 lg:px-6">Order Type</th>
                            <th class="px-4 py-4 lg:px-6">Vendor Ref#</th>
                            <th class="px-4 py-4 lg:px-6">Company</th>
                            <th class="px-4 py-4 lg:px-6">Destination</th>
                            <th class="px-4 py-4 lg:px-6">Travel Date</th>
                            <th class="px-4 py-4 text-right lg:px-6">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($folders as $folder)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-4 text-concierge-navy lg:px-6">
                                    @if ($folder->agent)
                                        <a href="{{ route('admin.agents.overview', $folder->agent) }}"
                                            class="font-medium text-concierge-accent hover:underline">
                                            {{ $folder->agent->name }}
                                        </a>
                                    @else
                                        Unassigned
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-concierge-muted lg:px-6">{{ $folder->customer_name ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-navy lg:px-6">{{ $folder->order_type ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-muted lg:px-6">{{ $folder->vendor_reference ?? '—' }}
                                </td>
                                <td class="px-4 py-4 text-concierge-navy lg:px-6">{{ $folder->company?->name ?? '—' }}</td>
                                <td class="px-4 py-4 text-concierge-muted lg:px-6">{{ $folder->destination?->name ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-concierge-muted lg:px-6">
                                    {{ $folder->travel_date?->format('M j, Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-4 text-right lg:px-6">
                                    <a href="{{ route('admin.folders.show', $folder) }}"
                                        class="lead-row-action inline-flex cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-concierge-accent"
                                        title="View" aria-label="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>
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
