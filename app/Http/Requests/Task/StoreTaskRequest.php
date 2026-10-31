<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [Task::class, $this->route('project')]);
    }

    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('project_user', 'user_id')->where('project_id', $project->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_to.exists' => __('tasks.assignee_invalid'),
            'due_date.after_or_equal' => __('tasks.due_date_invalid'),
        ];
    }
}
