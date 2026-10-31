@extends('layouts.app')

@section('title', __('tasks.board_title'))

@section('content')
    <div class="mb-4">
        <a href="{{ route('projects.show', $project) }}" class="text-sm font-medium text-content-muted transition-colors duration-150 hover:text-content">
            {{ $project->title }}
        </a>
    </div>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ __('tasks.board_title') }}</h1>
            <p class="mt-1 text-sm text-content-muted">{{ $project->title }}</p>
        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <x-ui.button :href="route('projects.tasks.index', $project)" variant="secondary">
                {{ __('tasks.list') }}
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

    <div class="mt-6">
        <livewire:tasks.board :project="$project" />
    </div>
@endsection
