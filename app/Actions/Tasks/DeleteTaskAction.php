<?php

namespace App\Actions\Tasks;

use App\Models\Task;

final class DeleteTaskAction
{
    public function execute(Task $task): void
    {
        $task->delete();
    }
}
