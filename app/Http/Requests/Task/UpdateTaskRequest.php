<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('task'));
    }

    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'due_date' => ['nullable', 'date'],
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
        ];
    }
}
