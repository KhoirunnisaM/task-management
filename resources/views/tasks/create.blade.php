@extends('layouts.app')

@section('title', __('tasks.create_title'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('projects.tasks.index', $project) }}" class="text-sm font-medium text-content-muted hover:text-content">
                {{ __('tasks.back_to_tasks') }}
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight">{{ __('tasks.create_title') }}</h1>
            <p class="mt-1 text-sm text-content-muted">{{ $project->title }}</p>
        </div>

        @include('tasks.partials.form', [
            'project' => $project,
            'task' => $task,
            'priorities' => $priorities,
            'action' => route('projects.tasks.store', $project),
            'method' => 'POST',
        ])
    </div>
@endsection
