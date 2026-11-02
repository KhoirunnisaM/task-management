@props(['task', 'showProject' => true])

<a
    href="{{ route('projects.tasks.show', [$task->project, $task]) }}"
    {{ $attributes->class('flex items-start gap-3 border-l-[3px] border-l-line p-4 transition-colors duration-150 hover:bg-canvas') }}
>
    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
            <p class="min-w-0 truncate text-sm font-semibold text-content">{{ $task->title }}</p>
            <x-ui.task-status-badge :status="$task->status" />
        </div>

        @if ($showProject && $task->project)
            <p class="mt-1 truncate text-xs text-content-muted">{{ $task->project->title }}</p>
        @endif

        <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-content-muted">
            <x-ui.priority-bars :priority="$task->priority" />

            @if ($task->due_date)
                <x-task.due-date :task="$task" />
            @endif
        </div>
    </div>

    <x-ui.avatar :user="$task->assignee" size="md" class="mt-0.5" />
</a>
