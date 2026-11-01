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

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <section class="rounded-card border border-line bg-surface p-5 lg:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-lg font-semibold tracking-tight">{{ __('comments.title') }}</h2>
                    <span class="text-xs text-content-muted">{{ __('comments.count', ['count' => $task->comments->count()]) }}</span>
                </div>

                @can('create', [App\Models\Comment::class, $task])
                    <form method="POST" action="{{ route('projects.tasks.comments.store', [$project, $task]) }}" class="mt-4">
                        @csrf

                        <x-ui.form-field :label="__('comments.add_title')" name="body" :hint="__('comments.add_hint')">
                            <textarea
                                id="body"
                                name="body"
                                rows="3"
                                required
                                placeholder="{{ __('comments.placeholder') }}"
                                @class([
                                    'w-full rounded-control border bg-surface px-3 py-2 text-sm text-content placeholder:text-content-muted',
                                    'border-danger' => $errors->has('body'),
                                    'border-line' => ! $errors->has('body'),
                                ])
                            >{{ old('body') }}</textarea>
                        </x-ui.form-field>

                        <div class="mt-3 flex justify-end">
                            <x-ui.button type="submit">{{ __('comments.save') }}</x-ui.button>
                        </div>
                    </form>
                @endcan

                <div class="mt-6 space-y-3">
                    @forelse ($task->comments as $comment)
                        <article class="rounded-card border border-line p-4">
                            <div class="flex items-start gap-3">
                                <x-ui.avatar :user="$comment->user" size="md" />

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                                        <p class="text-sm font-medium">{{ $comment->user?->name ?? __('common.not_set') }}</p>
                                        <time datetime="{{ $comment->created_at?->toIso8601String() }}" class="text-xs text-content-muted">
                                            {{ $comment->created_at?->translatedFormat('d M Y H:i') }}
                                        </time>
                                        @if ($comment->updated_at?->gt($comment->created_at))
                                            <span class="text-xs text-content-muted">· {{ __('comments.edited') }}</span>
                                        @endif
                                    </div>

                                    <p class="mt-2 whitespace-pre-line text-sm text-content">{{ $comment->body }}</p>
                                </div>

                                @canany(['update', 'delete'], $comment)
                                    <div class="flex shrink-0 items-center gap-1">
                                        @can('update', $comment)
                                            <x-ui.modal :title="__('comments.edit_title')" max-width="max-w-lg">
                                                <x-slot:trigger>
                                                    <button type="button" class="rounded-control px-2.5 py-1.5 text-xs font-medium text-content-muted hover:bg-canvas hover:text-content">
                                                        {{ __('comments.edit') }}
                                                    </button>
                                                </x-slot:trigger>

                                                <form method="POST" action="{{ route('projects.tasks.comments.update', [$project, $task, $comment]) }}" class="space-y-4">
                                                    @csrf
                                                    @method('PATCH')

                                                    <div>
                                                        <label for="comment-body-{{ $comment->id }}" class="mb-1.5 block text-sm font-medium text-content">
                                                            {{ __('comments.body') }}
                                                        </label>
                                                        <textarea
                                                            id="comment-body-{{ $comment->id }}"
                                                            name="body"
                                                            rows="4"
                                                            required
                                                            @class([
                                                                'w-full rounded-control border bg-surface px-3 py-2 text-sm text-content',
                                                                'border-danger' => $errors->has('body'),
                                                                'border-line' => ! $errors->has('body'),
                                                            ])
                                                        >{{ old('body', $comment->body) }}</textarea>
                                                        @error('body')
                                                            <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    <div class="flex justify-end gap-2">
                                                        <x-ui.button type="button" variant="secondary" @click="open = false">
                                                            {{ __('common.cancel') }}
                                                        </x-ui.button>
                                                        <x-ui.button type="submit">{{ __('common.save') }}</x-ui.button>
                                                    </div>
                                                </form>
                                            </x-ui.modal>
                                        @endcan

                                        @can('delete', $comment)
                                            <x-ui.confirm-dialog
                                                :action="route('projects.tasks.comments.destroy', [$project, $task, $comment])"
                                                :title="__('comments.delete_title')"
                                                :body="__('comments.delete_body')"
                                                :confirm="__('comments.delete_confirm')"
                                                class="rounded-control px-2.5 py-1.5 text-xs font-medium text-danger hover:bg-danger/5"
                                            >
                                                {{ __('common.delete') }}
                                            </x-ui.confirm-dialog>
                                        @endcan
                                    </div>
                                @endcanany
                            </div>
                        </article>
                    @empty
                        <div class="rounded-card border border-dashed border-line p-6 text-center">
                            <p class="text-sm font-medium">{{ __('comments.empty_title') }}</p>
                            <p class="mt-1 text-xs text-content-muted">{{ __('comments.empty_body') }}</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-card border border-line bg-surface p-5">
                <h2 class="text-lg font-semibold tracking-tight">{{ __('activity.title') }}</h2>
                <x-activity.timeline :logs="$task->activityLogs" />
            </section>
        </div>
    </div>
@endsection
