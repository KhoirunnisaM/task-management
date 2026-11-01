<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Task $task) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $task = $this->task->loadMissing('project:id,title');

        return [
            'task_id' => $task->getKey(),
            'project_id' => $task->project_id,
            'title' => $task->title,
            'message' => __('notifications.task_assigned', [
                'title' => $task->title,
                'project' => $task->project?->title ?? __('common.not_set'),
            ]),
            'url' => route('projects.tasks.show', [$task->project_id, $task->getKey()]),
        ];
    }
}
