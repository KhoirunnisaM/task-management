@extends('layouts.app')

@section('title', __('projects.edit_title'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('projects.show', $project) }}" class="text-sm font-medium text-content-muted hover:text-content">
                {{ __('common.back') }}
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight">{{ __('projects.edit_title') }}</h1>
            <p class="mt-1 text-sm text-content-muted">{{ $project->title }}</p>
        </div>

        @include('projects.partials.form', [
            'project' => $project,
            'statuses' => $statuses,
            'action' => route('projects.update', $project),
            'method' => 'PUT',
        ])
    </div>
@endsection
