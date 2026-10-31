<?php

namespace App\Actions\Projects;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateProjectAction
{
    public function execute(User $owner, array $data): Project
    {
        return DB::transaction(function () use ($owner, $data) {
            $project = Project::query()->create([
                ...$data,
                'owner_id' => $owner->getKey(),
            ]);

            $project->members()->attach($owner->getKey(), [
                'role' => ProjectRole::Manager->value,
            ]);

            return $project;
        });
    }
}
