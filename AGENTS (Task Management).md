# AGENTS.md: Panduan Developer

Panduan ini untuk **developer manusia dan AI coding agent** (Claude Code, Cursor, Copilot, dll.) yang mengerjakan aplikasi *Task Management Proyek*. Baca dokumen ini sebelum menulis kode. Jika ada konflik antara dokumen ini dan permintaan di prompt, tanyakan dulu, jangan menebak.

Dokumen terkait: [`PRD.md`](./PRD.md) (apa yang dibangun) · [`DESIGN_SYSTEM.md`](./DESIGN_SYSTEM.md) (bagaimana tampilannya).

---

## 1. Konteks Proyek

| Item | Nilai |
|---|---|
| Aplikasi | Project & Task Management (Kanban, tim, peran, status) |
| Framework | Laravel stabil terbaru (PHP 8.3+) |
| Database | MySQL 8 (Laragon) |
| Frontend | Blade + Livewire v3 + Alpine.js + Tailwind CSS (Vite) |
| Arsitektur | Controller tipis → Form Request → Policy → **Action/Service** → Eloquent |
| Bahasa kode | Inggris (class, method, variabel, nama tabel) |
| Bahasa UI | Indonesia (lewat `lang/id`, bukan hardcode di Blade) |

Sebelum menambah fitur, cek ID kebutuhan di `PRD.md` (mis. `FR-33`) dan sebutkan di pesan commit.

## 2. Setup Lokal (Laragon)

```bash
# 1. Buat project di C:\laragon\www
composer create-project laravel/laravel task-manager
cd task-manager

# 2. Konfigurasi .env
#    APP_NAME="Task Manager"
#    APP_URL=http://task-manager.test      (Laragon membuat virtual host otomatis)
#    APP_LOCALE=id
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=task_manager              (buat dulu lewat HeidiSQL/phpMyAdmin)
#    DB_USERNAME=root
#    DB_PASSWORD=
#    QUEUE_CONNECTION=database

# 3. Dependensi
composer require livewire/livewire
composer require laravel/breeze --dev     # lalu: php artisan breeze:install livewire
npm install

# 4. Database
php artisan migrate --seed

# 5. Jalankan
npm run dev                # Vite
php artisan queue:work     # terminal terpisah (notifikasi)
php artisan schedule:work  # terminal terpisah (pengingat tenggat)
```

> Starter kit resmi Laravel dapat berubah antarversi. Ikuti dokumentasi resmi Laravel versi yang terpasang jika perintah Breeze berbeda.

### Perintah harian

| Tujuan | Perintah |
|---|---|
| Format kode | `./vendor/bin/pint` |
| Jalankan test | `php artisan test` (atau `./vendor/bin/pest`) |
| Reset DB + data demo | `php artisan migrate:fresh --seed` |
| Cek rute | `php artisan route:list` |
| Cek N+1 | Aktifkan `Model::preventLazyLoading(! app()->isProduction())` di `AppServiceProvider` |

## 3. Struktur Folder

```
app/
├── Actions/                 # Satu kelas = satu aksi bisnis
│   ├── Projects/            #   CreateProjectAction, UpdateProjectAction, ArchiveProjectAction
│   ├── Members/             #   AddMemberAction, ChangeMemberRoleAction, RemoveMemberAction
│   └── Tasks/               #   CreateTaskAction, UpdateTaskAction, UpdateTaskStatusAction, ReorderTasksAction
├── Enums/                   # TaskStatus, TaskPriority, ProjectStatus, ProjectRole, UserRole
├── Events/                  # TaskCreated, TaskStatusChanged, TaskAssigned
├── Listeners/               # LogTaskActivity, NotifyAssignee
├── Http/
│   ├── Controllers/         # ProjectController, TaskController, MemberController
│   └── Requests/            # Project/, Task/, Member/ (Store*Request, Update*Request)
├── Livewire/
│   ├── Dashboard/           # Overview
│   ├── Projects/            # Index, Show
│   └── Tasks/               # Board, TaskModal, CommentList
├── Models/                  # User, Project, ProjectMember (pivot), Task, Comment, ActivityLog
├── Notifications/           # TaskAssignedNotification, TaskDueSoonNotification
├── Policies/                # ProjectPolicy, TaskPolicy, CommentPolicy
└── Services/                # DashboardService, ProjectProgressService (query/orkestrasi baca)
database/
├── factories/
├── migrations/
└── seeders/                 # DatabaseSeeder, DemoDataSeeder
lang/id/                     # Teks UI (status, prioritas, pesan)
resources/views/
├── components/              # Blade components (ui/button, ui/badge, task/card, ...)
├── layouts/                 # app.blade.php, guest.blade.php
└── livewire/                # View untuk komponen Livewire
tests/
├── Feature/                 # Alur HTTP/Livewire + otorisasi
└── Unit/                    # Action & Enum
```

## 4. Aturan Arsitektur

| Lapisan | Boleh | Dilarang |
|---|---|---|
| **Route** | Mendefinisikan URL, middleware, route-model binding | Logika apa pun |
| **Controller / Livewire** | Menerima input, memanggil Action, mengembalikan response/state | Query kompleks, aturan bisnis, `DB::transaction` |
| **Form Request** | Validasi + `authorize()` lewat Policy | Menulis ke database |
| **Policy** | Keputusan boleh/tidak berdasarkan peran | Query berat, efek samping |
| **Action** | **Satu** aturan bisnis, transaksi DB, dispatch event | Mengakses `request()`, session, atau view |
| **Service** | Menggabungkan beberapa query untuk dibaca (dashboard, statistik) | Mengubah data (pakai Action) |
| **Model** | Relasi, scope, cast, accessor sederhana | Orkestrasi lintas model |
| **Blade** | Menampilkan data yang sudah siap | Query, `@php` panjang, aturan bisnis |

Aturan praktis:

- Method Controller maksimal ±10 baris. Jika lebih, pindahkan ke Action.
- Action punya **satu** method publik: `execute()`. Dependency lewat constructor, data lewat argumen yang bertipe jelas.
- Semua perubahan status task **harus** lewat `UpdateTaskStatusAction`.
- Efek samping (log, notifikasi) lewat **Event + Listener**, bukan dipanggil langsung di Action.

## 5. Konvensi

**Penamaan**

- Model singular (`Task`), tabel plural snake_case (`tasks`), pivot `project_user`.
- Action diawali kata kerja: `CreateTaskAction`, bukan `TaskCreator`.
- Enum PascalCase untuk case, nilai string snake_case: `TaskStatus::InProgress = 'in_progress'`.
- Route bernama: `projects.show`, `projects.tasks.store`.

**Database**

- Migration baru untuk setiap perubahan. Jangan edit migration yang sudah dijalankan di environment bersama.
- Foreign key dengan `constrained()` dan `cascadeOnDelete()` / `nullOnDelete()` yang disengaja.
- Tambahkan index sesuai PRD bagian 10. Kolom status/prioritas disimpan sebagai `string`, bukan `enum` database.
- Soft delete untuk `projects` dan `tasks`.

**Model**

- Selalu isi `$fillable`. **Jangan** `$guarded = []`.
- Cast enum dan tanggal lewat `casts()`.

## 6. Pola Kode

### 6.1 Enum

```php
<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case InReview = 'in_review';
    case Completed = 'completed';

    public function label(): string
    {
        return __("tasks.status.{$this->value}");
    }
}
```

`TaskPriority` (`low`, `medium`, `high`, `urgent`), `ProjectStatus`, `ProjectRole` (`Admin`, `manager`, `member`) dan `UserRole` (`admin`, `Manager`, `Member`) mengikuti pola yang sama.

### 6.2 Migration (tasks)

```php
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained()->cascadeOnDelete();
    $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
    $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('status')->default('todo');
    $table->string('priority')->default('medium');
    $table->date('due_date')->nullable();
    $table->unsignedInteger('position')->default(0);
    $table->timestamp('completed_at')->nullable();
    $table->softDeletes();
    $table->timestamps();

    $table->index(['project_id', 'status', 'position']);
    $table->index('assigned_to');
    $table->index('due_date');
});
```

### 6.3 Model dan relasi

```php
class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['admin_id', 'title', 'description', 'status', 'start_date', 'end_date'];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(ProjectMember::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $user->isAdmin()
            ? $query
            : $query->whereHas('members', fn ($q) => $q->whereKey($user->id));
    }

    public function isadmin(User $user): bool
    {
        return $this->admin_id === $user->id;
    }

    public function roleOf(User $user): ?ProjectRole
    {
        return $this->members->firstWhere('id', $user->id)?->pivot->role;
    }

    public function canManage(User $user): bool
    {
        return $this->isadmin($user) || $this->roleOf($user) === ProjectRole::Manager;
    }
}
```

`ProjectMember extends Pivot` dengan `$table = 'project_user'`, `$incrementing = true`, dan cast `role => ProjectRole::class`. Pastikan admin juga tercatat di pivot saat proyek dibuat (lihat `CreateProjectAction`).

### 6.4 Action

```php
<?php

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Events\TaskStatusChanged;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateTaskStatusAction
{
    public function execute(Task $task, TaskStatus $status, User $actor, ?int $position = null): Task
    {
        return DB::transaction(function () use ($task, $status, $actor, $position) {
            $previous = $task->status;

            $task->fill([
                'status' => $status,
                'completed_at' => $status === TaskStatus::Completed ? now() : null,
            ]);

            if ($position !== null) {
                $task->position = $position;
            }

            $task->save();

            if ($previous !== $status) {
                event(new TaskStatusChanged($task, $previous, $status, $actor));
            }

            return $task;
        });
    }
}
```

### 6.5 Form Request

```php
<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [Task::class, $this->route('project')]);
    }

    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'assigned_to' => [
                'nullable',
                Rule::exists('project_user', 'user_id')->where('project_id', $project->id),
            ],
        ];
    }
}
```

### 6.6 Policy

```php
<?php

namespace App\Policies;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function create(User $user, Project $project): bool
    {
        return ! $project->isArchived()
            && ($project->canManage($user) || $project->roleOf($user) === ProjectRole::Member);
    }

    public function update(User $user, Task $task): bool
    {
        $project = $task->project;

        if ($project->isArchived()) {
            return false;
        }

        return $project->canManage($user)
            || ($project->roleOf($user) === ProjectRole::Member
                && ($task->assigned_to === $user->id || $task->created_by === $user->id));
    }

    public function updateStatus(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return ! $task->project->isArchived()
            && ($task->project->canManage($user) || $task->created_by === $user->id);
    }
}
```

> `before()` mengembalikan `null` (bukan `false`) agar non-admin tetap dinilai oleh method policy. Untuk admin, tetap tolak aksi pada proyek *archived* bila PRD mengharuskannya.

### 6.7 Controller tipis

```php
class TaskController extends Controller
{
    public function store(StoreTaskRequest $request, Project $project, CreateTaskAction $action): RedirectResponse
    {
        $action->execute($project, $request->validated(), $request->user());

        return back()->with('success', __('tasks.created'));
    }

    public function destroy(Project $project, Task $task, DeleteTaskAction $action): RedirectResponse
    {
        $this->authorize('delete', $task);
        $action->execute($task);

        return back()->with('success', __('tasks.deleted'));
    }
}
```

### 6.8 Komponen Livewire (Kanban Board)

```php
class Board extends Component
{
    public Project $project;

    public function mount(Project $project): void
    {
        $this->authorize('view', $project);
        $this->project = $project;
    }

    #[Computed]
    public function columns(): Collection
    {
        $tasks = $this->project->tasks()
            ->with('assignee:id,name,avatar')
            ->orderBy('position')
            ->get()
            ->groupBy(fn (Task $t) => $t->status->value);

        return collect(TaskStatus::cases())
            ->mapWithKeys(fn ($s) => [$s->value => $tasks->get($s->value, collect())]);
    }

    public function moveTask(int $taskId, string $status, int $position, UpdateTaskStatusAction $action): void
    {
        $task = $this->project->tasks()->findOrFail($taskId);

        $this->authorize('updateStatus', $task); // wajib: method Livewire = endpoint publik

        $action->execute($task, TaskStatus::from($status), auth()->user(), $position);

        unset($this->columns);
    }

    public function render(): View
    {
        return view('livewire.tasks.board');
    }
}
```

Aturan Livewire:

- **Setiap method publik wajib memanggil `authorize()`** karena dapat dipanggil dari browser.
- Validasi input dengan Form Object atau `#[Validate]`; jangan percaya `$status` mentah (`TaskStatus::from()` akan melempar error untuk nilai tidak valid).
- Gunakan `wire:key` pada setiap item dalam loop.
- Jangan simpan koleksi besar sebagai public property; gunakan `#[Computed]`.
- Gunakan `wire:model.live.debounce.300ms` untuk pencarian, `wire:model` (deferred) untuk form.

## 7. Otorisasi

- Semua listing proyek lewat scope `Project::forUser($user)`.
- Route-model binding untuk task harus di-scope ke proyek: `Route::scopeBindings()` pada grup `projects/{project}/tasks/{task}`.
- Daftarkan Policy (auto-discovery sudah cukup jika nama mengikuti konvensi).
- Setiap fitur baru harus punya test otorisasi untuk minimal: admin, Member, dan non-anggota.

## 8. Performa

- Eager load relasi yang dipakai di view (`with()`); aktifkan `preventLazyLoading` di lokal.
- Gunakan `withCount` untuk statistik: `Project::withCount(['tasks', 'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'completed')])`.
- Paginate daftar panjang (proyek, aktivitas); Kanban memuat per kolom dengan batas wajar dan tombol "muat lagi".
- Pilih kolom seperlunya pada relasi (`assignee:id,name,avatar`).

## 9. Activity Log, Event, dan Queue

| Event | Listener | Efek |
|---|---|---|
| `TaskCreated` | `LogTaskActivity` | Catat `task.created` |
| `TaskStatusChanged` | `LogTaskActivity` | Catat `task.status_changed` dengan `old`/`new` di `properties` |
| `TaskAssigned` | `LogTaskActivity`, `NotifyAssignee` | Log + notifikasi (queued) |

- Listener yang mengirim notifikasi mengimplementasikan `ShouldQueue`.
- Pengingat tenggat H-1: Artisan command terjadwal di `routes/console.php`, mengirim notifikasi lewat queue.

## 10. Testing

Minimal yang harus ada per fitur: **happy path**, **validasi gagal**, **otorisasi ditolak**.

```php
it('lets a member change the status of their assigned task', function () {
    $project = Project::factory()->hasMembers(1, ['role' => 'member'])->create();
    $member = $project->members->first();
    $task = Task::factory()->for($project)->create(['assigned_to' => $member->id]);

    Livewire::actingAs($member)
        ->test(Board::class, ['project' => $project])
        ->call('moveTask', $task->id, 'in_progress', 0);

    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);
});

it('blocks a member from changing task status', function () {
    $project = Project::factory()->hasMembers(1, ['role' => 'member'])->create();
    $member = $project->members->first();
    $task = Task::factory()->for($project)->create();

    Livewire::actingAs($member)
        ->test(Board::class, ['project' => $project])
        ->call('moveTask', $task->id, 'completed', 0)
        ->assertForbidden();
});
```

Gunakan database test terpisah (`DB_DATABASE=task_manager_test`) atau SQLite in-memory untuk kecepatan.

## 11. Git dan Commit

- Branch: `feature/FR-33-kanban-drag-drop`, `fix/task-overdue-badge`.
- Commit mengikuti Conventional Commits: `feat(tasks): add drag-and-drop status change (FR-33)`.
- Jangan commit `.env`, `vendor/`, `node_modules/`, atau file build.
- Satu PR = satu fitur/perbaikan; sertakan screenshot untuk perubahan UI.

## 12. Alur Kerja untuk AI Agent

1. **Baca** bagian terkait di `PRD.md` dan aturan di dokumen ini. Jika kebutuhan ambigu, ajukan pertanyaan singkat sebelum mengubah banyak file.
2. **Rencanakan** perubahan: daftar file yang akan dibuat/diubah (migration, model, action, request, policy, view, test).
3. **Implementasikan** berurutan: migration → enum/model → action → request/policy → controller/Livewire → view → test.
4. **Verifikasi**: `./vendor/bin/pint` lalu `php artisan test`. Laporkan hasil nyata, jangan mengklaim lolos tanpa menjalankan.
5. **Ringkas**: jelaskan apa yang berubah, keputusan yang diambil, dan hal yang belum dikerjakan.

### Definition of Done

- [ ] Sesuai ID kebutuhan di PRD
- [ ] Validasi lewat Form Request/Form Object
- [ ] Otorisasi lewat Policy + test untuk peran yang relevan
- [ ] Tidak ada logika bisnis di Controller/Blade
- [ ] Tidak ada N+1 pada halaman terkait
- [ ] Teks UI lewat `lang/id`; komponen UI mengikuti `DESIGN_SYSTEM.md`
- [ ] Pint bersih, test lolos
- [ ] Migration dapat dijalankan dari nol (`migrate:fresh --seed`)

## 13. Daftar Larangan

- Jangan `$guarded = []` atau `Model::unguard()`.
- Jangan menulis query di Blade atau memanggil `Model::all()` untuk data besar.
- Jangan `DB::raw()` / `whereRaw()` dengan input pengguna tanpa binding.
- Jangan mengubah status task dengan `$task->update(['status' => ...])` di luar `UpdateTaskStatusAction`.
- Jangan mengandalkan penyembunyian tombol di UI sebagai satu-satunya otorisasi.
- Jangan menambah paket baru tanpa alasan jelas; sebutkan alasannya di PR.
- Jangan hardcode warna/ukuran di luar token pada `DESIGN_SYSTEM.md`.
- Jangan menggunakan `localStorage` untuk data yang seharusnya di server.

## 14. Urutan Implementasi yang Disarankan

1. Setup, auth, layout dasar (sidebar + topbar) dengan token desain.
2. Enum, migration, model, factory, seeder demo.
3. Proyek: Index + Show (kerangka), `CreateProjectAction`, `ProjectPolicy`.
4. Anggota proyek: Action + UI.
5. Task: CRUD, Form Request, `TaskPolicy`, `UpdateTaskStatusAction`.
6. Kanban Board (Livewire + Alpine + SortableJS).
7. Dashboard + `DashboardService`.
8. Activity log, komentar, notifikasi, scheduler.
9. Test menyeluruh, optimasi, dokumentasi.
