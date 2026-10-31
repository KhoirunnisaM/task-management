<?php

namespace Database\Seeders;

use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $admin = User::factory()->admin()->create([
                'name' => 'Admin Demo',
                'email' => 'admin@taskmanager.test',
            ]);

            $manager = User::factory()->manager()->create([
                'name' => 'Rina Manajer',
                'email' => 'rina@taskmanager.test',
            ]);

            $secondManager = User::factory()->manager()->create([
                'name' => 'Dedi Manajer',
                'email' => 'dedi@taskmanager.test',
            ]);

            $members = collect([
                User::factory()->member()->create([
                    'name' => 'Sari Anggota',
                    'email' => 'sari@taskmanager.test',
                ]),
                User::factory()->member()->create([
                    'name' => 'Budi Anggota',
                    'email' => 'budi@taskmanager.test',
                ]),
                User::factory()->member()->create([
                    'name' => 'Nadia Anggota',
                    'email' => 'nadia@taskmanager.test',
                ]),
                User::factory()->member()->create([
                    'name' => 'Fajar Anggota',
                    'email' => 'fajar@taskmanager.test',
                ]),
            ]);

            $projectDefinitions = [
                [
                    'title' => 'Website Redesign',
                    'description' => 'Perombakan situs perusahaan: struktur konten, desain, dan alur pendaftaran.',
                    'status' => ProjectStatus::Active,
                    'start_date' => now()->subDays(21)->toDateString(),
                    'end_date' => now()->addDays(35)->toDateString(),
                    'manager' => $manager,
                    'members' => [$members[0], $members[1], $members[2]],
                    'tasks' => [
                        [
                            'title' => 'Rancang skema database',
                            'description' => 'Tentukan tabel, relasi, dan index untuk modul konten.',
                            'status' => TaskStatus::InProgress,
                            'priority' => TaskPriority::High,
                            'assignee' => $members[0],
                            'due_days' => 1,
                        ],
                        [
                            'title' => 'Buat halaman login',
                            'description' => 'Bangun form login, validasi, dan pesan error.',
                            'status' => TaskStatus::Todo,
                            'priority' => TaskPriority::Medium,
                            'assignee' => $members[1],
                            'due_days' => 5,
                        ],
                        [
                            'title' => 'Review desain dashboard',
                            'description' => 'Bandingkan hasil implementasi dengan token desain.',
                            'status' => TaskStatus::InReview,
                            'priority' => TaskPriority::Medium,
                            'assignee' => $members[0],
                            'due_days' => 2,
                        ],
                        [
                            'title' => 'Setup CI/CD',
                            'description' => 'Pipeline test dan build otomatis untuk branch utama.',
                            'status' => TaskStatus::Completed,
                            'priority' => TaskPriority::Low,
                            'assignee' => $manager,
                            'due_days' => -1,
                        ],
                        [
                            'title' => 'Dokumentasi API',
                            'description' => 'Catat endpoint, parameter, dan contoh respons.',
                            'status' => TaskStatus::Todo,
                            'priority' => TaskPriority::Low,
                            'assignee' => $members[2],
                            'due_days' => 10,
                        ],
                        [
                            'title' => 'Perbaiki bug navigasi',
                            'description' => 'Menu aktif tidak konsisten saat berpindah halaman.',
                            'status' => TaskStatus::InProgress,
                            'priority' => TaskPriority::Urgent,
                            'assignee' => $members[1],
                            'due_days' => -2,
                        ],
                        [
                            'title' => 'Uji aksesibilitas',
                            'description' => 'Periksa fokus keyboard, kontras, dan label form.',
                            'status' => TaskStatus::InReview,
                            'priority' => TaskPriority::High,
                            'assignee' => $members[2],
                            'due_days' => 3,
                        ],
                    ],
                ],
                [
                    'title' => 'Aplikasi Mobile Internal',
                    'description' => 'Aplikasi pendamping untuk anggota tim lapangan.',
                    'status' => ProjectStatus::Planning,
                    'start_date' => now()->addDays(7)->toDateString(),
                    'end_date' => now()->addDays(90)->toDateString(),
                    'manager' => $secondManager,
                    'members' => [$members[1], $members[2], $members[3]],
                    'tasks' => [
                        [
                            'title' => 'Riset kebutuhan lapangan',
                            'description' => 'Wawancara singkat dengan tiga tim operasional.',
                            'status' => TaskStatus::Todo,
                            'priority' => TaskPriority::High,
                            'assignee' => $members[1],
                            'due_days' => 4,
                        ],
                        [
                            'title' => 'Tentukan ruang lingkup MVP',
                            'description' => 'Pilih fitur yang wajib ada pada rilis pertama.',
                            'status' => TaskStatus::InProgress,
                            'priority' => TaskPriority::Medium,
                            'assignee' => $members[3],
                            'due_days' => 6,
                        ],
                        [
                            'title' => 'Sketsa alur utama',
                            'description' => 'Buat alur login, daftar tugas, dan unggah bukti.',
                            'status' => TaskStatus::Todo,
                            'priority' => TaskPriority::Medium,
                            'assignee' => $members[2],
                            'due_days' => 9,
                        ],
                    ],
                ],
                [
                    'title' => 'Migrasi Data Lama',
                    'description' => 'Arsip proyek migrasi data yang sudah ditutup.',
                    'status' => ProjectStatus::Archived,
                    'start_date' => now()->subMonths(5)->toDateString(),
                    'end_date' => now()->subMonths(2)->toDateString(),
                    'manager' => $manager,
                    'members' => [$members[0], $members[3]],
                    'tasks' => [
                        [
                            'title' => 'Bersihkan data duplikat',
                            'description' => 'Gabungkan kontak ganda dan simpan riwayat perubahan.',
                            'status' => TaskStatus::Completed,
                            'priority' => TaskPriority::High,
                            'assignee' => $members[0],
                            'due_days' => -30,
                        ],
                        [
                            'title' => 'Verifikasi hasil migrasi',
                            'description' => 'Cocokkan jumlah baris dan sampel data lama.',
                            'status' => TaskStatus::Completed,
                            'priority' => TaskPriority::Medium,
                            'assignee' => $members[3],
                            'due_days' => -25,
                        ],
                    ],
                ],
            ];

            foreach ($projectDefinitions as $definition) {
                $project = Project::factory()->create([
                    'owner_id' => $admin->getKey(),
                    'title' => $definition['title'],
                    'description' => $definition['description'],
                    'status' => $definition['status'],
                    'start_date' => $definition['start_date'],
                    'end_date' => $definition['end_date'],
                ]);

                $project->members()->attach($admin->getKey(), [
                    'role' => ProjectRole::Manager->value,
                ]);
                $project->members()->attach($definition['manager']->getKey(), [
                    'role' => ProjectRole::Manager->value,
                ]);

                foreach ($definition['members'] as $member) {
                    $project->members()->attach($member->getKey(), [
                        'role' => ProjectRole::Member->value,
                    ]);
                }

                ActivityLog::factory()->create([
                    'project_id' => $project->getKey(),
                    'task_id' => null,
                    'user_id' => $admin->getKey(),
                    'action' => 'project.created',
                    'properties' => ['title' => $project->title],
                ]);

                $positions = [];

                foreach ($definition['tasks'] as $taskDefinition) {
                    $status = $taskDefinition['status'];
                    $position = $positions[$status->value] ?? 0;
                    $positions[$status->value] = $position + 1;

                    $task = Task::factory()->create([
                        'project_id' => $project->getKey(),
                        'created_by' => $definition['manager']->getKey(),
                        'assigned_to' => $taskDefinition['assignee']->getKey(),
                        'title' => $taskDefinition['title'],
                        'description' => $taskDefinition['description'],
                        'status' => $status,
                        'priority' => $taskDefinition['priority'],
                        'due_date' => now()->addDays($taskDefinition['due_days'])->toDateString(),
                        'position' => $position,
                        'completed_at' => $status === TaskStatus::Completed ? now()->subDay() : null,
                    ]);

                    foreach (collect($definition['members'])->take(2) as $commenter) {
                        Comment::factory()->create([
                            'task_id' => $task->getKey(),
                            'user_id' => $commenter->getKey(),
                        ]);
                    }

                    ActivityLog::factory()->create([
                        'project_id' => $project->getKey(),
                        'task_id' => $task->getKey(),
                        'user_id' => $definition['manager']->getKey(),
                        'action' => 'task.created',
                        'properties' => ['title' => $task->title],
                    ]);

                    ActivityLog::factory()->create([
                        'project_id' => $project->getKey(),
                        'task_id' => $task->getKey(),
                        'user_id' => $definition['manager']->getKey(),
                        'action' => 'task.assigned',
                        'properties' => ['assigned_to' => $task->assigned_to],
                    ]);

                    if ($status !== TaskStatus::Todo) {
                        ActivityLog::factory()->create([
                            'project_id' => $project->getKey(),
                            'task_id' => $task->getKey(),
                            'user_id' => $definition['manager']->getKey(),
                            'action' => 'task.status_changed',
                            'properties' => [
                                'old' => TaskStatus::Todo->value,
                                'new' => $status->value,
                            ],
                        ]);
                    }
                }
            }
        });
    }
}
