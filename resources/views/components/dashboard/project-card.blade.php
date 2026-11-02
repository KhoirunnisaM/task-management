@props(['project'])

<article class="flex flex-col rounded-card border border-line bg-surface p-4 shadow-card">
    <div class="flex items-start justify-between gap-2">
        <a href="{{ route('projects.show', $project) }}" class="min-w-0 text-sm font-semibold tracking-tight text-content transition-colors duration-150 hover:text-brand-text">
            {{ $project->title }}
        </a>

        <x-ui.status-badge :status="$project->status" class="shrink-0" />
    </div>

    <div class="mt-1 flex min-w-0 items-center gap-1.5 text-xs text-content-muted">
        <x-ui.avatar :user="$project->owner" size="sm" />
        <span class="truncate">{{ $project->owner?->name ?? __('common.not_set') }}</span>
    </div>

    @if ($project->end_date)
        <p class="mt-2 flex items-center gap-1.5 text-xs text-content-muted">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-3.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
            {{ $project->end_date->translatedFormat('d M Y') }}
        </p>
    @endif

    <x-ui.progress-bar :value="$project->progress" :label="__('projects.progress')" class="mt-3" />

    <div class="mt-3 flex items-center justify-between gap-2 text-xs text-content-muted">
        <span class="tabular-nums">{{ $project->completed_tasks_count }}/{{ $project->tasks_count }}</span>
        <x-ui.avatar-stack :members="$project->members" :limit="3" />
    </div>
</article>
