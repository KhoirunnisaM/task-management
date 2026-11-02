<?php

namespace App\Services;

use App\Enums\DashboardRole;
use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DashboardService
{
    public function __construct(private readonly ProjectProgressService $progress) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboardFor(User $user): array
    {
        $role = $this->roleFor($user);

        return [
            'role' => $role,
            ...match ($role) {
                DashboardRole::Admin => $this->adminDashboard(),
                DashboardRole::Manager => $this->managerDashboard($user),
                DashboardRole::Member => $this->memberDashboard($user),
            },
        ];
    }

    public function roleFor(User $user): DashboardRole
    {
        if ($user->isAdmin()) {
            return DashboardRole::Admin;
        }

        $managesProject = Project::query()
            ->whereHas('members', fn (Builder $members) => $this->managerPivot($members, $user))
            ->exists();

        return $managesProject ? DashboardRole::Manager : DashboardRole::Member;
    }

    /**
     * @return array<string, mixed>
     */
    private function adminDashboard(): array
    {
        $projects = Project::query()
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as active', [ProjectStatus::Active->value])
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as completed', [ProjectStatus::Completed->value])
            ->first();

        // Batasi agregasi ke task yang proyeknya masih ada. Menghapus proyek
        // bersifat soft delete dan tidak otomatis menghapus task-nya, sehingga
        // tanpa filter ini task dari proyek terhapus tetap ikut terhitung.
        $tasks = $this->taskCounts(
            Task::query()->whereHas('project'),
        );

        return [
            'stats' => [
                'total_projects' => (int) $projects->total,
                'total_users' => User::query()->count(),
                'active_projects' => (int) $projects->active,
                'completed_projects' => (int) $projects->completed,
                'total_tasks' => (int) $tasks->total,
                'overdue_tasks' => (int) $tasks->overdue,
                'tasks_todo' => (int) $tasks->todo,
                'tasks_in_progress' => (int) $tasks->in_progress,
                'tasks_in_review' => (int) $tasks->in_review,
                'tasks_completed' => (int) $tasks->completed,
            ],
            'recentProjects' => $this->projects(
                Project::query()->latest('updated_at')->latest('id'),
                6,
            ),
            'runningProjects' => $this->projects(
                Project::query()
                    ->where('status', ProjectStatus::Active->value)
                    ->orderByRaw('case when end_date is null then 1 else 0 end')
                    ->orderBy('end_date')
                    ->latest('id'),
                5,
            ),
            'activities' => $this->activity(
                ActivityLog::query(),
                12,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function managerDashboard(User $user): array
    {
        $projectIds = $this->managedProjectIds($user);
        $tasks = $this->taskCounts(
            Task::query()->whereIn('project_id', $projectIds),
        );

        return [
            'stats' => [
                'managed_projects' => count($projectIds),
                'total_tasks' => (int) $tasks->total,
                'todo' => (int) $tasks->todo,
                'in_progress' => (int) $tasks->in_progress,
                'in_review' => (int) $tasks->in_review,
                'completed' => (int) $tasks->completed,
                'overdue_tasks' => (int) $tasks->overdue,
            ],
            'managedProjects' => $this->projects(
                Project::query()
                    ->whereIn('id', $projectIds)
                    ->latest('updated_at')
                    ->latest('id'),
                6,
            ),
            'latestTasks' => $this->tasks(
                Task::query()->whereIn('project_id', $projectIds),
                8,
                latest: true,
            ),
            'dueSoonTasks' => $this->dueSoonTasks(
                Task::query()->whereIn('project_id', $projectIds),
            ),
            'teamMembers' => $this->teamMembers($projectIds),
            'activities' => $this->activity(
                ActivityLog::query()->whereIn('project_id', $projectIds),
                10,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function memberDashboard(User $user): array
    {
        $tasks = $this->taskCounts($this->assignedTaskQuery($user));

        return [
            'stats' => [
                'active_projects' => Project::query()
                    ->forUser($user)
                    ->where('status', ProjectStatus::Active->value)
                    ->count(),
                'my_tasks' => (int) $tasks->total - (int) $tasks->completed,
                'overdue_tasks' => (int) $tasks->overdue,
                'completed_this_week' => $this->assignedTaskQuery($user)
                    ->where('status', TaskStatus::Completed->value)
                    ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'assigned_tasks' => (int) $tasks->total,
                'todo' => (int) $tasks->todo,
                'in_progress' => (int) $tasks->in_progress,
                'in_review' => (int) $tasks->in_review,
                'completed' => (int) $tasks->completed,
            ],
            'myTasks' => $this->assignedTaskQuery($user)
                ->where('status', '!=', TaskStatus::Completed->value)
                ->with(['project:id,title', 'assignee:id,name'])
                ->orderByRaw('case when due_date is null then 1 else 0 end')
                ->orderBy('due_date')
                ->orderBy('id')
                ->limit(8)
                ->get(),
            'dueSoonTasks' => $this->dueSoonTasks($this->assignedTaskQuery($user)),
            'overdueTasks' => $this->assignedTaskQuery($user)
                ->overdue()
                ->with(['project:id,title', 'assignee:id,name'])
                ->orderBy('due_date')
                ->orderBy('id')
                ->limit(5)
                ->get(),
            'recentProjects' => $this->projects(
                Project::query()->forUser($user)->latest('updated_at')->latest('id'),
                6,
            ),
            'activities' => $this->activity(
                ActivityLog::query()->whereHas('project', fn (Builder $project) => $project->forUser($user)),
                12,
            ),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function managedProjectIds(User $user): array
    {
        return Project::query()
            ->whereHas('members', fn (Builder $members) => $this->managerPivot($members, $user))
            ->pluck('id')
            ->all();
    }

    private function managerPivot(Builder $members, User $user): Builder
    {
        return $members
            ->whereKey($user->getKey())
            ->where('project_user.role', ProjectRole::Manager->value);
    }

    private function assignedTaskQuery(User $user): Builder
    {
        return Task::query()
            ->where('assigned_to', $user->getKey())
            ->whereHas('project', fn (Builder $project) => $project->forUser($user));
    }

    private function taskCounts(Builder $query): object
    {
        return $query
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as todo', [TaskStatus::Todo->value])
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as in_progress', [TaskStatus::InProgress->value])
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as in_review', [TaskStatus::InReview->value])
            ->selectRaw('sum(case when status = ? then 1 else 0 end) as completed', [TaskStatus::Completed->value])
            ->selectRaw(
                'sum(case when due_date < ? and status != ? then 1 else 0 end) as overdue',
                [today()->toDateString(), TaskStatus::Completed->value],
            )
            ->first();
    }

    /**
     * @return Collection<int, Project>
     */
    private function projects(Builder $query, int $limit): Collection
    {
        $projects = $query
            ->with(['owner:id,name', 'members:id,name'])
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn (Builder $tasks) => $tasks
                    ->where('status', TaskStatus::Completed->value),
            ])
            ->limit($limit)
            ->get();

        $projects->each(
            fn (Project $project) => $project->setAttribute(
                'progress',
                $this->progress->percentage($project),
            )
        );

        return $projects;
    }

    /**
     * @return Collection<int, Task>
     */
    private function tasks(Builder $query, int $limit, bool $latest = false): Collection
    {
        if ($latest) {
            $query->latest('created_at')->latest('id');
        }

        return $query
            ->with(['project:id,title', 'assignee:id,name'])
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Task>
     */
    private function dueSoonTasks(Builder $query): Collection
    {
        return $query
            ->where('status', '!=', TaskStatus::Completed->value)
            ->whereBetween('due_date', [today(), today()->addDays(7)])
            ->with(['project:id,title', 'assignee:id,name'])
            ->orderBy('due_date')
            ->orderBy('id')
            ->limit(5)
            ->get();
    }

    /**
     * @param  array<int, int>  $projectIds
     * @return Collection<int, User>
     */
    private function teamMembers(array $projectIds): Collection
    {
        if ($projectIds === []) {
            return new Collection;
        }

        return User::query()
            ->whereHas('projects', fn (Builder $projects) => $projects->whereIn('projects.id', $projectIds))
            ->orderBy('name')
            ->limit(12)
            ->get();
    }

    /**
     * @return Collection<int, ActivityLog>
     */
    private function activity(Builder $query, int $limit): Collection
    {
        return $query
            ->with([
                'user:id,name',
                'project:id,title',
                'task:id,title',
            ])
            ->latest('created_at')
            ->latest('id')
            ->limit($limit)
            ->get();
    }
}
