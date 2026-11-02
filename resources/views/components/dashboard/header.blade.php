@props(['role'])

<div {{ $attributes->class('flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between') }}>
    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2.5">
            <h1 class="text-2xl font-bold tracking-tight">{{ __('dashboard.greeting', ['name' => auth()->user()->name]) }}</h1>
            <span class="rounded-full bg-brand-soft px-2.5 py-0.5 text-xs font-medium text-brand-text">
                {{ $role->label() }}
            </span>
        </div>
        <p class="mt-1 text-sm text-content-muted">{{ $role->subtitle() }}</p>
    </div>

    <div class="shrink-0">
        @if ($role === App\Enums\DashboardRole::Admin)
            @can('create', App\Models\Project::class)
                <x-ui.button :href="route('projects.create')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('projects.create') }}
                </x-ui.button>
            @endcan
        @elseif ($role === App\Enums\DashboardRole::Manager)
            <x-ui.button :href="route('projects.index')" variant="secondary">
                {{ __('dashboard.actions.view_projects') }}
            </x-ui.button>
        @else
            <x-ui.button :href="route('projects.index')" variant="secondary">
                {{ __('dashboard.actions.my_projects') }}
            </x-ui.button>
        @endif
    </div>
</div>
