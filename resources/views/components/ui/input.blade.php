@props(['name', 'type' => 'text', 'value' => null, 'leading' => false])

@php
    $inputId = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $describedBy = collect(explode(' ', (string) $attributes->get('aria-describedby')))
        ->filter()
        ->push($hasError ? $name.'-error' : null)
        ->filter()
        ->unique()
        ->implode(' ');
@endphp

<input
    id="{{ $inputId }}"
    name="{{ $name }}"
    type="{{ $type }}"
    value="{{ $value ?? old($name) }}"
    @if ($hasError) aria-invalid="true" @endif
    {{ $attributes->merge(['aria-describedby' => $describedBy ?: null])->class([
        'h-9 w-full rounded-control border bg-surface text-sm text-content placeholder:text-content-muted transition-colors duration-150',
        'pl-9 pr-3' => $leading,
        'px-3' => ! $leading,
        'border-danger' => $hasError,
        'border-line' => ! $hasError,
    ]) }}
>
