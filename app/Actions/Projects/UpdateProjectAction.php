<?php

namespace App\Actions\Projects;

use App\Models\Project;

final class UpdateProjectAction
{
    public function execute(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->refresh();
    }
}
