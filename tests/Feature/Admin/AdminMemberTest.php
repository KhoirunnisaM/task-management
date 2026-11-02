<?php

namespace Tests\Feature\Admin;

use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMemberTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create(['name' => 'Admin Utama']);
    }

    public function test_admin_can_see_the_all_members_menu_and_open_the_page(): void
    {
        $member = User::factory()->member()->create(['name' => 'Budi Santoso', 'email' => 'budi@example.test']);

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('common.all_members'))
            ->assertSee(route('admin.members.index'));

        $this->actingAs($this->admin)
            ->get(route('admin.members.index'))
            ->assertOk()
            ->assertSee(__('admin_members.index_title'))
            ->assertSee($this->admin->name)
            ->assertSee($member->name)
            ->assertSee($member->email);
    }

    public function test_manager_and_member_cannot_see_the_menu_or_access_the_page(): void
    {
        $manager = User::factory()->manager()->create();
        $member = User::factory()->member()->create();

        foreach ([$manager, $member] as $user) {
            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertDontSee(route('admin.members.index'));

            $this->actingAs($user)
                ->get(route('admin.members.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('admin.members.show', $this->admin))
                ->assertForbidden();
        }
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.members.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_search_members_by_name_or_email(): void
    {
        $budi = User::factory()->member()->create(['name' => 'Budi Santoso', 'email' => 'budi@example.test']);
        $sari = User::factory()->member()->create(['name' => 'Sari Dewi', 'email' => 'sari@example.test']);

        $this->actingAs($this->admin)
            ->get(route('admin.members.index', ['search' => 'Budi']))
            ->assertOk()
            ->assertSee($budi->name)
            ->assertDontSee($sari->name);

        $this->actingAs($this->admin)
            ->get(route('admin.members.index', ['search' => 'sari@example.test']))
            ->assertOk()
            ->assertSee($sari->name)
            ->assertDontSee($budi->name);
    }

    public function test_admin_can_filter_members_by_global_role(): void
    {
        $manager = User::factory()->manager()->create(['name' => 'Manajer Satu']);
        User::factory()->member()->create(['name' => 'Anggota Satu']);

        $this->actingAs($this->admin)
            ->get(route('admin.members.index', ['role' => UserRole::Manager->value]))
            ->assertOk()
            ->assertViewHas('users', fn ($users) => $users->total() === 1
                && $users->first()->is($manager));
    }

    public function test_admin_can_sort_members_by_name(): void
    {
        User::factory()->member()->create(['name' => 'Zara']);
        User::factory()->member()->create(['name' => 'Ahmad']);

        $this->actingAs($this->admin)
            ->get(route('admin.members.index', ['sort' => 'name_asc']))
            ->assertOk()
            ->assertViewHas('users', fn ($users) => $users->first()->name === 'Admin Utama');

        $this->actingAs($this->admin)
            ->get(route('admin.members.index', ['sort' => 'name_desc']))
            ->assertOk()
            ->assertViewHas('users', fn ($users) => $users->first()->name === 'Zara');
    }

    public function test_members_are_paginated(): void
    {
        User::factory()->count(11)->member()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.members.index'))
            ->assertOk()
            ->assertViewHas('users', fn ($users) => $users->count() === 10
                && $users->total() === 12
                && $users->lastPage() === 2);
    }

    public function test_admin_can_open_member_detail_with_project_roles(): void
    {
        $manager = User::factory()->manager()->create(['name' => 'Rina Manajer']);
        $member = User::factory()->member()->create(['name' => 'Sari Anggota']);

        $project = Project::factory()->create([
            'owner_id' => $this->admin->id,
            'title' => 'Website Redesign',
            'status' => ProjectStatus::Active,
        ]);

        $project->members()->attach($this->admin->id, ['role' => ProjectRole::Manager->value]);
        $project->members()->attach($manager->id, ['role' => ProjectRole::Manager->value]);
        $project->members()->attach($member->id, ['role' => ProjectRole::Member->value]);

        $this->actingAs($this->admin)
            ->get(route('admin.members.show', $member))
            ->assertOk()
            ->assertSee($member->name)
            ->assertSee($member->email)
            ->assertSee(__('admin_members.show_title'))
            ->assertSee('Website Redesign')
            ->assertSee(__('admin_members.project_role'))
            ->assertSee(__('roles.member'))
            ->assertDontSee($member->password);
    }
}
