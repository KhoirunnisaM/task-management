@extends('layouts.app')

@section('title', $task->title)

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-4">
            <a href="{{ route('projects.tasks.index', $project) }}" class="text-sm font-medium text-content-muted hover:text-content">
                {{ __('tasks.back_to_tasks') }}
            </a>
        </div>

        <header class="rounded-card border border-line bg-surface p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-mono text-xs text-content-muted">TSK-{{ $task->id }}</span>
                        <x-ui.task-status-badge :status="$task->status" />
                        @if ($task->isOverdue())
                            <span class="rounded-full bg-danger/10 px-2 py-0.5 text-xs font-medium text-danger">{{ __('tasks.overdue') }}</span>
                        @endif
                    </div>

                    <h1 class="mt-2 text-2xl font-bold tracking-tight">{{ $task->title }}</h1>
                    <p class="mt-1 text-sm text-content-muted">{{ $project->title }}</p>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    @can('update', $task)
                        <x-ui.button :href="route('projects.tasks.edit', [$project, $task])" variant="secondary">
                            {{ __('common.edit') }}
                        </x-ui.button>
                    @endcan

                    @can('delete', $task)
                        <x-ui.confirm-dialog
                            :action="route('projects.tasks.destroy', [$project, $task])"
                            :title="__('tasks.delete_title')"
                            :body="__('tasks.delete_body', ['title' => $task->title])"
                            :confirm="__('tasks.delete_confirm')"
                            class="inline-flex h-9 items-center rounded-control px-4 text-sm font-medium text-danger hover:bg-danger/5"
                        >
                            {{ __('common.delete') }}
                        </x-ui.confirm-dialog>
                    @endcan
                </div>
            </div>
        </header>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-card border border-line bg-surface p-5">
                    <h2 class="text-lg font-semibold tracking-tight">{{ __('tasks.description') }}</h2>
                    <p class="mt-2 max-w-prose whitespace-pre-line text-sm text-content-muted">
                        {{ $task->description ?: __('projects.no_description') }}
                    </p>
                </section>

                @can('updateStatus', $task)
                    <section class="rounded-card border border-line bg-surface p-5">
                        <h2 class="text-lg font-semibold tracking-tight">{{ __('tasks.status_field') }}</h2>
                        <p class="mt-1 text-xs text-content-muted">{{ __('tasks.status_field') }} · {{ $task->status->label() }}</p>

                        <div class="mt-4">
                            @include('tasks.partials.status-form', [
                                'project' => $project,
                                'task' => $task,
                                'statuses' => $statuses,
                            ])
                        </div>
                    </section>
                @endcan
            </div>

            <aside class="rounded-card border border-line bg-surface p-5">
                <h2 class="text-lg font-semibold tracking-tight">{{ __('tasks.detail') }}</h2>

                <dl class="mt-4 space-y-4">
                    <div>
                        <dt class="text-xs font-medium text-content-muted">{{ __('tasks.priority_label') }}</dt>
                        <dd class="mt-1 flex items-center gap-2 text-sm font-medium">
                            <x-ui.priority-bars :priority="$task->priority" />
                            {{ $task->priority->label() }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-content-muted">{{ __('tasks.due_date') }}</dt>
                        <dd class="mt-1 text-sm font-medium">
                            @if ($task->due_date)
                                <x-task.due-date :task="$task" class="text-sm" />
                                <span class="ml-1 text-content-muted">({{ $task->due_date->translatedFormat('d M Y') }})</span>
                            @else
                                <span class="text-content-muted">{{ __('common.not_set') }}</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-content-muted">{{ __('tasks.assignee') }}</dt>
                        <dd class="mt-1 flex items-center gap-2 text-sm font-medium">
                            <x-ui.avatar :user="$task->assignee" size="md" />
                            {{ $task->assignee?->name ?? __('tasks.unassigned') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-content-muted">{{ __('tasks.created_by') }}</dt>
                        <dd class="mt-1 flex items-center gap-2 text-sm font-medium">
                            <x-ui.avatar :user="$task->creator" size="md" />
                            {{ $task->creator?->name ?? __('common.not_set') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-content-muted">{{ __('tasks.created_at') }}</dt>
                        <dd class="mt-1 text-sm font-medium">{{ $task->created_at?->translatedFormat('d M Y H:i') }}</dd>
                    </div>
                </dl>
            </aside>
        </div>
    </div>
@endsection
