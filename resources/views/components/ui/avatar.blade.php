@props(['user' => null, 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'size-6 text-[10px]',
        'md' => 'size-8 text-xs',
        'lg' => 'size-10 text-sm',
    ];

    $colors = [
        'bg-brand-soft text-brand-text',
        'bg-status-in-review-soft text-status-in-review',
        'bg-status-in-progress-soft text-status-in-progress',
        'bg-status-completed-soft text-status-completed',
        'bg-status-todo-soft text-status-todo',
        'bg-warning/10 text-warning',
    ];

    $initials = $user
        ? \Illuminate\Support\Str::of($user->name)->explode(' ')->take(2)->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->implode('')
        : null;

    $color = $user ? $colors[$user->id % count($colors)] : 'border border-dashed border-line text-content-muted';
@endphp

@if ($user)
    <span
        {{ $attributes->class([
            'inline-flex shrink-0 items-center justify-center rounded-full font-semibold',
            $sizes[$size] ?? $sizes['md'],
            $color,
        ]) }}
        title="{{ $user->name }}"
    >
        {{ $initials }}
        <span class="sr-only">{{ $user->name }}</span>
    </span>
@else
    <span
        {{ $attributes->class([
            'inline-flex shrink-0 items-center justify-center rounded-full',
            $sizes[$size] ?? $sizes['md'],
            $color,
        ]) }}
        title="{{ __('tasks.unassigned') }}"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 19.5a7.5 7.5 0 0 1 15 0" />
        </svg>
        <span class="sr-only">{{ __('tasks.unassigned') }}</span>
    </span>
@endif
