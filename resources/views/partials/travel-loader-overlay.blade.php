<div id="travel-loader-overlay"
    class="travel-loader-overlay pointer-events-none invisible fixed inset-0 z-[200] flex items-center justify-center opacity-0 transition-[visibility,opacity] duration-200"
    aria-hidden="true" aria-live="polite">
    @include('partials.travel-loader', ['size' => 'lg', 'message' => 'Loading…', 'class' => 'travel-loader-overlay__content'])
</div>
