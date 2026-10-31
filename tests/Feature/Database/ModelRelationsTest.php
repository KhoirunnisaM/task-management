<?php

namespace Tests\Feature\Database;

use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ModelRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_relations_and_user_scoping(): void
    {
        $admin = User::factory()->admin()->create();
        $manager = User::factory()->manager()->create();
        $member = User::factory()->member()->create();
        $outsider = User::factory()->member()->create();

        $project = Project::factory()->create([
            'owner_id' => $admin->getKey(),
            'status' => ProjectStatus::Active,
        ]);

        $project->members()->attach($admin->getKey(), ['role' => ProjectRole::Manager->value]);
        $project->members()->attach($manager->getKey(), ['role' => ProjectRole::Manager->value]);
        $project->members()->attach($member->getKey(), ['role' => ProjectRole::Member->value]);
        $project->load('members');

        $this->assertTrue($project->owner->is($admin));
        $this->assertCount(3, $project->members);
        $this->assertSame(ProjectRole::Manager, $project->roleOf($manager));
        $this->assertSame(ProjectRole::Member, $project->roleOf($member));
        $this->assertNull($project->roleOf($outsider));
        $this->assertTrue($project->isOwner($admin));
        $this->assertFalse($project->isOwner($manager));
        $this->assertFalse($project->isArchived());

        $this->assertTrue(Project::forUser($admin)->whereKey($project->getKey())->exists());
        $this->assertTrue(Project::forUser($member)->whereKey($project->getKey())->exists());
        $this->assertFalse(Project::forUser($outsider)->whereKey($project->getKey())->exists());
    }

    public function test_task_casts_relations_overdue_scope_and_soft_deletes(): void
    {
        Carbon::setTestNow('2026-10-31 10:00:00');

        $admin = User::factory()->admin()->create();
        $member = User::factory()->member()->create();
        $project = Project::factory()->create([
            'owner_id' => $admin->getKey(),
            'status' => ProjectStatus::Active,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->getKey(),
            'created_by' => $admin->getKey(),
            'assigned_to' => $member->getKey(),
            'status' => TaskStatus::InProgress,
            'priority' => TaskPriority::Urgent,
            'due_date' => '2026-10-29',
            'position' => 3,
        ]);

        $this->assertSame(TaskStatus::InProgress, $task->status);
        $this->assertSame(TaskPriority::Urgent, $task->priority);
        $this->assertSame(3, $task->position);
        $this->assertSame('2026-10-29', $task->due_date->toDateString());
        $this->assertTrue($task->isOverdue());
        $this->assertTrue(Task::overdue()->whereKey($task->getKey())->exists());

        $this->assertTrue($task->project->is($project));
        $this->assertTrue($task->creator->is($admin));
        $this->assertTrue($task->assignee->is($member));

        $task->update([
            'status' => TaskStatus::Completed,
            'completed_at' => now(),
        ]);

        $this->assertFalse($task->fresh()->isOverdue());
        $this->assertFalse(Task::overdue()->whereKey($task->getKey())->exists());

        $task->delete();

        $this->assertSoftDeleted($task);
        $this->assertNull(Task::find($task->getKey()));

        Carbon::setTestNow();
    }

    public function test_activity_log_properties_are_cast_and_only_created_at_is_used(): void
    {
        $log = ActivityLog::factory()->create([
            'properties' => [
                'old' => 'todo',
                'new' => 'in_progress',
            ],
        ]);

        $this->assertSame(['old' => 'todo', 'new' => 'in_progress'], $log->properties);
        $this->assertNotNull($log->created_at);
        $this->assertNull($log->updated_at);
    }

    public function test_project_member_pivot_has_a_unique_constraint(): void
    {
        $project = Project::factory()->create();
        $member = User::factory()->member()->create();

        $project->members()->attach($member->getKey(), ['role' => ProjectRole::Member->value]);

        $this->expectException(QueryException::class);

        $project->members()->attach($member->getKey(), ['role' => ProjectRole::Member->value]);
    }

    public function test_removing_an_assignee_sets_the_task_assignee_to_null_through_the_foreign_key(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->member()->create();
        $project = Project::factory()->create(['owner_id' => $admin->getKey()]);
        $task = Task::factory()->create([
            'project_id' => $project->getKey(),
            'created_by' => $admin->getKey(),
            'assigned_to' => $assignee->getKey(),
        ]);

        $assignee->delete();

        $this->assertNull($task->fresh()->assigned_to);
    }
}
