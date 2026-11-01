<?php

namespace App\Actions\Comments;

use App\Events\CommentCreated;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateCommentAction
{
    public function execute(Task $task, string $body, User $author): Comment
    {
        $comment = DB::transaction(fn () => $task->comments()->create([
            'user_id' => $author->getKey(),
            'body' => $body,
        ]));

        CommentCreated::dispatch($comment, $task, $author);

        return $comment->load('user:id,name');
    }
}
