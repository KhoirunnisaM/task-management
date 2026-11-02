@props(['fallback', 'label' => null])

@php
    $label = $label ?? __('common.back');
@endphp

<a
    href="{{ $fallback }}"
    x-data="{
        goBack(event) {
            try {
                if (! document.referrer) {
                    return;
                }

                const referrer = new URL(document.referrer);

                if (referrer.origin !== window.location.origin || referrer.href === window.location.href) {
                    return;
                }

                event.preventDefault();
                window.history.back();
            } catch (error) {
                // Fall back to the explicit URL when the referrer is unavailable.
            }
        },
    }"
    x-on:click="goBack($event)"
    {{ $attributes->class('inline-flex h-8 w-fit items-center gap-1.5 rounded-control border border-line bg-surface px-3 text-xs font-medium text-content-muted transition-colors duration-150 hover:bg-canvas hover:text-content focus:outline-none focus:ring-2 focus:ring-brand/20') }}
>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-3.5 shrink-0" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
    </svg>
    {{ $label }}
</a>
