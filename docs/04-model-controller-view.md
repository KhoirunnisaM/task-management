# 04 — Model, Controller, View, Route, dan Migration per Fitur

Dokumen ini menjelaskan bagaimana berkas-berkas saling terhubung pada setiap fitur utama. Semua nama file, route, dan kelas diambil langsung dari kode.

## 4.1 Daftar route aktual

`php artisan route:list --except-vendor` menghasilkan 32 route berikut.

| Method | URI | Nama route | Handler |
|---|---|---|---|
| ANY | `/` | — | redirect ke `/dashboard` |
| GET | `/login` | `login` | `Auth\LoginController@create` |
| POST | `/login` | — | `Auth\LoginController@store` |
| GET | `/register` | `register` | `Auth\RegisterController@create` |
| POST | `/register` | — | `Auth\RegisterController@store` |
| GET | `/dashboard` | `dashboard` | `DashboardController` (`__invoke`) |
| POST | `/logout` | `logout` | `Auth\LoginController@destroy` |
| GET | `/admin/members` | `admin.members.index` | `AdminMemberController@index` |
| GET | `/admin/members/{user}` | `admin.members.show` | `AdminMemberController@show` |
| GET | `/projects` | `projects.index` | `ProjectController@index` |
| POST | `/projects` | `projects.store` | `ProjectController@store` |
| GET | `/projects/create` | `projects.create` | `ProjectController@create` |
| GET | `/projects/{project}` | `projects.show` | `ProjectController@show` |
| PUT/PATCH | `/projects/{project}` | `projects.update` | `ProjectController@update` |
| DELETE | `/projects/{project}` | `projects.destroy` | `ProjectController@destroy` |
| GET | `/projects/{project}/board` | `projects.tasks.board` | `TaskController@board` |
| GET | `/projects/{project}/edit` | `projects.edit` | `ProjectController@edit` |
| POST | `/projects/{project}/members` | `projects.members.store` | `ProjectMemberController@store` |
| PATCH | `/projects/{project}/members/{member}` | `projects.members.update` | `ProjectMemberController@update` |
| DELETE | `/projects/{project}/members/{member}` | `projects.members.destroy` | `ProjectMemberController@destroy` |
| GET | `/projects/{project}/tasks` | `projects.tasks.index` | `TaskController@index` |
| POST | `/projects/{project}/tasks` | `projects.tasks.store` | `TaskController@store` |
| GET | `/projects/{project}/tasks/create` | `projects.tasks.create` | `TaskController@create` |
| GET | `/projects/{project}/tasks/{task}` | `projects.tasks.show` | `TaskController@show` |
| PUT/PATCH | `/projects/{project}/tasks/{task}` | `projects.tasks.update` | `TaskController@update` |
| DELETE | `/projects/{project}/tasks/{task}` | `projects.tasks.destroy` | `TaskController@destroy` |
| GET | `/projects/{project}/tasks/{task}/edit` | `projects.tasks.edit` | `TaskController@edit` |
| PATCH | `/projects/{project}/tasks/{task}/status` | `projects.tasks.status` | `TaskController@updateStatus` |
| PATCH | `/projects/{project}/tasks/{task}/reorder` | `projects.tasks.reorder` | `TaskController@reorder` |
| POST | `/projects/{project}/tasks/{task}/comments` | `projects.tasks.comments.store` | `CommentController@store` |
| PATCH | `/projects/{project}/tasks/{task}/comments/{comment}` | `projects.tasks.comments.update` | `CommentController@update` |
| DELETE | `/projects/{project}/tasks/{task}/comments/{comment}` | `projects.tasks.comments.destroy` | `CommentController@destroy` |

Catatan:

- Resource `projects` dan `projects.tasks` dibuat dengan `Route::resource`, sehingga nama route mengikuti pola Laravel.
- Grup tugas dan komentar memakai `Route::scopeBindings()`, sehingga `{task}` harus milik `{project}` dan `{comment}` harus milik `{task}`. Jika tidak cocok, Laravel mengembalikan 404.
- Halaman admin dilindungi `can:viewAny,App\Models\User` (hanya Admin).

## 4.2 Fitur autentikasi

| Komponen | Berkas |
|---|---|
| Route | `GET/POST /login`, `GET/POST /register`, `POST /logout` |
| Controller | `app/Http/Controllers/Auth/LoginController.php`, `RegisterController.php` |
| Form Request | `app/Http/Requests/Auth/LoginRequest.php`, `RegisterRequest.php` |
| Model | `app/Models/User.php` |
| Migration | `0001_01_01_000000_create_users_table.php`, `2026_10_31_000001_add_role_and_avatar_to_users_table.php` |
| View | `resources/views/auth/login.blade.php`, `auth/register.blade.php`, `layouts/guest.blade.php` |
| Policy | Tidak ada; semua guest boleh mengakses halaman login/register |

**Bagaimana terhubung:**

1. `RegisterRequest` menormalkan nama/email (`trim`, lowercase), memvalidasi email unik dan password minimal 8 karakter dengan konfirmasi.
2. `RegisterController@store` membuat `User` dengan `role => UserRole::Member`, lalu redirect ke login. Role yang dikirim pengguna diabaikan (diuji di `RegistrationTest::test_registration_ignores_a_role_sent_by_the_user`).
3. `LoginRequest` memakai `Auth::attempt`, rate limit 5 percobaan per kombinasi email+IP, dan melempar `ValidationException` dengan pesan `auth.failed` bila gagal.
4. `LoginController@store` meregenerasi sesi lalu redirect ke `dashboard` (atau URL yang diminta sebelumnya).
5. `LoginController@destroy` logout guard `web`, invalidasi sesi, regenerasi token CSRF.

## 4.3 Fitur dashboard

| Komponen | Berkas |
|---|---|
| Route | `GET /dashboard` → `dashboard` |
| Controller | `app/Http/Controllers/DashboardController.php` |
| Service | `app/Services/DashboardService.php`, `ProjectProgressService.php` |
| Enum | `app/Enums/DashboardRole.php`, `ProjectRole.php`, `ProjectStatus.php`, `TaskStatus.php` |
| Model | `User`, `Project`, `Task`, `ActivityLog` |
| View | `resources/views/dashboard.blade.php` + `dashboard/admin.blade.php`, `manager.blade.php`, `member.blade.php`; komponen `components/dashboard/*` |
| Migration | `projects`, `project_user`, `tasks`, `activity_logs` |

`DashboardController` memanggil `DashboardService::dashboardFor($user)`. Service menentukan peran tampilan:

```php
public function roleFor(User $user): DashboardRole
{
    if ($user->isAdmin()) {
        return DashboardRole::Admin;
    }

    $managesProject = Project::query()
        ->whereHas('members', fn (Builder $members) => $this->managerPivot($members, $user))
        ->exists();

    return $managesProject ? DashboardRole::Manager : DashboardRole::Member;
}
```

Hasilnya berupa array dengan kunci `role`, `stats`, dan kumpulan data lain sesuai peran. `dashboard.blade.php` meng-include partial sesuai `$role->value`. Setiap partial memakai komponen `x-dashboard.header`, `x-dashboard.stat-strip`, `x-dashboard.panel`, `x-dashboard.project-card`, `x-dashboard.task-row`, dan `x-activity.timeline`.

Statistik Admin menghitung tugas hanya dari proyek yang masih ada:

```php
$tasks = $this->taskCounts(Task::query()->whereHas('project'));
```

Filter ini penting karena `DeleteProjectAction` hanya melakukan soft delete proyek, sedangkan tugasnya tetap tersimpan.

## 4.4 Fitur proyek

| Komponen | Berkas |
|---|---|
| Route | `projects.index`, `projects.create`, `projects.store`, `projects.show`, `projects.edit`, `projects.update`, `projects.destroy` |
| Controller | `app/Http/Controllers/ProjectController.php` |
| Form Request | `app/Http/Requests/Project/{IndexProjectRequest,StoreProjectRequest,UpdateProjectRequest}.php` |
| Action | `app/Actions/Projects/{CreateProjectAction,UpdateProjectAction,DeleteProjectAction}.php` |
| Service | `app/Services/ProjectQueryService.php`, `ProjectProgressService.php` |
| Policy | `app/Policies/ProjectPolicy.php` |
| Model | `app/Models/Project.php`, `ProjectMember.php`, `Task.php`, `ActivityLog.php` |
| Migration | `2026_10_31_000002_create_projects_table.php`, `2026_10_31_000003_create_project_user_table.php` |
| View | `resources/views/projects/{index,show,create,edit}.blade.php`, `projects/partials/form.blade.php`, `components/ui/*` |

**Alur daftar proyek:**

1. `IndexProjectRequest` memvalidasi `search` dan `status`, dan mengizinkan semua user login (`ProjectPolicy::viewAny` selalu `true`).
2. `ProjectQueryService::paginateFor()` memakai scope `Project::forUser($user)`; Admin mendapat semua proyek, non-Admin hanya proyek yang diikuti. Query memuat `owner`, `members`, menghitung `tasks` dan `completed_tasks_count`, lalu `paginate(9)`.
3. Controller menambahkan atribut `progress` dari `ProjectProgressService::percentage()`.
4. View `projects/index.blade.php` menampilkan kartu proyek, status badge, progress bar, periode, jumlah tugas/anggota, avatar stack, dan tombol sesuai `@can`.

**Alur detail proyek:**

1. `ProjectController@show` memanggil `$this->authorize('view', $project)`.
2. Memuat `owner`, `members`, 30 `activityLogs` terbaru, serta `withCount` tugas dan tugas selesai.
3. `projects/show.blade.php` memakai Alpine `tab` untuk tab **Ringkasan**, **Anggota**, **Aktivitas**, plus tautan ke **Board** dan **Daftar tugas**. Tab anggota aktif otomatis jika ada error validasi (`$errors->any()`).

**Alur create/update/delete:**

- `CreateProjectAction` membuka transaksi: membuat proyek dengan `owner_id`, lalu `attach` pembuat ke `project_user` dengan peran `manager`.
- `UpdateProjectAction` hanya memperbarui atribut proyek.
- `DeleteProjectAction` memanggil `$project->delete()` (soft delete). Tidak ada penghapusan tugas.
- `ProjectPolicy::update` mengizinkan Admin atau Manager proyek; `delete` hanya Admin.

## 4.5 Fitur anggota proyek

| Komponen | Berkas |
|---|---|
| Route | `projects.members.store`, `projects.members.update`, `projects.members.destroy` |
| Controller | `app/Http/Controllers/ProjectMemberController.php` |
| Form Request | `app/Http/Requests/Member/{StoreMemberRequest,UpdateMemberRequest}.php` |
| Action | `app/Actions/Members/{AddMemberAction,ChangeMemberRoleAction,RemoveMemberAction}.php` |
| Policy | `ProjectPolicy::manageMembers`, `changeMemberRole`, `removeMember` |
| Model | `Project`, `User`, `ProjectMember` |
| Migration | `project_user` (kolom `id`, `project_id`, `user_id`, `role`, timestamps, unique `project_id+user_id`) |
| View | `resources/views/projects/show.blade.php` (tab anggota) |

**Aturan yang diterapkan Policy:**

- `manageMembers`: proyek tidak arsip **dan** user Admin atau Manager proyek.
- `changeMemberRole` / `removeMember`: hanya untuk anggota yang peran pivot-nya `member`. Manager proyek (termasuk pemilik) tidak dapat diturunkan atau dikeluarkan.

**Aksi:**

- `AddMemberAction` memakai `syncWithoutDetaching` dengan peran default `member`.
- `ChangeMemberRoleAction` memakai `updateExistingPivot`.
- `RemoveMemberAction` dalam satu transaksi mengosongkan `assigned_to` semua tugas anggota tersebut di proyek itu, lalu `detach` pivot. Foreign key `tasks.assigned_to` juga `nullOnDelete`, sehingga konsisten.

## 4.6 Fitur tugas (CRUD, daftar, detail)

| Komponen | Berkas |
|---|---|
| Route | `projects.tasks.index/create/store/show/edit/update/destroy` |
| Controller | `app/Http/Controllers/TaskController.php` |
| Form Request | `app/Http/Requests/Task/{IndexTaskRequest,StoreTaskRequest,UpdateTaskRequest}.php` |
| Action | `CreateTaskAction`, `UpdateTaskAction`, `DeleteTaskAction` |
| Service | `TaskQueryService` |
| Policy | `TaskPolicy` |
| Model | `Task`, `Project`, `User`, `Comment`, `ActivityLog` |
| Migration | `2026_10_31_000004_create_tasks_table.php` |
| View | `tasks/{index,show,create,edit,board}.blade.php`, `tasks/partials/form.blade.php`, `tasks/partials/status-form.blade.php`, `components/task/card.blade.php`, `components/task/due-date.blade.php` |

**Pembuatan tugas:**

1. `TaskController@create` mengotorisasi `create` dan mengirim `new Task`, daftar prioritas, dan proyek dengan anggota.
2. Form `tasks/partials/form.blade.php` mengirim `POST projects/{project}/tasks`.
3. `StoreTaskRequest` memvalidasi:
   - `title` wajib, maks 255;
   - `description` opsional, maks 5000;
   - `priority` wajib dan harus salah satu `TaskPriority`;
   - `due_date` opsional, harus tanggal valid dan `after_or_equal:today`;
   - `assigned_to` opsional, harus ada di `project_user` untuk proyek tersebut.
4. `CreateTaskAction` menghitung posisi terakhir di kolom `todo`, menyimpan tugas dengan status `todo`, lalu mengirim `TaskCreated` dan (bila ada penanggung jawab) `TaskAssigned`.
5. Redirect ke halaman detail tugas dengan flash sukses.

**Perubahan tugas:**

- `UpdateTaskRequest` tidak menerapkan `after_or_equal:today`, sehingga tanggal lama tetap dapat disimpan saat edit.
- `UpdateTaskAction` membandingkan nilai lama dan baru. Perubahan penanggung jawab memicu `TaskAssigned`; perubahan prioritas dan tenggat memicu `TaskUpdated` dengan field terkait. Status tidak diubah di sini — status memiliki route dan action sendiri.

**Daftar tugas:**

- `TaskQueryService::forProject()` menerapkan filter `search`, `status`, `priority`, `assignee` (termasuk `unassigned`), dan `due` (`overdue`, `today`, `week`, `none`), mengurutkan `position` lalu `id`, dan mengelompokkan hasil per status.
- `tasks/index.blade.php` menampilkan empat bagian status dengan kartu tugas, kontrol ubah status, tombol naik/turun urutan, edit, dan hapus sesuai izin.

**Detail tugas:**

- `TaskController@show` memuat assignee, creator, komentar (terlama dulu), dan 30 activity log terbaru.
- `tasks/show.blade.php` menampilkan deskripsi, status, prioritas, tenggat, penanggung jawab, pembuat, waktu dibuat, form komentar, daftar komentar, dan timeline aktivitas.

## 4.7 Fitur Kanban Board

| Komponen | Berkas |
|---|---|
| Route halaman | `GET projects/{project}/board` → `projects.tasks.board` |
| Controller halaman | `TaskController@board` |
| Komponen Livewire | `app/Livewire/Tasks/Board.php` |
| View Livewire | `resources/views/livewire/tasks/board.blade.php` |
| View halaman | `resources/views/tasks/board.blade.php` |
| Action | `UpdateTaskStatusAction` |
| Service | `TaskQueryService` |
| Policy | `TaskPolicy::viewAny`, `updateStatus` |
| JavaScript | `resources/js/app.js` (`Alpine.data('kanbanColumn')`) |

**Alur:**

1. `TaskController@board` mengotorisasi `viewAny` lalu merender `tasks/board.blade.php`.
2. Halaman memanggil `<livewire:tasks.board :project="$project" />`.
3. `Board::mount()` mengotorisasi ulang dan memuat anggota proyek.
4. Properti publik `search`, `assignee`, `priority`, `due` menggerakkan filter; `#[Computed] columns()` mengambil data dari `TaskQueryService`.
5. Setiap kolom memiliki `x-data="kanbanColumn({ status })"`; SortableJS memanggil `moveTask(taskId, status, position)`.
6. `Board::moveTask()` memvalidasi status/posisi, memastikan tugas milik proyek (`$this->project->tasks()->findOrFail($taskId)`), mengotorisasi `updateStatus`, lalu memanggil `UpdateTaskStatusAction`.
7. Alternatif tanpa drag: dropdown `moveTaskToStatus(taskId, status)` pada setiap kartu.

## 4.8 Fitur status dan urutan tugas

| Komponen | Berkas |
|---|---|
| Route | `projects.tasks.status`, `projects.tasks.reorder` |
| Controller | `TaskController@updateStatus`, `TaskController@reorder` |
| Form Request | `UpdateTaskStatusRequest`, `ReorderTaskRequest` |
| Action | `UpdateTaskStatusAction`, `ReorderTasksAction` |
| Policy | `TaskPolicy::updateStatus`, `reorder` |
| View | `tasks/partials/status-form.blade.php`, `tasks/index.blade.php`, `livewire/tasks/board.blade.php` |

`UpdateTaskStatusAction` adalah satu-satunya tempat status diubah. Action ini:

- mengisi `completed_at` saat status menjadi `completed` (mempertahankan nilai lama bila sudah ada), dan mengosongkannya bila keluar dari `completed`;
- menata ulang posisi kolom asal dan kolom tujuan dalam transaksi;
- mengirim `TaskStatusChanged` hanya jika status benar-benar berubah dan `$actor` diberikan.

`ReorderTasksAction` menyusun ulang posisi tugas dalam status yang sama berdasarkan indeks target, lalu menulis `position` berurutan.

## 4.9 Fitur komentar

| Komponen | Berkas |
|---|---|
| Route | `projects.tasks.comments.store/update/destroy` |
| Controller | `app/Http/Controllers/CommentController.php` |
| Form Request | `StoreCommentRequest`, `UpdateCommentRequest` |
| Action | `CreateCommentAction`, `UpdateCommentAction`, `DeleteCommentAction` |
| Policy | `CommentPolicy` |
| Model | `Comment`, `Task`, `User` |
| Migration | `2026_10_31_000005_create_comments_table.php` |
| View | `resources/views/tasks/show.blade.php` (bagian komentar) |

- `CommentPolicy::create`: proyek tidak arsip dan user Admin atau anggota proyek.
- `CommentPolicy::update`/`delete`: proyek tidak arsip dan user Admin atau penulis komentar.
- Body wajib, maks 2000 karakter.
- Setiap operasi membungkus perubahan dalam transaksi dan mengirim event (`CommentCreated`, `CommentUpdated`, `CommentDeleted`) agar activity log tercatat.

## 4.10 Fitur activity log

| Komponen | Berkas |
|---|---|
| Event | `TaskCreated`, `TaskUpdated`, `TaskStatusChanged`, `TaskAssigned`, `CommentCreated`, `CommentUpdated`, `CommentDeleted` |
| Listener | `app/Listeners/LogTaskActivity.php` |
| Model | `app/Models/ActivityLog.php` |
| Migration | `2026_10_31_000006_create_activity_logs_table.php` |
| View | `components/activity/timeline.blade.php`; dipakai di `tasks/show`, `projects/show`, dan dashboard |

`LogTaskActivity` menyediakan method `handleTaskCreated`, `handleTaskStatusChanged`, `handleTaskAssigned`, `handleTaskUpdated`, `handleCommentCreated`, `handleCommentUpdated`, `handleCommentDeleted`. Setiap method memanggil `record()` yang menulis `project_id`, `task_id`, `user_id`, `action`, dan `properties` (JSON). Contoh aksi: `task.created`, `task.status_changed`, `task.assigned`, `task.priority_changed`, `task.due_date_changed`, `comment.created`, `comment.updated`, `comment.deleted`.

Komponen `timeline` memakai helper pada model (`actionLabel`, `subjectTitle`, `fieldLabel`, `oldValueLabel`, `newValueLabel`) untuk menampilkan kalimat yang dapat dibaca.

## 4.11 Fitur notifikasi

| Komponen | Berkas |
|---|---|
| Event | `TaskAssigned` |
| Listener | `app/Listeners/SendTaskAssignedNotification.php` (implements `ShouldQueue`) |
| Notification | `TaskAssignedNotification`, `TaskDueSoonNotification` (channel `database`, queued) |
| Command | `app/Console/Commands/SendDueSoonTaskReminders.php` (`tasks:send-due-soon-reminders`) |
| Scheduler | `routes/console.php` — `dailyAt('08:00')->withoutOverlapping()` |
| Migration | `2026_11_01_000001_create_notifications_table.php` |
| Konfigurasi | `QUEUE_CONNECTION=database` |

Tidak ada tampilan notifikasi di antarmuka. Notifikasi hanya tersimpan di tabel `notifications` dan dapat diperiksa lewat database atau Tinker.

## 4.12 Fitur halaman admin anggota

| Komponen | Berkas |
|---|---|
| Route | `admin.members.index`, `admin.members.show` |
| Controller | `app/Http/Controllers/AdminMemberController.php` |
| Form Request | `app/Http/Requests/User/IndexUserRequest.php` |
| Service | `app/Services/UserQueryService.php` |
| Policy | `app/Policies/UserPolicy.php` (`viewAny`, `view` hanya Admin) |
| Model | `User`, `Project` |
| View | `resources/views/admin/members/index.blade.php`, `show.blade.php` |

Daftar anggota mendukung pencarian nama/email, filter peran global, dan urutan (`joined_desc`, `joined_asc`, `name_asc`, `name_desc`), dengan 10 baris per halaman. Halaman detail menampilkan informasi akun, jumlah proyek, dan daftar proyek beserta peran pivot pengguna.

## 4.13 Ringkasan hubungan antarfile

```
routes/web.php
   │
   ├─ LoginController ── LoginRequest ── User
   ├─ RegisterController ── RegisterRequest ── User
   ├─ DashboardController ── DashboardService ── Project/Task/ActivityLog
   ├─ ProjectController ── ProjectRequest ── ProjectPolicy ── Action Project ── Project/ProjectMember
   ├─ ProjectMemberController ── MemberRequest ── ProjectPolicy ── Action Member ── project_user
   ├─ TaskController ── TaskRequest ── TaskPolicy ── Action Task ── Task
   ├─ CommentController ── CommentRequest ── CommentPolicy ── Action Comment ── Comment
   └─ AdminMemberController ── IndexUserRequest ── UserPolicy ── UserQueryService ── User
                                   │
                                   └─ Event → Listener → activity_logs / notifications
```
