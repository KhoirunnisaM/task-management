@extends('layouts.app')

@section('title', $project->title)

@section('content')
    <div x-data="{ tab: '{{ $errors->any() ? 'members' : 'overview' }}' }">
        <div class="mb-4">
            <a href="{{ route('projects.index') }}" class="text-sm font-medium text-content-muted hover:text-content">
                {{ __('common.back') }}
            </a>
        </div>

        <header class="rounded-card border border-line bg-surface p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-3xl font-bold tracking-tight">{{ $project->title }}</h1>
                        <x-ui.status-badge :status="$project->status" />
                    </div>
                    <p class="mt-1.5 text-sm text-content-muted">{{ $project->owner->name }}</p>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    @can('create', [App\Models\Task::class, $project])
                        <x-ui.button :href="route('projects.tasks.create', $project)">
                            {{ __('tasks.create') }}
                        </x-ui.button>
                    @endcan

                    @can('update', $project)
                        <x-ui.button :href="route('projects.edit', $project)" variant="secondary">
                            {{ __('common.edit') }}
                        </x-ui.button>
                    @endcan

                    @can('delete', $project)
                        <x-ui.confirm-dialog
                            :action="route('projects.destroy', $project)"
                            :title="__('projects.delete_title')"
                            :body="__('projects.delete_body', ['title' => $project->title])"
                            :confirm="__('projects.delete_confirm')"
                            class="inline-flex h-9 items-center rounded-control px-4 text-sm font-medium text-danger hover:bg-danger/5"
                        >
                            {{ __('common.delete') }}
                        </x-ui.confirm-dialog>
                    @endcan
                </div>
            </div>

            <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <x-ui.progress-bar :value="$progress" :label="__('projects.progress')" class="w-full sm:max-w-md" />

                <div class="flex items-center gap-3">
                    <x-ui.avatar-stack :members="$project->members" />
                    @can('manageMembers', $project)
                        <x-ui.button variant="secondary" size="sm" @click="tab = 'members'">
                            {{ __('members.add') }}
                        </x-ui.button>
                    @endcan
                </div>
            </div>
        </header>

        @if ($project->isArchived())
            <div class="mt-4 rounded-card border border-warning/30 bg-warning/5 p-4 text-sm text-warning">
                {{ __('projects.archived_notice') }}
            </div>
        @endif

        <div class="mt-6 border-b border-line">
            <nav class="flex gap-5" aria-label="{{ __('projects.sections') }}">
                @foreach (['overview' => __('projects.overview'), 'members' => __('members.title'), 'activity' => __('projects.activity')] as $key => $label)
                    <button
                        type="button"
                        @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'border-brand text-content' : 'border-transparent text-content-muted hover:text-content'"
                        class="-mb-px border-b-2 pb-3 text-sm font-medium transition-colors duration-150"
                    >
                        {{ $label }}
                    </button>
                @endforeach

                <a
                    href="{{ route('projects.tasks.board', $project) }}"
                    class="-mb-px border-b-2 border-transparent pb-3 text-sm font-medium text-content-muted transition-colors duration-150 hover:text-content"
                >
                    {{ __('tasks.board') }}
                </a>

                <a
                    href="{{ route('projects.tasks.index', $project) }}"
                    class="-mb-px border-b-2 border-transparent pb-3 text-sm font-medium text-content-muted transition-colors duration-150 hover:text-content"
                >
                    {{ __('tasks.list') }}
                </a>
            </nav>
        </div>

        <section x-show="tab === 'overview'" class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="rounded-card border border-line bg-surface p-5 lg:col-span-2">
                <h2 class="text-lg font-semibold tracking-tight">{{ __('projects.description') }}</h2>
                <p class="mt-2 max-w-prose whitespace-pre-line text-sm text-content-muted">
                    {{ $project->description ?: __('projects.no_description') }}
                </p>

                <dl class="mt-6 grid gap-4 border-t border-line pt-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium text-content-muted">{{ __('projects.owner') }}</dt>
                        <dd class="mt-1 text-sm font-medium">{{ $project->owner->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-content-muted">{{ __('projects.period') }}</dt>
                        <dd class="mt-1 text-sm font-medium">
                            @if ($project->start_date || $project->end_date)
                                {{ $project->start_date?->translatedFormat('d M Y') ?? __('common.not_set') }}
                                <span class="text-content-muted">–</span>
                                {{ $project->end_date?->translatedFormat('d M Y') ?? __('common.not_set') }}
                            @else
                                {{ __('common.not_set') }}
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-card border border-line bg-surface p-5">
                <h2 class="text-lg font-semibold tracking-tight">{{ __('projects.progress') }}</h2>

                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-2xl font-bold tabular-nums">{{ $project->tasks_count }}</p>
                        <p class="mt-0.5 text-xs text-content-muted">{{ __('projects.task_count', ['count' => $project->tasks_count]) }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold tabular-nums text-status-completed">{{ $completedTasks }}</p>
                        <p class="mt-0.5 text-xs text-content-muted">{{ __('projects.completed_tasks') }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold tabular-nums">{{ $project->members->count() }}</p>
                        <p class="mt-0.5 text-xs text-content-muted">{{ __('members.title') }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold tabular-nums">{{ $progress }}%</p>
                        <p class="mt-0.5 text-xs text-content-muted">{{ __('projects.progress') }}</p>
                    </div>
                </div>

                <x-ui.progress-bar :value="$progress" class="mt-5" />
            </div>
        </section>

        <section x-show="tab === 'members'" x-cloak class="mt-6">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-3 lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold tracking-tight">{{ __('members.title') }}</h2>
                        <span class="text-xs text-content-muted">{{ __('projects.member_count', ['count' => $project->members->count()]) }}</span>
                    </div>

                    <div class="divide-y divide-line rounded-card border border-line bg-surface">
                        @foreach ($project->members->sortBy(fn ($member) => $member->id === $project->owner_id ? 0 : 1) as $member)
                            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex min-w-0 items-center gap-3">
                                    <x-ui.avatar :user="$member" size="lg" />
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium">{{ $member->name }}</p>
                                        <p class="truncate text-xs text-content-muted">{{ $member->email }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 sm:shrink-0">
                                    @if ($member->pivot->role === App\Enums\ProjectRole::Manager)
                                        <span class="rounded-full bg-brand-soft px-2.5 py-0.5 text-xs font-medium text-brand-text">
                                            {{ $member->pivot->role->label() }}
                                        </span>

                                        @if ($member->id === $project->owner_id)
                                            <span class="text-xs text-content-muted">{{ __('projects.owner') }}</span>
                                        @endif
                                    @else
                                        @can('changeMemberRole', [$project, $member])
                                            <form method="POST" action="{{ route('projects.members.update', [$project, $member]) }}" class="flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <label for="role-{{ $member->id }}" class="sr-only">{{ __('members.role') }}</label>
                                                <select
                                                    id="role-{{ $member->id }}"
                                                    name="role"
                                                    class="h-8 rounded-control border border-line bg-surface px-2 text-xs text-content"
                                                    onchange="this.form.submit()"
                                                >
                                                    @foreach (App\Enums\ProjectRole::cases() as $role)
                                                        <option value="{{ $role->value }}" @selected($member->pivot->role === $role)>{{ $role->label() }}</option>
                                                    @endforeach
                                                </select>
                                                <noscript>
                                                    <x-ui.button type="submit" variant="secondary" size="sm">{{ __('common.save') }}</x-ui.button>
                                                </noscript>
                                            </form>
                                        @else
                                            <span class="rounded-full bg-status-todo-soft px-2.5 py-0.5 text-xs font-medium text-status-todo">
                                                {{ $member->pivot->role->label() }}
                                            </span>
                                        @endcan

                                        @can('removeMember', [$project, $member])
                                            <x-ui.confirm-dialog
                                                :action="route('projects.members.destroy', [$project, $member])"
                                                :title="__('members.remove_title')"
                                                :body="__('members.remove_body', ['name' => $member->name])"
                                                :confirm="__('members.remove_confirm')"
                                                class="rounded-control px-2.5 py-1.5 text-xs font-medium text-danger hover:bg-danger/5"
                                            >
                                                {{ __('members.remove') }}
                                            </x-ui.confirm-dialog>
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <div class="rounded-card border border-line bg-surface p-5">
                        <h2 class="text-lg font-semibold tracking-tight">{{ __('members.add_title') }}</h2>

                        @can('manageMembers', $project)
                            @if ($eligibleMembers->isNotEmpty())
                                <p class="mt-1 text-xs text-content-muted">{{ __('members.add_hint') }}</p>

                                <form method="POST" action="{{ route('projects.members.store', $project) }}" class="mt-4 space-y-3">
                                    @csrf
                                    <x-ui.form-field :label="__('members.select_user')" name="user_id">
                                        <select
                                            id="user_id"
                                            name="user_id"
                                            required
                                            @class([
                                                'h-9 w-full rounded-control border bg-surface px-3 text-sm text-content',
                                                'border-danger' => $errors->has('user_id'),
                                                'border-line' => ! $errors->has('user_id'),
                                            ])
                                        >
                                            <option value="">{{ __('members.select_user_placeholder') }}</option>
                                            @foreach ($eligibleMembers as $eligibleMember)
                                                <option value="{{ $eligibleMember->id }}">{{ $eligibleMember->name }} — {{ $eligibleMember->email }}</option>
                                            @endforeach
                                        </select>
                                    </x-ui.form-field>

                                    <x-ui.button type="submit" class="w-full">{{ __('members.add') }}</x-ui.button>
                                </form>
                            @else
                                <p class="mt-2 text-sm text-content-muted">{{ __('members.no_eligible_users') }}</p>
                            @endif
                        @else
                            <p class="mt-2 text-sm text-content-muted">
                                {{ $project->isArchived() ? __('projects.archived_notice') : __('common.unauthorized') }}
                            </p>
                        @endcan
                    </div>

                    <p class="mt-3 px-1 text-xs text-content-muted">{{ __('members.manager_hint') }}</p>
                </div>
            </div>
        </section>

        <section x-show="tab === 'activity'" x-cloak class="mt-6">
            <h2 class="text-lg font-semibold tracking-tight">{{ __('projects.activity') }}</h2>

            @if ($project->activityLogs->isEmpty())
                <x-ui.empty-state class="mt-3" :title="__('projects.activity_empty')" :body="__('projects.activity_empty_body')" />
            @else
                <ol class="mt-3 divide-y divide-line rounded-card border border-line bg-surface">
                    @foreach ($project->activityLogs as $log)
                        <li class="flex items-start gap-3 p-4">
                            <x-ui.avatar :user="$log->user" size="md" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-content">
                                    <span class="font-medium">{{ $log->user?->name ?? __('common.not_set') }}</span>
                                    {{ __('activity.actions.'.$log->action) }}
                                    @if (isset($log->properties['title']))
                                        <span class="font-medium">“{{ $log->properties['title'] }}”</span>
                                    @endif
                                </p>
                                <p class="mt-0.5 text-xs text-content-muted">{{ $log->created_at?->diffForHumans() }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>
@endsection
