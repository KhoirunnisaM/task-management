<?php

namespace App\Actions\Members;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ChangeMemberRoleAction
{
    public function execute(Project $project, User $member, ProjectRole $role): void
    {
        DB::transaction(function () use ($project, $member, $role) {
            $project->members()->updateExistingPivot($member->getKey(), [
                'role' => $role->value,
            ]);
        });
    }
}
