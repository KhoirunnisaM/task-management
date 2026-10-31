<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Project;

class ProjectProgressService
{
    public function percentage(Project $project): int
    {
        $total = (int) ($project->tasks_count ?? $project->tasks()->count());

        if ($total === 0) {
            return 0;
        }

        $completed = $this->completedCount($project);

        return (int) round(($completed / $total) * 100);
    }

    public function completedCount(Project $project): int
    {
        return (int) ($project->completed_tasks_count ?? $project->tasks()
            ->where('status', TaskStatus::Completed->value)
            ->count());
    }
}
