<?php

namespace Tests\Feature\Dashboard;

use App\Enums\DashboardRole;
use App\Enums\ProjectRole;
use App\Enums\TaskStatus;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_uses_global_scope_and_stats(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->member()->create();

        $active = Project::factory()->active()->create([
            'owner_id' => $admin->id,
            'title' => 'Proyek Aktif Admin',
        ]);
        $completed = Project::factory()->completed()->create([
            'owner_id' => $admin->id,
            'title' => 'Proyek Selesai Admin',
        ]);

        Task::factory()->for($active)->create([
            'created_by' => $admin->id,
            'assigned_to' => $admin->id,
            'title' => 'Task belum selesai',
        ]);
        Task::factory()->for($active)->overdue()->create([
            'created_by' => $admin->id,
            'assigned_to' => $admin->id,
            'title' => 'Task terlambat',
        ]);
        Task::factory()->for($completed)->completed()->create([
            'created_by' => $admin->id,
            'title' => 'Task selesai',
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('role', DashboardRole::Admin)
            ->assertViewHas('stats', fn (array $stats) => $stats['total_projects'] === 2
                && $stats['total_users'] === 2
                && $stats['active_projects'] === 1
                && $stats['completed_projects'] === 1
                && $stats['total_tasks'] === 3
                && $stats['overdue_tasks'] === 1
                && $stats['tasks_todo'] === 1
                && $stats['tasks_in_progress'] === 1
                && $stats['tasks_completed'] === 1)
            ->assertSee('Proyek Aktif Admin')
            ->assertSee('Proyek Selesai Admin')
            ->assertSee(__('dashboard.admin.recent_projects'));
    }

    public function test_manager_dashboard_only_uses_projects_where_the_user_is_manager(): void
    {
        $manager = User::factory()->member()->create();

        $managed = Project::factory()->active()->create([
            'title' => 'Proyek Dikelola',
        ]);
        $managed->members()->attach($manager->id, ['role' => ProjectRole::Manager->value]);

        $followed = Project::factory()->active()->create([
            'title' => 'Proyek Diikuti Sebagai Anggota',
        ]);
        $followed->members()->attach($manager->id, ['role' => ProjectRole::Member->value]);

        $foreign = Project::factory()->active()->create([
            'title' => 'Proyek Asing',
        ]);

        Task::factory()->for($managed)->create([
            'created_by' => $manager->id,
            'assigned_to' => $manager->id,
            'title' => 'Task proyek dikelola',
            'due_date' => today()->subDay(),
        ]);
        Task::factory()->for($followed)->create([
            'created_by' => $manager->id,
            'title' => 'Task proyek diikuti',
        ]);
        Task::factory()->for($foreign)->create([
            'created_by' => $manager->id,
            'title' => 'Task proyek asing',
        ]);

        ActivityLog::factory()->create([
            'project_id' => $managed->id,
            'user_id' => $manager->id,
            'properties' => ['title' => 'Aktivitas proyek dikelola'],
        ]);
        ActivityLog::factory()->create([
            'project_id' => $followed->id,
            'user_id' => $manager->id,
            'properties' => ['title' => 'Aktivitas proyek diikuti'],
        ]);
        ActivityLog::factory()->create([
            'project_id' => $foreign->id,
            'user_id' => $manager->id,
            'properties' => ['title' => 'Aktivitas proyek asing'],
        ]);

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('role', DashboardRole::Manager)
            ->assertViewHas('stats', fn (array $stats) => $stats['managed_projects'] === 1
                && $stats['total_tasks'] === 1
                && $stats['todo'] === 1
                && $stats['overdue_tasks'] === 1)
            ->assertSee('Proyek Dikelola')
            ->assertSee('Task proyek dikelola')
            ->assertSee('Aktivitas proyek dikelola')
            ->assertDontSee('Proyek Diikuti Sebagai Anggota')
            ->assertDontSee('Task proyek diikuti')
            ->assertDontSee('Aktivitas proyek diikuti')
            ->assertDontSee('Proyek Asing')
            ->assertDontSee('Task proyek asing')
            ->assertDontSee('Aktivitas proyek asing');
    }

    public function test_global_manager_role_without_project_pivot_uses_member_dashboard(): void
    {
        $user = User::factory()->manager()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('role', DashboardRole::Member)
            ->assertViewHas('stats', fn (array $stats) => $stats['assigned_tasks'] === 0
                && $stats['my_tasks'] === 0
                && $stats['overdue_tasks'] === 0);
    }

    public function test_member_without_projects_sees_an_empty_dashboard(): void
    {
        $member = User::factory()->member()->create();

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('role', DashboardRole::Member)
            ->assertViewHas('stats', fn (array $stats) => $stats['active_projects'] === 0
                && $stats['assigned_tasks'] === 0
                && $stats['overdue_tasks'] === 0
                && $stats['completed_this_week'] === 0)
            ->assertViewHas('myTasks', fn ($tasks) => $tasks->isEmpty())
            ->assertViewHas('recentProjects', fn ($projects) => $projects->isEmpty())
            ->assertViewHas('activities', fn ($activities) => $activities->isEmpty())
            ->assertSee(__('dashboard.my_tasks_empty'))
            ->assertSee(__('dashboard.projects_empty'))
            ->assertSee(__('dashboard.activity_empty'));
    }

    public function test_member_dashboard_excludes_tasks_from_projects_not_followed(): void
    {
        $member = User::factory()->member()->create();

        $followed = Project::factory()->active()->create(['title' => 'Proyek Saya']);
        $followed->members()->attach($member->id, ['role' => ProjectRole::Member->value]);

        $foreign = Project::factory()->active()->create(['title' => 'Proyek Orang Lain']);

        Task::factory()->for($followed)->create([
            'created_by' => $member->id,
            'assigned_to' => $member->id,
            'title' => 'Task milik saya',
        ]);
        Task::factory()->for($foreign)->create([
            'created_by' => $member->id,
            'assigned_to' => $member->id,
            'title' => 'Task rahasia orang lain',
            'due_date' => today()->subDay(),
        ]);

        ActivityLog::factory()->create([
            'project_id' => $followed->id,
            'user_id' => $member->id,
            'properties' => ['title' => 'Aktivitas proyek saya'],
        ]);
        ActivityLog::factory()->create([
            'project_id' => $foreign->id,
            'user_id' => $member->id,
            'properties' => ['title' => 'Aktivitas rahasia'],
        ]);

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats['assigned_tasks'] === 1
                && $stats['my_tasks'] === 1
                && $stats['overdue_tasks'] === 0)
            ->assertSee('Task milik saya')
            ->assertSee('Aktivitas proyek saya')
            ->assertDontSee('Proyek Orang Lain')
            ->assertDontSee('Task rahasia orang lain')
            ->assertDontSee('Aktivitas rahasia');
    }

    public function test_manager_statistics_count_overdue_tasks_only_in_managed_projects(): void
    {
        $manager = User::factory()->member()->create();

        $managed = Project::factory()->active()->create();
        $managed->members()->attach($manager->id, ['role' => ProjectRole::Manager->value]);

        Task::factory()->for($managed)->overdue()->create(['created_by' => $manager->id]);
        Task::factory()->for($managed)->completed()->create(['created_by' => $manager->id]);
        Task::factory()->for($managed)->create([
            'created_by' => $manager->id,
            'status' => TaskStatus::InReview,
        ]);

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats['total_tasks'] === 3
                && $stats['todo'] === 0
                && $stats['in_progress'] === 1
                && $stats['in_review'] === 1
                && $stats['completed'] === 1
                && $stats['overdue_tasks'] === 1);
    }
}
