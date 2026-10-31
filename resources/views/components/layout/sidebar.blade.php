@php
    $navigation = [
        [
            'label' => __('projects.title'),
            'href' => route('projects.index'),
            'active' => request()->routeIs('projects.*'),
            'icon' => 'M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-19.5 0A2.25 2.25 0 0 0 4.5 15h15a2.25 2.25 0 0 0 2.25-2.25m-19.5 0v-6A2.25 2.25 0 0 1 4.5 4.5h15a2.25 2.25 0 0 1 2.25 2.25v6',
        ],
    ];
@endphp

<div
    x-cloak
    x-show="sidebarOpen"
    x-transition.opacity.duration.150ms
    class="fixed inset-0 z-30 bg-content/30 lg:hidden"
    @click="sidebarOpen = false"
></div>

<aside
    x-cloak
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-40 flex w-[248px] flex-col border-r border-line bg-surface transition-transform duration-150 lg:translate-x-0"
    aria-label="{{ __('common.main_navigation') }}"
    @keydown.escape.window="sidebarOpen = false"
>
    <div class="flex h-14 shrink-0 items-center gap-2.5 border-b border-line px-5">
        <span class="flex size-8 items-center justify-center rounded-control bg-brand text-sm font-bold text-white" aria-hidden="true">TM</span>
        <span class="text-sm font-semibold tracking-tight">{{ __('common.app_name') }}</span>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        @foreach ($navigation as $item)
            <a
                href="{{ $item['href'] }}"
                @class([
                    'flex items-center gap-2.5 rounded-control px-3 py-2 text-sm font-medium transition-colors duration-150',
                    'bg-brand-soft text-brand-text' => $item['active'],
                    'text-content-muted hover:bg-canvas hover:text-content' => ! $item['active'],
                ])
                @if ($item['active']) aria-current="page" @endif
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                </svg>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="shrink-0 border-t border-line p-3">
        <div class="flex items-center gap-2.5 rounded-control px-2 py-2">
            <x-ui.avatar :user="auth()->user()" size="md" />

            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-content-muted">{{ auth()->user()->role->label() }}</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="rounded-control p-2 text-content-muted transition-colors duration-150 hover:bg-canvas hover:text-content"
                    aria-label="{{ __('auth.logout') }}"
                    title="{{ __('auth.logout') }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
