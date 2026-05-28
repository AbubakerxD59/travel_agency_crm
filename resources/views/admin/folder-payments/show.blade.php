@extends('layouts.admin')

@section('title', 'Payment #' . $payment->id)

@section('content')
    <div class="mx-auto max-w-4xl">
        <div
            class="rounded-2xl border border-slate-200/70 bg-gradient-to-r from-white via-slate-50/60 to-white p-6 shadow-sm lg:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-concierge-muted">Payment details</p>
                    <h1 class="mt-1 text-2xl font-bold text-concierge-navy lg:text-3xl">Payment #{{ $payment->id }}</h1>
                    <p class="mt-1 text-sm text-concierge-muted">Folder
                        <a href="{{ route('admin.folders.show', $payment->folder) }}"
                            class="font-medium text-concierge-navy underline">#{{ $payment->folder_id }}</a>
                        · {{ $payment->folder?->customer_name ?? '—' }}
                    </p>
                </div>
                <a href="{{ route('admin.folder-payments.index', ['folder_id' => $payment->folder_id]) }}"
                    class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-concierge-navy shadow-sm transition hover:bg-slate-50">
                    Back to payments
                </a>
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

        <div class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-concierge-navy">Overview</h2>
            <dl class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Agent</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">
                        {{ $payment->folder ? folder_agent_display_name($payment->folder) : '—' }}
                    </dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Amount</dt>
                    <dd class="mt-1 text-sm font-medium tabular-nums text-concierge-navy">{{ $payment->amount }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Reference no.</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $payment->reference_no ?: '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Payment date</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">
                        {{ $payment->payment_date?->format('Y-m-d') ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Mode</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $payment->mode_of_payment }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Bank</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">{{ $payment->bank?->name ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3 sm:col-span-2">
                    <dt class="text-xs uppercase tracking-wide text-concierge-muted">Status</dt>
                    <dd class="mt-1 text-sm font-medium text-concierge-navy">
                        @if ($payment->approval_status === 'pending')
                            <span class="text-amber-700">Pending</span>
                        @elseif ($payment->approval_status === 'rejected')
                            <span class="text-rose-700">Rejected</span>
                        @else
                            <span>Approved</span>
                        @endif
                        @if ($payment->isLocked())
                            @include('partials.folders.payment-locked-icon', ['class' => 'ml-1 inline-flex h-5 w-5 items-center justify-center rounded bg-slate-200 text-slate-600'])
                        @endif
                    </dd>
                </div>
            </dl>

            @if ($payment->approval_status === 'pending' && ! $payment->isLocked())
                <div class="mt-6 flex flex-wrap gap-2 border-t border-slate-100 pt-6">
                    <form method="POST" action="{{ route('admin.folder-payments.approve', $payment) }}"
                        onsubmit="return confirm({{ json_encode(__('Approve this payment?')) }})">
                        @csrf
                        <button type="submit"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            Approve
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.folder-payments.reject', $payment) }}"
                        onsubmit="return confirm({{ json_encode(__('Reject this payment?')) }})">
                        @csrf
                        <button type="submit"
                            class="rounded-lg border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50">
                            Reject
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-concierge-navy">Payment receipt / proof</h2>

            @if ($payment->imageUrl())
                <div class="mt-4">
                    <a href="{{ $payment->imageUrl() }}" target="_blank" rel="noopener noreferrer"
                        class="inline-block rounded-xl border border-slate-200 bg-slate-50/50 p-2 transition hover:border-concierge-accent/40">
                        <img src="{{ $payment->imageUrl() }}" alt="Payment receipt"
                            class="max-h-72 max-w-full rounded-lg object-contain">
                    </a>
                    <p class="mt-2 text-xs text-concierge-muted">Click image to open full size in a new tab.</p>
                </div>
            @else
                <p class="mt-3 text-sm text-concierge-muted">No image uploaded yet.</p>
            @endif

            @if ($canEditImage)
                <form id="folder-payment-image-form" method="POST"
                    action="{{ route('admin.folder-payments.image.update', $payment) }}" enctype="multipart/form-data"
                    class="mt-6 border-t border-slate-100 pt-6">
                    @csrf
                    <div data-folder-payment-image-upload
                        data-existing-image-url="{{ $payment->imageUrl() ?? '' }}">
                        @include('partials.companies.image-upload-field', [
                            'inputId' => 'folder_payment_image',
                            'label' => 'Upload receipt image',
                            'optionalHint' => $payment->imageUrl()
                                ? 'Drag & drop a new image to replace the current one. Max 2 MB.'
                                : 'Drag & drop or browse to attach a receipt (JPEG, PNG, GIF, or WebP). Max 2 MB.',
                        ])
                    </div>

                    @if ($errors->any())
                        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            <ul class="list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-3">
                        <button type="submit"
                            class="cursor-pointer rounded-xl bg-concierge-navy px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 hover:bg-concierge-navy-deep">
                            Save image
                        </button>
                        @if ($payment->imageUrl())
                            <button type="submit" form="folder-payment-remove-image-form"
                                class="cursor-pointer rounded-xl border border-rose-200 bg-white px-5 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-50"
                                onclick="return confirm({{ json_encode(__('Remove this payment image?')) }})">
                                Remove image
                            </button>
                        @endif
                    </div>
                </form>

                @if ($payment->imageUrl())
                    <form id="folder-payment-remove-image-form" method="POST"
                        action="{{ route('admin.folder-payments.image.destroy', $payment) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
            @else
                <p class="mt-4 text-sm text-amber-800">
                    {{ __('This payment is locked. The receipt image cannot be changed.') }}
                </p>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    @if ($canEditImage)
        @vite(['resources/js/folder-payment-show.js'])
    @endif
@endpush
