@props([
    'inputId',
    'inputName' => 'image',
    'label' => 'Image',
    'hint' => 'JPEG, PNG, GIF, or WebP. Max 2 MB.',
    'optionalHint' => null,
    'ariaLabel' => 'Upload image',
])

<div class="company-image-upload" data-company-image-upload>
    <span class="block text-sm font-medium text-concierge-navy">{{ $label }}</span>

    <div
        class="company-image-dropzone mt-1.5"
        data-company-image-dropzone
        tabindex="0"
        role="button"
        aria-label="{{ $ariaLabel }}"
    >
        <input
            id="{{ $inputId }}"
            name="{{ $inputName }}"
            type="file"
            accept="image/jpeg,image/png,image/gif,image/webp"
            class="sr-only"
            data-company-image-input
        >

        <div class="company-image-dropzone__empty" data-company-image-empty>
            <svg xmlns="http://www.w3.org/2000/svg" class="company-image-dropzone__icon" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
            </svg>
            <p class="company-image-dropzone__title">Drag &amp; drop an image here</p>
            <p class="company-image-dropzone__subtitle">or <span class="font-medium text-concierge-accent">browse files</span></p>
        </div>

        <div class="company-image-dropzone__preview hidden" data-company-image-preview>
            <img src="" alt="" class="company-image-dropzone__img" data-company-image-preview-img>
            <button type="button"
                class="company-image-dropzone__remove"
                data-company-image-remove
                aria-label="Remove image">
                Remove
            </button>
        </div>
    </div>

    <p class="mt-1 text-xs text-concierge-muted">
        {{ $optionalHint ?? $hint }}
    </p>
</div>
