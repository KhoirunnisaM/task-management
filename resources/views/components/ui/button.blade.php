@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'bg-brand text-white hover:bg-brand-hover',
        'secondary' => 'border border-line bg-surface text-content hover:bg-canvas',
        'ghost' => 'text-content-muted hover:bg-brand-soft hover:text-brand-text',
        'danger' => 'bg-danger text-white hover:opacity-90',
    ];

    $sizes = [
        'sm' => 'h-8 px-3 text-xs',
        'md' => 'h-9 px-4',
        'lg' => 'h-11 px-5',
    ];

    $classes = [
        'inline-flex items-center justify-center gap-2 rounded-control text-sm font-medium transition-colors duration-150 disabled:pointer-events-none disabled:opacity-50',
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </button>
@endif
