<?php

namespace Tests\Feature\Dashboard;

use App\Enums\ProjectRole;
use App\Enums\TaskStatus;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $member;

    private User $otherMember;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->member = User::factory()->member()->create();
        $this->otherMember = User::factory()->member()->create();

        $this->project = Project::factory()->active()->create([
            'owner_id' => $this->admin->id,
            'title' => 'Proyek Utama',
        ]);

        $this->project->members()->attach($this->admin->id, ['role' => ProjectRole::Manager->value]);
        $this->project->members()->attach($this->member->id, ['role' => ProjectRole::Member->value]);
        $this->project->members()->attach($this->otherMember->id, ['role' => ProjectRole::Member->value]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_my_tasks_ordered_by_due_date(): void
    {
        $later = $this->task(['title' => 'Tugas nanti', 'due_date' => today()->addDays(3), 'assigned_to' => $this->member->id]);
        $sooner = $this->task(['title' => 'Tugas besok', 'due_date' => today()->addDay(), 'assigned_to' => $this->member->id]);
        $noDue = $this->task(['title' => 'Tugas tanpa tenggat', 'due_date' => null, 'assigned_to' => $this->member->id]);
        $other = $this->task(['title' => 'Tugas orang lain', 'assigned_to' => $this->otherMember->id]);
        $this->task(['title' => 'Tugas selesai', 'assigned_to' => $this->member->id, 'status' => TaskStatus::Completed, 'completed_at' => now()]);

        $this->actingAs($this->member)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('myTasks', function ($tasks) use ($later, $sooner, $noDue) {
                return $tasks->pluck('id')->all() === [$sooner->id, $later->id, $noDue->id];
            })
            ->assertSeeInOrder(['Tugas besok', 'Tugas nanti', 'Tugas tanpa tenggat'])
            ->assertDontSee($other->title)
            ->assertDontSee('Tugas selesai');

        $this->assertNotNull($later->fresh());
    }

    public function test_stats_only_count_projects_and_tasks_visible_to_the_user(): void
    {
        $this->task(['assigned_to' => $this->member->id, 'due_date' => today()->addDay()]);
        $this->task(['assigned_to' => $this->member->id, 'due_date' => today()->subDays(2)]);
        $this->task([
            'assigned_to' => $this->member->id,
            'status' => TaskStatus::Completed,
            'completed_at' => now(),
        ]);
        $this->task(['assigned_to' => $this->member->id, 'status' => TaskStatus::Completed, 'completed_at' => now()->subMonth()]);

        $foreignProject = Project::factory()->active()->create(['title' => 'Proyek Asing']);
        Task::factory()->for($foreignProject)->create([
            'assigned_to' => $this->member->id,
            'due_date' => today()->subDays(3),
        ]);

        $this->actingAs($this->member)
            ->get(route('dashboard'))
            ->assertViewHas('stats', function (array $stats) {
                return $stats['active_projects'] === 1
                    && $stats['my_tasks'] === 2
                    && $stats['overdue_tasks'] === 1
                    && $stats['completed_this_week'] === 1;
            });
    }

    public function test_member_does_not_see_other_project_activity_or_projects(): void
    {
        $foreignProject = Project::factory()->active()->create(['title' => 'Proyek Rahasia']);
        $foreignTask = Task::factory()->for($foreignProject)->create(['title' => 'Task Rahasia']);

        ActivityLog::factory()->create([
            'project_id' => $this->project->id,
            'task_id' => null,
            'user_id' => $this->member->id,
            'properties' => ['title' => 'Aktivitas proyek sendiri'],
        ]);

        ActivityLog::factory()->create([
            'project_id' => $foreignProject->id,
            'task_id' => $foreignTask->id,
            'user_id' => $this->member->id,
            'properties' => ['title' => 'Aktivitas proyek asing'],
        ]);

        $this->actingAs($this->member)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Aktivitas proyek sendiri')
            ->assertSee(__('activity.actions.task.created'))
            ->assertDontSee('Aktivitas proyek asing')
            ->assertDontSee('Proyek Rahasia')
            ->assertDontSee('Task Rahasia');
    }

    public function test_admin_sees_all_projects_and_activity(): void
    {
        $foreignProject = Project::factory()->active()->create(['title' => 'Proyek Lain']);

        ActivityLog::factory()->create([
            'project_id' => $foreignProject->id,
            'user_id' => $this->admin->id,
            'properties' => ['title' => 'Aktivitas proyek lain'],
        ]);

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Proyek Lain')
            ->assertSee('Aktivitas proyek lain')
            ->assertSee(__('projects.create'));

        $this->actingAs($this->member)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(__('projects.create'));
    }

    public function test_recent_projects_include_progress_percentage(): void
    {
        $this->task(['assigned_to' => $this->member->id, 'status' => TaskStatus::Completed, 'completed_at' => now()]);
        $this->task(['assigned_to' => $this->member->id]);

        $this->actingAs($this->member)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('recentProjects', function ($projects) {
                $project = $projects->firstWhere('id', $this->project->id);

                return $project !== null && $project->progress === 50;
            });
    }

    public function test_archived_projects_are_not_counted_as_active(): void
    {
        Project::factory()->archived()->create(['owner_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertViewHas('stats', fn (array $stats) => $stats['active_projects'] === 1);
    }

    public function test_dashboard_query_count_does_not_grow_with_more_items(): void
    {
        $task = $this->task(['assigned_to' => $this->member->id, 'due_date' => today()->addDay()]);

        ActivityLog::factory()->create([
            'project_id' => $this->project->id,
            'task_id' => $task->id,
            'user_id' => $this->member->id,
        ]);

        $baseline = $this->dashboardQueryCount();

        for ($index = 0; $index < 5; $index++) {
            $project = Project::factory()->active()->create([
                'owner_id' => $this->admin->id,
                'title' => "Proyek tambahan {$index}",
            ]);

            $project->members()->attach($this->member->id, [
                'role' => ProjectRole::Member->value,
            ]);

            $task = Task::factory()->for($project)->create([
                'created_by' => $this->admin->id,
                'assigned_to' => $this->member->id,
                'due_date' => today()->addDays($index + 1),
            ]);

            ActivityLog::factory()->create([
                'project_id' => $project->id,
                'task_id' => $task->id,
                'user_id' => $this->member->id,
            ]);
        }

        $this->assertSame($baseline, $this->dashboardQueryCount());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function task(array $attributes = []): Task
    {
        return Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->admin->id,
            'status' => TaskStatus::Todo,
            ...$attributes,
        ]);
    }

    private function dashboardQueryCount(): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($this->member)
            ->get(route('dashboard'))
            ->assertOk();

        $count = count(DB::getQueryLog());

        DB::disableQueryLog();

        return $count;
    }
}
