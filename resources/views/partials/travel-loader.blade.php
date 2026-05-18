@props([
    'size' => 'md',
    'message' => '',
    'class' => '',
])

@php
    $sizeClass = match ($size) {
        'sm', 'lg' => "travel-loader--{$size}",
        default => 'travel-loader--md',
    };
    $ariaLabel = $message !== '' ? $message : 'Loading';
@endphp

<div @class(['travel-loader', $sizeClass, $class]) role="status" aria-live="polite" aria-busy="true"
    aria-label="{{ $ariaLabel }}">
    <div class="travel-loader__scene" aria-hidden="true">
        <div class="travel-loader__cloud travel-loader__cloud--1"></div>
        <div class="travel-loader__cloud travel-loader__cloud--2"></div>
        <div class="travel-loader__globe">
            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="2" opacity="0.35" />
                <ellipse cx="24" cy="24" rx="8" ry="20" stroke="currentColor" stroke-width="1.5" opacity="0.5" />
                <path d="M4 24h40" stroke="currentColor" stroke-width="1.5" opacity="0.45" />
                <path d="M8 14c8 4 24 4 32 0M8 34c8-4 24-4 32 0" stroke="currentColor" stroke-width="1.5"
                    opacity="0.35" stroke-linecap="round" />
            </svg>
        </div>
        <div class="travel-loader__orbit">
            <svg class="travel-loader__plane" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M20.5 11.5L4 6.5l2.5 5.5-2 2 3.5 1 2 5.5 2-1.5 2 1.5 2-5.5 3.5-1-2-2 2.5-5.5z"
                    fill="currentColor" />
            </svg>
        </div>
    </div>
    @if ($message !== '' && $size !== 'sm')
        <p class="travel-loader__message">{{ $message }}</p>
    @endif
</div>
