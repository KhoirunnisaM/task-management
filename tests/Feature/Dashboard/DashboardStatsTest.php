<?php

namespace Tests\Feature\Dashboard;

use App\Actions\Tasks\DeleteTaskAction;
use App\Actions\Tasks\UpdateTaskStatusAction;
use App\Enums\ProjectRole;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_statistics_follow_the_task_lifecycle(): void
    {
        $admin = User::factory()->admin()->create();

        $project = Project::factory()->active()->create(['owner_id' => $admin->id]);
        $project->members()->attach($admin->id, ['role' => ProjectRole::Manager->value]);

        $task = Task::factory()->for($project)->create([
            'created_by' => $admin->id,
            'status' => TaskStatus::InProgress,
            'due_date' => today()->subDays(2),
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats['total_projects'] === 1
                && $stats['active_projects'] === 1
                && $stats['total_tasks'] === 1
                && $stats['tasks_in_progress'] === 1
                && $stats['tasks_completed'] === 0
                && $stats['overdue_tasks'] === 1);

        // Menambah task langsung terlihat pada permintaan dashboard berikutnya.
        $extra = Task::factory()->for($project)->create([
            'created_by' => $admin->id,
            'status' => TaskStatus::Todo,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertViewHas('stats', fn (array $stats) => $stats['total_tasks'] === 2
                && $stats['tasks_todo'] === 1
                && $stats['overdue_tasks'] === 1);

        // Menyelesaikan task mengubah status dan menghilangkan status terlambat.
        app(UpdateTaskStatusAction::class)->execute($task, TaskStatus::Completed, actor: $admin);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertViewHas('stats', fn (array $stats) => $stats['total_tasks'] === 2
                && $stats['tasks_in_progress'] === 0
                && $stats['tasks_completed'] === 1
                && $stats['overdue_tasks'] === 0);

        // Task yang dihapus (soft delete) tidak lagi dihitung.
        app(DeleteTaskAction::class)->execute($extra);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertViewHas('stats', fn (array $stats) => $stats['total_tasks'] === 1
                && $stats['tasks_todo'] === 0
                && $stats['tasks_completed'] === 1);
    }

    public function test_admin_statistics_ignore_tasks_belonging_to_soft_deleted_projects(): void
    {
        $admin = User::factory()->admin()->create();

        $active = Project::factory()->active()->create(['owner_id' => $admin->id]);
        Task::factory()->for($active)->create(['created_by' => $admin->id]);

        $deleted = Project::factory()->active()->create(['owner_id' => $admin->id]);
        Task::factory()->for($deleted)->overdue()->create(['created_by' => $admin->id]);
        Task::factory()->for($deleted)->completed()->create(['created_by' => $admin->id]);
        $deleted->delete();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats['total_projects'] === 1
                && $stats['total_tasks'] === 1
                && $stats['overdue_tasks'] === 0
                && $stats['tasks_completed'] === 0);
    }

    public function test_manager_statistics_only_cover_projects_they_manage(): void
    {
        $manager = User::factory()->member()->create();

        $managed = Project::factory()->active()->create(['title' => 'Proyek Dikelola']);
        $managed->members()->attach($manager->id, ['role' => ProjectRole::Manager->value]);

        $followed = Project::factory()->active()->create(['title' => 'Proyek Diikuti']);
        $followed->members()->attach($manager->id, ['role' => ProjectRole::Member->value]);

        $managedTask = Task::factory()->for($managed)->overdue()->create(['created_by' => $manager->id]);
        Task::factory()->for($followed)->overdue()->create(['created_by' => $manager->id]);

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertViewHas('stats', fn (array $stats) => $stats['managed_projects'] === 1
                && $stats['total_tasks'] === 1
                && $stats['overdue_tasks'] === 1
                && $stats['todo'] === 0
                && $stats['in_progress'] === 1);

        // Task baru pada proyek yang dikelola menambah angka.
        Task::factory()->for($managed)->create([
            'created_by' => $manager->id,
            'status' => TaskStatus::Todo,
        ]);

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertViewHas('stats', fn (array $stats) => $stats['total_tasks'] === 2
                && $stats['todo'] === 1);

        app(UpdateTaskStatusAction::class)->execute($managedTask, TaskStatus::Completed, actor: $manager);

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertViewHas('stats', fn (array $stats) => $stats['completed'] === 1
                && $stats['overdue_tasks'] === 0);

        app(DeleteTaskAction::class)->execute($managedTask);

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertViewHas('stats', fn (array $stats) => $stats['total_tasks'] === 1
                && $stats['completed'] === 0);
    }

    public function test_member_statistics_follow_their_own_assigned_tasks(): void
    {
        $member = User::factory()->member()->create();

        $project = Project::factory()->active()->create(['title' => 'Proyek Anggota']);
        $project->members()->attach($member->id, ['role' => ProjectRole::Member->value]);

        $task = Task::factory()->for($project)->assignedTo($member)->create([
            'created_by' => $member->id,
            'status' => TaskStatus::InProgress,
            'due_date' => today()->subDay(),
        ]);

        // Task orang lain di proyek yang sama tidak boleh ikut dihitung.
        Task::factory()->for($project)->create([
            'created_by' => $member->id,
            'due_date' => today()->subDay(),
        ]);

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertViewHas('stats', fn (array $stats) => $stats['assigned_tasks'] === 1
                && $stats['my_tasks'] === 1
                && $stats['completed'] === 0
                && $stats['overdue_tasks'] === 1);

        app(UpdateTaskStatusAction::class)->execute($task, TaskStatus::Completed, actor: $member);

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertViewHas('stats', fn (array $stats) => $stats['assigned_tasks'] === 1
                && $stats['my_tasks'] === 0
                && $stats['completed'] === 1
                && $stats['overdue_tasks'] === 0);

        $extra = Task::factory()->for($project)->assignedTo($member)->create([
            'created_by' => $member->id,
        ]);

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertViewHas('stats', fn (array $stats) => $stats['assigned_tasks'] === 2
                && $stats['my_tasks'] === 1);

        app(DeleteTaskAction::class)->execute($extra);

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertViewHas('stats', fn (array $stats) => $stats['assigned_tasks'] === 1
                && $stats['my_tasks'] === 0);
    }

    public function test_member_statistics_ignore_tasks_from_soft_deleted_projects(): void
    {
        $member = User::factory()->member()->create();

        $project = Project::factory()->active()->create();
        $project->members()->attach($member->id, ['role' => ProjectRole::Member->value]);

        Task::factory()->for($project)->assignedTo($member)->create(['created_by' => $member->id]);

        $project->delete();

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertViewHas('stats', fn (array $stats) => $stats['active_projects'] === 0
                && $stats['assigned_tasks'] === 0
                && $stats['overdue_tasks'] === 0);
    }
}
