@props(['task'])

@php
    $dueDate = $task->due_date;
    $label = null;
    $classes = 'text-content-muted';

    if ($dueDate) {
        if ($task->isCompleted()) {
            $label = $dueDate->translatedFormat('d M Y');
        } elseif ($task->isOverdue()) {
            $days = (int) $dueDate->startOfDay()->diffInDays(today()->startOfDay());
            $label = __('tasks.overdue_days', ['days' => $days]);
            $classes = 'font-medium text-danger';
        } elseif ($dueDate->isToday()) {
            $label = __('tasks.due_today_label');
            $classes = 'font-medium text-warning';
        } elseif ($dueDate->isTomorrow()) {
            $label = __('tasks.due_tomorrow_label');
        } elseif ($dueDate->lte(today()->addDays(7))) {
            $label = $dueDate->translatedFormat('l');
        } else {
            $label = $dueDate->translatedFormat('d M Y');
        }
    }
@endphp

@if ($label)
    <span {{ $attributes->class(['inline-flex items-center gap-1 text-xs', $classes]) }}>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-3.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        {{ $label }}
    </span>
@endif
