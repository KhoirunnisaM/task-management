@props(['items', 'label' => null])

@php
    $grid = match (true) {
        count($items) <= 2 => 'sm:grid-cols-2',
        count($items) === 3 => 'sm:grid-cols-3',
        count($items) === 4 => 'sm:grid-cols-4',
        count($items) === 5 => 'sm:grid-cols-3 xl:grid-cols-5',
        count($items) === 6 => 'sm:grid-cols-3 xl:grid-cols-6',
        default => 'sm:grid-cols-3 xl:grid-cols-7',
    };
@endphp

<section
    {{ $attributes->class([
        'grid grid-cols-2 gap-px overflow-hidden rounded-card border border-line bg-line',
        $grid,
    ]) }}
    aria-label="{{ $label ?? __('dashboard.title') }}"
>
    @foreach ($items as $item)
        @php($tone = $item['tone'] ?? null)

        <div class="bg-surface p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p @class([
                        'text-2xl font-bold tabular-nums',
                        'text-danger' => $tone === 'danger',
                        'text-status-completed' => $tone === 'success',
                    ])>{{ $item['value'] }}</p>
                    <p class="mt-0.5 text-xs leading-5 text-content-muted">{{ $item['label'] }}</p>
                </div>

                @if (! empty($item['icon']))
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-control bg-brand-soft text-brand-text" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    @endforeach
</section>
