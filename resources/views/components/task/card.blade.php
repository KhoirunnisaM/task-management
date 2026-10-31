@props(['task', 'project' => null])

@php
    $project = $project ?? $task->project;

    $edge = [
        'todo' => 'border-l-status-todo',
        'in_progress' => 'border-l-status-in-progress',
        'in_review' => 'border-l-status-in-review',
        'completed' => 'border-l-status-completed',
    ][$task->status->value];
@endphp

<article {{ $attributes->class([
    'rounded-card border border-l-[3px] border-line bg-surface p-3 shadow-card',
    $edge,
]) }}>
    <div class="flex items-start justify-between gap-2">
        <span class="font-mono text-xs text-content-muted">TSK-{{ $task->id }}</span>

        @isset($menu)
            @if (! $menu->isEmpty())
                <div class="-mr-1 -mt-1">{{ $menu }}</div>
            @endif
        @endisset
    </div>

    <a
        href="{{ route('projects.tasks.show', [$project, $task]) }}"
        class="mt-1 line-clamp-2 block text-sm font-semibold text-content transition-colors duration-150 hover:text-brand-text"
    >
        {{ $task->title }}
    </a>

    <div class="mt-3 flex items-center justify-between gap-3">
        <div class="flex min-w-0 flex-wrap items-center gap-3 text-xs text-content-muted">
            <x-ui.priority-bars :priority="$task->priority" />

            @if ($task->due_date)
                <x-task.due-date :task="$task" />
            @endif

            <span class="inline-flex min-w-0 items-center gap-1.5">
                <x-ui.avatar :user="$task->assignee" size="sm" />
                <span class="truncate">{{ $task->assignee?->name ?? __('tasks.unassigned') }}</span>
            </span>
        </div>
    </div>

    @isset($actions)
        @if (! $actions->isEmpty())
            <div class="mt-3 flex flex-wrap items-center gap-1.5 border-t border-line pt-3">
                {{ $actions }}
            </div>
        @endif
    @endisset
</article>
