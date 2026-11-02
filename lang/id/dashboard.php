<?php

return [
    'title' => 'Dashboard',
    'greeting' => 'Halo, :name',
    'subtitle' => 'Ini ringkasan pekerjaan yang menunggu dan proyek yang sedang berjalan.',

    'roles' => [
        'admin' => 'Admin',
        'manager' => 'Manajer',
        'member' => 'Anggota',
    ],

    'subtitles' => [
        'admin' => 'Pantau seluruh proyek, pengguna, dan aktivitas aplikasi.',
        'manager' => 'Ringkasan proyek yang kamu kelola beserta pekerjaan tim.',
        'member' => 'Fokus pada task yang ditugaskan dan proyek yang kamu ikuti.',
    ],

    'actions' => [
        'view_projects' => 'Lihat proyek',
        'my_projects' => 'Proyek saya',
    ],

    'stats' => [
        'total_projects' => 'Total proyek',
        'total_users' => 'Total pengguna',
        'active_projects' => 'Proyek aktif',
        'completed_projects' => 'Proyek selesai',
        'total_tasks' => 'Total task',
        'overdue_tasks' => 'Task terlambat',
        'managed_projects' => 'Proyek dikelola',
        'assigned_tasks' => 'Task ditugaskan',
        'my_tasks' => 'Task saya',
        'todo' => 'Belum dimulai',
        'in_progress' => 'Dikerjakan',
        'in_review' => 'Ditinjau',
        'completed' => 'Selesai',
        'completed_this_week' => 'Selesai minggu ini',
    ],

    'admin' => [
        'recent_projects' => 'Proyek terbaru',
        'recent_projects_hint' => 'Proyek yang terakhir diperbarui.',
        'running_projects' => 'Proyek berjalan',
        'running_projects_hint' => 'Proyek yang sedang aktif dikerjakan.',
        'task_summary' => 'Ringkasan status task',
        'task_summary_hint' => 'Distribusi seluruh task di aplikasi.',
        'activity_hint' => 'Aktivitas terbaru dari seluruh proyek.',
    ],

    'manager' => [
        'projects' => 'Proyek yang dikelola',
        'projects_hint' => 'Proyek dengan peran Manajer untukmu.',
        'projects_empty_body' => 'Proyek yang menetapkanmu sebagai Manajer akan muncul di sini.',
        'latest_tasks' => 'Task terbaru',
        'latest_tasks_hint' => 'Task terbaru pada proyek yang kamu kelola.',
        'due_soon' => 'Mendekati tenggat',
        'due_soon_hint' => 'Task yang tenggatnya dalam 7 hari ke depan.',
        'team' => 'Anggota tim',
        'team_hint' => 'Anggota pada proyek yang kamu kelola.',
        'activity_hint' => 'Aktivitas terbaru pada proyek yang kamu kelola.',
    ],

    'member' => [
        'my_tasks_hint' => 'Task yang ditugaskan kepadamu, diurutkan berdasarkan tenggat.',
        'due_soon' => 'Mendekati tenggat',
        'due_soon_hint' => 'Task yang tenggatnya dalam 7 hari ke depan.',
        'overdue' => 'Task terlambat',
        'overdue_hint' => 'Task yang sudah melewati tenggat dan belum selesai.',
        'projects' => 'Proyek yang saya ikuti',
        'projects_hint' => 'Proyek yang kamu ikuti beserta progresnya.',
        'projects_empty_body' => 'Proyek yang kamu ikuti akan muncul di sini.',
        'activity_hint' => 'Aktivitas terbaru dari proyek yang kamu ikuti.',
    ],

    'my_tasks' => 'Task saya',
    'my_tasks_hint' => 'Diurutkan berdasarkan tenggat terdekat.',
    'my_tasks_empty' => 'Tidak ada task untukmu',
    'my_tasks_empty_body' => 'Task yang ditugaskan kepadamu akan muncul di sini.',

    'tasks_empty' => 'Belum ada task',
    'tasks_empty_body' => 'Task akan muncul di sini setelah dibuat pada proyek terkait.',

    'due_soon_empty' => 'Tidak ada tenggat terdekat',
    'due_soon_empty_body' => 'Task dengan tenggat dalam 7 hari ke depan akan muncul di sini.',

    'overdue_empty' => 'Tidak ada task terlambat',
    'overdue_empty_body' => 'Semua task masih dalam tenggat. Kerja bagus!',

    'team_empty' => 'Belum ada anggota',
    'team_empty_body' => 'Anggota proyek yang kamu kelola akan muncul di sini.',

    'recent_projects' => 'Proyek terbaru',
    'recent_projects_hint' => 'Proyek yang terakhir diperbarui.',
    'projects_empty' => 'Belum ada proyek',
    'projects_empty_body' => 'Buat proyek pertama untuk mulai membagi tugas ke tim.',

    'recent_activity' => 'Aktivitas terbaru',
    'activity_empty' => 'Belum ada aktivitas',
    'activity_empty_body' => 'Perubahan pada task akan tercatat di sini.',

    'task_project' => 'Proyek :project',
];
