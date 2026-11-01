<?php

namespace App\Events;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

final class CommentUpdated
{
    use Dispatchable;

    public function __construct(
        public readonly Comment $comment,
        public readonly Task $task,
        public readonly string $oldBody,
        public readonly string $newBody,
        public readonly User $actor,
    ) {}
}
