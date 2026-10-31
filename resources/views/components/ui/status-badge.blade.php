@props(['status'])

@php
    $styles = [
        'planning' => 'bg-status-todo-soft text-status-todo',
        'active' => 'bg-status-in-progress-soft text-status-in-progress',
        'on_hold' => 'bg-warning/10 text-warning',
        'archived' => 'bg-line text-content-muted',
        'todo' => 'bg-status-todo-soft text-status-todo',
        'in_progress' => 'bg-status-in-progress-soft text-status-in-progress',
        'in_review' => 'bg-status-in-review-soft text-status-in-review',
        'completed' => 'bg-status-completed-soft text-status-completed',
    ];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium',
    $styles[$status->value] ?? 'bg-line text-content-muted',
]) }}>
    <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
    {{ $status->label() }}
</span>
