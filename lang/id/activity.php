<?php

return [
    'actions' => [
        'project' => [
            'created' => 'membuat proyek',
            'updated' => 'memperbarui proyek',
            'deleted' => 'menghapus proyek',
            'archived' => 'mengarsipkan proyek',
        ],
        'member' => [
            'added' => 'menambahkan anggota',
            'role_changed' => 'mengubah peran anggota',
            'removed' => 'mengeluarkan anggota',
        ],
        'task' => [
            'created' => 'membuat task',
            'updated' => 'memperbarui task',
            'status_changed' => 'memindahkan task',
            'assigned' => 'mengubah assignee task',
            'priority_changed' => 'mengubah prioritas task',
            'due_date_changed' => 'mengubah tenggat task',
            'deleted' => 'menghapus task',
        ],
        'comment' => [
            'created' => 'berkomentar pada',
            'updated' => 'memperbarui komentar pada',
            'deleted' => 'menghapus komentar pada',
        ],
    ],

    'fields' => [
        'status' => 'Status',
        'priority' => 'Prioritas',
        'due_date' => 'Tenggat',
        'assigned_to' => 'Assignee',
        'body' => 'Komentar',
    ],

    'title' => 'Aktivitas',
    'empty_title' => 'Belum ada aktivitas',
    'empty_body' => 'Perubahan pada task akan tercatat di sini.',
    'from_to' => ':old → :new',
];
