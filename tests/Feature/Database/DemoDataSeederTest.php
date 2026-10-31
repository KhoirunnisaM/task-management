<?php

namespace Tests\Feature\Database;

use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_data_seeder_creates_projects_tasks_comments_and_activity_logs(): void
    {
        $this->seed(DemoDataSeeder::class);

        $this->assertDatabaseCount('users', 7);
        $this->assertDatabaseCount('projects', 3);
        $this->assertDatabaseCount('tasks', 12);

        $admin = User::query()->where('email', 'admin@taskmanager.test')->firstOrFail();
        $this->assertSame(UserRole::Admin, $admin->role);

        $project = Project::query()->where('title', 'Website Redesign')->firstOrFail();
        $this->assertSame(ProjectStatus::Active, $project->status);
        $this->assertSame($admin->getKey(), $project->owner_id);
        $this->assertSame(ProjectRole::Manager, $project->roleOf($admin));
        $this->assertSame(7, $project->tasks()->count());
        $this->assertTrue($project->tasks()->where('status', TaskStatus::InProgress->value)->exists());

        $this->assertTrue(Project::query()->where('status', ProjectStatus::Archived->value)->exists());
        $this->assertGreaterThan(0, Comment::query()->count());
        $this->assertGreaterThan(0, ActivityLog::query()->count());
        $this->assertTrue(ActivityLog::query()->where('action', 'project.created')->exists());
        $this->assertTrue(ActivityLog::query()->where('action', 'task.status_changed')->exists());
    }
}
