@php
    $taskStatusSummary = [
        ['status' => App\Enums\TaskStatus::Todo, 'value' => $stats['todo']],
        ['status' => App\Enums\TaskStatus::InProgress, 'value' => $stats['in_progress']],
        ['status' => App\Enums\TaskStatus::InReview, 'value' => $stats['in_review']],
        ['status' => App\Enums\TaskStatus::Completed, 'value' => $stats['completed']],
    ];

    $icons = [
        'projects' => 'M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-19.5 0A2.25 2.25 0 0 0 4.5 15h15a2.25 2.25 0 0 0 2.25-2.25m-19.5 0v-6A2.25 2.25 0 0 1 4.5 4.5h15a2.25 2.25 0 0 1 2.25 2.25v6',
        'tasks' => 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
        'todo' => 'M9 12a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-9a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z',
        'in_progress' => 'M3.75 12h16.5m-16.5 0 4.5-4.5m-4.5 4.5 4.5 4.5',
        'in_review' => 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178ZM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
        'completed' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'overdue' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    ];
@endphp

<x-dashboard.header :role="$role" />

<x-dashboard.stat-strip
    class="mt-6"
    :items="[
        ['label' => __('dashboard.stats.managed_projects'), 'value' => $stats['managed_projects'], 'icon' => $icons['projects']],
        ['label' => __('dashboard.stats.total_tasks'), 'value' => $stats['total_tasks'], 'icon' => $icons['tasks']],
        ['label' => __('dashboard.stats.todo'), 'value' => $stats['todo'], 'icon' => $icons['todo']],
        ['label' => __('dashboard.stats.in_progress'), 'value' => $stats['in_progress'], 'icon' => $icons['in_progress']],
        ['label' => __('dashboard.stats.in_review'), 'value' => $stats['in_review'], 'icon' => $icons['in_review']],
        ['label' => __('dashboard.stats.completed'), 'value' => $stats['completed'], 'tone' => 'success', 'icon' => $icons['completed']],
        ['label' => __('dashboard.stats.overdue_tasks'), 'value' => $stats['overdue_tasks'], 'tone' => $stats['overdue_tasks'] > 0 ? 'danger' : null, 'icon' => $icons['overdue']],
    ]"
/>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <x-dashboard.panel
        class="lg:col-span-2"
        :title="__('dashboard.manager.projects')"
        :hint="__('dashboard.manager.projects_hint')"
        :href="route('projects.index')"
        :link-label="__('dashboard.actions.view_projects')"
        :empty="$managedProjects->isEmpty()"
        :empty-title="__('dashboard.projects_empty')"
        :empty-body="__('dashboard.manager.projects_empty_body')"
    >
        <div class="mt-3 grid gap-4 sm:grid-cols-2">
            @foreach ($managedProjects as $project)
                <x-dashboard.project-card :project="$project" />
            @endforeach
        </div>
    </x-dashboard.panel>

    <x-dashboard.panel
        :title="__('dashboard.recent_activity')"
        :hint="__('dashboard.manager.activity_hint')"
    >
        <x-activity.timeline
            :logs="$activities"
            :show-project="true"
            :empty-title="__('dashboard.activity_empty')"
            :empty-body="__('dashboard.activity_empty_body')"
        />
    </x-dashboard.panel>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <x-dashboard.panel
        class="lg:col-span-2"
        :title="__('dashboard.manager.latest_tasks')"
        :hint="__('dashboard.manager.latest_tasks_hint')"
        :empty="$latestTasks->isEmpty()"
        :empty-title="__('dashboard.tasks_empty')"
        :empty-body="__('dashboard.tasks_empty_body')"
    >
        <div class="mt-3 divide-y divide-line overflow-hidden rounded-card border border-line bg-surface">
            @foreach ($latestTasks as $task)
                <x-dashboard.task-row :task="$task" />
            @endforeach
        </div>
    </x-dashboard.panel>

    <div class="space-y-6">
        <x-dashboard.panel
            :title="__('dashboard.manager.due_soon')"
            :hint="__('dashboard.manager.due_soon_hint')"
            :empty="$dueSoonTasks->isEmpty()"
            :empty-title="__('dashboard.due_soon_empty')"
            :empty-body="__('dashboard.due_soon_empty_body')"
        >
            <div class="mt-3 divide-y divide-line overflow-hidden rounded-card border border-line bg-surface">
                @foreach ($dueSoonTasks as $task)
                    <x-dashboard.task-row :task="$task" />
                @endforeach
            </div>
        </x-dashboard.panel>

        <x-dashboard.panel
            :title="__('dashboard.manager.team')"
            :hint="__('dashboard.manager.team_hint')"
            :empty="$teamMembers->isEmpty()"
            :empty-title="__('dashboard.team_empty')"
            :empty-body="__('dashboard.team_empty_body')"
        >
            <div class="mt-3 divide-y divide-line rounded-card border border-line bg-surface">
                @foreach ($teamMembers as $member)
                    <div class="flex items-center gap-3 p-4">
                        <x-ui.avatar :user="$member" size="md" />

                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">{{ $member->name }}</p>
                            <p class="truncate text-xs text-content-muted">{{ $member->email }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-dashboard.panel>
    </div>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($taskStatusSummary as $row)
        <div class="flex items-center justify-between gap-3 rounded-card border border-line bg-surface p-4">
            <x-ui.status-badge :status="$row['status']" />
            <span class="text-lg font-bold tabular-nums">{{ $row['value'] }}</span>
        </div>
    @endforeach
</div>
