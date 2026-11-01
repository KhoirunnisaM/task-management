<?php

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Events\TaskStatusChanged;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateTaskStatusAction
{
    public function execute(
        Task $task,
        TaskStatus $status,
        ?int $position = null,
        ?User $actor = null,
    ): Task {
        $previousStatus = $task->status;

        $task = DB::transaction(function () use ($task, $status, $position, $previousStatus) {
            $completedAt = $status === TaskStatus::Completed
                ? ($task->completed_at ?? now())
                : null;

            if ($previousStatus !== $status) {
                $task->project->tasks()
                    ->where('status', $previousStatus->value)
                    ->whereKeyNot($task->getKey())
                    ->orderBy('position')
                    ->orderBy('id')
                    ->get()
                    ->each(fn (Task $item, int $index) => $item->update(['position' => $index]));
            }

            $targetTasks = $task->project->tasks()
                ->where('status', $status->value)
                ->whereKeyNot($task->getKey())
                ->orderBy('position')
                ->orderBy('id')
                ->get()
                ->values();

            if ($position !== null) {
                $target = max(0, min($position, $targetTasks->count()));
                $targetTasks->splice($target, 0, [$task]);
            } elseif ($previousStatus !== $status) {
                $targetTasks->push($task);
            }

            if ($position !== null || $previousStatus !== $status) {
                $targetTasks->each(function (Task $item, int $index) use ($task) {
                    if ($item->is($task)) {
                        $task->position = $index;

                        return;
                    }

                    $item->update(['position' => $index]);
                });
            }

            $task->status = $status;
            $task->completed_at = $completedAt;
            $task->save();

            return $task->refresh();
        });

        if ($actor !== null && $previousStatus !== $status) {
            TaskStatusChanged::dispatch($task, $previousStatus, $status, $actor);
        }

        return $task;
    }
}
