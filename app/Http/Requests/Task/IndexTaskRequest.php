<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', [Task::class, $this->route('project')]);
    }

    public function rules(): array
    {
        $project = $this->route('project');
        $assignees = $project->members()
            ->pluck('users.id')
            ->map(fn ($id) => (string) $id)
            ->all();

        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
            'assignee' => ['nullable', 'string', Rule::in(array_merge(['unassigned'], $assignees))],
            'due' => ['nullable', Rule::in(['overdue', 'today', 'week', 'none'])],
        ];
    }
}
