<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Collection;

class TaskQueryService
{
    public function forProject(Project $project, array $filters): Collection
    {
        return $project->tasks()
            ->with(['assignee:id,name', 'creator:id,name'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where('title', 'like', '%'.$search.'%'))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query
                ->where('status', $status))
            ->when($filters['priority'] ?? null, fn ($query, $priority) => $query
                ->where('priority', $priority))
            ->when($filters['assignee'] ?? null, function ($query, $assignee) {
                return $assignee === 'unassigned'
                    ? $query->whereNull('assigned_to')
                    : $query->where('assigned_to', $assignee);
            })
            ->when($filters['due'] ?? null, fn ($query, $due) => match ($due) {
                'overdue' => $query->overdue(),
                'today' => $query->whereDate('due_date', today()),
                'week' => $query->whereDate('due_date', '>=', today())
                    ->whereDate('due_date', '<=', today()->addDays(7)),
                'none' => $query->whereNull('due_date'),
                default => $query,
            })
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Task $task) => $task->status->value);
    }
}
