@props([
    'title',
    'hint' => null,
    'href' => null,
    'linkLabel' => null,
    'empty' => false,
    'emptyTitle' => null,
    'emptyBody' => null,
])

<section {{ $attributes }}>
    <div class="flex items-end justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold tracking-tight">{{ $title }}</h2>
            @if ($hint)
                <p class="mt-0.5 text-xs text-content-muted">{{ $hint }}</p>
            @endif
        </div>

        @if ($href)
            <a href="{{ $href }}" class="shrink-0 text-sm font-medium text-brand-text transition-colors duration-150 hover:text-brand">
                {{ $linkLabel }}
            </a>
        @endif
    </div>

    @if ($empty)
        <x-ui.empty-state class="mt-3" :title="$emptyTitle" :body="$emptyBody" />
    @else
        {{ $slot }}
    @endif
</section>
