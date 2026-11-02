<?php

namespace Tests\Feature\Tasks;

use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskDueDateFormTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();

        $this->project = Project::factory()->create([
            'owner_id' => $this->admin->id,
            'status' => ProjectStatus::Active,
        ]);

        $this->project->members()->attach($this->admin->id, [
            'role' => ProjectRole::Manager->value,
        ]);
    }

    public function test_create_form_renders_a_real_date_input_with_label(): void
    {
        $this->actingAs($this->admin)
            ->get(route('projects.tasks.create', $this->project))
            ->assertOk()
            ->assertSee('name="due_date"', false)
            ->assertSee('type="date"', false)
            ->assertSee('min="'.today()->toDateString().'"', false)
            ->assertSee(__('tasks.due_date'))
            ->assertSee(__('common.optional'))
            // Regresi: komponen input tidak boleh tertinggal sebagai tag mentah.
            ->assertDontSee('<x-ui.input', false);
    }

    public function test_edit_form_shows_the_stored_due_date_value(): void
    {
        $task = Task::factory()->for($this->project)->create([
            'created_by' => $this->admin->id,
            'due_date' => '2026-12-15',
        ]);

        $this->actingAs($this->admin)
            ->get(route('projects.tasks.edit', [$this->project, $task]))
            ->assertOk()
            ->assertSee('name="due_date"', false)
            ->assertSee('type="date"', false)
            ->assertSee('value="2026-12-15"', false)
            ->assertDontSee('<x-ui.input', false);
    }

    public function test_due_date_is_saved_to_the_database(): void
    {
        $this->actingAs($this->admin)->post(route('projects.tasks.store', $this->project), [
            'title' => 'Task dengan tenggat',
            'priority' => TaskPriority::Medium->value,
            'due_date' => '2026-12-20',
        ])->assertRedirect();

        $task = Task::query()->where('title', 'Task dengan tenggat')->firstOrFail();

        $this->assertSame('2026-12-20', $task->due_date->toDateString());
    }

    public function test_due_date_can_be_left_empty(): void
    {
        $this->actingAs($this->admin)->post(route('projects.tasks.store', $this->project), [
            'title' => 'Task tanpa tenggat',
            'priority' => TaskPriority::Medium->value,
            'due_date' => '',
        ])->assertRedirect();

        $task = Task::query()->where('title', 'Task tanpa tenggat')->firstOrFail();

        $this->assertNull($task->due_date);
    }

    public function test_due_date_validation_rejects_invalid_and_past_dates_on_create(): void
    {
        $this->actingAs($this->admin)
            ->post(route('projects.tasks.store', $this->project), [
                'title' => 'Task tenggat tidak valid',
                'priority' => TaskPriority::Medium->value,
                'due_date' => 'bukan-tanggal',
            ])
            ->assertSessionHasErrors('due_date');

        $this->actingAs($this->admin)
            ->post(route('projects.tasks.store', $this->project), [
                'title' => 'Task tenggat lampau',
                'priority' => TaskPriority::Medium->value,
                'due_date' => today()->subDay()->toDateString(),
            ])
            ->assertSessionHasErrors('due_date');
    }

    public function test_due_date_can_be_updated_and_cleared_on_edit(): void
    {
        $task = Task::factory()->for($this->project)->create([
            'created_by' => $this->admin->id,
            'due_date' => '2026-10-01',
        ]);

        $this->actingAs($this->admin)
            ->put(route('projects.tasks.update', [$this->project, $task]), [
                'title' => $task->title,
                'priority' => TaskPriority::Medium->value,
                'due_date' => '2026-12-31',
            ])
            ->assertRedirect();

        $this->assertSame('2026-12-31', $task->fresh()->due_date->toDateString());

        $this->actingAs($this->admin)
            ->put(route('projects.tasks.update', [$this->project, $task]), [
                'title' => $task->title,
                'priority' => TaskPriority::Medium->value,
                'due_date' => '',
            ])
            ->assertRedirect();

        $this->assertNull($task->fresh()->due_date);
    }
}
