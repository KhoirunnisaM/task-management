<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class TaskDueSoonNotification extends Notification implements ShouldQueue
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
            'due_date' => $task->due_date?->toDateString(),
            'message' => __('notifications.task_due_soon', [
                'title' => $task->title,
                'project' => $task->project?->title ?? __('common.not_set'),
                'date' => $task->due_date?->translatedFormat('d M Y'),
            ]),
            'url' => route('projects.tasks.show', [$task->project_id, $task->getKey()]),
        ];
    }
}
