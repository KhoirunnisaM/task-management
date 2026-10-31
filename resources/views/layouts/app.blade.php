<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('common.app_name')) · {{ __('common.app_name') }}</title>

    @include('layouts.partials.theme-script')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    x-data="{
        sidebarOpen: false,
        dark: document.documentElement.classList.contains('dark'),
    }"
    class="min-h-screen bg-canvas text-content"
>
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-control focus:bg-brand focus:px-4 focus:py-2 focus:text-sm focus:text-white">
        {{ __('common.skip_to_content') }}
    </a>

    <x-layout.sidebar />

    <div class="lg:pl-[248px]">
        <x-layout.topbar />

        <main id="main-content" class="w-full max-w-[1440px] px-4 py-6 sm:px-6 lg:py-8">
            <x-ui.flash />

            @yield('content')
        </main>
    </div>
</body>
</html>
