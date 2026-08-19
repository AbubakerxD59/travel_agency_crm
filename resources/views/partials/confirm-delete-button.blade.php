@php
    $title = $title ?? 'Delete this record?';
    $text = $text ?? 'This action cannot be undone.';
    $label = $label ?? 'Delete';
    $variant = $variant ?? 'icon';
    $buttonClass = $buttonClass ?? ($variant === 'text'
        ? 'rounded-lg border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50'
        : 'lead-row-action cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-rose-600');
@endphp
<form method="POST" action="{{ $action }}" class="js-confirm-delete-form"
    data-title="{{ $title }}" data-text="{{ $text }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="{{ $buttonClass }}" title="{{ $label }}" aria-label="{{ $label }}">
        @if ($variant === 'text')
            {{ $label }}
        @else
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
            </svg>
        @endif
    </button>
</form>
