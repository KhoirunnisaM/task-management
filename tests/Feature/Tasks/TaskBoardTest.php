<?php

namespace Tests\Feature\Tasks;

use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Livewire\Tasks\Board;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TaskBoardTest extends TestCase
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

    public function test_board_page_lists_tasks_for_project_members_and_forbids_outsiders(): void
    {
        $this->task($this->member, title: 'Task terlihat');

        $this->actingAs($this->member)
            ->get(route('projects.tasks.board', $this->project))
            ->assertOk()
            ->assertSee(__('tasks.board_title'))
            ->assertSee('Task terlihat')
            ->assertSee(__('tasks.board_empty_column'));

        $this->actingAs($this->outsider)
            ->get(route('projects.tasks.board', $this->project))
            ->assertForbidden();
    }

    public function test_board_renders_the_four_workflow_columns_in_order(): void
    {
        $this->actingAs($this->member)
            ->get(route('projects.tasks.board', $this->project))
            ->assertOk()
            ->assertSeeInOrder([
                TaskStatus::Todo->label(),
                TaskStatus::InProgress->label(),
                TaskStatus::InReview->label(),
                TaskStatus::Completed->label(),
            ]);
    }

    public function test_board_card_shows_the_expected_task_metadata_and_scopes_tasks_to_the_project(): void
    {
        $task = $this->task(
            $this->member,
            priority: TaskPriority::High,
            due: today()->addDay(),
            title: 'Kartu dengan metadata',
        );

        $foreignProject = Project::factory()->active()->create();
        $foreignTask = Task::factory()->for($foreignProject)->create(['title' => 'Task proyek lain']);

        $this->actingAs($this->member)
            ->get(route('projects.tasks.board', $this->project))
            ->assertOk()
            ->assertSee('TSK-'.$task->id)
            ->assertSee('Kartu dengan metadata')
            ->assertSee(__('tasks.due_tomorrow_label'))
            ->assertSee('Prioritas Tinggi')
            ->assertDontSee($foreignTask->title);
    }

    public function test_move_task_rejects_a_negative_position(): void
    {
        $task = $this->task($this->member, title: 'Task dengan posisi tidak valid');

        Livewire::actingAs($this->manager)
            ->test(Board::class, ['project' => $this->project])
            ->call('moveTask', $task->id, TaskStatus::InProgress->value, -1)
            ->assertHasErrors('position');

        $this->assertSame(TaskStatus::Todo, $task->fresh()->status);
    }

    public function test_admin_can_move_any_task_in_the_project(): void
    {
        $task = $this->task($this->otherMember, title: 'Task anggota lain');

        Livewire::actingAs($this->admin)
            ->test(Board::class, ['project' => $this->project])
            ->call('moveTask', $task->id, TaskStatus::InReview->value, 0)
            ->assertHasNoErrors();

        $this->assertSame(TaskStatus::InReview, $task->fresh()->status);
        $this->assertSame(0, $task->fresh()->position);
    }

    public function test_manager_can_move_a_task_between_columns_with_position(): void
    {
        $existing = $this->task($this->manager, TaskStatus::InProgress, position: 0, title: 'Sudah dikerjakan');
        $task = $this->task($this->manager, TaskStatus::Todo, position: 0, title: 'Akan dikerjakan');

        Livewire::actingAs($this->manager)
            ->test(Board::class, ['project' => $this->project])
            ->call('moveTask', $task->id, TaskStatus::InProgress->value, 0)
            ->assertHasNoErrors();

        $this->assertSame(TaskStatus::InProgress, $task->fresh()->status);
        $this->assertSame(0, $task->fresh()->position);
        $this->assertSame(1, $existing->fresh()->position);
    }

    public function test_same_column_move_updates_positions(): void
    {
        $first = $this->task($this->manager, position: 0, title: 'Task A');
        $second = $this->task($this->manager, position: 1, title: 'Task B');
        $third = $this->task($this->manager, position: 2, title: 'Task C');

        Livewire::actingAs($this->manager)
            ->test(Board::class, ['project' => $this->project])
            ->call('moveTask', $third->id, TaskStatus::Todo->value, 0)
            ->assertHasNoErrors();

        $this->assertSame(0, $third->fresh()->position);
        $this->assertSame(1, $first->fresh()->position);
        $this->assertSame(2, $second->fresh()->position);
    }

    public function test_moving_task_out_reindexes_the_source_column(): void
    {
        $first = $this->task($this->manager, position: 0, title: 'Task A');
        $second = $this->task($this->manager, position: 1, title: 'Task B');
        $third = $this->task($this->manager, position: 2, title: 'Task C');

        Livewire::actingAs($this->manager)
            ->test(Board::class, ['project' => $this->project])
            ->call('moveTask', $first->id, TaskStatus::InProgress->value, 0)
            ->assertHasNoErrors();

        $this->assertSame(0, $second->fresh()->position);
        $this->assertSame(1, $third->fresh()->position);
    }

    public function test_assigned_member_can_move_own_task_but_not_another_members_task(): void
    {
        $ownTask = $this->task($this->member, title: 'Task milik sendiri');
        $otherTask = $this->task($this->otherMember, title: 'Task orang lain');

        Livewire::actingAs($this->member)
            ->test(Board::class, ['project' => $this->project])
            ->call('moveTask', $ownTask->id, TaskStatus::InProgress->value, 0)
            ->assertHasNoErrors();

        $this->assertSame(TaskStatus::InProgress, $ownTask->fresh()->status);

        Livewire::actingAs($this->member)
            ->test(Board::class, ['project' => $this->project])
            ->call('moveTask', $otherTask->id, TaskStatus::Completed->value, 0)
            ->assertStatus(403);

        $this->assertSame(TaskStatus::Todo, $otherTask->fresh()->status);
    }

    public function test_task_can_be_moved_without_drag_through_the_status_menu(): void
    {
        $existing = $this->task($this->manager, TaskStatus::InReview, position: 0, title: 'Sudah ditinjau');
        $task = $this->task($this->manager, title: 'Pindah lewat menu');

        Livewire::actingAs($this->manager)
            ->test(Board::class, ['project' => $this->project])
            ->call('moveTaskToStatus', $task->id, TaskStatus::InReview->value)
            ->assertHasNoErrors();

        $this->assertSame(TaskStatus::InReview, $task->fresh()->status);
        $this->assertSame(1, $task->fresh()->position);
        $this->assertSame(0, $existing->fresh()->position);
    }

    public function test_completed_at_is_managed_when_moving_between_columns(): void
    {
        $task = $this->task($this->member);

        Livewire::actingAs($this->member)
            ->test(Board::class, ['project' => $this->project])
            ->call('moveTask', $task->id, TaskStatus::Completed->value, 0)
            ->assertHasNoErrors();

        $this->assertSame(TaskStatus::Completed, $task->fresh()->status);
        $this->assertNotNull($task->fresh()->completed_at);

        Livewire::actingAs($this->member)
            ->test(Board::class, ['project' => $this->project])
            ->call('moveTask', $task->id, TaskStatus::InReview->value, 0)
            ->assertHasNoErrors();

        $this->assertSame(TaskStatus::InReview, $task->fresh()->status);
        $this->assertNull($task->fresh()->completed_at);
    }

    public function test_archived_project_blocks_moves_even_for_admin(): void
    {
        $task = $this->task($this->manager);
        $this->project->update(['status' => ProjectStatus::Archived]);

        Livewire::actingAs($this->admin)
            ->test(Board::class, ['project' => $this->project])
            ->call('moveTask', $task->id, TaskStatus::Completed->value, 0)
            ->assertStatus(403);

        $this->assertSame(TaskStatus::Todo, $task->fresh()->status);
    }

    public function test_invalid_status_is_rejected_without_changing_the_task(): void
    {
        $task = $this->task($this->manager);

        Livewire::actingAs($this->manager)
            ->test(Board::class, ['project' => $this->project])
            ->call('moveTask', $task->id, 'invalid-status', 0)
            ->assertHasErrors('status');

        $this->assertSame(TaskStatus::Todo, $task->fresh()->status);
    }

    public function test_board_filters_by_search_assignee_priority_and_due_date(): void
    {
        $alpha = $this->task(
            $this->member,
            priority: TaskPriority::High,
            due: today(),
            title: 'Alpha redesign',
        );
        $beta = $this->task(
            $this->otherMember,
            priority: TaskPriority::Low,
            title: 'Beta migrasi',
        );

        $board = Livewire::actingAs($this->manager)
            ->test(Board::class, ['project' => $this->project]);

        $board->set('search', 'Alpha')
            ->assertSee($alpha->title)
            ->assertDontSee($beta->title);

        $board->set('search', '')
            ->set('assignee', (string) $this->member->id)
            ->assertSee($alpha->title)
            ->assertDontSee($beta->title);

        $board->set('assignee', '')
            ->set('priority', TaskPriority::High->value)
            ->assertSee($alpha->title)
            ->assertDontSee($beta->title);

        $board->set('priority', '')
            ->set('due', 'today')
            ->assertSee($alpha->title)
            ->assertDontSee($beta->title);

        $board->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('assignee', '')
            ->assertSet('priority', '')
            ->assertSet('due', '')
            ->assertSee($alpha->title)
            ->assertSee($beta->title);
    }

    private function task(
        User $assignee,
        TaskStatus $status = TaskStatus::Todo,
        int $position = 0,
        TaskPriority $priority = TaskPriority::Medium,
        ?string $title = null,
        $due = null,
    ): Task {
        return Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'assigned_to' => $assignee->id,
            'title' => $title ?? 'Task contoh',
            'status' => $status,
            'priority' => $priority,
            'position' => $position,
            'due_date' => $due?->toDateString(),
        ]);
    }
}
