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

class TaskListingTest extends TestCase
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

    public function test_project_members_can_view_task_list_and_outsiders_cannot(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'assigned_to' => $this->member->id,
            'title' => 'Task terlihat',
        ]);

        $this->actingAs($this->member)
            ->get(route('projects.tasks.index', $this->project))
            ->assertOk()
            ->assertSee('Task terlihat');

        $this->actingAs($this->member)
            ->get(route('projects.tasks.show', [$this->project, $task]))
            ->assertOk()
            ->assertSee('Task terlihat');

        $this->actingAs($this->outsider)
            ->get(route('projects.tasks.index', $this->project))
            ->assertForbidden();

        $this->actingAs($this->outsider)
            ->get(route('projects.tasks.show', [$this->project, $task]))
            ->assertForbidden();
    }

    public function test_task_list_supports_search(): void
    {
        $alpha = $this->task('Alpha redesign', TaskStatus::Todo, TaskPriority::Low);
        $this->task('Beta migrasi', TaskStatus::Todo, TaskPriority::High);

        $this->actingAs($this->member)
            ->get(route('projects.tasks.index', ['project' => $this->project, 'search' => 'Alpha']))
            ->assertOk()
            ->assertSee($alpha->title)
            ->assertDontSee('Beta migrasi');
    }

    public function test_task_list_supports_status_and_priority_filters(): void
    {
        $this->task('Task todo', TaskStatus::Todo, TaskPriority::Low);
        $this->task('Task selesai', TaskStatus::Completed, TaskPriority::Urgent);

        $this->actingAs($this->member)
            ->get(route('projects.tasks.index', ['project' => $this->project, 'status' => TaskStatus::Completed->value]))
            ->assertOk()
            ->assertSee('Task selesai')
            ->assertDontSee('Task todo');

        $this->actingAs($this->member)
            ->get(route('projects.tasks.index', ['project' => $this->project, 'priority' => TaskPriority::Urgent->value]))
            ->assertOk()
            ->assertSee('Task selesai')
            ->assertDontSee('Task todo');
    }

    public function test_task_list_supports_assignee_filter_including_unassigned(): void
    {
        $assigned = $this->task('Task milik anggota', TaskStatus::Todo, TaskPriority::Medium, $this->member);
        $unassigned = $this->task('Task tanpa assignee', TaskStatus::Todo, TaskPriority::Medium);

        $this->actingAs($this->manager)
            ->get(route('projects.tasks.index', ['project' => $this->project, 'assignee' => $this->member->id]))
            ->assertOk()
            ->assertSee($assigned->title)
            ->assertDontSee($unassigned->title);

        $this->actingAs($this->manager)
            ->get(route('projects.tasks.index', ['project' => $this->project, 'assignee' => 'unassigned']))
            ->assertOk()
            ->assertSee($unassigned->title)
            ->assertDontSee($assigned->title);
    }

    public function test_task_list_supports_due_date_filters_and_shows_overdue_state(): void
    {
        $overdue = $this->task('Task terlambat', TaskStatus::Todo, TaskPriority::High, null, today()->subDays(2));
        $today = $this->task('Task hari ini', TaskStatus::InProgress, TaskPriority::Medium, null, today());
        $noDue = $this->task('Task tanpa tenggat', TaskStatus::Todo, TaskPriority::Low);

        $this->actingAs($this->manager)
            ->get(route('projects.tasks.index', ['project' => $this->project, 'due' => 'overdue']))
            ->assertOk()
            ->assertSee($overdue->title)
            ->assertSee(__('tasks.overdue_days', ['days' => 2]))
            ->assertDontSee($today->title)
            ->assertDontSee($noDue->title);

        $this->actingAs($this->manager)
            ->get(route('projects.tasks.index', ['project' => $this->project, 'due' => 'today']))
            ->assertOk()
            ->assertSee($today->title)
            ->assertDontSee($overdue->title);

        $this->actingAs($this->manager)
            ->get(route('projects.tasks.index', ['project' => $this->project, 'due' => 'none']))
            ->assertOk()
            ->assertSee($noDue->title)
            ->assertDontSee($overdue->title);
    }

    public function test_completed_task_with_past_due_date_is_not_marked_overdue(): void
    {
        $task = $this->task('Task selesai tepat waktu', TaskStatus::Completed, TaskPriority::Low, null, today()->subDays(3));

        $this->actingAs($this->manager)
            ->get(route('projects.tasks.index', ['project' => $this->project, 'search' => 'tepat waktu']))
            ->assertOk()
            ->assertSee($task->title)
            ->assertDontSee(__('tasks.overdue_days', ['days' => 3]));
    }

    public function test_archived_project_task_list_is_read_only_for_admin(): void
    {
        $this->task('Task arsip', TaskStatus::Todo, TaskPriority::Medium);
        $this->project->update(['status' => ProjectStatus::Archived]);

        $this->actingAs($this->admin)
            ->get(route('projects.tasks.index', $this->project))
            ->assertOk()
            ->assertSee('Task arsip')
            ->assertDontSee(__('tasks.create'));
    }

    private function task(
        string $title,
        TaskStatus $status,
        TaskPriority $priority,
        ?User $assignee = null,
        $dueDate = null,
    ): Task {
        return Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'assigned_to' => $assignee?->id,
            'title' => $title,
            'status' => $status,
            'priority' => $priority,
            'due_date' => $dueDate?->toDateString(),
        ]);
    }
}
