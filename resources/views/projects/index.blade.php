@extends('layouts.app')

@section('title', __('projects.index_title'))

@section('content')
    <x-ui.back-button :fallback="route('dashboard')" class="mb-4" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ __('projects.index_title') }}</h1>
            <p class="mt-1 text-sm text-content-muted">{{ __('projects.index_subtitle') }}</p>
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

    <form method="GET" action="{{ route('projects.index') }}" class="mt-6 flex flex-col gap-2 rounded-card border border-line bg-surface p-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <label for="search" class="sr-only">{{ __('common.search') }}</label>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-content-muted" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <x-ui.input
                name="search"
                type="search"
                :value="$filters['search'] ?? ''"
                :placeholder="__('projects.search_placeholder')"
                leading
            />
        </div>

        <div class="flex flex-wrap gap-2">
            <label for="status" class="sr-only">{{ __('common.status') }}</label>
            <x-ui.select id="status" name="status">
                <option value="">{{ __('projects.all_status') }}</option>
                @foreach (App\Enums\ProjectStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </x-ui.select>

            <x-ui.button type="submit" variant="secondary">{{ __('common.search') }}</x-ui.button>

            @if (($filters['search'] ?? null) || ($filters['status'] ?? null))
                <x-ui.button :href="route('projects.index')" variant="ghost">{{ __('common.reset_filter') }}</x-ui.button>
            @endif
        </div>
    </form>

    @if ($projects->isEmpty())
        <x-ui.empty-state
            class="mt-6"
            :title="(($filters['search'] ?? null) || ($filters['status'] ?? null)) ? __('projects.empty_filtered_title') : __('projects.empty_title')"
            :body="(($filters['search'] ?? null) || ($filters['status'] ?? null)) ? __('projects.empty_filtered_body') : __('projects.empty_body')"
        >
            @can('create', App\Models\Project::class)
                <x-ui.button :href="route('projects.create')">{{ __('projects.create') }}</x-ui.button>
            @endcan
        </x-ui.empty-state>
    @else
        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($projects as $project)
                <article class="flex flex-col rounded-card border border-line bg-surface p-5 shadow-card transition-colors duration-150 hover:border-brand/30">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('projects.show', $project) }}" class="text-base font-semibold tracking-tight text-content transition-colors duration-150 hover:text-brand-text">
                                {{ $project->title }}
                            </a>
                            <div class="mt-1 flex min-w-0 items-center gap-1.5 text-xs text-content-muted">
                                <x-ui.avatar :user="$project->owner" size="sm" />
                                <span class="truncate">{{ $project->owner->name }}</span>
                            </div>
                        </div>

                        <x-ui.status-badge :status="$project->status" class="shrink-0" />
                    </div>

                    @if ($project->description)
                        <p class="mt-3 line-clamp-2 text-sm text-content-muted">{{ $project->description }}</p>
                    @endif

                    <x-ui.progress-bar :value="$project->progress" :label="__('projects.progress')" class="mt-4" />

                    @if ($project->start_date || $project->end_date)
                        <p class="mt-3 flex flex-wrap items-center gap-1.5 text-xs text-content-muted">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-3.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                            <span>{{ $project->start_date?->translatedFormat('d M Y') ?? __('common.not_set') }}</span>
                            <span aria-hidden="true">–</span>
                            <span>{{ $project->end_date?->translatedFormat('d M Y') ?? __('common.not_set') }}</span>
                        </p>
                    @endif

                    <div class="mt-4 flex items-center justify-between gap-3 border-t border-line pt-4">
                        <div class="text-xs text-content-muted">
                            <span class="tabular-nums">{{ __('projects.task_count', ['count' => $project->tasks_count]) }}</span>
                            <span aria-hidden="true"> · </span>
                            <span class="tabular-nums">{{ __('projects.member_count', ['count' => $project->members->count()]) }}</span>
                        </div>

                        <x-ui.avatar-stack :members="$project->members" :limit="3" />
                    </div>

                    <div class="mt-4 flex items-center gap-1">
                        <x-ui.button :href="route('projects.show', $project)" variant="ghost" size="sm">
                            {{ __('projects.detail') }}
                        </x-ui.button>

                        @can('update', $project)
                            <x-ui.button :href="route('projects.edit', $project)" variant="ghost" size="sm">
                                {{ __('common.edit') }}
                            </x-ui.button>
                        @endcan

                        @can('delete', $project)
                            <x-ui.confirm-dialog
                                :action="route('projects.destroy', $project)"
                                :title="__('projects.delete_title')"
                                :body="__('projects.delete_body', ['title' => $project->title])"
                                :confirm="__('projects.delete_confirm')"
                                class="ml-auto rounded-control px-3 py-2 text-xs font-medium text-danger hover:bg-danger/5"
                            >
                                {{ __('common.delete') }}
                            </x-ui.confirm-dialog>
                        @endcan
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $projects->links() }}
        </div>
    @endif
@endsection
