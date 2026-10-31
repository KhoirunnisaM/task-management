@extends('layouts.app')

@section('title', __('tasks.edit_title'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="text-sm font-medium text-content-muted hover:text-content">
                {{ __('common.back') }}
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight">{{ __('tasks.edit_title') }}</h1>
            <p class="mt-1 text-sm text-content-muted">{{ $project->title }} · TSK-{{ $task->id }}</p>
        </div>

        <div class="mb-5 rounded-card border border-line bg-surface p-5">
            <h2 class="mb-3 text-sm font-semibold">{{ __('tasks.status_field') }}</h2>
            @include('tasks.partials.status-form', [
                'project' => $project,
                'task' => $task,
                'statuses' => $statuses,
            ])
        </div>

        @include('tasks.partials.form', [
            'project' => $project,
            'task' => $task,
            'priorities' => $priorities,
            'action' => route('projects.tasks.update', [$project, $task]),
            'method' => 'PUT',
        ])
    </div>
@endsection
