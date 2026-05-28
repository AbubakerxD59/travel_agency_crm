@props([
    'index',
    'row' => [],
    'locked' => false,
])

@php
    $imageUrl = data_get($row, 'image_url');
    if (! $imageUrl && data_get($row, 'image')) {
        $imageUrl = public_storage_url((string) data_get($row, 'image'));
    }
@endphp

<div class="payment-image-field min-w-[8.5rem] space-y-1.5">
    <input type="hidden" name="payments[{{ $index }}][form_index]" value="{{ $index }}" data-payment-form-index>

    @if ($locked)
        @if ($imageUrl)
            <a href="{{ $imageUrl }}" target="_blank" rel="noopener noreferrer">
                <img src="{{ $imageUrl }}" alt="Payment receipt"
                    class="h-10 w-10 rounded-lg border border-slate-200 object-cover">
            </a>
        @else
            <span class="text-xs text-concierge-muted">—</span>
        @endif
    @else
        @if ($imageUrl)
            <a href="{{ $imageUrl }}" target="_blank" rel="noopener noreferrer" class="payment-image-existing-link">
                <img src="{{ $imageUrl }}" alt="Payment receipt"
                    class="payment-image-preview h-10 w-10 rounded-lg border border-slate-200 object-cover">
            </a>
        @endif
        <input type="file" name="payments[{{ $index }}][image]" accept="image/jpeg,image/png,image/gif,image/webp"
            class="block w-full max-w-[10rem] cursor-pointer text-xs text-concierge-muted file:mr-2 file:cursor-pointer file:rounded-lg file:border-0 file:bg-concierge-navy file:px-2 file:py-1 file:text-xs file:font-medium file:text-white hover:file:bg-concierge-navy-deep">
        @if ($imageUrl)
            <label class="flex cursor-pointer items-center gap-1.5 text-xs text-concierge-muted">
                <input type="checkbox" name="payments[{{ $index }}][remove_image]" value="1"
                    class="rounded border-slate-300 text-rose-600 focus:ring-rose-500/30">
                {{ __('Remove image') }}
            </label>
        @endif
    @endif
</div>
