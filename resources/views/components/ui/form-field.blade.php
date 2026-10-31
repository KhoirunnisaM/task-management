@props(['label', 'name', 'hint' => null, 'optional' => false])

<div {{ $attributes }}>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-content">
        {{ $label }}
        @if ($optional)
            <span class="font-normal text-content-muted">{{ __('common.optional') }}</span>
        @endif
    </label>

    {{ $slot }}

    @if ($hint)
        <p class="mt-1.5 text-xs text-content-muted">{{ $hint }}</p>
    @endif

    @error($name)
        <p id="{{ $name }}-error" class="mt-1.5 text-xs text-danger">{{ $message }}</p>
    @enderror
</div>
