@extends('layouts.app')

@section('title', __('tasks.index_title'))

@section('content')
    <x-ui.back-button :fallback="route('projects.show', $project)" class="mb-4" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ __('tasks.index_title') }}</h1>
            <p class="mt-1 text-sm text-content-muted">{{ $project->title }}</p>
        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <x-ui.button :href="route('projects.tasks.board', $project)" variant="secondary">
                {{ __('tasks.board') }}
            </x-ui.button>

            @can('create', [App\Models\Task::class, $project])
                <x-ui.button :href="route('projects.tasks.create', $project)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('tasks.create') }}
                </x-ui.button>
            @endcan
        </div>
    </div>

    <form method="GET" action="{{ route('projects.tasks.index', $project) }}" class="mt-6 rounded-card border border-line bg-surface p-3">
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
            <div class="relative lg:col-span-2">
                <label for="search" class="sr-only">{{ __('common.search') }}</label>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-content-muted" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <x-ui.input
                    name="search"
                    type="search"
                    :value="$filters['search'] ?? ''"
                    :placeholder="__('tasks.search_placeholder')"
                    leading
                />
            </div>

            <div>
                <label for="status" class="sr-only">{{ __('tasks.status_field') }}</label>
                <x-ui.select id="status" name="status">
                    <option value="">{{ __('tasks.all_status') }}</option>
                    @foreach (App\Enums\TaskStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div>
                <label for="priority" class="sr-only">{{ __('tasks.priority_label') }}</label>
                <x-ui.select id="priority" name="priority">
                    <option value="">{{ __('tasks.all_priority') }}</option>
                    @foreach (App\Enums\TaskPriority::cases() as $priority)
                        <option value="{{ $priority->value }}" @selected(($filters['priority'] ?? '') === $priority->value)>{{ $priority->label() }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div>
                <label for="assignee" class="sr-only">{{ __('tasks.assignee') }}</label>
                <x-ui.select id="assignee" name="assignee">
                    <option value="">{{ __('tasks.all_assignee') }}</option>
                    <option value="unassigned" @selected(($filters['assignee'] ?? '') === 'unassigned')>{{ __('tasks.unassigned') }}</option>
                    @foreach ($project->members->sortBy('name') as $member)
                        <option value="{{ $member->id }}" @selected((string) ($filters['assignee'] ?? '') === (string) $member->id)>{{ $member->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </div>

        <div class="mt-2 flex flex-wrap items-center gap-2">
            <div>
                <label for="due" class="sr-only">{{ __('tasks.due_date') }}</label>
                <x-ui.select id="due" name="due">
                    <option value="">{{ __('tasks.all_due') }}</option>
                    <option value="overdue" @selected(($filters['due'] ?? '') === 'overdue')>{{ __('tasks.due_overdue') }}</option>
                    <option value="today" @selected(($filters['due'] ?? '') === 'today')>{{ __('tasks.due_today') }}</option>
                    <option value="week" @selected(($filters['due'] ?? '') === 'week')>{{ __('tasks.due_week') }}</option>
                    <option value="none" @selected(($filters['due'] ?? '') === 'none')>{{ __('tasks.due_none') }}</option>
                </x-ui.select>
            </div>

            <x-ui.button type="submit" variant="secondary">{{ __('common.search') }}</x-ui.button>

            @if ($hasFilters)
                <x-ui.button :href="route('projects.tasks.index', $project)" variant="ghost">{{ __('common.reset_filter') }}</x-ui.button>
            @endif
        </div>
    </form>

    @if ($tasksByStatus->flatten()->isEmpty())
        <x-ui.empty-state
            class="mt-6"
            :title="$hasFilters ? __('tasks.empty_filtered_title') : __('tasks.empty_title')"
            :body="$hasFilters ? __('tasks.empty_filtered_body') : __('tasks.empty_body')"
        >
            @can('create', [App\Models\Task::class, $project])
                <x-ui.button :href="route('projects.tasks.create', $project)">{{ __('tasks.create') }}</x-ui.button>
            @endcan
        </x-ui.empty-state>
    @else
        @php
            $dots = [
                'todo' => 'bg-status-todo',
                'in_progress' => 'bg-status-in-progress',
                'in_review' => 'bg-status-in-review',
                'completed' => 'bg-status-completed',
            ];

            $edges = [
                'todo' => 'border-t-status-todo',
                'in_progress' => 'border-t-status-in-progress',
                'in_review' => 'border-t-status-in-review',
                'completed' => 'border-t-status-completed',
            ];
        @endphp

        <div class="mt-6 space-y-5">
            @foreach (App\Enums\TaskStatus::cases() as $status)
                @php($group = $tasksByStatus->get($status->value, collect()))

                <section class="rounded-card border border-t-[3px] border-line bg-surface {{ $edges[$status->value] }}">
                    <header class="flex items-center gap-2 border-b border-line px-4 py-3">
                        <span class="size-2 rounded-full {{ $dots[$status->value] }}" aria-hidden="true"></span>
                        <h2 class="text-sm font-semibold">{{ $status->label() }}</h2>
                        <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-canvas px-1.5 py-0.5 text-[11px] font-semibold tabular-nums text-content-muted">{{ $group->count() }}</span>
                    </header>

                    @if ($group->isEmpty())
                        <p class="px-4 py-5 text-xs text-content-muted">{{ __('tasks.empty_column') }}</p>
                    @else
                        <div class="space-y-2 p-2">
                            @foreach ($group as $index => $task)
                                <x-task.card :task="$task" :project="$project">
                                    <x-slot:actions>
                                        @can('updateStatus', $task)
                                            <form method="POST" action="{{ route('projects.tasks.status', [$project, $task]) }}" class="flex items-center">
                                                @csrf
                                                @method('PATCH')
                                                <label for="status-{{ $task->id }}" class="sr-only">{{ __('tasks.status_field') }}</label>
                                                <select
                                                    id="status-{{ $task->id }}"
                                                    name="status"
                                                    class="h-8 rounded-control border border-line bg-surface px-2 text-xs text-content"
                                                    onchange="this.form.submit()"
                                                >
                                                    @foreach (App\Enums\TaskStatus::cases() as $option)
                                                        <option value="{{ $option->value }}" @selected($task->status === $option)>{{ $option->label() }}</option>
                                                    @endforeach
                                                </select>
                                                <noscript>
                                                    <x-ui.button type="submit" variant="secondary" size="sm" class="ml-1">{{ __('common.save') }}</x-ui.button>
                                                </noscript>
                                            </form>
                                        @endcan

                                        @if (! $hasFilters)
                                            @can('reorder', $task)
                                                <form method="POST" action="{{ route('projects.tasks.reorder', [$project, $task]) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="position" value="{{ $index - 1 }}">
                                                    <button
                                                        type="submit"
                                                        class="rounded-control p-1.5 text-content-muted transition-colors duration-150 hover:bg-canvas hover:text-content disabled:pointer-events-none disabled:opacity-40"
                                                        @disabled($loop->first)
                                                        aria-label="{{ __('tasks.move_up') }}"
                                                        title="{{ __('tasks.move_up') }}"
                                                    >
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                                                        </svg>
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('projects.tasks.reorder', [$project, $task]) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="position" value="{{ $index + 1 }}">
                                                    <button
                                                        type="submit"
                                                        class="rounded-control p-1.5 text-content-muted transition-colors duration-150 hover:bg-canvas hover:text-content disabled:pointer-events-none disabled:opacity-40"
                                                        @disabled($loop->last)
                                                        aria-label="{{ __('tasks.move_down') }}"
                                                        title="{{ __('tasks.move_down') }}"
                                                    >
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif

                                        @can('update', $task)
                                            <x-ui.button :href="route('projects.tasks.edit', [$project, $task])" variant="ghost" size="sm">
                                                {{ __('common.edit') }}
                                            </x-ui.button>
                                        @endcan

                                        @can('delete', $task)
                                            <x-ui.confirm-dialog
                                                :action="route('projects.tasks.destroy', [$project, $task])"
                                                :title="__('tasks.delete_title')"
                                                :body="__('tasks.delete_body', ['title' => $task->title])"
                                                :confirm="__('tasks.delete_confirm')"
                                                class="rounded-control px-2.5 py-1.5 text-xs font-medium text-danger hover:bg-danger/5"
                                            >
                                                {{ __('common.delete') }}
                                            </x-ui.confirm-dialog>
                                        @endcan
                                    </x-slot:actions>
                                </x-task.card>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    @endif
@endsection
