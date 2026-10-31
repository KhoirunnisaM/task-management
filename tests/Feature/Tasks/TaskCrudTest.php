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

class TaskCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $manager;

    private User $member;

    private User $outsider;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->manager = User::factory()->manager()->create();
        $this->member = User::factory()->member()->create();
        $this->outsider = User::factory()->member()->create();

        $this->project = Project::factory()->create([
            'owner_id' => $this->admin->id,
            'status' => ProjectStatus::Active,
        ]);

        $this->project->members()->attach($this->admin->id, ['role' => ProjectRole::Manager->value]);
        $this->project->members()->attach($this->manager->id, ['role' => ProjectRole::Manager->value]);
        $this->project->members()->attach($this->member->id, ['role' => ProjectRole::Member->value]);
    }

    public function test_admin_can_create_a_task(): void
    {
        $this->actingAs($this->admin)
            ->get(route('projects.tasks.create', $this->project))
            ->assertOk()
            ->assertSee(__('tasks.create_title'));

        $response = $this->actingAs($this->admin)->post(route('projects.tasks.store', $this->project), [
            'title' => 'Rancang skema database',
            'description' => 'Tentukan tabel dan relasi.',
            'priority' => TaskPriority::High->value,
            'due_date' => today()->addDays(3)->toDateString(),
            'assigned_to' => $this->member->id,
        ]);

        $task = Task::query()->firstOrFail();

        $response->assertRedirect(route('projects.tasks.show', [$this->project, $task]));
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'project_id' => $this->project->id,
            'created_by' => $this->admin->id,
            'assigned_to' => $this->member->id,
            'title' => 'Rancang skema database',
            'status' => TaskStatus::Todo->value,
            'priority' => TaskPriority::High->value,
            'position' => 0,
        ]);
    }

    public function test_manager_can_create_a_task_and_it_is_appended_to_the_todo_column(): void
    {
        $first = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'position' => 0,
        ]);

        $this->actingAs($this->manager)
            ->post(route('projects.tasks.store', $this->project), [
                'title' => 'Task kedua',
                'priority' => TaskPriority::Medium->value,
            ])
            ->assertRedirect();

        $second = Task::query()->where('title', 'Task kedua')->firstOrFail();

        $this->assertSame(1, $second->position);
        $this->assertSame(TaskStatus::Todo, $second->status);
        $this->assertNotSame($first->id, $second->id);
    }

    public function test_member_cannot_create_a_task(): void
    {
        $this->actingAs($this->member)
            ->get(route('projects.tasks.create', $this->project))
            ->assertForbidden();

        $this->actingAs($this->member)
            ->post(route('projects.tasks.store', $this->project), [
                'title' => 'Task dari member',
                'priority' => TaskPriority::Low->value,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_task_creation_validates_fields_assignee_and_due_date(): void
    {
        $this->actingAs($this->manager)
            ->from(route('projects.tasks.create', $this->project))
            ->post(route('projects.tasks.store', $this->project), [
                'title' => '',
                'priority' => 'invalid',
                'due_date' => today()->subDay()->toDateString(),
                'assigned_to' => $this->outsider->id,
            ])
            ->assertRedirect(route('projects.tasks.create', $this->project))
            ->assertSessionHasErrors(['title', 'priority', 'due_date', 'assigned_to']);

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_manager_can_update_a_task_without_bypassing_the_status_action(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'assigned_to' => $this->member->id,
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Low,
        ]);

        $this->actingAs($this->manager)
            ->put(route('projects.tasks.update', [$this->project, $task]), [
                'title' => 'Judul diperbarui',
                'description' => 'Deskripsi baru.',
                'priority' => TaskPriority::Urgent->value,
                'due_date' => today()->subDays(2)->toDateString(),
                'assigned_to' => $this->member->id,
                'status' => TaskStatus::Completed->value,
            ])
            ->assertRedirect(route('projects.tasks.show', [$this->project, $task]));

        $task->refresh();

        $this->assertSame('Judul diperbarui', $task->title);
        $this->assertSame(TaskPriority::Urgent, $task->priority);
        $this->assertSame(TaskStatus::Todo, $task->status);
        $this->assertNull($task->completed_at);
    }

    public function test_member_and_outsider_cannot_update_a_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'assigned_to' => $this->member->id,
        ]);

        $payload = [
            'title' => 'Tidak boleh',
            'priority' => TaskPriority::Low->value,
        ];

        $this->actingAs($this->member)
            ->put(route('projects.tasks.update', [$this->project, $task]), $payload)
            ->assertForbidden();

        $this->actingAs($this->outsider)
            ->put(route('projects.tasks.update', [$this->project, $task]), $payload)
            ->assertForbidden();

        $this->assertNotSame('Tidak boleh', $task->fresh()->title);
    }

    public function test_admin_and_manager_can_delete_tasks_but_member_cannot(): void
    {
        $managerTask = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
        ]);
        $adminTask = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->member)
            ->delete(route('projects.tasks.destroy', [$this->project, $managerTask]))
            ->assertForbidden();

        $this->actingAs($this->manager)
            ->delete(route('projects.tasks.destroy', [$this->project, $managerTask]))
            ->assertRedirect(route('projects.tasks.index', $this->project));

        $this->actingAs($this->admin)
            ->delete(route('projects.tasks.destroy', [$this->project, $adminTask]))
            ->assertRedirect(route('projects.tasks.index', $this->project));

        $this->assertSoftDeleted($managerTask);
        $this->assertSoftDeleted($adminTask);
    }

    public function test_task_routes_are_scoped_to_the_project(): void
    {
        $otherProject = Project::factory()->create(['owner_id' => $this->admin->id]);
        $otherTask = Task::factory()->create([
            'project_id' => $otherProject->id,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('projects.tasks.show', [$this->project, $otherTask]))
            ->assertNotFound();
    }
}
