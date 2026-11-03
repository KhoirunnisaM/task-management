# 09 — Pengujian

## 9.1 Cara menjalankan tes

Dari akar proyek:

```bash
php artisan test
```

Atau langsung melalui PHPUnit:

```bash
vendor/bin/phpunit
```

Formatter dijalankan terpisah:

```bash
vendor/bin/pint
```

Konfigurasi pengujian ada di `phpunit.xml`:

- dua test suite: `Unit` dan `Feature`;
- `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`;
- `APP_ENV=testing`;
- `CACHE_STORE=array`, `SESSION_DRIVER=array`, `QUEUE_CONNECTION=sync`, `MAIL_MAILER=array`, `BCRYPT_ROUNDS=4`.

Database SQLite in-memory membuat setiap tes bersih dan cepat, serta tidak menyentuh database pengembangan `task_manager`.

## 9.2 Hasil pengujian terakhir

Hasil yang benar-benar dijalankan saat dokumentasi ini disusun:

| Item | Hasil |
|---|---|
| Perintah | `php artisan test` |
| Jumlah tes | **129** |
| Lulus | **129** |
| Gagal | **0** |
| Assertion | **619** |
| Status | **Lulus semua** |
| Pint | **Lulus** (`vendor/bin/pint`) |

Angka tersebut berasal dari keluaran PHPUnit:

```json
{"tool":"phpunit","result":"passed","tests":129,"passed":129,"assertions":619,"duration_ms":24298}
```

## 9.3 Sebaran berkas tes

| Berkas | Jumlah tes | Fokus |
|---|---:|---|
| `tests/Feature/ExampleTest.php` | 1 | Root diarahkan ke dashboard |
| `tests/Feature/Admin/AdminMemberTest.php` | 8 | Menu dan halaman admin, pencarian, filter peran, urutan, pagination, detail anggota |
| `tests/Feature/Auth/AuthenticationTest.php` | 4 | Redirect guest, halaman login, login/logout, kredensial salah |
| `tests/Feature/Auth/RegistrationTest.php` | 8 | Halaman register, tautan login, registrasi, pengabaian role, email unik, panjang password, konfirmasi, user login tidak bisa membuka register |
| `tests/Feature/Comments/CommentTest.php` | 6 | Komentar anggota, penolakan orang luar, validasi body, edit/hapus oleh penulis/Admin, proyek arsip, scoping route |
| `tests/Feature/Dashboard/DashboardStatsTest.php` | 5 | Statistik Admin/Manager/Member, tugas dari proyek terhapus tidak dihitung |
| `tests/Feature/Dashboard/DashboardTest.php` | 8 | Dashboard per pengguna, urutan tugas, aktivitas, progres, proyek arsip, jumlah query |
| `tests/Feature/Dashboard/RoleDashboardTest.php` | 6 | Pemilihan dashboard berdasarkan peran, manager global tanpa proyek, member tanpa proyek |
| `tests/Feature/Database/DemoDataSeederTest.php` | 1 | Seeder membuat proyek, tugas, komentar, activity log |
| `tests/Feature/Database/EnumTest.php` | 2 | Nilai enum sesuai kontrak database, label Indonesia |
| `tests/Feature/Database/ModelRelationsTest.php` | 5 | Relasi proyek/user, cast tugas, scope overdue, soft delete, unique pivot, null on delete assignee |
| `tests/Feature/Navigation/BackButtonTest.php` | 2 | Tautan kembali di register dan halaman aplikasi |
| `tests/Feature/Notifications/TaskNotificationTest.php` | 8 | Notifikasi penugasan, penugasan saat create, unassign, queue, channel database, pengingat H-1, penyimpanan notifikasi, jadwal harian |
| `tests/Feature/Projects/ProjectCrudTest.php` | 9 | Create, validasi, non-admin, daftar sesuai keanggotaan, filter, manager update tanpa delete, isolasi proyek, soft delete, progres |
| `tests/Feature/Projects/ProjectMemberTest.php` | 7 | Tambah anggota, larangan member, duplikat, promosi, larangan menurunkan manager, unassign saat keluar, proyek arsip |
| `tests/Feature/Tasks/TaskActivityLogTest.php` | 5 | Log create, perubahan status, perubahan assignee/prioritas/tenggat, tanpa log bila tidak berubah, render timeline |
| `tests/Feature/Tasks/TaskBoardTest.php` | 14 | Akses board, empat kolom, metadata kartu, posisi negatif, izin pindah, reorder kolom, completed_at, proyek arsip, status invalid, filter |
| `tests/Feature/Tasks/TaskCrudTest.php` | 8 | Create Admin/Manager, larangan Member, validasi, update, otorisasi, delete, scoping route |
| `tests/Feature/Tasks/TaskDueDateFormTest.php` | 6 | Input tanggal nyata, label opsional, nilai edit, penyimpanan, boleh kosong, validasi tanggal lampau, update/kosongkan |
| `tests/Feature/Tasks/TaskListingTest.php` | 7 | Akses daftar, pencarian, filter status/prioritas, filter assignee dan unassigned, filter tenggat, overdue, proyek arsip |
| `tests/Feature/Tasks/TaskStatusAndOrderingTest.php` | 8 | Status oleh Member yang ditugaskan, larangan Member lain, otorisasi manager/admin/outsider, append kolom, reorder, larangan reorder Member, proyek arsip |
| `tests/Unit/ExampleTest.php` | 1 | Tes dasar `true == true` |

Total: **129 tes**.

## 9.4 Skenario, data masukan, hasil yang diharapkan, dan hasil aktual

Tabel berikut merangkum skenario penting. Semua baris berstatus **Berhasil** karena seluruh tes lulus pada eksekusi terakhir.

### Autentikasi dan registrasi

| Skenario | Data masukan | Hasil yang diharapkan | Hasil aktual | Status |
|---|---|---|---|---|
| Guest membuka halaman proyek | `GET /projects` tanpa login | Redirect ke `/login` | Redirect ke `/login` | Berhasil |
| Guest membuka login | `GET /login` | 200, judul login, tautan register | Sesuai | Berhasil |
| Login benar | Email + password valid | Redirect `/dashboard`, user terautentikasi | Sesuai | Berhasil |
| Login salah | Password salah | Error pada field `email` | Sesuai | Berhasil |
| Registrasi valid | Nama, email baru, password 8+, konfirmasi | User dibuat dengan role `member`, redirect login | Sesuai | Berhasil |
| Registrasi menyertakan role | `role=admin` pada input | Role diabaikan, tetap `member` | Sesuai | Berhasil |
| Email duplikat | Email yang sudah terdaftar | Error validasi `email` | Sesuai | Berhasil |
| Password pendek | 7 karakter | Error validasi `password` | Sesuai | Berhasil |
| Konfirmasi beda | Password ≠ konfirmasi | Error validasi | Sesuai | Berhasil |
| User login membuka register | `GET /register` saat login | Dialihkan (middleware guest) | Sesuai | Berhasil |

### Proyek dan anggota

| Skenario | Data masukan | Hasil yang diharapkan | Hasil aktual | Status |
|---|---|---|---|---|
| Admin membuat proyek | Judul, status, deskripsi | Proyek tersimpan, pembuat menjadi Manager proyek | Sesuai | Berhasil |
| Validasi proyek | Tanggal selesai < tanggal mulai | Error pada `end_date` | Sesuai | Berhasil |
| Non-admin membuat proyek | Manager/Member | 403 | Sesuai | Berhasil |
| Daftar proyek | Dua pengguna dengan proyek berbeda | Hanya proyek yang diikuti (Admin semua) | Sesuai | Berhasil |
| Filter proyek | `search` dan `status` | Hasil terfilter | Sesuai | Berhasil |
| Manager mengubah proyek | `PUT /projects/{id}` | Berhasil | Sesuai | Berhasil |
| Manager menghapus proyek | `DELETE /projects/{id}` | 403 | Sesuai | Berhasil |
| Admin menghapus proyek | `DELETE /projects/{id}` | Soft delete (`deleted_at` terisi) | Sesuai | Berhasil |
| Progress proyek | 1 dari 2 tugas selesai | 50% | Sesuai | Berhasil |
| Tambah anggota | `user_id` yang belum menjadi anggota | Pivot dibuat dengan role `member` | Sesuai | Berhasil |
| Tambah anggota duplikat | `user_id` yang sudah menjadi anggota | Error validasi `user_id` | Sesuai | Berhasil |
| Promosi anggota | `role=manager` | Pivot berubah menjadi manager | Sesuai | Berhasil |
| Turunkan manager | `role=member` pada manager | Ditolak | Sesuai | Berhasil |
| Keluarkan anggota | `DELETE` anggota | Pivot terhapus, tugas miliknya menjadi `assigned_to = null` | Sesuai | Berhasil |
| Proyek arsip | Perubahan anggota/tugas/komentar | Ditolak (read-only) | Sesuai | Berhasil |

### Tugas

| Skenario | Data masukan | Hasil yang diharapkan | Hasil aktual | Status |
|---|---|---|---|---|
| Admin membuat tugas | Judul, prioritas, tenggat, assignee | Tugas tersimpan di kolom `todo`, posisi terakhir | Sesuai | Berhasil |
| Manager membuat tugas | Sama | Tugas ditambahkan ke akhir kolom Todo | Sesuai | Berhasil |
| Member membuat tugas | Sama | 403 | Sesuai | Berhasil |
| Validasi create | Judul kosong, prioritas salah, tenggat lampau, assignee bukan anggota | Error pada `title`, `priority`, `due_date`, `assigned_to` | Sesuai | Berhasil |
| Update tugas | Judul/prioritas/tenggat/assignee baru | Tersimpan, log perubahan dibuat | Sesuai | Berhasil |
| Member/outsider update | `PUT` tugas | 403 | Sesuai | Berhasil |
| Hapus tugas | Admin/Manager vs Member | Admin/Manager berhasil, Member 403 | Sesuai | Berhasil |
| Scoping route | Task milik proyek lain | 404 | Sesuai | Berhasil |
| Tenggat tersimpan | `due_date=2026-12-20` | Tersimpan, tampil di edit | Sesuai | Berhasil |
| Tenggat kosong | `due_date=''` | `NULL` | Sesuai | Berhasil |
| Format tanggal salah | `bukan-tanggal` | Error validasi | Sesuai | Berhasil |
| Tanggal lampau saat create | Kemarin | Error validasi | Sesuai | Berhasil |
| Edit mengosongkan tenggat | `due_date=''` | `NULL` | Sesuai | Berhasil |
| Label tenggat | Halaman create | Teks "Tenggat" dan "(opsional)" tampil | Sesuai | Berhasil |
| Input tanggal nyata | Halaman create/edit | Ada `name="due_date" type="date"`; tidak ada tag `<x-ui.input>` mentah | Sesuai | Berhasil |

### Kanban, status, dan urutan

| Skenario | Data masukan | Hasil yang diharapkan | Hasil aktual | Status |
|---|---|---|---|---|
| Akses board | Anggota vs orang luar | Anggota 200, orang luar 403 | Sesuai | Berhasil |
| Empat kolom | Halaman board | Kolom todo, in_progress, in_review, completed berurutan | Sesuai | Berhasil |
| Metadata kartu | Tugas dengan assignee, prioritas, tenggat | Kartu menampilkan data tersebut, hanya tugas proyek itu | Sesuai | Berhasil |
| Posisi negatif | `position=-1` | Error validasi | Sesuai | Berhasil |
| Admin pindah tugas | `moveTask` | Berhasil | Sesuai | Berhasil |
| Manager pindah dengan posisi | Status + posisi | Tersimpan, urutan benar | Sesuai | Berhasil |
| Pindah dalam kolom yang sama | Posisi baru | Urutan diperbarui | Sesuai | Berhasil |
| Pindah keluar kolom | Status baru | Kolom asal dirapikan ulang | Sesuai | Berhasil |
| Member memindahkan tugas sendiri | Assignee = dirinya | Berhasil | Sesuai | Berhasil |
| Member memindahkan tugas orang lain | Assignee = orang lain | 403 | Sesuai | Berhasil |
| Pindah lewat menu status | `moveTaskToStatus` | Berhasil | Sesuai | Berhasil |
| `completed_at` | Pindah ke/dari `completed` | Diisi saat completed, dikosongkan saat keluar | Sesuai | Berhasil |
| Proyek arsip | Pindah status | Ditolak, termasuk Admin | Sesuai | Berhasil |
| Status invalid | `status=unknown` | Error validasi, tugas tidak berubah | Sesuai | Berhasil |
| Filter board | Search, assignee, priority, due | Hasil sesuai | Sesuai | Berhasil |
| Reorder | `position` baru | Urutan berubah | Sesuai | Berhasil |
| Reorder oleh Member | `PATCH reorder` | 403 | Sesuai | Berhasil |
| Status append | Pindah ke kolom tujuan | Tugas masuk di akhir kolom | Sesuai | Berhasil |

### Komentar, aktivitas, dan notifikasi

| Skenario | Data masukan | Hasil yang diharapkan | Hasil aktual | Status |
|---|---|---|---|---|
| Anggota berkomentar | `body` valid | Komentar tersimpan, log `comment.created` | Sesuai | Berhasil |
| Orang luar berkomentar | `body` valid | 403 | Sesuai | Berhasil |
| Body kosong | `body=''` | Error validasi | Sesuai | Berhasil |
| Edit komentar | Penulis/Admin vs anggota lain | Penulis/Admin berhasil, lain 403 | Sesuai | Berhasil |
| Hapus komentar | Penulis/Admin vs anggota lain | Penulis/Admin berhasil, lain 403 | Sesuai | Berhasil |
| Proyek arsip | Mutation komentar | Ditolak | Sesuai | Berhasil |
| Scoping komentar | Komentar milik tugas lain | 404 | Sesuai | Berhasil |
| Log create tugas | Membuat tugas | Baris `task.created` | Sesuai | Berhasil |
| Log status | Mengubah status | `task.status_changed` dengan old/new | Sesuai | Berhasil |
| Log assignee/prioritas/tenggat | Mengubah field tersebut | Log sesuai field | Sesuai | Berhasil |
| Tanpa perubahan | Menyimpan nilai sama | Tidak ada log baru | Sesuai | Berhasil |
| Timeline tampil | Halaman tugas/proyek | Teks aktivitas terlihat | Sesuai | Berhasil |
| Notifikasi penugasan | Assignee baru | Notifikasi database untuk assignee | Sesuai | Berhasil |
| Unassign | Assignee dikosongkan | Tidak ada notifikasi | Sesuai | Berhasil |
| Listener queued | Cek antarmuka | `ShouldQueue` dan koneksi database | Sesuai | Berhasil |
| Pengingat H-1 | Tugas due besok, belum selesai | Assignee menerima `TaskDueSoonNotification` | Sesuai | Berhasil |
| Penyimpanan notifikasi | Command dijalankan | Baris di tabel `notifications` | Sesuai | Berhasil |
| Jadwal harian | `Schedule` | Command terjadwal `dailyAt('08:00')` | Sesuai | Berhasil |

### Dashboard dan data

| Skenario | Data masukan | Hasil yang diharapkan | Hasil aktual | Status |
|---|---|---|---|---|
| Statistik Admin | Siklus status tugas | Angka sesuai status | Sesuai | Berhasil |
| Proyek terhapus | Proyek soft delete + tugas | Tugas tidak dihitung | Sesuai | Berhasil |
| Statistik Manager | Proyek yang dikelola vs bukan | Hanya proyek yang dikelola | Sesuai | Berhasil |
| Statistik Member | Tugas yang ditugaskan | Hanya tugas sendiri di proyek yang diikuti | Sesuai | Berhasil |
| Manager global tanpa pivot | `users.role=manager` | Dashboard Member | Sesuai | Berhasil |
| Member tanpa proyek | Tidak ada proyek | Dashboard kosong tanpa error | Sesuai | Berhasil |
| Jumlah query | Menambah data | Jumlah query tidak bertambah (hindari N+1) | Sesuai | Berhasil |
| Enum | Nilai enum | Sesuai kontrak database | Sesuai | Berhasil |
| Relasi model | Relasi dan soft delete | Sesuai | Sesuai | Berhasil |
| Seeder | `DemoDataSeeder` | Proyek, tugas, komentar, log terbentuk | Sesuai | Berhasil |

## 9.5 Catatan tentang pengujian yang tidak dapat diklaim

- Tidak ada pengujian browser end-to-end (misalnya Dusk/Playwright). Tes berjalan di level HTTP/Livewire dan komponen, bukan mengklik UI sungguhan.
- Tidak ada tes untuk lupa password, reset password, profil, atau pusat notifikasi karena fitur tersebut belum tersedia.
- Tidak ada pengujian performa formal; hanya ada satu tes yang memastikan jumlah query dashboard tidak bertambah seiring jumlah item.
- Hasil di atas berlaku untuk kondisi kode saat dokumentasi disusun. Jika kode berubah, jalankan ulang `php artisan test`.

## 9.6 Perintah verifikasi lain

```bash
php artisan route:list --except-vendor   # memastikan 32 route
vendor/bin/pint                          # memastikan gaya kode
php artisan migrate:fresh --seed         # membuat ulang database + data demo
php artisan queue:work                   # memproses notifikasi queued
php artisan schedule:work                # menjalankan scheduler lokal
```
