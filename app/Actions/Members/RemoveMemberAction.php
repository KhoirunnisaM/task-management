<?php

namespace App\Actions\Members;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class RemoveMemberAction
{
    public function execute(Project $project, User $member): void
    {
        DB::transaction(function () use ($project, $member) {
            $project->tasks()
                ->where('assigned_to', $member->getKey())
                ->update(['assigned_to' => null]);

            $project->members()->detach($member->getKey());
        });
    }
}
