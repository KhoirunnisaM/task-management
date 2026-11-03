# 01 — Gambaran Umum Aplikasi TaskFlow

## 1.1 Identitas aplikasi

| Hal | Keterangan |
|---|---|
| Nama aplikasi | **TaskFlow** |
| Jenis | Aplikasi web manajemen proyek dan tugas tim |
| Bahasa antarmuka | Indonesia |
| Zona waktu | `Asia/Jakarta` (lihat `config/app.php`) |
| Alamat pengembangan | `http://task-manager.test` (nilai `APP_URL` pada `.env`) |
| Repositori | `https://github.com/KhoirunnisaM/task-management.git` |

Nama "TaskFlow" muncul pada konfigurasi aplikasi, judul halaman (`<title>… · TaskFlow`), komponen logo `resources/views/components/brand/logo.blade.php`, serta berkas terjemahan `lang/id/common.php` dan `lang/id/auth.php`. Sebelumnya aplikasi memakai sebutan "Task Manager"; branding kemudian dirapikan menjadi TaskFlow. Berkas PRD masih memakai judul "Aplikasi Task Management Proyek" karena merupakan dokumen perencanaan awal.

## 1.2 Tujuan pembuatan

TaskFlow dibuat untuk menyatukan pekerjaan tim yang biasanya tersebar di grup chat, spreadsheet, dan catatan pribadi. Aplikasi ini memberi satu tempat untuk:

- mencatat proyek beserta status dan periodenya;
- memecah proyek menjadi tugas (task) dengan prioritas, penanggung jawab, dan tenggat;
- memantau kemajuan lewat papan Kanban empat kolom dan daftar tugas;
- mencatat siapa mengubah apa dan kapan melalui activity log;
- mendiskusikan tugas lewat komentar;
- membatasi data sesuai peran pengguna.

## 1.3 Latar belakang dan masalah yang diselesaikan

Berdasarkan `PRD (Task Management).md` bagian 1, masalah utamanya adalah:

1. Tugas tim tersebar di banyak kanal sehingga sulit dicari.
2. Tidak jelas siapa penanggung jawab sebuah pekerjaan.
3. Tenggat tidak terlihat dan mudah terlewat.
4. Progres proyek tidak terbaca karena tidak ada satu sumber data.
5. Anggota tim bisa melihat atau mengubah data proyek yang bukan haknya.

TaskFlow menjawab masalah tersebut dengan struktur data proyek → anggota → tugas → komentar/aktivitas, ditambah dashboard yang menampilkan ringkasan sesuai peran.

## 1.4 Manfaat aplikasi

- **Satu sumber kebenaran.** Proyek, tugas, status, penanggung jawab, dan tenggat tersimpan di database yang sama.
- **Progres terukur.** Persentase progres dihitung dari jumlah tugas `completed` dibagi total tugas (`app/Services/ProjectProgressService.php`).
- **Akuntabilitas.** Setiap perubahan penting tercatat di tabel `activity_logs` melalui listener `app/Listeners/LogTaskActivity.php`.
- **Kolaborasi terarah.** Komentar menempel pada tugas, bukan tersebar di chat.
- **Kontrol akses.** Policy dan middleware memastikan pengguna hanya melihat proyek yang diikuti (Admin melihat semua).
- **Ramah perangkat.** Layout responsif, sidebar dapat dibuka-tutup di layar kecil, dan papan Kanban dapat digeser horizontal di ponsel.

## 1.5 Jenis pengguna

TaskFlow memakai dua lapis peran:

**a. Peran global (`users.role`)** — enum `App\Enums\UserRole`:

| Nilai | Sebutan | Cakupan |
|---|---|---|
| `admin` | Admin | Seluruh aplikasi: semua proyek, semua pengguna, halaman admin |
| `manager` | Manager | Peran bawaan akun; hak proyek tetap ditentukan pivot |
| `member` | Member | Peran bawaan hasil registrasi publik |

**b. Peran proyek (`project_user.role`)** — enum `App\Enums\ProjectRole`:

| Nilai | Sebutan | Cakupan |
|---|---|---|
| `manager` | Manager proyek | Mengelola proyek, anggota, dan seluruh tugas proyek |
| `member` | Anggota | Mengerjakan tugas, mengubah status tugas yang ditugaskan kepadanya, berkomentar |

Dashboard menentukan tampilan lewat `DashboardService::roleFor()`: Admin memakai dashboard Admin; pengguna yang menjadi **manager pada minimal satu proyek** memakai dashboard Manager; selain itu memakai dashboard Member. Artinya, pengguna dengan `users.role = manager` tetapi tidak menjadi manager proyek mana pun akan melihat dashboard Member.

## 1.6 Fitur utama yang benar-benar tersedia

| Fitur | Ringkasan | Bukti implementasi |
|---|---|---|
| Registrasi | Daftar dengan nama, email, password; akun selalu dibuat sebagai Member | `RegisterController`, `RegisterRequest`, `tests/Feature/Auth/RegistrationTest.php` |
| Login / logout | Login dengan rate limit 5 percobaan, logout menghapus sesi | `LoginController`, `LoginRequest`, `tests/Feature/Auth/AuthenticationTest.php` |
| Dashboard per peran | Tiga varian tampilan: Admin, Manager, Member | `DashboardController`, `DashboardService`, `resources/views/dashboard/{admin,manager,member}.blade.php` |
| CRUD proyek | Buat/lihat/ubah/hapus (soft delete); hanya Admin dapat membuat dan menghapus | `ProjectController`, `ProjectPolicy`, 9 tes `ProjectCrudTest` |
| Anggota proyek | Tambah anggota, ubah peran, keluarkan anggota; manager proyek tidak dapat diturunkan/dikeluarkan | `ProjectMemberController`, `ProjectPolicy`, `actions/Members` |
| CRUD tugas | Buat/lihat/ubah/hapus tugas dengan prioritas, tenggat, penanggung jawab | `TaskController`, `actions/Tasks` |
| Papan Kanban | Empat kolom status, drag-and-drop (SortableJS + Livewire), menu pindah status | `app/Livewire/Tasks/Board.php`, `resources/views/livewire/tasks/board.blade.php` |
| Daftar tugas + filter | Cari judul, filter status, prioritas, penanggung jawab, tenggat | `TaskQueryService`, `IndexTaskRequest` |
| Status dan urutan | Perubahan status mengisi/mengosongkan `completed_at`, posisi dihitung ulang | `UpdateTaskStatusAction`, `ReorderTasksAction` |
| Komentar | Tambah, ubah, hapus komentar pada detail tugas | `CommentController`, `CommentPolicy`, `actions/Comments` |
| Activity log | Timeline perubahan tugas dan komentar di detail tugas/proyek dan dashboard | `LogTaskActivity`, `resources/views/components/activity/timeline.blade.php` |
| Notifikasi database | Notifikasi saat ditugaskan dan pengingat H-1 | `SendTaskAssignedNotification`, `SendDueSoonTaskReminders`, `notifications` |
| Halaman admin anggota | Daftar seluruh pengguna, filter peran, urutan, detail proyek pengguna | `AdminMemberController`, `resources/views/admin/members/*` |
| Tema gelap | Tombol tema di topbar, preferensi disimpan di `localStorage` | `resources/views/layouts/partials/theme-script.blade.php`, `topbar.blade.php` |

## 1.7 Batasan sistem

Batasan berikut berlaku pada kode saat ini, bukan sekadar rencana:

1. **Belum ada lupa/reset password.** Tabel `password_reset_tokens` dibuat oleh migration bawaan, tetapi tidak ada route, controller, atau tampilan reset password.
2. **Belum ada halaman profil.** Tidak ada route profil, ubah nama/email, atau ubah password. Data pengguna hanya dapat dilihat Admin lewat halaman anggota.
3. **Tidak ada halaman komentar terpisah.** Komentar hanya tampil dan dikelola di halaman detail tugas.
4. **Belum ada pusat notifikasi di antarmuka.** Notifikasi tersimpan di tabel `notifications` (channel `database`), tetapi belum ada halaman/lonceng notifikasi. Queue memakai driver `database`, sehingga worker (`php artisan queue:work`) perlu berjalan agar notifikasi queued diproses.
5. **Belum ada integrasi pihak ketiga**, time tracking, aplikasi mobile native, multi-tenant, atau WebSocket — sesuai non-tujuan PRD.
6. **Penghapusan proyek hanya soft delete.** Tugas di dalamnya tidak ikut terhapus; statistik Admin secara eksplisit memfilter tugas yang proyeknya masih ada (`Task::query()->whereHas('project')`).
7. **Papan Kanban memakai satu komponen Livewire** dan drag-and-drop berbasis SortableJS; belum ada pembaruan real-time antar pengguna.
8. **Belum ada fitur export/import** data.

## 1.8 Ringkasan alur pemakaian

1. Pengguna membuka aplikasi → diarahkan ke `/dashboard`; jika belum login, diarahkan ke `/login`.
2. Pengguna mendaftar (opsional) lalu login.
3. Dashboard menampilkan statistik dan daftar kerja sesuai peran.
4. Admin membuat proyek; pembuat otomatis menjadi Manager proyek.
5. Manager menambahkan anggota dan membuat tugas.
6. Anggota melihat tugas, memindahkan status tugas yang ditugaskan kepadanya, dan berkomentar.
7. Semua perubahan penting muncul di activity log dan, untuk penugasan, menghasilkan notifikasi database.
8. Pengguna logout.

Dokumen lanjutan: lihat `docs/README.md` untuk daftar isi lengkap.
