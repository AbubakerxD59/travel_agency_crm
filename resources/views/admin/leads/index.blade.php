@extends('layouts.admin')

@section('title', 'Lead Management')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Lead Management</h1>
                <p class="mt-1 max-w-xl text-sm text-concierge-muted">Track inquiries, assignments, and conversion status across your concierge team.</p>
            </div>
            <button type="button" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-concierge-navy px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 transition hover:bg-concierge-navy-deep">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add New Lead
            </button>
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <select class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-concierge-navy focus:border-concierge-accent focus:outline-none focus:ring-2 focus:ring-concierge-accent/20" aria-label="Assigned Agent">
                <option>Assigned Agent</option>
            </select>
            <select class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-concierge-navy focus:border-concierge-accent focus:outline-none focus:ring-2 focus:ring-concierge-accent/20" aria-label="Lead Source">
                <option>Lead Source</option>
            </select>
            <select class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-concierge-navy focus:border-concierge-accent focus:outline-none focus:ring-2 focus:ring-concierge-accent/20" aria-label="Status">
                <option>Status</option>
            </select>
            <button type="button" class="ml-auto inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-concierge-muted hover:bg-slate-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
                More Filters
            </button>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80 text-xs font-semibold uppercase tracking-wide text-concierge-muted">
                            <th class="px-6 py-4">Customer Name</th>
                            <th class="px-6 py-4">Contact Info</th>
                            <th class="px-6 py-4">City</th>
                            <th class="px-6 py-4">Source</th>
                            <th class="px-6 py-4">Assigned Agent</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($leads as $lead)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-concierge-sidebar text-xs font-bold text-concierge-navy">{{ $lead['initials'] }}</span>
                                        <span class="font-medium text-concierge-navy">{{ $lead['name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-concierge-navy">{{ $lead['email'] }}</div>
                                    <div class="text-xs text-concierge-muted">{{ $lead['phone'] }}</div>
                                </td>
                                <td class="px-6 py-4 text-concierge-muted">{{ $lead['city'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="concierge-pill concierge-pill-{{ $lead['source_class'] }}">{{ $lead['source'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-concierge-navy">{{ $lead['agent'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="concierge-pill concierge-pill-{{ str_replace('_', '-', $lead['status_class']) }}">{{ $lead['status'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button type="button" class="rounded-lg p-2 text-concierge-muted hover:bg-slate-100 hover:text-concierge-navy" aria-label="Edit lead">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-concierge-muted">Showing <span class="font-medium text-concierge-navy">1</span> to <span class="font-medium text-concierge-navy">5</span> of <span class="font-medium text-concierge-navy">{{ $total }}</span> leads</p>
                <div class="flex items-center gap-1">
                    <button type="button" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-concierge-muted hover:bg-slate-50" aria-label="Previous page">&larr;</button>
                    <button type="button" class="rounded-lg bg-concierge-navy px-3 py-1.5 text-sm font-medium text-white">1</button>
                    <button type="button" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-concierge-navy hover:bg-slate-50">2</button>
                    <button type="button" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-concierge-navy hover:bg-slate-50">3</button>
                    <button type="button" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-concierge-muted hover:bg-slate-50" aria-label="Next page">&rarr;</button>
                </div>
            </div>
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-3">
            <div class="rounded-2xl bg-concierge-navy p-6 text-white shadow-lg shadow-concierge-navy/20">
                <p class="text-sm font-medium text-white/80">Conversion velocity</p>
                <p class="mt-2 text-3xl font-bold">82.4%</p>
                <p class="mt-2 text-xs text-emerald-200">+12.3% from last month</p>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-concierge-muted">Active leads</p>
                <p class="mt-2 text-3xl font-bold text-concierge-navy">1,248</p>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full w-[68%] rounded-full bg-concierge-accent"></div>
                </div>
                <p class="mt-2 text-xs text-concierge-muted">Shared visibility for your AI-assisted workflows.</p>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-concierge-muted">Lead quality index</p>
                <p class="mt-2 flex items-center gap-2 text-xl font-semibold text-concierge-navy">
                    Premium
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                </p>
                <p class="mt-2 text-xs text-concierge-muted">Stricter qualification on inbound luxury requests.</p>
            </div>
        </div>
    </div>
@endsection
