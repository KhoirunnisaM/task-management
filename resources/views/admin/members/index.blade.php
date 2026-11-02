@extends('layouts.app')

@section('title', __('admin_members.index_title'))

@section('content')
    <x-ui.back-button :fallback="route('dashboard')" class="mb-4" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ __('admin_members.index_title') }}</h1>
            <p class="mt-1 text-sm text-content-muted">{{ __('admin_members.index_subtitle') }}</p>
        </div>

        <span class="inline-flex w-fit items-center rounded-full bg-brand-soft px-3 py-1 text-xs font-medium text-brand-text">
            {{ trans_choice('admin_members.total_members', $users->total(), ['count' => $users->total()]) }}
        </span>
    </div>

    <form method="GET" action="{{ route('admin.members.index') }}" class="mt-6 rounded-card border border-line bg-surface p-3">
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_180px_190px_auto]">
            <div class="relative">
                <label for="search" class="sr-only">{{ __('common.search') }}</label>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-content-muted" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <x-ui.input
                    name="search"
                    type="search"
                    :value="$filters['search'] ?? ''"
                    :placeholder="__('admin_members.search_placeholder')"
                    leading
                />
            </div>

            <div>
                <label for="role" class="sr-only">{{ __('admin_members.filter_role') }}</label>
                <x-ui.select id="role" name="role">
                    <option value="">{{ __('admin_members.all_roles') }}</option>
                    @foreach (App\Enums\UserRole::cases() as $role)
                        <option value="{{ $role->value }}" @selected(($filters['role'] ?? '') === $role->value)>
                            {{ $role->label() }}
                        </option>
                    @endforeach
                </x-ui.select>
            </div>

            <div>
                <label for="sort" class="sr-only">{{ __('admin_members.sort') }}</label>
                <x-ui.select id="sort" name="sort">
                    @foreach ([
                        'joined_desc' => __('admin_members.sort_joined_desc'),
                        'joined_asc' => __('admin_members.sort_joined_asc'),
                        'name_asc' => __('admin_members.sort_name_asc'),
                        'name_desc' => __('admin_members.sort_name_desc'),
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['sort'] ?? 'joined_desc') === $value)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-ui.button type="submit" variant="secondary">{{ __('common.search') }}</x-ui.button>

                @if (($filters['search'] ?? null) || ($filters['role'] ?? null) || ($filters['sort'] ?? null))
                    <x-ui.button :href="route('admin.members.index')" variant="ghost">{{ __('common.reset_filter') }}</x-ui.button>
                @endif
            </div>
        </div>
    </form>

    @if ($users->isEmpty())
        <x-ui.empty-state
            class="mt-6"
            :title="(($filters['search'] ?? null) || ($filters['role'] ?? null)) ? __('admin_members.empty_filtered_title') : __('admin_members.empty_title')"
            :body="(($filters['search'] ?? null) || ($filters['role'] ?? null)) ? __('admin_members.empty_filtered_body') : __('admin_members.empty_body')"
        >
            @if (($filters['search'] ?? null) || ($filters['role'] ?? null))
                <x-ui.button :href="route('admin.members.index')" variant="secondary">{{ __('common.reset_filter') }}</x-ui.button>
            @endif
        </x-ui.empty-state>
    @else
        <div class="mt-6 overflow-hidden rounded-card border border-line bg-surface">
            <div class="hidden grid-cols-[56px_minmax(0,1.8fr)_120px_150px_100px] items-center gap-3 border-b border-line bg-canvas px-4 py-2.5 text-xs font-medium text-content-muted sm:grid">
                <span>{{ __('admin_members.number') }}</span>
                <span>{{ __('admin_members.member') }}</span>
                <span>{{ __('common.role') }}</span>
                <span>{{ __('admin_members.joined_at') }}</span>
                <span class="text-right">{{ __('common.actions') }}</span>
            </div>

            @foreach ($users as $user)
                <div class="grid gap-3 border-b border-line p-4 last:border-b-0 sm:grid-cols-[56px_minmax(0,1.8fr)_120px_150px_100px] sm:items-center">
                    <span class="text-xs tabular-nums text-content-muted">
                        {{ $users->firstItem() + $loop->index }}
                    </span>

                    <div class="flex min-w-0 items-center gap-3">
                        <x-ui.avatar :user="$user" size="md" />

                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">{{ $user->name }}</p>
                            <p class="truncate text-xs text-content-muted">{{ $user->email }}</p>
                        </div>
                    </div>

                    <div>
                        <span @class([
                            'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                            'bg-brand-soft text-brand-text' => $user->role === App\Enums\UserRole::Admin,
                            'bg-status-in-progress-soft text-status-in-progress' => $user->role === App\Enums\UserRole::Manager,
                            'bg-status-todo-soft text-status-todo' => $user->role === App\Enums\UserRole::Member,
                        ])>
                            {{ $user->role->label() }}
                        </span>
                    </div>

                    <p class="text-xs text-content-muted">
                        {{ $user->created_at?->translatedFormat('d M Y') ?? __('common.not_set') }}
                    </p>

                    <div class="sm:text-right">
                        <x-ui.button :href="route('admin.members.show', $user)" variant="secondary" size="sm">
                            {{ __('admin_members.detail') }}
                        </x-ui.button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    @endif
@endsection
