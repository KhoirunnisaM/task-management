# Dokumentasi Teknis TaskFlow

Dokumentasi ini menjelaskan aplikasi **TaskFlow** berdasarkan kode yang benar-benar diterapkan di repositori, bukan berdasarkan asumsi atau rencana PRD. Setiap nama file, route, tabel, dan teknologi dapat ditelusuri kembali ke proyek.

## Daftar isi

| Dokumen | Isi |
|---|---|
| [01 — Gambaran Umum](01-gambaran-umum.md) | Identitas aplikasi, tujuan, latar belakang, manfaat, jenis pengguna, fitur utama, batasan |
| [02 — Teknologi dan Tools](02-teknologi-dan-tools.md) | PHP, Laravel, MySQL, Blade, Livewire, Tailwind, Alpine, SortableJS, Vite, Composer, NPM, Laragon, VS Code, OpenCode, Git/GitHub, paket pengujian |
| [03 — Arsitektur dan Struktur Folder](03-arsitektur-dan-struktur-folder.md) | Alur request, diagram arsitektur, struktur folder aktual, fungsi setiap folder dengan contoh file |
| [04 — Model, Controller, View](04-model-controller-view.md) | Daftar 32 route, hubungan model–controller–request–action–policy–view per fitur |
| [05 — Database dan ERD](05-database-dan-erd.md) | Semua tabel, kolom, primary key, foreign key, index, relasi Eloquent, ERD Mermaid, perbedaan dengan PRD |
| [06 — Alur Kerja Sistem](06-alur-kerja-sistem.md) | Langkah register, login/logout, dashboard, proyek, anggota, tugas, tenggat, Kanban, komentar, aktivitas, profil |
| [07 — Hak Akses Pengguna](07-hak-akses-pengguna.md) | Peran global dan peran proyek, middleware, policy, tabel perbandingan hak akses |
| [08 — Dokumentasi Tampilan](08-dokumentasi-tampilan.md) | Setiap halaman yang tersedia, informasi, tindakan, file view, dan daftar screenshot untuk laporan |
| [09 — Pengujian](09-pengujian.md) | Cara menjalankan tes, hasil nyata 129 tes/619 assertion, skenario dan status |
| [10 — Proses Pengembangan](10-proses-pengembangan.md) | Persiapan lingkungan, database, migration/model, autentikasi, fitur, UI, tes, Git/GitHub, cara menjalankan ulang |

Dokumen pendukung yang sudah ada sebelumnya:

- `docs/admin-members-and-back-navigation.md` — catatan pekerjaan halaman Semua Anggota dan tombol kembali.
- `docs/ui-ux-branding.md` — catatan pekerjaan branding dan UI/UX.

## Cara membaca

- **Untuk presentasi umum:** mulai dari `01`, lalu `08` (tampilan), lalu `06` (alur kerja).
- **Untuk laporan teknis:** baca `03` (arsitektur), `04` (kode), `05` (database), lalu `07` (hak akses).
- **Untuk penguji/dosen:** `09` memuat hasil pengujian nyata; `10` memuat langkah menjalankan aplikasi.
- **Untuk pengembang baru:** `03` → `04` → `05` → jalankan proyek dengan panduan `10`.

## Konvensi penulisan

1. **Berdasarkan kode.** Semua pernyataan merujuk pada file yang ada. Jika sesuatu belum tersedia, ditulis eksplisit **belum tersedia**.
2. **Path lengkap.** Nama file ditulis relatif dari akar proyek, misalnya `app/Http/Controllers/TaskController.php`.
3. **Nama route apa adanya.** Contoh: `projects.tasks.board`, `admin.members.index`.
4. **Diagram Mermaid.** Diagram arsitektur, ERD, dan alur kerja memakai sintaks Mermaid sehingga dapat dirender di GitHub, GitLab, VS Code, atau alat lain yang mendukung Mermaid.
5. **Bahasa Indonesia natural.** Istilah teknis yang lazim (route, controller, policy, migration) tetap dipakai agar mudah dicari di kode.

## Status implementasi ringkas

| Area | Status |
|---|---|
| Registrasi, login, logout | Tersedia |
| Lupa/reset password | Belum tersedia |
| Dashboard Admin/Manager/Member | Tersedia |
| CRUD proyek | Tersedia |
| Anggota proyek | Tersedia |
| CRUD tugas | Tersedia |
| Tenggat tugas | Tersedia |
| Papan Kanban + drag-and-drop | Tersedia |
| Komentar | Tersedia (inline di detail tugas) |
| Activity log | Tersedia |
| Notifikasi database | Tersedia (belum ada UI) |
| Halaman admin anggota | Tersedia |
| Profil pengguna | Belum tersedia |
| Pusat notifikasi | Belum tersedia |

## Hasil verifikasi terakhir

| Pemeriksaan | Hasil |
|---|---|
| `php artisan test` | 129 tes, 619 assertion, semua lulus |
| `vendor/bin/pint` | Lulus |
| `php artisan route:list --except-vendor` | 32 route |
| Repositori Git | Branch `main`, working tree bersih saat dokumentasi disusun |

## Struktur dokumentasi

```
docs/
├─ README.md                        ← Anda di sini
├─ 01-gambaran-umum.md
├─ 02-teknologi-dan-tools.md
├─ 03-arsitektur-dan-struktur-folder.md
├─ 04-model-controller-view.md
├─ 05-database-dan-erd.md
├─ 06-alur-kerja-sistem.md
├─ 07-hak-akses-pengguna.md
├─ 08-dokumentasi-tampilan.md
├─ 09-pengujian.md
├─ 10-proses-pengembangan.md
├─ admin-members-and-back-navigation.md   (dokumen lama)
└─ ui-ux-branding.md                      (dokumen lama)
```
