@extends('layouts.app')

@section('title', __('dashboard.title'))

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ __('dashboard.greeting', ['name' => auth()->user()->name]) }}</h1>
            <p class="mt-1 text-sm text-content-muted">{{ __('dashboard.subtitle') }}</p>
        </div>

        @can('create', App\Models\Project::class)
            <x-ui.button :href="route('projects.create')" class="shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ __('projects.create') }}
            </x-ui.button>
        @endcan
    </div>

    <section class="mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-card border border-line bg-line sm:grid-cols-4" aria-label="{{ __('dashboard.title') }}">
        <div class="bg-surface p-4 sm:p-5">
            <p class="text-2xl font-bold tabular-nums">{{ $stats['active_projects'] }}</p>
            <p class="mt-0.5 text-xs text-content-muted">{{ __('dashboard.stats.active_projects') }}</p>
        </div>
        <div class="bg-surface p-4 sm:p-5">
            <p class="text-2xl font-bold tabular-nums">{{ $stats['my_tasks'] }}</p>
            <p class="mt-0.5 text-xs text-content-muted">{{ __('dashboard.stats.my_tasks') }}</p>
        </div>
        <div class="bg-surface p-4 sm:p-5">
            <p @class(['text-2xl font-bold tabular-nums', 'text-danger' => $stats['overdue_tasks'] > 0])>{{ $stats['overdue_tasks'] }}</p>
            <p class="mt-0.5 text-xs text-content-muted">{{ __('dashboard.stats.overdue_tasks') }}</p>
        </div>
        <div class="bg-surface p-4 sm:p-5">
            <p class="text-2xl font-bold tabular-nums">{{ $stats['completed_this_week'] }}</p>
            <p class="mt-0.5 text-xs text-content-muted">{{ __('dashboard.stats.completed_this_week') }}</p>
        </div>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold tracking-tight">{{ __('dashboard.my_tasks') }}</h2>
                    <p class="mt-0.5 text-xs text-content-muted">{{ __('dashboard.my_tasks_hint') }}</p>
                </div>

                <a href="{{ route('projects.index') }}" class="text-sm font-medium text-brand-text hover:text-brand">
                    {{ __('projects.title') }}
                </a>
            </div>

            @if ($myTasks->isEmpty())
                <x-ui.empty-state
                    class="mt-3"
                    :title="__('dashboard.my_tasks_empty')"
                    :body="__('dashboard.my_tasks_empty_body')"
                />
            @else
                <div class="mt-3 divide-y divide-line rounded-card border border-line bg-surface">
                    @foreach ($myTasks as $task)
                        <a
                            href="{{ route('projects.tasks.show', [$task->project, $task]) }}"
                            class="flex items-start gap-3 border-l-[3px] border-l-line p-4 transition-colors duration-150 hover:bg-canvas"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="min-w-0 truncate text-sm font-semibold text-content">{{ $task->title }}</p>
                                    <x-ui.task-status-badge :status="$task->status" />
                                </div>

                                <p class="mt-1 truncate text-xs text-content-muted">
                                    {{ $task->project->title }}
                                </p>

                                <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-content-muted">
                                    <x-ui.priority-bars :priority="$task->priority" />

                                    @if ($task->due_date)
                                        <x-task.due-date :task="$task" />
                                    @endif
                                </div>
                            </div>

                            <x-ui.avatar :user="$task->assignee" size="md" class="mt-0.5" />
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <section>
            <h2 class="text-lg font-semibold tracking-tight">{{ __('dashboard.recent_activity') }}</h2>

            @if ($activities->isEmpty())
                <x-ui.empty-state
                    class="mt-3"
                    :title="__('dashboard.activity_empty')"
                    :body="__('dashboard.activity_empty_body')"
                />
            @else
                <ol class="mt-3 divide-y divide-line rounded-card border border-line bg-surface">
                    @foreach ($activities as $activity)
                        @php
                            $taskTitle = $activity->task?->title ?? ($activity->properties['title'] ?? null);
                            $actionLabel = __('activity.actions.'.$activity->action);
                        @endphp

                        <li class="flex items-start gap-3 p-4">
                            <x-ui.avatar :user="$activity->user" size="md" />

                            <div class="min-w-0 flex-1">
                                <p class="text-sm leading-5 text-content">
                                    <span class="font-medium">{{ $activity->user?->name ?? __('common.not_set') }}</span>
                                    {{ $actionLabel }}
                                    @if ($taskTitle)
                                        <span class="font-medium">“{{ $taskTitle }}”</span>
                                    @endif
                                </p>

                                <p class="mt-1 truncate text-xs text-content-muted">
                                    {{ $activity->project?->title }}
                                    @if ($activity->created_at)
                                        <span aria-hidden="true"> · </span>
                                        {{ $activity->created_at->diffForHumans() }}
                                    @endif
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>

    <section class="mt-8">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold tracking-tight">{{ __('dashboard.recent_projects') }}</h2>
                <p class="mt-0.5 text-xs text-content-muted">{{ __('dashboard.recent_projects_hint') }}</p>
            </div>

            <a href="{{ route('projects.index') }}" class="text-sm font-medium text-brand-text hover:text-brand">
                {{ __('projects.all_projects') }}
            </a>
        </div>

        @if ($recentProjects->isEmpty())
            <x-ui.empty-state
                class="mt-3"
                :title="__('dashboard.projects_empty')"
                :body="__('dashboard.projects_empty_body')"
            >
                @can('create', App\Models\Project::class)
                    <x-ui.button :href="route('projects.create')">{{ __('projects.create') }}</x-ui.button>
                @endcan
            </x-ui.empty-state>
        @else
            <div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($recentProjects as $project)
                    <article class="rounded-card border border-line bg-surface p-4 shadow-card">
                        <div class="flex items-start justify-between gap-2">
                            <a href="{{ route('projects.show', $project) }}" class="min-w-0 text-sm font-semibold tracking-tight text-content hover:text-brand-text">
                                {{ $project->title }}
                            </a>

                            <x-ui.status-badge :status="$project->status" class="shrink-0" />
                        </div>

                        <p class="mt-1 text-xs text-content-muted">{{ $project->owner->name }}</p>

                        <x-ui.progress-bar :value="$project->progress" class="mt-3" />

                        <div class="mt-3 flex items-center justify-between gap-2 text-xs text-content-muted">
                            <span class="tabular-nums">{{ $project->completed_tasks_count }}/{{ $project->tasks_count }}</span>
                            <x-ui.avatar-stack :members="$project->members" :limit="3" />
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
