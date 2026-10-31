<?php

namespace App\Policies;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->roleOf($user) !== null;
    }

    public function view(User $user, Task $task): bool
    {
        return $user->isAdmin() || $task->project->roleOf($user) !== null;
    }

    public function create(User $user, Project $project): bool
    {
        return ! $project->isArchived()
            && ($user->isAdmin() || $project->roleOf($user) === ProjectRole::Manager);
    }

    public function update(User $user, Task $task): bool
    {
        return ! $task->project->isArchived()
            && ($user->isAdmin() || $task->project->roleOf($user) === ProjectRole::Manager);
    }

    public function updateStatus(User $user, Task $task): bool
    {
        if ($task->project->isArchived()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $role = $task->project->roleOf($user);

        return $role === ProjectRole::Manager
            || ($role === ProjectRole::Member && (int) $task->assigned_to === (int) $user->getKey());
    }

    public function delete(User $user, Task $task): bool
    {
        return ! $task->project->isArchived()
            && ($user->isAdmin() || $task->project->roleOf($user) === ProjectRole::Manager);
    }

    public function reorder(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }
}
