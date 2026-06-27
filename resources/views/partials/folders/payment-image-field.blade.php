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

<div class="payment-image-field min-w-[9rem]">
    <input type="hidden" name="payments[{{ $index }}][form_index]" value="{{ $index }}" data-payment-form-index>

    @if ($locked)
        @if ($imageUrl)
            <a href="{{ $imageUrl }}" target="_blank" rel="noopener noreferrer">
                <img src="{{ $imageUrl }}" alt="Payment receipt"
                    class="h-16 w-16 rounded-lg border border-slate-200 object-cover">
            </a>
        @else
            <span class="text-xs text-concierge-muted">—</span>
        @endif
    @else
        <div class="payment-receipt-upload" data-payment-receipt-upload
            data-existing-image-url="{{ $imageUrl ?? '' }}">
            <div class="company-image-upload" data-company-image-upload>
                <div class="payment-receipt-dropzone company-image-dropzone" data-company-image-dropzone tabindex="0"
                    role="button" aria-label="{{ __('Upload payment receipt') }}">
                    <input type="file" name="payments[{{ $index }}][image]"
                        accept="image/jpeg,image/png,image/gif,image/webp" class="sr-only"
                        data-company-image-input>

                    <div class="company-image-dropzone__empty payment-receipt-dropzone__empty"
                        data-company-image-empty>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="company-image-dropzone__icon payment-receipt-dropzone__icon" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        <p class="company-image-dropzone__title payment-receipt-dropzone__title">
                            {{ __('Drop receipt here') }}</p>
                        <p class="company-image-dropzone__subtitle payment-receipt-dropzone__subtitle">
                            {{ __('Paste, or') }}
                            <span class="font-medium text-concierge-accent">{{ __('browse') }}</span>
                        </p>
                    </div>

                    <div class="company-image-dropzone__preview payment-receipt-dropzone__preview hidden"
                        data-company-image-preview>
                        <img src="" alt="" class="company-image-dropzone__img payment-receipt-dropzone__img"
                            data-company-image-preview-img>
                        <button type="button" class="company-image-dropzone__remove payment-receipt-dropzone__remove"
                            data-company-image-remove aria-label="{{ __('Remove receipt image') }}">
                            {{ __('Remove') }}
                        </button>
                    </div>
                </div>
            </div>

            @if ($imageUrl)
                <input type="checkbox" name="payments[{{ $index }}][remove_image]" value="1"
                    class="sr-only" data-payment-remove-image>
            @endif
        </div>
    @endif
</div>
