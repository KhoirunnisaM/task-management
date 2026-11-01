<?php

namespace App\Events;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

final class TaskCreated
{
    use Dispatchable;

    public function __construct(
        public readonly Project $project,
        public readonly Task $task,
        public readonly User $actor,
    ) {}
}
