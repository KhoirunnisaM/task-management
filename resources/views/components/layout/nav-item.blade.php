@props(['href', 'label', 'icon', 'active' => false])

<a
    href="{{ $href }}"
    @class([
        'group relative flex items-center gap-2.5 rounded-control px-3 py-2.5 text-sm font-medium transition-colors duration-150',
        'bg-brand-soft text-brand-text' => $active,
        'text-content-muted hover:bg-canvas hover:text-content' => ! $active,
    ])
    @if ($active) aria-current="page" @endif
>
    @if ($active)
        <span class="absolute inset-y-2 left-0 w-0.5 rounded-full bg-brand" aria-hidden="true"></span>
    @endif

    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5 shrink-0" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
    </svg>

    <span class="truncate">{{ $label }}</span>
</a>
