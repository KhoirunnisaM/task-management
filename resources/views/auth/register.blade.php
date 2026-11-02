@extends('layouts.guest')

@section('title', __('auth.register'))

@section('content')
    <x-ui.back-button :fallback="route('login')" class="mb-4" />

    <div class="rounded-card border border-line bg-surface p-6 shadow-card sm:p-8">
        <div class="flex items-start gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-control bg-brand-soft text-brand-text">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>
            </span>

            <div class="min-w-0">
                <h1 class="text-xl font-semibold tracking-tight">{{ __('auth.register_title') }}</h1>
                <p class="mt-1 text-sm text-content-muted">{{ __('auth.register_subtitle') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
            @csrf

            <x-ui.form-field :label="__('auth.name')" name="name">
                <x-ui.input
                    name="name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                />
            </x-ui.form-field>

            <x-ui.form-field :label="__('auth.email')" name="email">
                <x-ui.input
                    name="email"
                    type="email"
                    required
                    autocomplete="username"
                />
            </x-ui.form-field>

            <x-ui.form-field :label="__('auth.password')" name="password" :hint="__('auth.password_hint')">
                <x-ui.input
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                />
            </x-ui.form-field>

            <x-ui.form-field :label="__('auth.password_confirmation')" name="password_confirmation">
                <x-ui.input
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                />
            </x-ui.form-field>

            <x-ui.button type="submit" size="lg" class="w-full">
                {{ __('auth.register') }}
            </x-ui.button>
        </form>

        <p class="mt-4 text-center text-sm text-content-muted">
            {{ __('auth.have_account') }}
            <a href="{{ route('login') }}" class="font-medium text-brand-text transition-colors duration-150 hover:text-brand">
                {{ __('auth.login') }}
            </a>
        </p>
    </div>
@endsection
