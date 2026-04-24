@extends('layouts.admin')

@section('title', 'Lead #' . $lead->id)

@section('content')
    <div class="mx-auto max-w-7xl">
        <div
            class="rounded-2xl border border-slate-200/70 bg-gradient-to-r from-white via-slate-50/60 to-white p-6 shadow-sm lg:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-concierge-muted">Lead Details</p>
                    <h1 class="mt-1 text-2xl font-bold text-concierge-navy lg:text-3xl">Lead #{{ $lead->id }}</h1>
                    <p class="mt-1 text-sm text-concierge-muted">Contact and assignment details for this lead.</p>
                </div>
                <a href="{{ route('admin.leads.index') }}"
                    class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-concierge-navy shadow-sm transition hover:bg-slate-50">
                    Back to leads
                </a>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-concierge-navy">Overview</h2>
            <dl class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Agent</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->agent?->name ?? 'Unassigned' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Customer name</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->customer_name ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Phone number</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->phone_number ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Email</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->email ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Company</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->company?->name ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">City</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->city ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Source</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $lead->source ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Created</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">
                        {{ $lead->created_at?->format('M j, Y g:i A') ?? '—' }}
                    </dd>
                </div>
            </dl>

            <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                <dt class="text-xs uppercase tracking-wide text-concierge-muted">Notes</dt>
                <dd class="mt-1 whitespace-pre-wrap text-sm font-medium text-concierge-navy">{{ $lead->notes ?? '—' }}</dd>
            </div>
        </div>
    </div>
@endsection
