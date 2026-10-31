@props(['priority'])

@php
    $level = [
        'low' => 1,
        'medium' => 2,
        'high' => 3,
        'urgent' => 4,
    ][$priority->value];

    $color = [
        'low' => 'bg-priority-low',
        'medium' => 'bg-priority-medium',
        'high' => 'bg-priority-high',
        'urgent' => 'bg-priority-urgent',
    ][$priority->value];
@endphp

<span {{ $attributes->class('inline-flex items-end gap-0.5') }} title="{{ __('tasks.priority_label') }} {{ $priority->label() }}">
    @foreach ([6, 9, 12, 15] as $index => $height)
        <span class="w-[3px] rounded-sm {{ $index < $level ? $color : 'bg-line' }}" style="height: {{ $height }}px"></span>
    @endforeach
    <span class="sr-only">{{ __('tasks.priority_label') }} {{ $priority->label() }}</span>
</span>
