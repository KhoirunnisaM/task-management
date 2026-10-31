<?php

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class CreateTaskAction
{
    public function execute(Project $project, array $data, User $creator): Task
    {
        return DB::transaction(function () use ($project, $data, $creator) {
            $maxPosition = $project->tasks()
                ->where('status', TaskStatus::Todo->value)
                ->max('position');

            return Task::query()->create([
                ...Arr::only($data, ['title', 'description', 'priority', 'due_date', 'assigned_to']),
                'project_id' => $project->getKey(),
                'created_by' => $creator->getKey(),
                'status' => TaskStatus::Todo->value,
                'position' => $maxPosition === null ? 0 : ((int) $maxPosition + 1),
            ]);
        });
    }
}
