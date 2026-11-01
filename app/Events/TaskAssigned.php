<?php

namespace App\Events;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

final class TaskAssigned
{
    use Dispatchable;

    public function __construct(
        public readonly Task $task,
        public readonly ?User $oldAssignee,
        public readonly ?User $newAssignee,
        public readonly User $actor,
    ) {}
}
