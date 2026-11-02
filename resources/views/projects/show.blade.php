@extends('layouts.app')

@section('title', $project->title)

@section('content')
    <div x-data="{ tab: '{{ $errors->any() ? 'members' : 'overview' }}' }">
        <x-ui.back-button :fallback="route('projects.index')" class="mb-4" />

        <header class="rounded-card border border-line bg-surface p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-3xl font-bold tracking-tight">{{ $project->title }}</h1>
                        <x-ui.status-badge :status="$project->status" />
                    </div>

                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-content-muted">
                        <span class="inline-flex items-center gap-1.5">
                            <x-ui.avatar :user="$project->owner" size="sm" />
                            {{ $project->owner->name }}
                        </span>

                        @if ($project->start_date || $project->end_date)
                            <span class="inline-flex items-center gap-1.5">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-3.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                                {{ $project->start_date?->translatedFormat('d M Y') ?? __('common.not_set') }}
                                <span aria-hidden="true">–</span>
                                {{ $project->end_date?->translatedFormat('d M Y') ?? __('common.not_set') }}
                            </span>
                        @endif

                        <span class="inline-flex items-center gap-1.5">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-3.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                            {{ __('projects.member_count', ['count' => $project->members->count()]) }}
                        </span>
                    </div>
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
                                                <x-ui.select
                                                    id="role-{{ $member->id }}"
                                                    name="role"
                                                    class="h-8 w-auto px-2 text-xs"
                                                    onchange="this.form.submit()"
                                                >
                                                    @foreach (App\Enums\ProjectRole::cases() as $role)
                                                        <option value="{{ $role->value }}" @selected($member->pivot->role === $role)>{{ $role->label() }}</option>
                                                    @endforeach
                                                </x-ui.select>
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
                                        <x-ui.select id="user_id" name="user_id" required>
                                            <option value="">{{ __('members.select_user_placeholder') }}</option>
                                            @foreach ($eligibleMembers as $eligibleMember)
                                                <option value="{{ $eligibleMember->id }}">{{ $eligibleMember->name }} — {{ $eligibleMember->email }}</option>
                                            @endforeach
                                        </x-ui.select>
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

            <x-activity.timeline
                :logs="$project->activityLogs"
                :empty-title="__('projects.activity_empty')"
                :empty-body="__('projects.activity_empty_body')"
            />
        </section>
    </div>
@endsection
