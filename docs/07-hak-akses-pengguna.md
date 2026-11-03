# 07 — Hak Akses Pengguna

TaskFlow memakai dua lapis peran dan tiga mekanisme pembatas: middleware route, Form Request, dan Policy. Tampilan juga memakai direktif `@can` agar tombol yang tidak berhak tidak muncul.

## 7.1 Dua lapis peran

### Peran global — `users.role`

Enum: `App\Enums\UserRole` (`admin`, `manager`, `member`).

- **Admin** — akses penuh ke seluruh aplikasi, termasuk halaman "Semua Anggota".
- **Manager** (global) — tidak otomatis menjadi manager proyek. Hak proyek tetap ditentukan pivot `project_user.role`.
- **Member** — peran default untuk akun baru hasil registrasi.

### Peran proyek — `project_user.role`

Enum: `App\Enums\ProjectRole` (`manager`, `member`).

- **Manager proyek** — dibuat otomatis saat proyek dibuat (pembuat proyek) atau saat anggota dipromosikan.
- **Member proyek** — dapat melihat proyek, mengubah status tugas yang ditugaskan kepadanya, dan berkomentar.

Helper `User::isAdmin()` memeriksa peran global. Helper `Project::roleOf(User $user)` mengembalikan `ProjectRole` dari pivot atau `null` bila bukan anggota.

## 7.2 Middleware dan route

| Route | Middleware | Efek |
|---|---|---|
| `/login`, `/register` | `guest` | Pengguna yang sudah login dialihkan |
| `/dashboard`, `/projects/*`, `/logout`, route tugas/komentar | `auth` | Wajib login |
| `/admin/members`, `/admin/members/{user}` | `auth` + `can:viewAny,App\Models\User` | Hanya Admin |

Tidak ada middleware kustom di `bootstrap/app.php`. Pembatasan detail dilakukan oleh Policy di Form Request, controller, atau komponen Livewire.

## 7.3 Policy yang berlaku

### `ProjectPolicy`

```php
public function view(User $user, Project $project): bool
{
    return $user->isAdmin() || $project->roleOf($user) !== null;
}

public function create(User $user): bool
{
    return $user->isAdmin();
}

public function update(User $user, Project $project): bool
{
    return $user->isAdmin() || $project->roleOf($user) === ProjectRole::Manager;
}

public function delete(User $user, Project $project): bool
{
    return $user->isAdmin();
}

public function manageMembers(User $user, Project $project): bool
{
    return ! $project->isArchived()
        && ($user->isAdmin() || $project->roleOf($user) === ProjectRole::Manager);
}
```

`changeMemberRole` dan `removeMember` memanggil `manageMembers` lalu memastikan peran anggota yang disentuh adalah `member`. Manager proyek tidak dapat diturunkan atau dikeluarkan.

### `TaskPolicy`

- `viewAny` — Admin atau anggota proyek.
- `view` — Admin atau anggota proyek.
- `create` — proyek tidak arsip dan (Admin atau Manager proyek).
- `update` — proyek tidak arsip dan (Admin atau Manager proyek).
- `updateStatus` — proyek tidak arsip; Admin boleh; Manager proyek boleh; Member hanya bila `assigned_to` = dirinya.
- `delete` — proyek tidak arsip dan (Admin atau Manager proyek).
- `reorder` — sama dengan `update`.

### `CommentPolicy`

- `create` — proyek tidak arsip dan (Admin atau anggota proyek).
- `update` / `delete` — proyek tidak arsip dan (Admin atau penulis komentar).

### `UserPolicy`

- `viewAny` — hanya Admin.
- `view` — hanya Admin.

## 7.4 Tabel hak akses aktual

Keterangan kolom:

- **Admin** — `users.role = admin`.
- **Manager proyek** — `project_user.role = manager` pada proyek tersebut.
- **Member proyek** — `project_user.role = member` pada proyek tersebut.
- **Manager global tanpa proyek** — `users.role = manager` tetapi bukan anggota proyek mana pun.
- **Orang luar** — bukan anggota proyek dan bukan Admin.

| Aksi | Admin | Manager proyek | Member proyek | Manager global tanpa proyek | Orang luar |
|---|---|---|---|---|---|
| Melihat daftar proyek sendiri | Semua proyek | Proyek yang diikuti | Proyek yang diikuti | Tidak ada | Tidak ada |
| Melihat detail proyek | Ya | Ya | Ya | Tidak | Tidak |
| Membuat proyek | Ya | Tidak | Tidak | Tidak | Tidak |
| Mengubah proyek | Ya | Ya | Tidak | Tidak | Tidak |
| Menghapus proyek (soft delete) | Ya | Tidak | Tidak | Tidak | Tidak |
| Menambah anggota | Ya | Ya (proyek tidak arsip) | Tidak | Tidak | Tidak |
| Mengubah peran anggota | Ya | Ya, hanya anggota `member` | Tidak | Tidak | Tidak |
| Mengeluarkan anggota | Ya | Ya, hanya anggota `member` | Tidak | Tidak | Tidak |
| Melihat papan Kanban | Ya | Ya | Ya | Tidak | Tidak |
| Membuat tugas | Ya | Ya (proyek tidak arsip) | Tidak | Tidak | Tidak |
| Melihat detail tugas | Ya | Ya | Ya | Tidak | Tidak |
| Mengubah tugas (judul, prioritas, tenggat, assignee) | Ya | Ya | Tidak | Tidak | Tidak |
| Menghapus tugas | Ya | Ya | Tidak | Tidak | Tidak |
| Mengubah status tugas | Ya | Ya | Hanya tugas yang ditugaskan kepadanya | Tidak | Tidak |
| Mengurutkan tugas (reorder) | Ya | Ya | Tidak | Tidak | Tidak |
| Menambah komentar | Ya | Ya | Ya (proyek tidak arsip) | Tidak | Tidak |
| Mengubah/menghapus komentar | Ya, semua komentar | Hanya komentarnya sendiri | Hanya komentarnya sendiri | Tidak | Tidak |
| Melihat activity log proyek/tugas | Ya | Ya | Ya | Tidak | Tidak |
| Membuka halaman Semua Anggota | Ya | Tidak | Tidak | Tidak | Tidak |
| Melihat detail anggota di panel admin | Ya | Tidak | Tidak | Tidak | Tidak |
| Registrasi | Terbuka untuk publik; akun selalu Member | | | | |
| Login/logout | Terbuka untuk semua pengguna | | | | |

Catatan penting:

1. **Proyek arsip bersifat read-only.** `ProjectPolicy::manageMembers`, `TaskPolicy::create/update/updateStatus/delete/reorder`, dan `CommentPolicy::create/update/delete` semuanya memeriksa `isArchived()` dan menolak perubahan, termasuk untuk Admin.
2. **Admin tidak otomatis anggota proyek.** Admin dapat melihat semua proyek melalui `ProjectPolicy::view`, tetapi untuk melihat data yang bergantung pada pivot (misalnya `roleOf`) tetap ditangani oleh cabang `$user->isAdmin()`.
3. **Manager global tanpa proyek tidak memperoleh hak proyek.** Dashboard-nya memakai tampilan Member dan daftar proyeknya kosong sampai ia ditambahkan ke proyek.
4. **Setiap route resource tetap memeriksa Policy.** Route `projects.show` memanggil `$this->authorize('view', $project)`; route `projects.tasks.*` memakai Form Request yang memanggil Policy.

## 7.5 Bagaimana tampilan menyesuaikan hak akses

- Tombol **Proyek Baru** di `projects/index.blade.php` dibungkus `@can('create', App\Models\Project::class)`.
- Tombol **Tambah anggota** dan form anggota memakai `@can('manageMembers', $project)`.
- Dropdown ubah peran memakai `@can('changeMemberRole', [$project, $member])`, tombol keluarkan memakai `@can('removeMember', [$project, $member])`.
- Tombol **Tugas Baru** memakai `@can('create', [App\Models\Task::class, $project])`.
- Tombol **Edit** dan **Hapus** tugas memakai `@can('update', $task)` dan `@can('delete', $task)`.
- Form ubah status memakai `@can('updateStatus', $task)`.
- Form komentar memakai `@can('create', [App\Models\Comment::class, $task])`; tombol edit/hapus komentar memakai `@canany(['update', 'delete'], $comment)`.
- Menu **Semua Anggota** di sidebar hanya dirender bila `auth()->user()->isAdmin()`.

Menyembunyikan tombol bukan satu-satunya pengaman: semua aksi tetap divalidasi di server, sehingga permintaan langsung ke route tetap ditolak bila tidak berhak (diuji di beberapa tes otorisasi).

## 7.6 Matriks aksi berdasarkan kode (ringkas)

| Policy method | Rumus |
|---|---|
| `ProjectPolicy::viewAny` | `true` |
| `ProjectPolicy::view` | `isAdmin() \|\| roleOf(user) !== null` |
| `ProjectPolicy::create` | `isAdmin()` |
| `ProjectPolicy::update` | `isAdmin() \|\| roleOf(user) === Manager` |
| `ProjectPolicy::delete` | `isAdmin()` |
| `ProjectPolicy::manageMembers` | `!isArchived() && (isAdmin() \|\| roleOf(user) === Manager)` |
| `ProjectPolicy::changeMemberRole` | `manageMembers && roleOf(member) === Member` |
| `ProjectPolicy::removeMember` | `manageMembers && roleOf(member) === Member` |
| `TaskPolicy::viewAny` / `view` | `isAdmin() \|\| roleOf(user) !== null` |
| `TaskPolicy::create` | `!isArchived() && (isAdmin() \|\| roleOf(user) === Manager)` |
| `TaskPolicy::update` / `delete` / `reorder` | `!isArchived() && (isAdmin() \|\| roleOf(user) === Manager)` |
| `TaskPolicy::updateStatus` | `!isArchived() && (isAdmin() \|\| Manager \|\| (Member && assigned_to === user))` |
| `CommentPolicy::create` | `!isArchived() && (isAdmin() \|\| roleOf(user) !== null)` |
| `CommentPolicy::update` / `delete` | `!isArchived() && (isAdmin() \|\| comment.user_id === user)` |
| `UserPolicy::viewAny` / `view` | `isAdmin()` |
