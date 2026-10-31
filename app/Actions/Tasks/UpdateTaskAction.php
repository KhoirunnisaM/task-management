<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use Illuminate\Support\Arr;

final class UpdateTaskAction
{
    public function execute(Task $task, array $data): Task
    {
        $task->update(Arr::only($data, [
            'title',
            'description',
            'priority',
            'due_date',
            'assigned_to',
        ]));

        return $task->refresh();
    }
}
