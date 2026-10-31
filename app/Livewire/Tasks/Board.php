<?php

namespace App\Livewire\Tasks;

use App\Actions\Tasks\UpdateTaskStatusAction;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskQueryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Board extends Component
{
    #[Locked]
    public Project $project;

    public string $search = '';

    public string $assignee = '';

    public string $priority = '';

    public string $due = '';

    public function mount(Project $project): void
    {
        $this->authorize('viewAny', [Task::class, $project]);

        $this->project = $project->load('members:id,name');
    }

    #[Computed]
    public function columns(): Collection
    {
        $this->authorize('viewAny', [Task::class, $this->project]);

        return app(TaskQueryService::class)->forProject($this->project, $this->filters());
    }

    #[Computed]
    public function members(): Collection
    {
        return $this->project->members()
            ->orderBy('name')
            ->get(['users.id', 'users.name']);
    }

    #[Computed]
    public function hasFilters(): bool
    {
        return collect($this->filters())->isNotEmpty();
    }

    public function updated(string $property): void
    {
        if (! in_array($property, ['search', 'assignee', 'priority', 'due'], true)) {
            return;
        }

        Validator::make($this->filtersForValidation(), $this->filterRules())->validate();

        unset($this->columns);
        unset($this->hasFilters);
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'assignee', 'priority', 'due');

        unset($this->columns);
        unset($this->hasFilters);
    }

    public function moveTask(int $taskId, string $status, int $position, UpdateTaskStatusAction $action): void
    {
        $this->authorize('viewAny', [Task::class, $this->project]);

        $validated = Validator::make([
            'status' => $status,
            'position' => $position,
        ], [
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'position' => ['required', 'integer', 'min:0'],
        ])->validate();

        $task = $this->project->tasks()->findOrFail($taskId);

        $this->authorize('updateStatus', $task);

        $action->execute($task, TaskStatus::from($validated['status']), (int) $validated['position']);

        unset($this->columns);
    }

    public function moveTaskToStatus(int $taskId, string $status, UpdateTaskStatusAction $action): void
    {
        $this->authorize('viewAny', [Task::class, $this->project]);

        $validated = Validator::make([
            'status' => $status,
        ], [
            'status' => ['required', Rule::enum(TaskStatus::class)],
        ])->validate();

        $task = $this->project->tasks()->findOrFail($taskId);

        $this->authorize('updateStatus', $task);

        $action->execute($task, TaskStatus::from($validated['status']));

        unset($this->columns);
    }

    public function render(): View
    {
        return view('livewire.tasks.board');
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(): array
    {
        return array_filter([
            'search' => $this->search,
            'assignee' => $this->assignee,
            'priority' => $this->priority,
            'due' => $this->due,
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersForValidation(): array
    {
        return [
            'search' => $this->search,
            'assignee' => $this->assignee,
            'priority' => $this->priority,
            'due' => $this->due,
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function filterRules(): array
    {
        $members = $this->project->members()
            ->pluck('users.id')
            ->map(fn ($id) => (string) $id)
            ->all();

        return [
            'search' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
            'assignee' => ['nullable', 'string', Rule::in(array_merge(['unassigned'], $members))],
            'due' => ['nullable', Rule::in(['overdue', 'today', 'week', 'none'])],
        ];
    }
}
