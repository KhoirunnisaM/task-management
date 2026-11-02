@props(['name' => null, 'id' => null])

@php
    $selectId = $id ?? $attributes->get('id', $name);
    $hasError = $name ? $errors->has($name) : false;
@endphp

<select
    @if ($selectId) id="{{ $selectId }}" @endif
    @if ($name) name="{{ $name }}" @endif
    @if ($hasError) aria-invalid="true" @endif
    {{ $attributes->class([
        'h-9 w-full rounded-control border bg-surface px-3 text-sm text-content transition-colors duration-150',
        'hover:border-content-muted/40',
        'focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20',
        'border-danger' => $hasError,
        'border-line' => ! $hasError,
    ]) }}
>
    {{ $slot }}
</select>
