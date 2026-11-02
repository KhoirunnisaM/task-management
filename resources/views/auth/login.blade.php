@extends('layouts.guest')

@section('title', __('auth.login'))

@section('content')
    <div class="rounded-card border border-line bg-surface p-6 shadow-card sm:p-8">
        <div class="flex items-start gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-control bg-brand-soft text-brand-text">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
            </span>

            <div class="min-w-0">
                <h1 class="text-xl font-semibold tracking-tight">{{ __('auth.login_title') }}</h1>
                <p class="mt-1 text-sm text-content-muted">{{ __('auth.login_subtitle') }}</p>
            </div>
        </div>

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

        <p class="mt-4 text-center text-sm text-content-muted">
            {{ __('auth.no_account') }}
            <a href="{{ route('register') }}" class="font-medium text-brand-text transition-colors duration-150 hover:text-brand">
                {{ __('auth.register_link') }}
            </a>
        </p>
    </div>

    
@endsection
