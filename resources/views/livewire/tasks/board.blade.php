@php
    $dots = [
        'todo' => 'bg-status-todo',
        'in_progress' => 'bg-status-in-progress',
        'in_review' => 'bg-status-in-review',
        'completed' => 'bg-status-completed',
    ];

    $edges = [
        'todo' => 'border-t-status-todo',
        'in_progress' => 'border-t-status-in-progress',
        'in_review' => 'border-t-status-in-review',
        'completed' => 'border-t-status-completed',
    ];
@endphp

<div>
    <div class="rounded-card border border-line bg-surface p-3">
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <div class="relative">
                <label for="board-search" class="sr-only">{{ __('common.search') }}</label>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="pointer-events-none absolute left-3 top-1/2 z-10 size-4 -translate-y-1/2 text-content-muted" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <x-ui.input
                    id="board-search"
                    name="search"
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    :placeholder="__('tasks.search_placeholder')"
                    leading
                />
            </div>

            <div>
                <label for="board-assignee" class="sr-only">{{ __('tasks.assignee') }}</label>
                <x-ui.select
                    id="board-assignee"
                    wire:model.live="assignee"
                >
                    <option value="">{{ __('tasks.all_assignee') }}</option>
                    <option value="unassigned">{{ __('tasks.unassigned') }}</option>
                    @foreach ($this->members as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div>
                <label for="board-priority" class="sr-only">{{ __('tasks.priority_label') }}</label>
                <x-ui.select
                    id="board-priority"
                    wire:model.live="priority"
                >
                    <option value="">{{ __('tasks.all_priority') }}</option>
                    @foreach (App\Enums\TaskPriority::cases() as $option)
                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div>
                <label for="board-due" class="sr-only">{{ __('tasks.due_date') }}</label>
                <x-ui.select
                    id="board-due"
                    wire:model.live="due"
                >
                    <option value="">{{ __('tasks.all_due') }}</option>
                    <option value="overdue">{{ __('tasks.due_overdue') }}</option>
                    <option value="today">{{ __('tasks.due_today') }}</option>
                    <option value="week">{{ __('tasks.due_week') }}</option>
                    <option value="none">{{ __('tasks.due_none') }}</option>
                </x-ui.select>
            </div>
        </div>

        <div class="mt-2 flex flex-wrap items-center gap-2">
            @if ($this->hasFilters)
                <x-ui.button type="button" variant="ghost" wire:click="resetFilters">
                    {{ __('common.reset_filter') }}
                </x-ui.button>
            @endif

            <p class="text-xs text-content-muted">{{ __('tasks.board_hint') }}</p>

            <span wire:loading wire:target="search,assignee,priority,due,resetFilters,moveTask" class="text-xs text-content-muted">
                {{ __('tasks.board_loading') }}
            </span>
        </div>
    </div>

    <div class="-mx-4 mt-6 overflow-x-auto px-4 pb-4 sm:-mx-6 sm:px-6 lg:mx-0 lg:overflow-visible lg:px-0">
        <div class="flex min-w-max snap-x snap-mandatory gap-4 lg:grid lg:min-w-0 lg:grid-cols-4">
            @foreach (App\Enums\TaskStatus::cases() as $status)
                @php($group = $this->columns->get($status->value, collect()))

                <section
                    wire:key="board-column-{{ $status->value }}"
                    class="w-[304px] max-w-[calc(100vw-2rem)] shrink-0 snap-start rounded-card border border-t-[3px] border-line bg-canvas p-2 lg:w-auto lg:max-w-none {{ $edges[$status->value] }}"
                >
                    <header class="flex items-center gap-2 px-1 py-1.5">
                        <span class="size-2 rounded-full {{ $dots[$status->value] }}" aria-hidden="true"></span>
                        <h2 class="text-sm font-semibold">{{ $status->label() }}</h2>
                        <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-surface px-1.5 py-0.5 text-[11px] font-semibold tabular-nums text-content-muted">{{ $group->count() }}</span>

                        @can('create', [App\Models\Task::class, $project])
                            <a
                                href="{{ route('projects.tasks.create', $project) }}"
                                class="ml-auto rounded-control p-1 text-content-muted transition-colors duration-150 hover:bg-surface hover:text-content"
                                aria-label="{{ __('tasks.create') }}"
                                title="{{ __('tasks.create') }}"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </a>
                        @endcan
                    </header>

                    <div
                        wire:key="board-list-{{ $status->value }}"
                        data-task-list
                        data-status="{{ $status->value }}"
                        x-data="kanbanColumn({ status: '{{ $status->value }}' })"
                        class="mt-2 min-h-[96px] space-y-2"
                    >
                        @forelse ($group as $task)
                            @php($canMove = auth()->user()->can('updateStatus', $task))

                            <x-task.card
                                :task="$task"
                                :project="$project"
                                wire:key="board-task-{{ $task->id }}"
                                data-task-id="{{ $task->id }}"
                                data-draggable="{{ $canMove ? 'true' : 'false' }}"
                                class="{{ $canMove ? 'cursor-grab active:cursor-grabbing focus-within:ring-2 focus-within:ring-brand' : '' }}"
                            >
                                <x-slot:menu>
                                    @if ($canMove)
                                        <label for="board-move-{{ $task->id }}" class="sr-only">{{ __('tasks.move_to') }}</label>
                                        <select
                                            id="board-move-{{ $task->id }}"
                                            wire:change="moveTaskToStatus({{ $task->id }}, $event.target.value)"
                                            class="h-7 max-w-[104px] rounded-control border border-line bg-surface px-1.5 text-[10px] text-content-muted"
                                            title="{{ __('tasks.move_to') }}"
                                        >
                                            <option value="">{{ __('tasks.move_to') }}</option>
                                            @foreach (App\Enums\TaskStatus::cases() as $option)
                                                @if ($option !== $task->status)
                                                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    @endif
                                </x-slot:menu>
                            </x-task.card>
                        @empty
                            <p class="rounded-control border border-dashed border-line px-3 py-4 text-center text-xs text-content-muted">
                                {{ __('tasks.board_empty_column') }}
                            </p>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</div>
