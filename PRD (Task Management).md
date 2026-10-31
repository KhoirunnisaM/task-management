# PRD: Aplikasi Task Management Proyek

**Versi:** 1.0 (Draft MVP)\
**Stack:** Laravel + MySQL (Laragon) + Livewire v3 + Alpine.js + Tailwind CSS\
**Arsitektur:** Service/Action Pattern, Form Request, Policy

## 1. Ringkasan Produk

Aplikasi web untuk mengelola proyek dan tugas tim secara terpusat. Pengguna dapat membuat proyek, mengundang anggota tim dengan peran tertentu, membuat dan menugaskan task, serta memantau progres melalui papan status bergaya Kanban (Trello/Jira sederhana).

**Masalah yang diselesaikan:** tugas tim tersebar di chat/spreadsheet, tidak jelas siapa mengerjakan apa, kapan tenggatnya, dan sudah sampai tahap mana.

## 2. Tujuan dan Non-Tujuan

### Tujuan

- Satu tempat untuk proyek, task, anggota tim, dan status pengerjaan.
- Visibilitas progres proyek secara real-time lewat dashboard dan Kanban board.
- Kontrol akses berbasis peran agar data proyek hanya dilihat/diubah oleh yang berhak.
- Kode bersih, mudah diuji, dan mudah dikembangkan (tanpa Fat Controller).

### Non-Tujuan (di luar MVP)

- Integrasi pihak ketiga (Slack, Google Calendar, GitHub).
- Time tracking dan penagihan.
- Aplikasi mobile native.
- Multi-tenant / organisasi terpisah.
- Real-time kolaborasi via WebSocket (dijadwalkan fase 2).

## 3. Persona dan Peran

| Peran | Level | Deskripsi |
| --- | --- | --- |
| **Admin** | Global (`users.role`) | Mengelola seluruh user dan semua proyek. |
| **Manager** | Proyek (pivot `role`) | Merencanakan proyek, Mengelola task dan anggota, tidak bisa menghapus proyek. |
| **Member** | Proyek (pivot `role`) | Mengerjakan task miliknya, memperbarui status, berkomentar. |


## 4. Ruang Lingkup MVP

1. Autentikasi (registrasi, login, logout, reset password).
2. CRUD Proyek dan manajemen anggota proyek.
3. CRUD Task dengan assignee, prioritas, dan tenggat.
4. Perubahan status task (Todo, In Progress, In Review, Completed), termasuk drag-and-drop pada Kanban.
5. Dashboard ringkasan.
6. Activity log perubahan task.
7. Komentar pada task.

## 5. Kebutuhan Fungsional

### 5.1 Autentikasi dan Profil

- **FR-01** Pengguna dapat registrasi dengan nama, email, dan password.
- **FR-02** Pengguna dapat login, logout, dan reset password via email.
- **FR-03** Pengguna dapat memperbarui profil dan password.

### 5.2 Manajemen Proyek

- **FR-10** Admin dapat membuat proyek (judul, deskripsi, status, tanggal mulai/selesai opsional). 
- **FR-11** Status proyek: `planning`, `active`, `on_hold`, `completed`, `archived`.
- **FR-12** Admin dapat mengedit & menghapus proyek (soft delete).
- **FR-13** Daftar proyek menampilkan hanya proyek yang diikuti pengguna (Admin melihat semua), dengan pencarian dan filter status.
- **FR-14** Setiap proyek menampilkan persentase progres (task Completed / total task).

### 5.3 Anggota Tim Proyek

- **FR-20** Manager dapat menambahkan user ke proyek dengan peran Member.
- **FR-21** Manager dapat mengubah dan mengeluarkan anggota.
- **FR-22** Manager tidak dapat dikeluarkan atau diturunkan perannya.
- **FR-23** Saat anggota dikeluarkan, task miliknya otomatis menjadi *unassigned*.

### 5.4 Manajemen Task

- **FR-30** Manager dapat membuat task: judul, deskripsi, prioritas (`low`, `medium`, `high`, `urgent`), tenggat, dan assignee.
- **FR-31** Assignee harus merupakan anggota proyek yang bersangkutan.
- **FR-32** Status task: `todo`, `in_progress`, `in_review`, `completed`.
- **FR-33** Status dapat diubah lewat form maupun drag-and-drop antar kolom Kanban.
- **FR-34** Task dapat difilter berdasarkan assignee, prioritas, status, dan tenggat; dapat dicari berdasarkan judul.
- **FR-35** Task melewati tenggat dan belum Completed ditandai *overdue*.
- **FR-36** Task dapat diurutkan dalam kolom (posisi) dan dihapus (soft delete) oleh Owner/Manager atau pembuatnya.

### 5.5 Komentar dan Activity Log

- **FR-40** Member dapat berkomentar pada task; Admin dapat mengedit/menghapus komentarnya.
- **FR-41** Sistem mencatat otomatis: task dibuat, status berubah, assignee berubah, prioritas/tenggat berubah (siapa, kapan, nilai lama dan baru).
- **FR-42** Activity log tampil sebagai timeline pada detail task dan proyek.

### 5.6 Notifikasi (via Queue)

- **FR-50** Email/notifikasi database ke user saat ditugaskan pada task.
- **FR-51** Pengingat tenggat H-1 melalui scheduled command dan queue.

### 5.7 Dashboard

- **FR-60** Kartu statistik: total proyek aktif, task saya, task overdue, task selesai minggu ini.
- **FR-61** Daftar *My Tasks* (diurutkan tenggat terdekat) dan proyek terbaru dengan progress bar.
- **FR-62** Feed aktivitas terbaru dari proyek yang diikuti.

## 6. Matriks Otorisasi (Policy)

| Aksi | Admin | Manager | Member |
| --- | :-: | :-: | :-: |
| Lihat proyek | Ya | Ya | Ya |
| Edit proyek | Ya | Ya | Tidak |
| Hapus proyek | Ya | Tidak | Tidak |
| Kelola anggota | Ya | Ya | Tidak |
| Buat task | Ya | Ya | Tidak |
| Edit task | Ya | Ya | Tidak |
| Ubah status task | Ya | Ya | Task sendiri/ditugaskan |
| Hapus task | Ya | Ya | Tidak |
| Komentar | Ya | Ya | Ya |

## 7. Aturan Bisnis dan Alur Status

**Alur status task:** `todo` → `in_progress` → `in_review` → `completed`.

- Task dapat dipindah maju maupun mundur (misal: `in_review` kembali ke `in_progress` bila ada revisi).
- Saat status menjadi `completed`, sistem mengisi `completed_at`; bila dibuka kembali, `completed_at` dikosongkan.
- Proyek berstatus `archived` bersifat read-only (tidak ada perubahan task/anggota).
- Tenggat task tidak boleh sebelum tanggal hari ini saat pertama kali dibuat.
- Semua perubahan status wajib melalui satu Action (`UpdateTaskStatusAction`) agar log dan `completed_at` konsisten.

## 8. Kebutuhan Non-Fungsional

- **Keamanan:** hashing password bawaan Laravel, proteksi CSRF, validasi via Form Request, otorisasi via Policy, mencegah mass-assignment (`$fillable`), rate limiting pada login.
- **Performa:** eager loading (hindari N+1), index pada kolom foreign key dan `status`, pagination, `withCount` untuk statistik.
- **Kualitas kode:** Controller tipis, logika bisnis di Action/Service, ketaatan pada PSR-12 (Laravel Pint), minimal test Feature untuk alur utama (Pest/PHPUnit).
- **UI/UX:** responsif (mobile-first), mendukung dark mode (opsional), umpan balik loading dan toast notifikasi.
- **Maintainability:** Enum PHP untuk status/prioritas/peran, Seeder dan Factory untuk data demo.

## 9. Arsitektur Teknis (Ringkas)

| Lapisan | Pilihan |
| --- | --- |
| Backend | Laravel stabil terbaru (PHP 8.3+), Eloquent ORM |
| Database | MySQL 8 (Laragon) |
| Frontend | Blade + Livewire v3 + Alpine.js + Tailwind CSS (Vite) |
| Autentikasi | Laravel Breeze (varian Livewire) atau starter kit resmi terbaru |
| Otorisasi | Policy + Gate |
| Validasi | Form Request (HTTP) dan Form Object (Livewire) |
| Logika bisnis | Action class (satu aksi, satu kelas) dan Service untuk orkestrasi |
| Async | Queue (driver `database`), Scheduler |
| Drag-and-drop | Alpine.js + `livewire-sortable` atau SortableJS |
| Testing | Pest / PHPUnit |

**Alur request:** `Route → Controller/Livewire Component → Form Request (validasi) → Policy (otorisasi) → Action/Service → Eloquent Model → Event/Observer (Activity Log, Notifikasi)`.

## 10. Draft Model Data

| Tabel | Kolom Utama |
| --- | --- |
| `users` | id, name, email, password, role (`admin`/`manager`/`member`), avatar, timestamps |
| `projects` | id, owner\_id (FK), title, description, status, start\_date, end\_date, soft deletes, timestamps |
| `project_user` | id, project\_id, user\_id, role (`manager`/`member`), timestamps; unique(project\_id, user\_id) |
| `tasks` | id, project\_id (FK), created\_by (FK), assigned\_to (FK nullable), title, description, status, priority, due\_date, position, completed\_at, soft deletes, timestamps |
| `comments` | id, task\_id (FK), user\_id (FK), body, timestamps |
| `activity_logs` | id, project\_id, task\_id (nullable), user\_id, action, properties (JSON), created\_at |

**Relasi:**

- User hasMany Project (sebagai admin); User belongsToMany Project (pivot `project_user` dengan `role`).
- Project hasMany Task; Project belongsToMany User.
- Task belongsTo Project, belongsTo User (assignee), belongsTo User (creator), hasMany Comment, hasMany ActivityLog.

**Index penting:** `tasks(project_id, status)`, `tasks(assigned_to)`, `tasks(due_date)`, `project_user(user_id)`.

## 11. Daftar Halaman dan UI

| Halaman | Isi Utama |
| --- | --- |
| Login / Register | Form autentikasi |
| **Dashboard** | Kartu statistik, My Tasks, proyek terbaru, feed aktivitas |
| Daftar Proyek | Grid/list proyek, pencarian, filter status, tombol *Proyek Baru* |
| **Detail Proyek** | Header (judul, status, progress), tab *Board / List / Anggota / Aktivitas*, Kanban 4 kolom |
| Modal/Slide-over Task | Detail, edit inline, assignee, komentar, riwayat |
| Manajemen Anggota | Tambah/ubah peran/hapus anggota |
| Profil | Ubah data dan password |

**Prinsip desain:** sidebar navigasi tetap, warna badge konsisten (prioritas: abu/biru/oranye/merah; status: abu/biru/kuning/hijau), kartu task menampilkan judul, avatar assignee, prioritas, dan tenggat.

## 12. Metrik Keberhasilan

- Pengguna dapat membuat proyek + task pertama dalam kurang dari 2 menit.
- Halaman Detail Proyek dengan 200 task dimuat kurang dari 2 detik (lokal).
- 0 query N+1 pada halaman utama (diverifikasi dengan Laravel Debugbar/Telescope).
- Cakupan test Feature minimal untuk alur: auth, CRUD proyek, CRUD task, ubah status, otorisasi.

## 13. Rencana Fase Pengembangan

| Fase | Cakupan | Output |
| --- | --- | --- |
| **0. Setup** | Instalasi Laragon, project Laravel, DB, Tailwind, Livewire, auth | Aplikasi kosong berjalan |
| **1. Fondasi data** | Migration, Enum, Model, Relasi, Factory, Seeder | Skema + data demo |
| **2. Proyek dan Tim** | CRUD proyek, anggota, Policy | Modul proyek lengkap |
| **3. Task** | CRUD task, filter, Action status, Form Request | Modul task lengkap |
| **4. Kanban UI** | Board drag-and-drop, modal task, dashboard | UI dinamis |
| **5. Log, Komentar, Notifikasi** | Observer, komentar, queue, scheduler | Fitur kolaborasi |
| **6. QA dan Deploy** | Testing, optimasi, dokumentasi | Siap rilis |

## 14. Risiko dan Mitigasi

| Risiko | Mitigasi |
| --- | --- |
| Drag-and-drop dan state Livewire tidak sinkron | Simpan perubahan via satu method Livewire; gunakan optimistic UI dengan Alpine |
| Kebocoran data antar proyek | Wajib Policy + query scoping (`forUser`) pada semua listing |
| Query lambat saat task banyak | Index, eager loading, pagination per kolom |
| Logika status tersebar | Sentralisasi di `UpdateTaskStatusAction` |

