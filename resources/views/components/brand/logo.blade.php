@props(['href' => null, 'tagline' => false, 'size' => 'md'])

@php
    $sizes = [
        'sm' => ['icon' => 'size-8', 'text' => 'text-base', 'tagline' => 'text-[9px]'],
        'md' => ['icon' => 'size-10', 'text' => 'text-lg', 'tagline' => 'text-[10px]'],
        'lg' => ['icon' => 'size-12', 'text' => 'text-xl', 'tagline' => 'text-[11px]'],
    ];

    $sizing = $sizes[$size] ?? $sizes['md'];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class('inline-flex min-w-0 items-center gap-3 rounded-control') }}>
@else
    <div {{ $attributes->class('inline-flex min-w-0 items-center gap-3') }}>
@endif
    <img
        src="{{ asset('images/taskflow-icon.svg') }}"
        alt=""
        class="{{ $sizing['icon'] }} shrink-0 rounded-[10px] shadow-card"
        aria-hidden="true"
    >

    <span class="min-w-0">
        <span class="block truncate font-bold leading-tight tracking-tight {{ $sizing['text'] }}">
            <span class="text-content">Task</span><span class="text-brand-text">Flow</span>
        </span>

        @if ($tagline)
            <span class="mt-1 block truncate font-semibold uppercase tracking-[0.18em] text-content-muted {{ $sizing['tagline'] }}">
                {{ __('common.tagline') }}
            </span>
        @endif
    </span>
@if ($href)
    </a>
@else
    </div>
@endif
