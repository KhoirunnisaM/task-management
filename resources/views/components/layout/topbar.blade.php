<header class="sticky top-0 z-20 flex h-14 items-center gap-2 border-b border-line bg-surface/95 px-4 backdrop-blur sm:gap-3 sm:px-6">
    <button
        type="button"
        class="rounded-control p-2 text-content-muted transition-colors duration-150 hover:bg-canvas hover:text-content lg:hidden"
        @click="sidebarOpen = true"
        aria-label="{{ __('common.open_navigation') }}"
        title="{{ __('common.open_navigation') }}"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-semibold text-content">@yield('title', __('common.app_name'))</p>
        <p class="hidden truncate text-[11px] text-content-muted sm:block">{{ auth()->user()->name }} · {{ auth()->user()->role->label() }}</p>
    </div>

    <button
        type="button"
        class="rounded-control p-2 text-content-muted transition-colors duration-150 hover:bg-canvas hover:text-content"
        @click="dark = ! dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('theme', dark ? 'dark' : 'light')"
        aria-label="{{ __('common.theme_dark') }}"
        title="{{ __('common.theme_dark') }}"
        x-bind:aria-label="dark ? '{{ __('common.theme_light') }}' : '{{ __('common.theme_dark') }}'"
        x-bind:title="dark ? '{{ __('common.theme_light') }}' : '{{ __('common.theme_dark') }}'"
    >
        <svg x-show="! dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
        </svg>
        <svg x-show="dark" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
        </svg>
    </button>

    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
        <button
            type="button"
            class="flex items-center gap-2 rounded-control border border-transparent p-1 pr-2 transition-colors duration-150 hover:border-line hover:bg-canvas"
            @click="open = ! open"
            aria-haspopup="menu"
            :aria-expanded="open"
        >
            <x-ui.avatar :user="auth()->user()" size="md" />

            <span class="hidden max-w-[140px] truncate text-sm font-medium text-content sm:block">{{ auth()->user()->name }}</span>

            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4 text-content-muted" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        <div
            x-cloak
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="absolute right-0 mt-2 w-64 rounded-overlay border border-line bg-surface p-2 shadow-overlay"
            role="menu"
        >
            <div class="flex items-center gap-3 rounded-control bg-canvas p-3">
                <x-ui.avatar :user="auth()->user()" size="lg" />

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-content-muted">{{ auth()->user()->email }}</p>
                    <p class="mt-0.5 text-[11px] font-medium text-brand-text">{{ auth()->user()->role->label() }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button
                    type="submit"
                    role="menuitem"
                    class="flex w-full items-center gap-2.5 rounded-control px-3 py-2 text-sm font-medium text-content-muted transition-colors duration-150 hover:bg-danger/5 hover:text-danger"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    {{ __('auth.logout') }}
                </button>
            </form>
        </div>
    </div>
</header>
