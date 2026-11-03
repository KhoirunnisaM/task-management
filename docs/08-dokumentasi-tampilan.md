# 08 — Dokumentasi Tampilan Aplikasi

Dokumen ini menjelaskan halaman yang benar-benar tersedia, informasi yang ditampilkan, tindakan yang dapat dilakukan, dan berkas view yang dipakai. Daftar screenshot untuk laporan dicantumkan di bagian akhir.

## 8.1 Kerangka tampilan

### Layout aplikasi (setelah login)

Berkas: `resources/views/layouts/app.blade.php`.

Bagian:

- **Sidebar** (`components/layout/sidebar.blade.php`) — logo TaskFlow, menu **Dashboard** dan **Proyek**, menu **Semua Anggota** khusus Admin, kartu pengguna (avatar, nama, peran), dan tombol keluar.
- **Topbar** (`components/layout/topbar.blade.php`) — tombol buka sidebar (mobile), judul halaman, nama + peran pengguna, tombol tema gelap/terang, dan menu pengguna berisi email, peran, serta tombol keluar.
- **Konten utama** — maksimal 1440px, padding responsif, menampilkan flash message (`x-ui.flash`) dan isi halaman.
- **Skip link** — tautan "Lewati ke konten" untuk pengguna keyboard.

Sidebar tetap di kiri pada layar ≥ `lg` dan menjadi panel geser pada layar kecil.

### Layout tamu (login/registrasi)

Berkas: `resources/views/layouts/guest.blade.php`.

Pada layar lebar, halaman terbagi dua: panel kiri berisi logo, headline, deskripsi, dan tiga keunggulan TaskFlow; panel kanan berisi form. Pada layar kecil, panel kiri disembunyikan dan logo tampil di atas form.

### Tema gelap

Tombol tema di topbar mengubah kelas `dark` pada `<html>` dan menyimpan pilihan di `localStorage` dengan kunci `theme`. Skrip awal di `layouts/partials/theme-script.blade.php` mencegah kedipan tema saat halaman dimuat. Token warna gelap didefinisikan di `resources/css/app.css`.

## 8.2 Halaman Login

| Aspek | Keterangan |
|---|---|
| URL | `/login` (route `login`) |
| Berkas | `resources/views/auth/login.blade.php`, `layouts/guest.blade.php` |
| Fungsi | Masuk ke aplikasi |
| Informasi | Judul "Masuk ke TaskFlow", subjudul, ikon, field email dan password, tautan ke registrasi |
| Tindakan | Mengisi kredensial, mencentang "Ingat saya", mengirim form, membuka halaman registrasi |
| Otorisasi | Middleware `guest`; pengguna yang sudah login dialihkan |

Tidak ada tombol "Kembali" di halaman login karena ini titik masuk aplikasi. Tautan "Buat akun" mengarah ke `register`.

## 8.3 Halaman Registrasi

| Aspek | Keterangan |
|---|---|
| URL | `/register` (route `register`) |
| Berkas | `resources/views/auth/register.blade.php`, `layouts/guest.blade.php`, `components/ui/back-button.blade.php` |
| Fungsi | Membuat akun baru |
| Informasi | Field nama lengkap, email, password, konfirmasi password, petunjuk minimal 8 karakter, tautan kembali ke login |
| Tindakan | Mengisi dan mengirim form; kembali ke login |
| Otorisasi | Middleware `guest`; akun selalu dibuat dengan peran Member |

## 8.4 Dashboard

| Aspek | Keterangan |
|---|---|
| URL | `/dashboard` (route `dashboard`) |
| Berkas | `resources/views/dashboard.blade.php`, `dashboard/admin.blade.php`, `dashboard/manager.blade.php`, `dashboard/member.blade.php` |
| Komponen | `components/dashboard/header.blade.php`, `stat-strip.blade.php`, `panel.blade.php`, `project-card.blade.php`, `task-row.blade.php`, `components/activity/timeline.blade.php` |
| Fungsi | Ringkasan pekerjaan sesuai peran |
| Tindakan | Membuka proyek, membuka tugas, berpindah ke daftar proyek, melihat aktivitas |

**Dashboard Admin** menampilkan:

- header peran Admin;
- strip statistik: total proyek, total pengguna, proyek aktif, proyek selesai, total tugas, tugas overdue;
- panel proyek terbaru (maks 6) dan ringkasan status tugas;
- panel proyek berjalan (maks 5) dan aktivitas terbaru.

**Dashboard Manager** menampilkan:

- strip statistik proyek yang dikelola: jumlah proyek, total tugas, todo, dikerjakan, ditinjau, selesai, overdue;
- panel proyek yang dikelola dan aktivitas;
- daftar tugas terbaru, tugas jatuh tempo ≤ 7 hari, dan anggota tim;
- ringkasan empat status tugas.

**Dashboard Member** menampilkan:

- strip statistik: tugas ditugaskan, todo, dikerjakan, ditinjau, selesai, overdue;
- daftar **Tugas Saya** (belum selesai, urut tenggat);
- tugas jatuh tempo dan tugas terlambat;
- ringkasan status, proyek terbaru, dan aktivitas dari proyek yang diikuti.

Semua dashboard memakai tombol kembali kontekstual (`x-ui.back-button`) dengan fallback ke dashboard.

## 8.5 Daftar Proyek

| Aspek | Keterangan |
|---|---|
| URL | `/projects` (route `projects.index`) |
| Berkas | `resources/views/projects/index.blade.php` |
| Fungsi | Menampilkan proyek yang dapat diakses pengguna |
| Informasi | Judul, pemilik (avatar + nama), status badge, deskripsi ringkas (2 baris), progress bar, periode, jumlah tugas, jumlah anggota, avatar anggota |
| Tindakan | Mencari judul, memfilter status, mereset filter, membuka detail, mengedit (jika berhak), menghapus (Admin), membuat proyek (Admin) |
| Data | 9 proyek per halaman, dengan pagination dan query string |

Kartu proyek menyembunyikan tombol **Edit** dan **Hapus** bila pengguna tidak berhak.

## 8.6 Detail Proyek

| Aspek | Keterangan |
|---|---|
| URL | `/projects/{project}` (route `projects.show`) |
| Berkas | `resources/views/projects/show.blade.php` |
| Fungsi | Pusat informasi satu proyek |
| Informasi | Judul, status, pemilik, periode, jumlah anggota, progress bar, avatar anggota, banner peringatan bila proyek arsip |
| Tab | **Ringkasan**, **Anggota**, **Aktivitas** (Alpine `tab`), plus tautan ke **Board** dan **Daftar tugas** |
| Tindakan | Membuat tugas, mengedit proyek, menghapus proyek, menambah anggota, mengubah peran, mengeluarkan anggota, membuka board/daftar tugas |

**Tab Ringkasan** menampilkan deskripsi, pemilik, periode, jumlah tugas, tugas selesai, jumlah anggota, dan persentase progres.

**Tab Anggota** menampilkan daftar anggota (pemilik di urutan atas), avatar, nama, email, badge peran, dropdown ubah peran, tombol keluarkan, serta panel tambah anggota. Jika semua pengguna sudah menjadi anggota, muncul pesan bahwa tidak ada calon anggota. Proyek arsip menampilkan pemberitahuan read-only.

**Tab Aktivitas** menampilkan timeline 30 aktivitas terbaru proyek.

## 8.7 Daftar Tugas

| Aspek | Keterangan |
|---|---|
| URL | `/projects/{project}/tasks` (route `projects.tasks.index`) |
| Berkas | `resources/views/tasks/index.blade.php`, `components/task/card.blade.php`, `components/task/due-date.blade.php` |
| Fungsi | Menampilkan tugas per status dalam bentuk daftar bertingkat |
| Informasi | Empat bagian status (Belum dimulai, Dikerjakan, Ditinjau, Selesai) dengan jumlah tugas; setiap kartu menampilkan judul, penanggung jawab, prioritas, tenggat, dan status |
| Tindakan | Mencari, memfilter status/prioritas/penanggung jawab/tenggat, mereset filter, mengubah status, menaikkan/menurunkan urutan, mengedit, menghapus, membuat tugas, membuka detail |
| Data | Tanpa pagination; dikelompokkan per status dan diurutkan berdasarkan `position` lalu `id` |

Filter berada dalam satu panel: pencarian (2 kolom di desktop), status, prioritas, penanggung jawab, dan filter tenggat (overdue, hari ini, 7 hari, tanpa tenggat). Tombol naik/turun urutan hanya muncul saat tidak ada filter aktif.

## 8.8 Kanban Board

| Aspek | Keterangan |
|---|---|
| URL | `/projects/{project}/board` (route `projects.tasks.board`) |
| Berkas halaman | `resources/views/tasks/board.blade.php` |
| Komponen | `app/Livewire/Tasks/Board.php`, `resources/views/livewire/tasks/board.blade.php`, `components/task/card.blade.php` |
| Fungsi | Papan empat kolom untuk memindahkan tugas antar status |
| Informasi | Empat kolom dengan titik warna status, jumlah kartu, daftar kartu tugas, dan indikator loading |
| Tindakan | Drag-and-drop kartu, memilih status dari dropdown kartu, memfilter pencarian/assignee/prioritas/tenggat, mereset filter, membuat tugas, membuka detail |
| Perilaku responsif | Di layar kecil, kolom berukuran 304px dan dapat digeser horizontal dengan snap; di layar ≥ `lg`, empat kolom tampil sebagai grid |

Kartu yang tidak boleh dipindahkan pengguna diberi `data-draggable="false"` sehingga tidak dapat diseret, dan dropdown pindah status tidak dirender.

## 8.9 Detail Tugas

| Aspek | Keterangan |
|---|---|
| URL | `/projects/{project}/tasks/{task}` (route `projects.tasks.show`) |
| Berkas | `resources/views/tasks/show.blade.php`, `tasks/partials/status-form.blade.php`, `components/activity/timeline.blade.php` |
| Fungsi | Menampilkan seluruh informasi satu tugas, komentar, dan riwayat |
| Informasi | Kode `TSK-{id}`, status badge, label overdue, judul, nama proyek, deskripsi, prioritas (batang), tenggat, penanggung jawab, pembuat, waktu dibuat, jumlah komentar, daftar komentar, timeline aktivitas |
| Tindakan | Mengedit, menghapus, mengubah status (jika berhak), menambah komentar, mengedit/menghapus komentar sendiri (atau semua komentar bila Admin) |

Komentar ditampilkan urut dari terlama, lengkap dengan avatar, nama, waktu, dan label "diedit" bila `updated_at` lebih baru dari `created_at`. Edit komentar memakai modal; hapus komentar memakai dialog konfirmasi.

## 8.10 Form Tambah/Edit Tugas

| Aspek | Keterangan |
|---|---|
| URL | `/projects/{project}/tasks/create` dan `/projects/{project}/tasks/{task}/edit` |
| Berkas | `resources/views/tasks/create.blade.php`, `tasks/edit.blade.php`, `tasks/partials/form.blade.php` |
| Informasi | Judul, prioritas (select), **Tenggat (opsional)** (`input type="date"`), penanggung jawab (select anggota), deskripsi (textarea), petunjuk pembuatan tugas |
| Tindakan | Menyimpan, membatalkan (kembali ke daftar/detail), mengubah status dari halaman edit |

Kolom tenggat memakai input tanggal native browser. Saat membuat tugas, `min` diisi tanggal hari ini; saat mengedit, `min` tidak dipasang dan nilai tersimpan ditampilkan kembali. Kolom boleh dikosongkan.

## 8.11 Form Tambah/Edit Proyek

| Aspek | Keterangan |
|---|---|
| URL | `/projects/create` dan `/projects/{project}/edit` |
| Berkas | `resources/views/projects/create.blade.php`, `projects/edit.blade.php`, `projects/partials/form.blade.php` |
| Informasi | Judul, status, tanggal mulai (opsional), tanggal selesai (opsional), deskripsi (opsional) |
| Tindakan | Menyimpan atau membatalkan |

## 8.12 Halaman Admin — Semua Anggota

| Aspek | Keterangan |
|---|---|
| URL | `/admin/members` (route `admin.members.index`) |
| Berkas | `resources/views/admin/members/index.blade.php` |
| Fungsi | Daftar seluruh pengguna aplikasi (khusus Admin) |
| Informasi | Jumlah total anggota, daftar pengguna dengan nomor, avatar, nama, email, badge peran global, tanggal bergabung, tombol detail |
| Tindakan | Mencari nama/email, memfilter peran, mengurutkan (terbaru, terlama, nama A–Z, nama Z–A), mereset filter, membuka detail |
| Data | 10 pengguna per halaman |

Tabel berubah menjadi kartu bertumpuk di layar kecil; header kolom hanya tampil di layar ≥ `sm`.

## 8.13 Halaman Admin — Detail Anggota

| Aspek | Keterangan |
|---|---|
| URL | `/admin/members/{user}` (route `admin.members.show`) |
| Berkas | `resources/views/admin/members/show.blade.php` |
| Fungsi | Melihat profil akun dan keterlibatan proyek seorang pengguna |
| Informasi | Avatar, nama, email, peran global, tanggal bergabung, jumlah proyek, informasi akun, catatan peran, daftar proyek beserta peran pivot dan status proyek |
| Tindakan | Kembali ke daftar anggota, membuka detail proyek |

Halaman ini bersifat baca-saja; tidak ada tombol ubah peran global atau hapus pengguna.

## 8.14 Halaman yang belum tersedia

| Halaman | Status |
|---|---|
| Profil pengguna | Belum tersedia |
| Ubah password | Belum tersedia |
| Lupa password | Belum tersedia |
| Reset password | Belum tersedia |
| Pusat notifikasi | Belum tersedia (notifikasi hanya tersimpan di database) |
| Halaman komentar terpisah | Belum tersedia (komentar inline di detail tugas) |
| Pengaturan aplikasi | Belum tersedia |

## 8.15 Daftar screenshot yang perlu disiapkan untuk laporan

Urutan berikut disarankan agar laporan mengalir dari masuk sampai fitur kolaborasi.

1. **Halaman login** — tampilan desktop dengan panel branding.
2. **Halaman registrasi** — form nama, email, password, konfirmasi.
3. **Dashboard Admin** — strip statistik dan panel proyek/aktivitas.
4. **Dashboard Manager** — statistik proyek yang dikelola dan daftar tugas terbaru.
5. **Dashboard Member** — Tugas Saya, tugas jatuh tempo, tugas terlambat.
6. **Daftar proyek** — beberapa kartu dengan progress bar dan filter.
7. **Detail proyek — tab Ringkasan** — header, progress, deskripsi.
8. **Detail proyek — tab Anggota** — daftar anggota, dropdown peran, panel tambah anggota.
9. **Detail proyek — tab Aktivitas** — timeline.
10. **Daftar tugas** — empat kelompok status dan panel filter.
11. **Kanban Board** — empat kolom berisi kartu tugas.
12. **Kanban Board saat drag-and-drop** — kartu terangkat/transparan (opsional).
13. **Form tambah tugas** — termasuk kolom **Tenggat (opsional)** yang terbuka.
14. **Form edit tugas** — memperlihatkan tanggal tersimpan.
15. **Detail tugas** — header, deskripsi, detail kanan, komentar.
16. **Detail tugas — komentar dan timeline aktivitas**.
17. **Halaman Semua Anggota (Admin)** — pencarian, filter peran, tabel pengguna.
18. **Detail anggota (Admin)** — informasi akun dan daftar proyek.
19. **Tema gelap** — salah satu halaman utama dalam mode gelap.
20. **Tampilan mobile** — sidebar terbuka dan papan Kanban yang digeser horizontal.

Tips pengambilan gambar:

- Gunakan data dari `DemoDataSeeder` agar tampilan tidak kosong.
- Ambil pada lebar desktop (misalnya 1440px) dan mobile (misalnya 390px).
- Pastikan flash message dan dialog konfirmasi ikut terdokumentasi bila relevan.
- Simpan gambar di folder terpisah (misalnya `docs/images/`) dan rujuk dari laporan.
