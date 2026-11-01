<?php

namespace App\Http\Controllers;

use App\Actions\Tasks\CreateTaskAction;
use App\Actions\Tasks\DeleteTaskAction;
use App\Actions\Tasks\ReorderTasksAction;
use App\Actions\Tasks\UpdateTaskAction;
use App\Actions\Tasks\UpdateTaskStatusAction;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\Task\IndexTaskRequest;
use App\Http\Requests\Task\ReorderTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(IndexTaskRequest $request, Project $project, TaskQueryService $query): View
    {
        $filters = $request->validated();

        return view('tasks.index', [
            'project' => $project->load('members:id,name'),
            'tasksByStatus' => $query->forProject($project, $filters),
            'filters' => $filters,
            'hasFilters' => collect($filters)->contains(fn ($value) => $value !== null && $value !== ''),
        ]);
    }

    public function board(Project $project): View
    {
        $this->authorize('viewAny', [Task::class, $project]);

        return view('tasks.board', [
            'project' => $project->load('members:id,name'),
        ]);
    }

    public function create(Project $project): View
    {
        $this->authorize('create', [Task::class, $project]);

        return view('tasks.create', [
            'project' => $project->load('members:id,name'),
            'task' => new Task,
            'priorities' => TaskPriority::cases(),
        ]);
    }

    public function store(
        StoreTaskRequest $request,
        Project $project,
        CreateTaskAction $action,
    ): RedirectResponse {
        $task = $action->execute($project, $request->validated(), $request->user());

        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', __('tasks.created'));
    }

    public function show(Project $project, Task $task): View
    {
        $this->authorize('view', $task);
        $task->load([
            'assignee:id,name',
            'creator:id,name',
            'comments' => fn ($comments) => $comments
                ->with('user:id,name')
                ->oldest(),
            'activityLogs' => fn ($logs) => $logs
                ->with('user:id,name')
                ->latest()
                ->limit(30),
        ]);

        return view('tasks.show', [
            'project' => $project,
            'task' => $task,
            'statuses' => TaskStatus::cases(),
        ]);
    }

    public function edit(Project $project, Task $task): View
    {
        $this->authorize('update', $task);

        return view('tasks.edit', [
            'project' => $project->load('members:id,name'),
            'task' => $task,
            'priorities' => TaskPriority::cases(),
            'statuses' => TaskStatus::cases(),
        ]);
    }

    public function update(
        UpdateTaskRequest $request,
        Project $project,
        Task $task,
        UpdateTaskAction $action,
    ): RedirectResponse {
        $action->execute($task, $request->validated(), $request->user());

        return redirect()->route('projects.tasks.show', [$project, $task])
            ->with('success', __('tasks.updated'));
    }

    public function destroy(
        Project $project,
        Task $task,
        DeleteTaskAction $action,
    ): RedirectResponse {
        $this->authorize('delete', $task);

        $action->execute($task);

        return redirect()->route('projects.tasks.index', $project)
            ->with('success', __('tasks.deleted'));
    }

    public function updateStatus(
        UpdateTaskStatusRequest $request,
        Project $project,
        Task $task,
        UpdateTaskStatusAction $action,
    ): RedirectResponse {
        $action->execute(
            $task,
            TaskStatus::from($request->validated('status')),
            actor: $request->user(),
        );

        return back()->with('success', __('tasks.status_updated'));
    }

    public function reorder(
        ReorderTaskRequest $request,
        Project $project,
        Task $task,
        ReorderTasksAction $action,
    ): RedirectResponse {
        $action->execute($task, (int) $request->validated('position'));

        return back()->with('success', __('tasks.reordered'));
    }
}
