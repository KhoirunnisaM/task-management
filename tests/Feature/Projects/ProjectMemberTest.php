<?php

namespace Tests\Feature\Projects;

use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectMemberTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $manager;

    private User $member;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->manager = User::factory()->manager()->create();
        $this->member = User::factory()->member()->create();

        $this->project = Project::factory()->create([
            'owner_id' => $this->admin->id,
            'status' => ProjectStatus::Active,
        ]);

        $this->project->members()->attach($this->admin->id, ['role' => ProjectRole::Manager->value]);
        $this->project->members()->attach($this->manager->id, ['role' => ProjectRole::Manager->value]);
        $this->project->members()->attach($this->member->id, ['role' => ProjectRole::Member->value]);
    }

    public function test_manager_can_add_a_user_as_member(): void
    {
        $candidate = User::factory()->member()->create();

        $this->actingAs($this->manager)
            ->post(route('projects.members.store', $this->project), [
                'user_id' => $candidate->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('project_user', [
            'project_id' => $this->project->id,
            'user_id' => $candidate->id,
            'role' => ProjectRole::Member->value,
        ]);
    }

    public function test_member_cannot_add_or_remove_project_members(): void
    {
        $candidate = User::factory()->member()->create();

        $this->actingAs($this->member)
            ->post(route('projects.members.store', $this->project), [
                'user_id' => $candidate->id,
            ])
            ->assertForbidden();

        $this->actingAs($this->member)
            ->delete(route('projects.members.destroy', [$this->project, $this->manager]))
            ->assertForbidden();

        $this->assertDatabaseMissing('project_user', [
            'project_id' => $this->project->id,
            'user_id' => $candidate->id,
        ]);
    }

    public function test_adding_an_existing_member_fails_validation(): void
    {
        $this->actingAs($this->manager)
            ->post(route('projects.members.store', $this->project), [
                'user_id' => $this->member->id,
            ])
            ->assertSessionHasErrors('user_id');
    }

    public function test_manager_can_promote_a_member_to_manager(): void
    {
        $this->actingAs($this->manager)
            ->patch(route('projects.members.update', [$this->project, $this->member]), [
                'role' => ProjectRole::Manager->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('project_user', [
            'project_id' => $this->project->id,
            'user_id' => $this->member->id,
            'role' => ProjectRole::Manager->value,
        ]);
    }

    public function test_manager_cannot_be_demoted_or_removed(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('projects.members.update', [$this->project, $this->manager]), [
                'role' => ProjectRole::Member->value,
            ])
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->delete(route('projects.members.destroy', [$this->project, $this->manager]))
            ->assertForbidden();

        $this->assertDatabaseHas('project_user', [
            'project_id' => $this->project->id,
            'user_id' => $this->manager->id,
            'role' => ProjectRole::Manager->value,
        ]);
    }

    public function test_removing_a_member_unassigns_their_project_tasks(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'assigned_to' => $this->member->id,
            'status' => TaskStatus::InProgress,
        ]);

        $this->actingAs($this->manager)
            ->delete(route('projects.members.destroy', [$this->project, $this->member]))
            ->assertRedirect();

        $this->assertDatabaseMissing('project_user', [
            'project_id' => $this->project->id,
            'user_id' => $this->member->id,
        ]);
        $this->assertNull($task->fresh()->assigned_to);
    }

    public function test_archived_projects_are_read_only_for_members(): void
    {
        $this->project->update(['status' => ProjectStatus::Archived]);

        $candidate = User::factory()->member()->create();

        $this->actingAs($this->manager)
            ->post(route('projects.members.store', $this->project), [
                'user_id' => $candidate->id,
            ])
            ->assertForbidden();

        $this->actingAs($this->manager)
            ->patch(route('projects.members.update', [$this->project, $this->member]), [
                'role' => ProjectRole::Manager->value,
            ])
            ->assertForbidden();

        $this->actingAs($this->manager)
            ->delete(route('projects.members.destroy', [$this->project, $this->member]))
            ->assertForbidden();
    }
}
