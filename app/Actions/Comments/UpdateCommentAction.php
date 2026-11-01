<?php

namespace App\Actions\Comments;

use App\Events\CommentUpdated;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateCommentAction
{
    public function execute(Task $task, Comment $comment, string $body, User $actor): Comment
    {
        $oldBody = $comment->body;

        DB::transaction(fn () => $comment->update(['body' => $body]));

        CommentUpdated::dispatch($comment, $task, $oldBody, $comment->body, $actor);

        return $comment->refresh()->load('user:id,name');
    }
}
