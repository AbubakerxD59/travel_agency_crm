@php
    $status = (string) ($status ?? '');
    $locked = (bool) ($locked ?? false);

    $label = match ($status) {
        'pending' => __('Pending'),
        'approved' => __('Approved'),
        'rejected' => __('Rejected'),
        default => $status !== '' ? ucfirst($status) : __('New'),
    };

    $statusClass = match ($status) {
        'pending' => 'text-amber-700',
        'rejected' => 'text-rose-700',
        'approved' => 'text-emerald-700',
        default => 'text-concierge-muted',
    };
@endphp

<div class="flex items-center gap-1.5">
    <span class="text-sm font-medium {{ $statusClass }}">{{ $label }}</span>
    @if ($locked)
        @include('partials.folders.payment-locked-icon')
    @endif
</div>
