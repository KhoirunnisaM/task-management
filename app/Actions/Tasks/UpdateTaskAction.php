<?php

namespace App\Actions\Tasks;

use App\Events\TaskAssigned;
use App\Events\TaskUpdated;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Arr;

final class UpdateTaskAction
{
    public function execute(Task $task, array $data, User $actor): Task
    {
        $oldAssignee = $task->assigned_to
            ? User::query()->find($task->assigned_to, ['id', 'name'])
            : null;
        $oldAssignedTo = $task->assigned_to === null ? null : (int) $task->assigned_to;
        $oldPriority = $task->priority;
        $oldDueDate = $task->due_date?->toDateString();

        $task->update(Arr::only($data, [
            'title',
            'description',
            'priority',
            'due_date',
            'assigned_to',
        ]));

        $task->refresh();

        $newAssignedTo = $task->assigned_to === null ? null : (int) $task->assigned_to;

        if ($oldAssignedTo !== $newAssignedTo) {
            $newAssignee = $newAssignedTo
                ? User::query()->find($newAssignedTo, ['id', 'name'])
                : null;

            TaskAssigned::dispatch($task, $oldAssignee, $newAssignee, $actor);
        }

        if ($oldPriority !== $task->priority) {
            TaskUpdated::dispatch(
                $task,
                'priority',
                $oldPriority?->value,
                $task->priority?->value,
                $actor,
            );
        }

        if ($oldDueDate !== $task->due_date?->toDateString()) {
            TaskUpdated::dispatch(
                $task,
                'due_date',
                $oldDueDate,
                $task->due_date?->toDateString(),
                $actor,
            );
        }

        return $task;
    }
}
