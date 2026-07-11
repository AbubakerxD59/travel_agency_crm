@php
    $locked = (bool) ($folder->lock ?? false);
    $canToggle = (bool) ($canToggle ?? false);
    $toggleUrl = $toggleUrl ?? null;
    if ($canToggle && $toggleUrl === null) {
        $toggleUrl = portal_route('folders.toggle-lock', $folder);
    }
    $lockTitle = $locked
        ? __('This folder is locked. Agents need Edit Locked Folders permission to edit it.')
        : __('Lock this folder to prevent agent edits.');
    $unlockTitle = __('Unlock this folder to allow agent edits again.');
@endphp

@if ($canToggle && $toggleUrl)
    <form method="POST" action="{{ $toggleUrl }}" class="inline">
        @csrf
        @method('PATCH')
        <button type="submit"
            class="folder-lock-action inline-flex cursor-pointer rounded-lg p-2 transition {{ $locked ? 'bg-slate-200 text-slate-700 hover:bg-slate-300' : 'text-concierge-muted hover:bg-slate-100 hover:text-concierge-navy' }}"
            title="{{ $locked ? $unlockTitle : $lockTitle }}"
            aria-label="{{ $locked ? $unlockTitle : $lockTitle }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </button>
    </form>
@elseif ($locked)
    @include('partials.folders.payment-locked-icon', [
        'title' => $lockTitle,
        'class' => 'inline-flex h-5 w-5 items-center justify-center rounded bg-slate-200 text-slate-600',
    ])
@endif
