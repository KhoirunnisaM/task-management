<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

class CommentPolicy
{
    public function create(User $user, Task $task): bool
    {
        return ! $task->project->isArchived()
            && ($user->isAdmin() || $task->project->roleOf($user) !== null);
    }

    public function update(User $user, Comment $comment): bool
    {
        return ! $comment->task->project->isArchived()
            && ($user->isAdmin() || (int) $comment->user_id === (int) $user->getKey());
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $this->update($user, $comment);
    }
}
