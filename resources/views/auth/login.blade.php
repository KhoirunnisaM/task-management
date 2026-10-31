@extends('layouts.guest')

@section('title', __('auth.login'))

@section('content')
    <div class="rounded-card border border-line bg-surface p-6 shadow-card sm:p-8">
        <h1 class="text-xl font-semibold tracking-tight">{{ __('auth.login_title') }}</h1>
        <p class="mt-1 text-sm text-content-muted">{{ __('auth.login_subtitle') }}</p>

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf

            <x-ui.form-field :label="__('auth.email')" name="email">
                <x-ui.input
                    name="email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                />
            </x-ui.form-field>

            <x-ui.form-field :label="__('auth.password')" name="password">
                <x-ui.input
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                />
            </x-ui.form-field>

            <label for="remember" class="flex items-center gap-2 text-sm text-content-muted">
                <input id="remember" name="remember" type="checkbox" class="size-4 rounded border-line text-brand focus:ring-brand">
                {{ __('auth.remember') }}
            </label>

            <x-ui.button type="submit" size="lg" class="w-full">
                {{ __('auth.login') }}
            </x-ui.button>
        </form>
    </div>

    <div class="mt-4 rounded-card border border-line bg-surface/60 p-4 text-xs text-content-muted">
        <p class="font-medium text-content">{{ __('auth.demo_title') }}</p>
        <p class="mt-1">{{ __('auth.demo_hint', ['password' => 'password']) }}</p>
        <ul class="mt-2 space-y-1 font-mono">
            <li>admin@taskmanager.test</li>
            <li>rina@taskmanager.test</li>
            <li>sari@taskmanager.test</li>
        </ul>
    </div>
@endsection
