<?php

namespace Tests\Feature\Tasks;

use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskStatusAndOrderingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $manager;

    private User $member;

    private User $otherMember;

    private User $outsider;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->manager = User::factory()->manager()->create();
        $this->member = User::factory()->member()->create();
        $this->otherMember = User::factory()->member()->create();
        $this->outsider = User::factory()->member()->create();

        $this->project = Project::factory()->create([
            'owner_id' => $this->admin->id,
            'status' => ProjectStatus::Active,
        ]);

        $this->project->members()->attach($this->admin->id, ['role' => ProjectRole::Manager->value]);
        $this->project->members()->attach($this->manager->id, ['role' => ProjectRole::Manager->value]);
        $this->project->members()->attach($this->member->id, ['role' => ProjectRole::Member->value]);
        $this->project->members()->attach($this->otherMember->id, ['role' => ProjectRole::Member->value]);
    }

    public function test_assigned_member_can_move_task_through_status_and_completed_at_is_managed(): void
    {
        $task = $this->taskFor($this->member);

        $this->actingAs($this->member)
            ->patch(route('projects.tasks.status', [$this->project, $task]), [
                'status' => TaskStatus::InProgress->value,
            ])
            ->assertRedirect();

        $this->assertSame(TaskStatus::InProgress, $task->fresh()->status);
        $this->assertNull($task->fresh()->completed_at);

        $this->actingAs($this->member)
            ->patch(route('projects.tasks.status', [$this->project, $task]), [
                'status' => TaskStatus::Completed->value,
            ])
            ->assertRedirect();

        $this->assertSame(TaskStatus::Completed, $task->fresh()->status);
        $this->assertNotNull($task->fresh()->completed_at);

        $this->actingAs($this->member)
            ->patch(route('projects.tasks.status', [$this->project, $task]), [
                'status' => TaskStatus::InReview->value,
            ])
            ->assertRedirect();

        $this->assertSame(TaskStatus::InReview, $task->fresh()->status);
        $this->assertNull($task->fresh()->completed_at);
    }

    public function test_member_cannot_update_status_of_a_task_assigned_to_someone_else(): void
    {
        $task = $this->taskFor($this->otherMember);

        $this->actingAs($this->member)
            ->patch(route('projects.tasks.status', [$this->project, $task]), [
                'status' => TaskStatus::Completed->value,
            ])
            ->assertForbidden();

        $this->assertSame(TaskStatus::Todo, $task->fresh()->status);
    }

    public function test_manager_admin_and_outsider_status_authorization(): void
    {
        $task = $this->taskFor($this->member);

        $this->actingAs($this->manager)
            ->patch(route('projects.tasks.status', [$this->project, $task]), [
                'status' => TaskStatus::InReview->value,
            ])
            ->assertRedirect();

        $this->assertSame(TaskStatus::InReview, $task->fresh()->status);

        $this->actingAs($this->admin)
            ->patch(route('projects.tasks.status', [$this->project, $task]), [
                'status' => TaskStatus::InProgress->value,
            ])
            ->assertRedirect();

        $this->assertSame(TaskStatus::InProgress, $task->fresh()->status);

        $this->actingAs($this->outsider)
            ->patch(route('projects.tasks.status', [$this->project, $task]), [
                'status' => TaskStatus::Completed->value,
            ])
            ->assertForbidden();

        $this->assertSame(TaskStatus::InProgress, $task->fresh()->status);
    }

    public function test_status_change_appends_task_to_the_end_of_the_target_column(): void
    {
        $existing = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'status' => TaskStatus::InProgress,
            'position' => 0,
        ]);

        $task = $this->taskFor($this->member);

        $this->actingAs($this->manager)
            ->patch(route('projects.tasks.status', [$this->project, $task]), [
                'status' => TaskStatus::InProgress->value,
            ])
            ->assertRedirect();

        $this->assertSame(0, $existing->fresh()->position);
        $this->assertSame(1, $task->fresh()->position);
    }

    public function test_manager_can_reorder_tasks_within_a_status(): void
    {
        $first = $this->taskFor($this->manager, 'Task A', 0);
        $second = $this->taskFor($this->manager, 'Task B', 1);
        $third = $this->taskFor($this->manager, 'Task C', 2);

        $this->actingAs($this->manager)
            ->patch(route('projects.tasks.reorder', [$this->project, $third]), [
                'position' => 0,
            ])
            ->assertRedirect();

        $this->assertSame(['Task C', 'Task A', 'Task B'], $this->project->tasks()
            ->where('status', TaskStatus::Todo->value)
            ->orderBy('position')
            ->pluck('title')
            ->all());

        $this->assertSame(0, $third->fresh()->position);
        $this->assertSame(1, $first->fresh()->position);
        $this->assertSame(2, $second->fresh()->position);
    }

    public function test_member_cannot_reorder_tasks(): void
    {
        $task = $this->taskFor($this->member);
        $this->taskFor($this->manager, 'Task B', 1);

        $this->actingAs($this->member)
            ->patch(route('projects.tasks.reorder', [$this->project, $task]), [
                'position' => 1,
            ])
            ->assertForbidden();

        $this->assertSame(0, $task->fresh()->position);
    }

    public function test_archived_project_blocks_status_and_reorder_even_for_admin(): void
    {
        $task = $this->taskFor($this->member);
        $other = $this->taskFor($this->manager, 'Task B', 1);

        $this->project->update(['status' => ProjectStatus::Archived]);

        $this->actingAs($this->admin)
            ->patch(route('projects.tasks.status', [$this->project, $task]), [
                'status' => TaskStatus::Completed->value,
            ])
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->patch(route('projects.tasks.reorder', [$this->project, $other]), [
                'position' => 0,
            ])
            ->assertForbidden();

        $this->assertSame(TaskStatus::Todo, $task->fresh()->status);
    }

    public function test_archived_project_blocks_task_mutations_even_for_admin(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->admin->id,
        ]);

        $this->project->update(['status' => ProjectStatus::Archived]);

        $this->actingAs($this->admin)
            ->get(route('projects.tasks.create', $this->project))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->put(route('projects.tasks.update', [$this->project, $task]), [
                'title' => 'Tidak boleh',
                'priority' => TaskPriority::Low->value,
            ])
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->delete(route('projects.tasks.destroy', [$this->project, $task]))
            ->assertForbidden();

        $this->assertNotSoftDeleted($task);
    }

    private function taskFor(User $assignee, string $title = 'Task contoh', int $position = 0): Task
    {
        return Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'assigned_to' => $assignee->id,
            'title' => $title,
            'status' => TaskStatus::Todo,
            'position' => $position,
        ]);
    }
}
