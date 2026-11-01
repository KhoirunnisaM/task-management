<?php

namespace App\Events;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

final class TaskUpdated
{
    use Dispatchable;

    public function __construct(
        public readonly Task $task,
        public readonly string $field,
        public readonly ?string $oldValue,
        public readonly ?string $newValue,
        public readonly User $actor,
    ) {}
}
