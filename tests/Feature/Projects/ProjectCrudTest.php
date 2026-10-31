<?php

namespace Tests\Feature\Projects;

use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_create_a_project(): void
    {
        $this->actingAs($this->admin)
            ->get(route('projects.create'))
            ->assertOk()
            ->assertSee(__('projects.create_title'));

        $response = $this->actingAs($this->admin)->post(route('projects.store'), [
            'title' => 'Website Redesign',
            'description' => 'Perombakan situs perusahaan.',
            'status' => ProjectStatus::Active->value,
            'start_date' => '2026-11-01',
            'end_date' => '2026-12-15',
        ]);

        $project = Project::query()->firstOrFail();

        $response->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'owner_id' => $this->admin->id,
            'title' => 'Website Redesign',
            'status' => ProjectStatus::Active->value,
        ]);
        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $this->admin->id,
            'role' => ProjectRole::Manager->value,
        ]);
    }

    public function test_project_creation_validates_required_fields_and_date_order(): void
    {
        $this->actingAs($this->admin)
            ->from(route('projects.create'))
            ->post(route('projects.store'), [
                'title' => '',
                'status' => 'invalid-status',
                'start_date' => '2026-12-15',
                'end_date' => '2026-11-01',
            ])
            ->assertRedirect(route('projects.create'))
            ->assertSessionHasErrors(['title', 'status', 'end_date']);

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_non_admin_cannot_create_a_project(): void
    {
        $member = User::factory()->member()->create();

        $this->actingAs($member)
            ->get(route('projects.create'))
            ->assertForbidden();

        $this->actingAs($member)
            ->post(route('projects.store'), [
                'title' => 'Proyek terlarang',
                'status' => ProjectStatus::Planning->value,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_index_only_lists_projects_the_user_belongs_to(): void
    {
        $manager = User::factory()->manager()->create();
        $member = User::factory()->member()->create();

        $joined = $this->projectFor($this->admin, 'Proyek Bergabung');
        $joined->members()->attach($member->id, ['role' => ProjectRole::Member->value]);

        $this->projectFor($this->admin, 'Proyek Lain');

        $this->actingAs($member)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Proyek Bergabung')
            ->assertDontSee('Proyek Lain');

        $this->actingAs($this->admin)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Proyek Bergabung')
            ->assertSee('Proyek Lain');

        $this->actingAs($manager)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertDontSee('Proyek Bergabung');
    }

    public function test_project_index_supports_search_and_status_filter(): void
    {
        $active = $this->projectFor($this->admin, 'Website Redesign', ProjectStatus::Active);
        $this->projectFor($this->admin, 'Migrasi Data', ProjectStatus::Archived);

        $this->actingAs($this->admin)
            ->get(route('projects.index', ['search' => 'Website']))
            ->assertOk()
            ->assertSee($active->title)
            ->assertDontSee('Migrasi Data');

        $this->actingAs($this->admin)
            ->get(route('projects.index', ['status' => ProjectStatus::Archived->value]))
            ->assertOk()
            ->assertSee('Migrasi Data')
            ->assertDontSee($active->title);
    }

    public function test_project_manager_can_update_but_not_delete_a_project(): void
    {
        $manager = User::factory()->manager()->create();
        $project = $this->projectFor($this->admin);
        $project->members()->attach($manager->id, ['role' => ProjectRole::Manager->value]);

        $this->actingAs($manager)
            ->get(route('projects.edit', $project))
            ->assertOk()
            ->assertSee(__('projects.edit_title'));

        $this->actingAs($manager)
            ->put(route('projects.update', $project), [
                'title' => 'Judul Baru',
                'status' => ProjectStatus::OnHold->value,
            ])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'title' => 'Judul Baru',
            'status' => ProjectStatus::OnHold->value,
        ]);

        $this->actingAs($manager)
            ->delete(route('projects.destroy', $project))
            ->assertForbidden();

        $this->assertNotSoftDeleted($project);
    }

    public function test_project_member_cannot_update_or_view_foreign_projects(): void
    {
        $member = User::factory()->member()->create();
        $outsider = User::factory()->member()->create();
        $project = $this->projectFor($this->admin);
        $project->members()->attach($member->id, ['role' => ProjectRole::Member->value]);

        $this->actingAs($member)
            ->put(route('projects.update', $project), [
                'title' => 'Tidak boleh',
                'status' => ProjectStatus::Active->value,
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->get(route('projects.show', $project))
            ->assertOk();

        $this->actingAs($outsider)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    }

    public function test_admin_can_soft_delete_a_project(): void
    {
        $project = $this->projectFor($this->admin);

        $this->actingAs($this->admin)
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertSoftDeleted($project);
    }

    public function test_project_detail_shows_progress_percentage(): void
    {
        $project = $this->projectFor($this->admin);

        $project->tasks()->createMany([
            [
                'created_by' => $this->admin->id,
                'title' => 'Task selesai',
                'status' => 'completed',
                'priority' => 'medium',
                'position' => 0,
            ],
            [
                'created_by' => $this->admin->id,
                'title' => 'Task berjalan',
                'status' => 'in_progress',
                'priority' => 'medium',
                'position' => 0,
            ],
        ]);

        $this->actingAs($this->admin)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('50%');
    }

    private function projectFor(
        User $owner,
        string $title = 'Proyek Contoh',
        ProjectStatus $status = ProjectStatus::Active,
    ): Project {
        $project = Project::factory()->create([
            'owner_id' => $owner->id,
            'title' => $title,
            'status' => $status,
        ]);

        $project->members()->attach($owner->id, ['role' => ProjectRole::Manager->value]);

        return $project;
    }
}
