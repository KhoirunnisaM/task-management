@php
    $navigation = [
        [
            'label' => __('dashboard.title'),
            'href' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
            'icon' => 'M2.25 12 11.204 3.045a1.125 1.125 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75',
        ],
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
    <div class="flex h-14 shrink-0 items-center border-b border-line px-4">
        <x-brand.logo :href="route('dashboard')" size="sm" class="w-full" />
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4">
        <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-content-muted">
            {{ __('common.workspace') }}
        </p>

        <div class="space-y-1">
            @foreach ($navigation as $item)
                <x-layout.nav-item
                    :href="$item['href']"
                    :label="$item['label']"
                    :icon="$item['icon']"
                    :active="$item['active']"
                />
            @endforeach
        </div>

        @if (auth()->user()->isAdmin())
            <p class="mt-6 px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-content-muted">
                {{ __('common.user_management') }}
            </p>

            <div class="space-y-1">
                <x-layout.nav-item
                    :href="route('admin.members.index')"
                    :label="__('common.all_members')"
                    icon="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"
                    :active="request()->routeIs('admin.members.*')"
                />
            </div>
        @endif

    </nav>

    <div class="shrink-0 border-t border-line p-3">
        <div class="flex items-center gap-2.5 rounded-control bg-canvas px-2.5 py-2">
            <x-ui.avatar :user="auth()->user()" size="md" />

            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-content-muted">{{ auth()->user()->role->label() }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button
                type="submit"
                class="flex w-full items-center gap-2.5 rounded-control px-3 py-2 text-sm font-medium text-content-muted transition-colors duration-150 hover:bg-danger/5 hover:text-danger"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
                {{ __('auth.logout') }}
            </button>
        </form>
    </div>
</aside>
