@extends('layouts.app')

@section('title', __('admin_members.show_title'))

@section('content')
    <x-ui.back-button :fallback="route('admin.members.index')" class="mb-4" />

    <header class="rounded-card border border-line bg-surface p-5 sm:p-6">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-4">
                <x-ui.avatar :user="$member" size="lg" />

                <div class="min-w-0">
                    <h1 class="truncate text-2xl font-bold tracking-tight">{{ $member->name }}</h1>
                    <p class="mt-1 truncate text-sm text-content-muted">{{ $member->email }}</p>

                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span @class([
                            'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                            'bg-brand-soft text-brand-text' => $member->role === App\Enums\UserRole::Admin,
                            'bg-status-in-progress-soft text-status-in-progress' => $member->role === App\Enums\UserRole::Manager,
                            'bg-status-todo-soft text-status-todo' => $member->role === App\Enums\UserRole::Member,
                        ])>
                            {{ $member->role->label() }}
                        </span>

                        <span class="text-xs text-content-muted">
                            {{ __('admin_members.joined_at') }} {{ $member->created_at?->translatedFormat('d M Y') ?? __('common.not_set') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="shrink-0 rounded-card border border-line bg-canvas px-4 py-3 text-center">
                <p class="text-2xl font-bold tabular-nums">{{ $member->projects_count }}</p>
                <p class="mt-0.5 text-xs text-content-muted">{{ __('admin_members.project_count_label') }}</p>
            </div>
        </div>
    </header>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <section class="rounded-card border border-line bg-surface p-5 lg:col-span-2">
            <h2 class="text-lg font-semibold tracking-tight">{{ __('admin_members.account_info') }}</h2>

            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium text-content-muted">{{ __('common.name') }}</dt>
                    <dd class="mt-1 text-sm font-medium">{{ $member->name }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-content-muted">{{ __('common.email') }}</dt>
                    <dd class="mt-1 break-all text-sm font-medium">{{ $member->email }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-content-muted">{{ __('admin_members.global_role') }}</dt>
                    <dd class="mt-1 text-sm font-medium">{{ $member->role->label() }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-content-muted">{{ __('admin_members.joined_at') }}</dt>
                    <dd class="mt-1 text-sm font-medium">{{ $member->created_at?->translatedFormat('d M Y H:i') ?? __('common.not_set') }}</dd>
                </div>
            </dl>
        </section>

        <aside class="rounded-card border border-line bg-surface p-5">
            <h2 class="text-lg font-semibold tracking-tight">{{ __('admin_members.global_role') }}</h2>
            <p class="mt-2 text-sm leading-6 text-content-muted">{{ __('admin_members.role_note') }}</p>
            <p class="mt-3 text-xs leading-5 text-content-muted">{{ __('admin_members.global_role_hint') }}</p>
        </aside>
    </div>

    <section class="mt-6">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold tracking-tight">{{ __('admin_members.projects_title') }}</h2>
            <span class="text-xs text-content-muted">{{ trans_choice('admin_members.project_count', $projects->count(), ['count' => $projects->count()]) }}</span>
        </div>

        @if ($projects->isEmpty())
            <x-ui.empty-state
                class="mt-3"
                :title="__('admin_members.projects_empty')"
                :body="__('admin_members.projects_empty_body')"
            />
        @else
            <div class="mt-3 divide-y divide-line overflow-hidden rounded-card border border-line bg-surface">
                @foreach ($projects as $project)
                    <article class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <a href="{{ route('projects.show', $project) }}" class="truncate text-sm font-semibold text-content transition-colors duration-150 hover:text-brand-text">
                                {{ $project->title }}
                            </a>

                            <p class="mt-1 flex flex-wrap items-center gap-2 text-xs text-content-muted">
                                <span>{{ __('admin_members.project_role') }}:</span>
                                <span class="font-medium text-content">{{ $project->pivot->role->label() }}</span>
                            </p>
                        </div>

                        <x-ui.status-badge :status="$project->status" class="shrink-0" />
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
