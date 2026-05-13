@extends('layouts.admin')

@section('title', 'Payment approvals')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Payment management</h1>
                <p class="mt-1 text-sm text-concierge-muted">Review payments added by agents on folders. Approve or reject
                    each row after confirmation.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                role="status">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                {{ session('error') }}</div>
        @endif

        @if ($filterFolderId)
            <p class="mt-4 text-sm text-concierge-muted">Filtered to folder #{{ $filterFolderId }}.
                <a href="{{ route('admin.folder-payments.index') }}" class="font-medium text-concierge-navy underline">Show
                    all pending</a>
            </p>
        @endif

        <div class="mt-8 overflow-x-auto rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <table class="min-w-[960px] w-full border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-100 text-left text-concierge-muted">
                        <th class="border border-slate-200 px-3 py-2">Folder</th>
                        <th class="border border-slate-200 px-3 py-2">Agent</th>
                        <th class="border border-slate-200 px-3 py-2">Customer</th>
                        <th class="border border-slate-200 px-3 py-2">Amount</th>
                        <th class="border border-slate-200 px-3 py-2">Reference No</th>
                        <th class="border border-slate-200 px-3 py-2">Date</th>
                        <th class="border border-slate-200 px-3 py-2">Mode</th>
                        <th class="border border-slate-200 px-3 py-2">Bank</th>
                        <th class="border border-slate-200 px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr class="bg-white">
                            <td class="border border-slate-200 px-3 py-2">
                                <a href="{{ route('admin.folders.show', $payment->folder) }}"
                                    class="font-medium text-concierge-navy underline">#{{ $payment->folder_id }}</a>
                            </td>
                            <td class="border border-slate-200 px-3 py-2">{{ $payment->folder?->agent?->name ?? '—' }}
                            </td>
                            <td class="border border-slate-200 px-3 py-2">{{ $payment->folder?->customer_name ?? '—' }}
                            </td>
                            <td class="border border-slate-200 px-3 py-2 tabular-nums">{{ $payment->amount }}</td>
                            <td class="border border-slate-200 px-3 py-2">{{ $payment->reference_no ?: '—' }}</td>
                            <td class="border border-slate-200 px-3 py-2">
                                {{ $payment->payment_date?->format('Y-m-d') ?? '—' }}</td>
                            <td class="border border-slate-200 px-3 py-2">{{ $payment->mode_of_payment }}</td>
                            <td class="border border-slate-200 px-3 py-2">{{ $payment->bank?->name ?? '—' }}</td>
                            <td class="border border-slate-200 px-3 py-2">
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST"
                                        action="{{ route('admin.folder-payments.approve', $payment) }}"
                                        onsubmit="return confirm({{ json_encode(__('Approve this payment?')) }})">
                                        @csrf
                                        <button type="submit"
                                            class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                            Approve
                                        </button>
                                    </form>
                                    <form method="POST"
                                        action="{{ route('admin.folder-payments.reject', $payment) }}"
                                        onsubmit="return confirm({{ json_encode(__('Reject this payment?')) }})">
                                        @csrf
                                        <button type="submit"
                                            class="rounded-lg border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="border border-slate-200 px-4 py-8 text-center text-concierge-muted" colspan="9">
                                No payments awaiting approval.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payments->hasPages())
            <div class="mt-6">{{ $payments->links() }}</div>
        @endif
    </div>
@endsection
