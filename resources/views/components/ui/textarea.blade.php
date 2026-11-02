@props(['name', 'value' => null, 'rows' => 4])

@php
    $textareaId = $attributes->get('id', $name);
    $hasError = $name ? $errors->has($name) : false;
    $describedBy = collect(explode(' ', (string) $attributes->get('aria-describedby')))
        ->filter()
        ->push($hasError ? $name.'-error' : null)
        ->filter()
        ->unique()
        ->implode(' ');
@endphp

<textarea
    id="{{ $textareaId }}"
    name="{{ $name }}"
    rows="{{ $rows }}"
    @if ($hasError) aria-invalid="true" @endif
    {{ $attributes->merge(['aria-describedby' => $describedBy ?: null])->class([
        'w-full rounded-control border bg-surface px-3 py-2 text-sm text-content placeholder:text-content-muted transition-colors duration-150',
        'hover:border-content-muted/40',
        'focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20',
        'border-danger' => $hasError,
        'border-line' => ! $hasError,
    ]) }}
>{{ $value ?? old($name) }}</textarea>
