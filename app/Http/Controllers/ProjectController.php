<?php

namespace App\Http\Controllers;

use App\Actions\Projects\CreateProjectAction;
use App\Actions\Projects\DeleteProjectAction;
use App\Actions\Projects\UpdateProjectAction;
use App\Enums\ProjectStatus;
use App\Http\Requests\Project\IndexProjectRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectProgressService;
use App\Services\ProjectQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(
        IndexProjectRequest $request,
        ProjectQueryService $query,
        ProjectProgressService $progress,
    ): View {
        $projects = $query->paginateFor($request->user(), $request->validated());

        $projects->getCollection()->each(
            fn (Project $project) => $project->setAttribute('progress', $progress->percentage($project))
        );

        return view('projects.index', [
            'projects' => $projects,
            'filters' => $request->validated(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('projects.create', [
            'project' => new Project,
            'statuses' => ProjectStatus::cases(),
        ]);
    }

    public function store(StoreProjectRequest $request, CreateProjectAction $action): RedirectResponse
    {
        $project = $action->execute($request->user(), $request->validated());

        return redirect()->route('projects.show', $project)
            ->with('success', __('projects.created'));
    }

    public function show(
        Project $project,
        ProjectProgressService $progress,
        ProjectQueryService $query,
    ): View {
        $this->authorize('view', $project);

        $project->load(['owner:id,name', 'members:id,name,email', 'activityLogs' => fn ($logs) => $logs
            ->with('user:id,name')
            ->latest()
            ->limit(30)])
            ->loadCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($tasks) => $tasks
                    ->where('status', 'completed'),
            ]);

        return view('projects.show', [
            'project' => $project,
            'progress' => $progress->percentage($project),
            'completedTasks' => $progress->completedCount($project),
            'eligibleMembers' => $query->eligibleMembers($project),
        ]);
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.edit', [
            'project' => $project,
            'statuses' => ProjectStatus::cases(),
        ]);
    }

    public function update(
        UpdateProjectRequest $request,
        Project $project,
        UpdateProjectAction $action,
    ): RedirectResponse {
        $action->execute($project, $request->validated());

        return redirect()->route('projects.show', $project)
            ->with('success', __('projects.updated'));
    }

    public function destroy(Project $project, DeleteProjectAction $action): RedirectResponse
    {
        $this->authorize('delete', $project);

        $action->execute($project);

        return redirect()->route('projects.index')
            ->with('success', __('projects.deleted'));
    }
}
