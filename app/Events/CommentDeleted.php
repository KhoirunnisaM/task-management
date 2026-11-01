<?php

namespace App\Events;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

final class CommentDeleted
{
    use Dispatchable;

    public function __construct(
        public readonly Comment $comment,
        public readonly Task $task,
        public readonly string $body,
        public readonly User $actor,
    ) {}
}
