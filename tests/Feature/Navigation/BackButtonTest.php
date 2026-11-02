<?php

namespace Tests\Feature\Navigation;

use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackButtonTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Project $project;

    private Task $task;

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

        $this->task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_register_page_has_a_back_link_to_login(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee(__('common.back'))
            ->assertSee('href="'.route('login').'"', false);
    }

    public function test_app_pages_have_contextual_back_links(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertSee('href="'.route('dashboard').'"', false);

        $this->actingAs($this->admin)
            ->get(route('projects.index'))
            ->assertSee('href="'.route('dashboard').'"', false);

        $this->actingAs($this->admin)
            ->get(route('projects.create'))
            ->assertSee('href="'.route('projects.index').'"', false);

        $this->actingAs($this->admin)
            ->get(route('projects.show', $this->project))
            ->assertSee('href="'.route('projects.index').'"', false);

        $this->actingAs($this->admin)
            ->get(route('projects.edit', $this->project))
            ->assertSee('href="'.route('projects.show', $this->project).'"', false);

        $this->actingAs($this->admin)
            ->get(route('projects.tasks.index', $this->project))
            ->assertSee('href="'.route('projects.show', $this->project).'"', false);

        $this->actingAs($this->admin)
            ->get(route('projects.tasks.board', $this->project))
            ->assertSee('href="'.route('projects.show', $this->project).'"', false);

        $this->actingAs($this->admin)
            ->get(route('projects.tasks.show', [$this->project, $this->task]))
            ->assertSee('href="'.route('projects.tasks.index', $this->project).'"', false);

        $this->actingAs($this->admin)
            ->get(route('projects.tasks.create', $this->project))
            ->assertSee('href="'.route('projects.tasks.index', $this->project).'"', false);

        $this->actingAs($this->admin)
            ->get(route('projects.tasks.edit', [$this->project, $this->task]))
            ->assertSee('href="'.route('projects.tasks.show', [$this->project, $this->task]).'"', false);

        $this->actingAs($this->admin)
            ->get(route('admin.members.index'))
            ->assertSee('href="'.route('dashboard').'"', false);

        $this->actingAs($this->admin)
            ->get(route('admin.members.show', $this->admin))
            ->assertSee('href="'.route('admin.members.index').'"', false);
    }
}
