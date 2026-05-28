@php
    $title = $title ?? __('This payment is locked and cannot be edited');
    $class =
        $class ??
        'inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-slate-200 text-slate-600';
@endphp
<span class="{{ $class }}" title="{{ $title }}" aria-label="{{ $title }}">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
        stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
    </svg>
</span>
