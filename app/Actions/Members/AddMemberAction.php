<?php

namespace App\Actions\Members;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class AddMemberAction
{
    public function execute(Project $project, User $user): void
    {
        DB::transaction(function () use ($project, $user) {
            $project->members()->syncWithoutDetaching([
                $user->getKey() => ['role' => ProjectRole::Member->value],
            ]);
        });
    }
}
