<?php

namespace App\Services;

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
     * @return array{active_projects: int, my_tasks: int, overdue_tasks: int, completed_this_week: int}
     */
    public function stats(User $user): array
    {
        return [
            'active_projects' => Project::query()
                ->forUser($user)
                ->where('status', ProjectStatus::Active->value)
                ->count(),
            'my_tasks' => $this->myTaskQuery($user)
                ->where('status', '!=', TaskStatus::Completed->value)
                ->count(),
            'overdue_tasks' => $this->myTaskQuery($user)->overdue()->count(),
            'completed_this_week' => $this->myTaskQuery($user)
                ->where('status', TaskStatus::Completed->value)
                ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];
    }

    /**
     * @return Collection<int, Task>
     */
    public function myTasks(User $user, int $limit = 8): Collection
    {
        return $this->myTaskQuery($user)
            ->where('status', '!=', TaskStatus::Completed->value)
            ->with(['project:id,title', 'assignee:id,name'])
            ->orderByRaw('case when due_date is null then 1 else 0 end')
            ->orderBy('due_date')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Project>
     */
    public function recentProjects(User $user, int $limit = 4): Collection
    {
        $projects = Project::query()
            ->forUser($user)
            ->with(['owner:id,name', 'members:id,name'])
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($query) => $query
                    ->where('status', TaskStatus::Completed->value),
            ])
            ->latest('updated_at')
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
     * @return Collection<int, ActivityLog>
     */
    public function recentActivity(User $user, int $limit = 10): Collection
    {
        return ActivityLog::query()
            ->whereHas('project', fn ($query) => $query->forUser($user))
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

    private function myTaskQuery(User $user): Builder
    {
        return Task::query()
            ->where('assigned_to', $user->getKey())
            ->whereHas('project', fn ($query) => $query->forUser($user));
    }
}
