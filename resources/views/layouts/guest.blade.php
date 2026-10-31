<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('auth.login')) · {{ __('common.app_name') }}</title>

    @include('layouts.partials.theme-script')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-canvas p-4 text-content">
    <x-ui.flash />

    <main class="w-full max-w-[420px]">
        <div class="mb-6 flex items-center justify-center gap-2.5">
            <span class="flex size-9 items-center justify-center rounded-control bg-brand text-sm font-bold text-white" aria-hidden="true">TM</span>
            <span class="text-base font-semibold tracking-tight">{{ __('common.app_name') }}</span>
        </div>

        @yield('content')
    </main>
</body>
</html>
