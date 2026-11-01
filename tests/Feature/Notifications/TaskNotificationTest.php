<?php

namespace Tests\Feature\Notifications;

use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Listeners\SendTaskAssignedNotification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskDueSoonNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $manager;

    private User $member;

    private User $otherMember;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->manager = User::factory()->manager()->create();
        $this->member = User::factory()->member()->create(['name' => 'Anggota Satu']);
        $this->otherMember = User::factory()->member()->create(['name' => 'Anggota Dua']);

        $this->project = Project::factory()->create([
            'owner_id' => $this->admin->id,
            'status' => ProjectStatus::Active,
        ]);

        $this->project->members()->attach($this->admin->id, ['role' => ProjectRole::Manager->value]);
        $this->project->members()->attach($this->manager->id, ['role' => ProjectRole::Manager->value]);
        $this->project->members()->attach($this->member->id, ['role' => ProjectRole::Member->value]);
        $this->project->members()->attach($this->otherMember->id, ['role' => ProjectRole::Member->value]);
    }

    public function test_user_receives_a_database_notification_when_assigned_to_a_task(): void
    {
        $task = $this->task(['assigned_to' => $this->member->id]);

        $this->actingAs($this->manager)
            ->put(route('projects.tasks.update', [$this->project, $task]), [
                'title' => $task->title,
                'priority' => $task->priority->value,
                'assigned_to' => $this->otherMember->id,
            ])
            ->assertRedirect();

        $notification = $this->otherMember->notifications()->sole();

        $this->assertSame(TaskAssignedNotification::class, $notification->type);
        $this->assertSame($task->id, $notification->data['task_id']);
        $this->assertSame($task->title, $notification->data['title']);
        $this->assertSame(0, $this->member->notifications()->count());
    }

    public function test_assigning_a_task_during_creation_sends_the_notification(): void
    {
        $this->actingAs($this->manager)
            ->post(route('projects.tasks.store', $this->project), [
                'title' => 'Task dengan assignee',
                'priority' => TaskPriority::High->value,
                'assigned_to' => $this->member->id,
            ])
            ->assertRedirect();

        $task = Task::query()->where('title', 'Task dengan assignee')->sole();
        $notification = $this->member->notifications()->sole();

        $this->assertSame(TaskAssignedNotification::class, $notification->type);
        $this->assertSame($task->id, $notification->data['task_id']);
    }

    public function test_unassigning_a_task_does_not_send_a_notification(): void
    {
        $task = $this->task(['assigned_to' => $this->member->id]);

        $this->actingAs($this->manager)
            ->put(route('projects.tasks.update', [$this->project, $task]), [
                'title' => $task->title,
                'priority' => $task->priority->value,
                'assigned_to' => null,
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_assignment_listener_is_queued_on_the_database_connection(): void
    {
        config(['queue.default' => 'database']);

        $task = $this->task(['assigned_to' => $this->member->id]);

        $this->actingAs($this->manager)
            ->put(route('projects.tasks.update', [$this->project, $task]), [
                'title' => $task->title,
                'priority' => $task->priority->value,
                'assigned_to' => $this->otherMember->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('jobs', 1);

        $payload = json_decode((string) DB::table('jobs')->value('payload'), true);

        $this->assertSame(SendTaskAssignedNotification::class, $payload['displayName']);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_assignment_notification_uses_the_database_channel_and_is_queued(): void
    {
        $task = $this->task(['assigned_to' => $this->member->id]);
        $notification = new TaskAssignedNotification($task);

        $this->assertInstanceOf(ShouldQueue::class, $notification);
        $this->assertSame(['database'], $notification->via($this->member));
    }

    public function test_due_soon_command_notifies_assignee_of_incomplete_tasks_due_tomorrow(): void
    {
        Notification::fake();

        $eligible = $this->task([
            'title' => 'Hampir tenggat',
            'assigned_to' => $this->member->id,
            'due_date' => today()->addDay()->toDateString(),
        ]);

        $this->task([
            'title' => 'Tenggat hari ini',
            'assigned_to' => $this->member->id,
            'due_date' => today()->toDateString(),
        ]);

        $this->task([
            'title' => 'Tenggat lebih lama',
            'assigned_to' => $this->member->id,
            'due_date' => today()->addDays(3)->toDateString(),
        ]);

        $completed = $this->task([
            'title' => 'Sudah selesai',
            'assigned_to' => $this->member->id,
            'status' => TaskStatus::Completed,
            'due_date' => today()->addDay()->toDateString(),
        ]);

        $this->task([
            'title' => 'Tanpa assignee',
            'assigned_to' => null,
            'due_date' => today()->addDay()->toDateString(),
        ]);

        $this->artisan('tasks:send-due-soon-reminders')
            ->expectsOutput(__('notifications.due_soon_sent', ['count' => 1]))
            ->assertSuccessful();

        Notification::assertSentTo(
            $this->member,
            TaskDueSoonNotification::class,
            fn (TaskDueSoonNotification $notification) => $notification->task->is($eligible),
        );
        Notification::assertSentTimes(TaskDueSoonNotification::class, 1);
        Notification::assertNotSentTo(
            $this->member,
            TaskDueSoonNotification::class,
            fn (TaskDueSoonNotification $notification) => $notification->task->is($completed),
        );

        $this->assertInstanceOf(ShouldQueue::class, new TaskDueSoonNotification($eligible));
        $this->assertSame(['database'], (new TaskDueSoonNotification($eligible))->via($this->member));
    }

    public function test_due_soon_notification_is_stored_in_the_database(): void
    {
        $task = $this->task([
            'title' => 'Reminder masuk database',
            'assigned_to' => $this->member->id,
            'due_date' => today()->addDay()->toDateString(),
        ]);

        $this->artisan('tasks:send-due-soon-reminders')->assertSuccessful();

        $notification = $this->member->notifications()->sole();

        $this->assertSame(TaskDueSoonNotification::class, $notification->type);
        $this->assertSame($task->id, $notification->data['task_id']);
        $this->assertSame($task->due_date->toDateString(), $notification->data['due_date']);
        $this->assertStringContainsString($task->title, $notification->data['message']);
    }

    public function test_due_soon_reminder_command_is_scheduled_daily(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains((string) $event->command, 'tasks:send-due-soon-reminders'));

        $this->assertNotNull($event);
        $this->assertSame('0 8 * * *', $event->expression);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function task(array $attributes = []): Task
    {
        return Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'title' => 'Task contoh',
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Medium,
            'due_date' => today()->addDays(2)->toDateString(),
            ...$attributes,
        ]);
    }
}
