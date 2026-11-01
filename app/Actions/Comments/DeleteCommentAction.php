<?php

namespace App\Actions\Comments;

use App\Events\CommentDeleted;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DeleteCommentAction
{
    public function execute(Task $task, Comment $comment, User $actor): void
    {
        $body = $comment->body;

        DB::transaction(fn () => $comment->delete());

        CommentDeleted::dispatch($comment, $task, $body, $actor);
    }
}
