<?php

namespace Tests\Feature\Tasks;

use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskActivityLogTest extends TestCase
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

    public function test_task_creation_is_recorded_in_the_activity_log(): void
    {
        $this->actingAs($this->manager)
            ->post(route('projects.tasks.store', $this->project), [
                'title' => 'Task baru',
                'priority' => TaskPriority::High->value,
                'assigned_to' => $this->member->id,
            ])
            ->assertRedirect();

        $task = Task::query()->where('title', 'Task baru')->sole();
        $log = ActivityLog::query()->where('action', 'task.created')->sole();

        $this->assertSame('task.created', $log->action);
        $this->assertSame($this->project->id, $log->project_id);
        $this->assertSame($task->id, $log->task_id);
        $this->assertSame($this->manager->id, $log->user_id);
        $this->assertSame('Task baru', $log->properties['title']);
        $this->assertNotNull($log->created_at);
    }

    public function test_status_change_is_recorded_with_old_and_new_values(): void
    {
        $task = $this->task();

        $this->actingAs($this->manager)
            ->patch(route('projects.tasks.status', [$this->project, $task]), [
                'status' => TaskStatus::InProgress->value,
            ])
            ->assertRedirect();

        $log = ActivityLog::query()->where('action', 'task.status_changed')->sole();

        $this->assertSame($this->project->id, $log->project_id);
        $this->assertSame($task->id, $log->task_id);
        $this->assertSame($this->manager->id, $log->user_id);
        $this->assertSame('status', $log->properties['field']);
        $this->assertSame(TaskStatus::Todo->value, $log->properties['old']);
        $this->assertSame(TaskStatus::InProgress->value, $log->properties['new']);
        $this->assertSame(__('tasks.status.in_progress'), $log->newValueLabel());
    }

    public function test_assignee_priority_and_due_date_changes_are_recorded(): void
    {
        $task = $this->task([
            'assigned_to' => $this->member->id,
            'priority' => TaskPriority::Low,
            'due_date' => today()->addDays(2)->toDateString(),
        ]);

        $this->actingAs($this->manager)
            ->put(route('projects.tasks.update', [$this->project, $task]), [
                'title' => $task->title,
                'priority' => TaskPriority::Urgent->value,
                'due_date' => today()->addDays(5)->toDateString(),
                'assigned_to' => $this->otherMember->id,
            ])
            ->assertRedirect();

        $logs = ActivityLog::query()->get()->keyBy('action');

        $this->assertCount(3, $logs);

        $assigned = $logs->get('task.assigned');
        $this->assertSame($this->member->name, $assigned->properties['old']);
        $this->assertSame($this->otherMember->name, $assigned->properties['new']);

        $priority = $logs->get('task.priority_changed');
        $this->assertSame(TaskPriority::Low->value, $priority->properties['old']);
        $this->assertSame(TaskPriority::Urgent->value, $priority->properties['new']);

        $dueDate = $logs->get('task.due_date_changed');
        $this->assertSame(today()->addDays(2)->toDateString(), $dueDate->properties['old']);
        $this->assertSame(today()->addDays(5)->toDateString(), $dueDate->properties['new']);
    }

    public function test_updating_without_relevant_changes_does_not_create_an_activity_log(): void
    {
        $task = $this->task([
            'assigned_to' => $this->member->id,
            'priority' => TaskPriority::Medium,
            'due_date' => today()->addDays(2)->toDateString(),
        ]);

        $this->actingAs($this->manager)
            ->put(route('projects.tasks.update', [$this->project, $task]), [
                'title' => 'Judul saja yang berubah',
                'priority' => TaskPriority::Medium->value,
                'due_date' => today()->addDays(2)->toDateString(),
                'assigned_to' => $this->member->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_task_and_project_pages_render_the_activity_timeline(): void
    {
        $task = $this->task();

        $this->actingAs($this->manager)
            ->patch(route('projects.tasks.status', [$this->project, $task]), [
                'status' => TaskStatus::Completed->value,
            ]);

        $this->actingAs($this->manager)
            ->get(route('projects.tasks.show', [$this->project, $task]))
            ->assertOk()
            ->assertSee(__('activity.actions.task.status_changed'))
            ->assertSee($task->title)
            ->assertSee(__('tasks.status.completed'));

        $this->actingAs($this->manager)
            ->get(route('projects.show', $this->project))
            ->assertOk()
            ->assertSee(__('activity.actions.task.status_changed'))
            ->assertSee($task->title);
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
            ...$attributes,
        ]);
    }
}
