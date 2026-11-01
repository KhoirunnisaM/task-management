<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Notifications\TaskDueSoonNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendDueSoonTaskReminders extends Command
{
    protected $signature = 'tasks:send-due-soon-reminders';

    protected $description = 'Send notifications for tasks that are due tomorrow.';

    public function handle(): int
    {
        $tasks = Task::query()
            ->with(['assignee:id,name', 'project:id,title'])
            ->whereDate('due_date', today()->addDay()->toDateString())
            ->where('status', '!=', TaskStatus::Completed->value)
            ->whereNotNull('assigned_to')
            ->get();

        $sent = 0;

        foreach ($tasks as $task) {
            if ($task->isCompleted() || $task->assignee === null) {
                continue;
            }

            Notification::send($task->assignee, new TaskDueSoonNotification($task));
            $sent++;
        }

        $this->info(__('notifications.due_soon_sent', ['count' => $sent]));

        return self::SUCCESS;
    }
}
