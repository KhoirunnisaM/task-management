@extends('layouts.app')

@section('title', __('projects.create_title'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <x-ui.back-button :fallback="route('projects.index')" class="mb-4" />
            <h1 class="text-2xl font-bold tracking-tight">{{ __('projects.create_title') }}</h1>
            <p class="mt-1 text-sm text-content-muted">{{ __('projects.create_subtitle') }}</p>
        </div>

        @include('projects.partials.form', [
            'project' => $project,
            'statuses' => $statuses,
            'action' => route('projects.store'),
            'method' => 'POST',
        ])
    </div>
@endsection
