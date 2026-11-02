@php
    $statusSummary = [
        ['status' => App\Enums\TaskStatus::Todo, 'value' => $stats['tasks_todo']],
        ['status' => App\Enums\TaskStatus::InProgress, 'value' => $stats['tasks_in_progress']],
        ['status' => App\Enums\TaskStatus::InReview, 'value' => $stats['tasks_in_review']],
        ['status' => App\Enums\TaskStatus::Completed, 'value' => $stats['tasks_completed']],
    ];

    $icons = [
        'projects' => 'M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-19.5 0A2.25 2.25 0 0 0 4.5 15h15a2.25 2.25 0 0 0 2.25-2.25m-19.5 0v-6A2.25 2.25 0 0 1 4.5 4.5h15a2.25 2.25 0 0 1 2.25 2.25v6',
        'users' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
        'active' => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z',
        'completed' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'tasks' => 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
        'overdue' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    ];
@endphp

<x-dashboard.header :role="$role" />

<x-dashboard.stat-strip
    class="mt-6"
    :items="[
        ['label' => __('dashboard.stats.total_projects'), 'value' => $stats['total_projects'], 'icon' => $icons['projects']],
        ['label' => __('dashboard.stats.total_users'), 'value' => $stats['total_users'], 'icon' => $icons['users']],
        ['label' => __('dashboard.stats.active_projects'), 'value' => $stats['active_projects'], 'icon' => $icons['active']],
        ['label' => __('dashboard.stats.completed_projects'), 'value' => $stats['completed_projects'], 'tone' => 'success', 'icon' => $icons['completed']],
        ['label' => __('dashboard.stats.total_tasks'), 'value' => $stats['total_tasks'], 'icon' => $icons['tasks']],
        ['label' => __('dashboard.stats.overdue_tasks'), 'value' => $stats['overdue_tasks'], 'tone' => $stats['overdue_tasks'] > 0 ? 'danger' : null, 'icon' => $icons['overdue']],
    ]"
/>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <x-dashboard.panel
        class="lg:col-span-2"
        :title="__('dashboard.admin.recent_projects')"
        :hint="__('dashboard.admin.recent_projects_hint')"
        :href="route('projects.index')"
        :link-label="__('projects.all_projects')"
        :empty="$recentProjects->isEmpty()"
        :empty-title="__('dashboard.projects_empty')"
        :empty-body="__('dashboard.projects_empty_body')"
    >
        <div class="mt-3 grid gap-4 sm:grid-cols-2">
            @foreach ($recentProjects as $project)
                <x-dashboard.project-card :project="$project" />
            @endforeach
        </div>
    </x-dashboard.panel>

    <x-dashboard.panel
        :title="__('dashboard.admin.task_summary')"
        :hint="__('dashboard.admin.task_summary_hint')"
    >
        <div class="mt-3 divide-y divide-line rounded-card border border-line bg-surface">
            @foreach ($statusSummary as $row)
                <div class="flex items-center justify-between gap-3 p-4">
                    <x-ui.status-badge :status="$row['status']" />
                    <span class="text-lg font-bold tabular-nums">{{ $row['value'] }}</span>
                </div>
            @endforeach
        </div>
    </x-dashboard.panel>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <x-dashboard.panel
        class="lg:col-span-2"
        :title="__('dashboard.admin.running_projects')"
        :hint="__('dashboard.admin.running_projects_hint')"
        :href="route('projects.index')"
        :link-label="__('projects.all_projects')"
        :empty="$runningProjects->isEmpty()"
        :empty-title="__('dashboard.projects_empty')"
        :empty-body="__('dashboard.projects_empty_body')"
    >
        <div class="mt-3 grid gap-4 sm:grid-cols-2">
            @foreach ($runningProjects as $project)
                <x-dashboard.project-card :project="$project" />
            @endforeach
        </div>
    </x-dashboard.panel>

    <x-dashboard.panel
        :title="__('dashboard.recent_activity')"
        :hint="__('dashboard.admin.activity_hint')"
    >
        <x-activity.timeline
            :logs="$activities"
            :show-project="true"
            :empty-title="__('dashboard.activity_empty')"
            :empty-body="__('dashboard.activity_empty_body')"
        />
    </x-dashboard.panel>
</div>
