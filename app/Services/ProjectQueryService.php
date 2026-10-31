<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProjectQueryService
{
    public function paginateFor(User $user, array $filters): LengthAwarePaginator
    {
        return Project::query()
            ->forUser($user)
            ->with(['owner:id,name', 'members:id,name,email'])
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($query) => $query
                    ->where('status', TaskStatus::Completed->value),
            ])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where('title', 'like', '%'.$search.'%'))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query
                ->where('status', $status))
            ->latest()
            ->paginate(9)
            ->withQueryString();
    }

    public function eligibleMembers(Project $project): Collection
    {
        return User::query()
            ->whereNotIn('id', $project->members()->select('users.id'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
