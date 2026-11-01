<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Events\CommentDeleted;
use App\Events\CommentUpdated;
use App\Events\TaskAssigned;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;
use App\Events\TaskUpdated;
use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\User;

final class LogTaskActivity
{
    public function handleTaskCreated(TaskCreated $event): void
    {
        $this->record($event->task, $event->actor, 'task.created', [
            'title' => $event->task->title,
        ]);
    }

    public function handleTaskStatusChanged(TaskStatusChanged $event): void
    {
        $this->record($event->task, $event->actor, 'task.status_changed', [
            'title' => $event->task->title,
            'field' => 'status',
            'old' => $event->oldStatus->value,
            'new' => $event->newStatus->value,
        ]);
    }

    public function handleTaskAssigned(TaskAssigned $event): void
    {
        $this->record($event->task, $event->actor, 'task.assigned', [
            'title' => $event->task->title,
            'field' => 'assigned_to',
            'old' => $event->oldAssignee?->name,
            'new' => $event->newAssignee?->name,
        ]);
    }

    public function handleTaskUpdated(TaskUpdated $event): void
    {
        $this->record($event->task, $event->actor, "task.{$event->field}_changed", [
            'title' => $event->task->title,
            'field' => $event->field,
            'old' => $event->oldValue,
            'new' => $event->newValue,
        ]);
    }

    public function handleCommentCreated(CommentCreated $event): void
    {
        $this->record($event->task, $event->actor, 'comment.created', [
            'title' => $event->task->title,
            'excerpt' => str($event->comment->body)->limit(140)->toString(),
        ]);
    }

    public function handleCommentUpdated(CommentUpdated $event): void
    {
        $this->record($event->task, $event->actor, 'comment.updated', [
            'title' => $event->task->title,
            'field' => 'body',
            'old' => $event->oldBody,
            'new' => $event->newBody,
        ]);
    }

    public function handleCommentDeleted(CommentDeleted $event): void
    {
        $this->record($event->task, $event->actor, 'comment.deleted', [
            'title' => $event->task->title,
            'field' => 'body',
            'old' => $event->body,
            'new' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function record(Task $task, User $actor, string $action, array $properties): void
    {
        ActivityLog::query()->create([
            'project_id' => $task->project_id,
            'task_id' => $task->getKey(),
            'user_id' => $actor->getKey(),
            'action' => $action,
            'properties' => $properties,
        ]);
    }
}
