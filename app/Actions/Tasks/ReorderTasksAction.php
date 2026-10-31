<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use Illuminate\Support\Facades\DB;

final class ReorderTasksAction
{
    public function execute(Task $task, int $position): void
    {
        DB::transaction(function () use ($task, $position) {
            $tasks = $task->project->tasks()
                ->where('status', $task->status->value)
                ->orderBy('position')
                ->orderBy('id')
                ->get()
                ->reject(fn (Task $item) => $item->is($task))
                ->values();

            $target = max(0, min($position, $tasks->count()));
            $tasks->splice($target, 0, [$task]);

            $tasks->each(function (Task $item, int $index) {
                $item->update(['position' => $index]);
            });
        });
    }
}
