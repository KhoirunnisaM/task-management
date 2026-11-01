<?php

namespace Tests\Feature\Comments;

use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $manager;

    private User $member;

    private User $otherMember;

    private User $outsider;

    private Project $project;

    private Task $task;

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

        $this->task = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
            'assigned_to' => $this->member->id,
        ]);
    }

    public function test_project_members_can_comment_on_a_task(): void
    {
        $this->actingAs($this->member)
            ->post(route('projects.tasks.comments.store', [$this->project, $this->task]), [
                'body' => 'Saya sudah mulai mengerjakan bagian ini.',
            ])
            ->assertRedirect();

        $comment = Comment::query()->sole();

        $this->assertSame($this->task->id, $comment->task_id);
        $this->assertSame($this->member->id, $comment->user_id);
        $this->assertSame('Saya sudah mulai mengerjakan bagian ini.', $comment->body);

        $log = ActivityLog::query()->where('action', 'comment.created')->sole();
        $this->assertSame($this->member->id, $log->user_id);
        $this->assertSame($this->task->id, $log->task_id);

        $this->actingAs($this->member)
            ->get(route('projects.tasks.show', [$this->project, $this->task]))
            ->assertOk()
            ->assertSee('Saya sudah mulai mengerjakan bagian ini.');
    }

    public function test_outsiders_cannot_comment_and_body_is_required(): void
    {
        $this->actingAs($this->outsider)
            ->post(route('projects.tasks.comments.store', [$this->project, $this->task]), [
                'body' => 'Tidak boleh.',
            ])
            ->assertForbidden();

        $this->actingAs($this->member)
            ->from(route('projects.tasks.show', [$this->project, $this->task]))
            ->post(route('projects.tasks.comments.store', [$this->project, $this->task]), [
                'body' => '',
            ])
            ->assertRedirect(route('projects.tasks.show', [$this->project, $this->task]))
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_only_the_author_or_admin_can_update_a_comment(): void
    {
        $comment = $this->commentBy($this->member);

        $payload = ['body' => 'Komentar yang diperbarui.'];

        $this->actingAs($this->otherMember)
            ->patch(route('projects.tasks.comments.update', [$this->project, $this->task, $comment]), $payload)
            ->assertForbidden();

        $this->actingAs($this->manager)
            ->patch(route('projects.tasks.comments.update', [$this->project, $this->task, $comment]), $payload)
            ->assertForbidden();

        $this->actingAs($this->member)
            ->patch(route('projects.tasks.comments.update', [$this->project, $this->task, $comment]), $payload)
            ->assertRedirect();

        $this->assertSame('Komentar yang diperbarui.', $comment->fresh()->body);

        $this->actingAs($this->admin)
            ->patch(route('projects.tasks.comments.update', [$this->project, $this->task, $comment]), [
                'body' => 'Diperbarui oleh admin.',
            ])
            ->assertRedirect();

        $this->assertSame('Diperbarui oleh admin.', $comment->fresh()->body);

        $log = ActivityLog::query()->where('action', 'comment.updated')->latest('id')->firstOrFail();
        $this->assertSame('Komentar yang diperbarui.', $log->properties['old']);
        $this->assertSame('Diperbarui oleh admin.', $log->properties['new']);
    }

    public function test_only_the_author_or_admin_can_delete_a_comment(): void
    {
        $memberComment = $this->commentBy($this->member);
        $adminComment = $this->commentBy($this->admin);

        $this->actingAs($this->otherMember)
            ->delete(route('projects.tasks.comments.destroy', [$this->project, $this->task, $memberComment]))
            ->assertForbidden();

        $this->actingAs($this->member)
            ->delete(route('projects.tasks.comments.destroy', [$this->project, $this->task, $memberComment]))
            ->assertRedirect();

        $this->assertModelMissing($memberComment);

        $this->actingAs($this->admin)
            ->delete(route('projects.tasks.comments.destroy', [$this->project, $this->task, $adminComment]))
            ->assertRedirect();

        $this->assertModelMissing($adminComment);
        $this->assertDatabaseCount('activity_logs', 2);
        $this->assertSame(2, ActivityLog::query()->where('action', 'comment.deleted')->count());
    }

    public function test_archived_projects_block_comment_mutations(): void
    {
        $comment = $this->commentBy($this->member);

        $this->project->update(['status' => ProjectStatus::Archived]);

        $this->actingAs($this->admin)
            ->post(route('projects.tasks.comments.store', [$this->project, $this->task]), [
                'body' => 'Tidak boleh.',
            ])
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->patch(route('projects.tasks.comments.update', [$this->project, $this->task, $comment]), [
                'body' => 'Tidak boleh.',
            ])
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->delete(route('projects.tasks.comments.destroy', [$this->project, $this->task, $comment]))
            ->assertForbidden();

        $this->assertNotSame('Tidak boleh.', $comment->fresh()->body);
    }

    public function test_comment_routes_are_scoped_to_the_task(): void
    {
        $comment = $this->commentBy($this->member);

        $otherTask = Task::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->manager->id,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('projects.tasks.comments.update', [$this->project, $otherTask, $comment]), [
                'body' => 'Salah task.',
            ])
            ->assertNotFound();
    }

    private function commentBy(User $user): Comment
    {
        return Comment::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => $user->id,
            'body' => 'Komentar awal.',
        ]);
    }
}
