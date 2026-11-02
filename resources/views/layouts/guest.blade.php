<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#3347C8">

    <title>@yield('title', __('auth.login')) · {{ __('common.app_name') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/taskflow-icon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    @include('layouts.partials.theme-script')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas text-content">
    <x-ui.flash />

    <div class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(440px,620px)]">
        <aside class="hidden flex-col justify-between border-r border-line bg-surface p-10 lg:flex xl:p-14">
            <x-brand.logo :href="route('login')" tagline size="md" />

            <div class="max-w-md">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-text">{{ __('auth.brand_eyebrow') }}</p>
                <h1 class="mt-3 text-3xl font-bold leading-tight tracking-tight">{{ __('auth.brand_headline') }}</h1>
                <p class="mt-4 text-sm leading-6 text-content-muted">{{ __('auth.brand_body') }}</p>

                <ul class="mt-8 space-y-4">
                    @foreach ([
                        ['icon' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h4.5A2.25 2.25 0 0 1 12.75 6v4.5A2.25 2.25 0 0 1 10.5 12.75H6A2.25 2.25 0 0 1 3.75 10.5V6ZM3.75 16.5A2.25 2.25 0 0 1 6 14.25h4.5a2.25 2.25 0 0 1 2.25 2.25V19.5A2.25 2.25 0 0 1 10.5 21.75H6a2.25 2.25 0 0 1-2.25-2.25V16.5ZM14.25 6A2.25 2.25 0 0 1 16.5 3.75H18A2.25 2.25 0 0 1 20.25 6v4.5A2.25 2.25 0 0 1 18 12.75h-1.5a2.25 2.25 0 0 1-2.25-2.25V6ZM14.25 16.5a2.25 2.25 0 0 1 2.25-2.25h1.5a2.25 2.25 0 0 1 2.25 2.25v3a2.25 2.25 0 0 1-2.25 2.25H18a2.25 2.25 0 0 1-2.25-2.25v-3Z', 'label' => __('auth.brand_feature_projects')],
                        ['icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'label' => __('auth.brand_feature_tasks')],
                        ['icon' => 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z', 'label' => __('auth.brand_feature_team')],
                    ] as $feature)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-control bg-brand-soft text-brand-text">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}" />
                                </svg>
                            </span>
                            <span class="pt-1 text-sm font-medium text-content">{{ $feature['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="text-xs font-medium text-content-muted">{{ now()->year }} {{ __('auth.brand_footer') }}</p>
        </aside>

        <main class="flex min-h-screen items-center justify-center p-4 sm:p-8">
            <div class="w-full max-w-[420px]">
                <div class="mb-6 flex justify-center lg:hidden">
                    <x-brand.logo :href="route('login')" tagline size="md" />
                </div>

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
