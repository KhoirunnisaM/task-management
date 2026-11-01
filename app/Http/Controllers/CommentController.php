<?php

namespace App\Http\Controllers;

use App\Actions\Comments\CreateCommentAction;
use App\Actions\Comments\DeleteCommentAction;
use App\Actions\Comments\UpdateCommentAction;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(
        StoreCommentRequest $request,
        Project $project,
        Task $task,
        CreateCommentAction $action,
    ): RedirectResponse {
        $action->execute($task, $request->validated('body'), $request->user());

        return back()->with('success', __('comments.created'));
    }

    public function update(
        UpdateCommentRequest $request,
        Project $project,
        Task $task,
        Comment $comment,
        UpdateCommentAction $action,
    ): RedirectResponse {
        $action->execute($task, $comment, $request->validated('body'), $request->user());

        return back()->with('success', __('comments.updated'));
    }

    public function destroy(
        Request $request,
        Project $project,
        Task $task,
        Comment $comment,
        DeleteCommentAction $action,
    ): RedirectResponse {
        $this->authorize('delete', $comment);

        $action->execute($task, $comment, $request->user());

        return back()->with('success', __('comments.deleted'));
    }
}
