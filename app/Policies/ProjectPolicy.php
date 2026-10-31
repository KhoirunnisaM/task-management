<?php

namespace App\Policies;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->roleOf($user) !== null;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->roleOf($user) === ProjectRole::Manager;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function manageMembers(User $user, Project $project): bool
    {
        return ! $project->isArchived()
            && ($user->isAdmin() || $project->roleOf($user) === ProjectRole::Manager);
    }

    public function changeMemberRole(User $user, Project $project, User $member): bool
    {
        return $this->manageMembers($user, $project)
            && $project->roleOf($member) === ProjectRole::Member;
    }

    public function removeMember(User $user, Project $project, User $member): bool
    {
        return $this->manageMembers($user, $project)
            && $project->roleOf($member) === ProjectRole::Member;
    }
}
