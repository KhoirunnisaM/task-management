# 02 — Teknologi dan Tools yang Digunakan

Dokumen ini hanya mencantumkan teknologi yang benar-benar ada pada `composer.json`, `package.json`, konfigurasi proyek, dan kode. Perbedaan antara rencana PRD dan implementasi nyata dijelaskan di bagian akhir.

## 2.1 Ringkasan tumpukan teknologi

| Lapisan | Teknologi | Versi (dari berkas konfigurasi) |
|---|---|---|
| Bahasa backend | PHP | `^8.3` |
| Framework | Laravel | `^13.17` |
| Database pengembangan | MySQL | via Laragon, basis data `task_manager` |
| Database pengujian | SQLite | `:memory:` (lihat `phpunit.xml`) |
| Templating | Blade | bawaan Laravel |
| Komponen reaktif | Livewire | `^4.4` |
| CSS | Tailwind CSS | `^4.0.0` (CSS-first, `@import 'tailwindcss'`) |
| Interaksi klien | Alpine.js | `^3.17.5` + `@alpinejs/focus` |
| Drag-and-drop | SortableJS | `^1.15.7` |
| Build tool | Vite + `laravel-vite-plugin` | Vite `^8.0.0`, plugin `^3.1` |
| Manajemen PHP | Composer | `composer.json` / `composer.lock` |
| Manajemen aset | NPM | `package.json` / `package-lock.json` |
| Pengujian | PHPUnit | `^12.5.12` |
| Formatter | Laravel Pint | `^1.27` |
| Data palsu | FakerPHP | `^1.23` |
| Debug/log | Laravel Pail | `^1.2.5` |
| Paket pengembangan | Laravel Pao | `^1.0.6` (tidak dipakai langsung di kode) |
| REPL | Laravel Tinker | `^3.0` |
| Lingkungan lokal | Laragon | Windows, Apache/Nginx + MySQL |
| Editor | Visual Studio Code | dipakai untuk menyunting kode |
| Agen pengembangan | OpenCode | dipakai untuk pemeriksaan dan dokumentasi |
| Version control | Git + GitHub | remote `KhoirunnisaM/task-management` |

## 2.2 PHP dan Laravel

**PHP 8.3** menjadi bahasa utama. Kode memanfaatkan fitur modern seperti enum (`App\Enums\*`), *readonly property* pada event (`app/Events/*.php`), *first-class callable*, dan tipe kembalian yang tegas.

**Laravel 13.17** menyediakan seluruh fondasi aplikasi:

- routing (`routes/web.php`),
- Eloquent ORM dan migration,
- Form Request untuk validasi,
- Policy untuk otorisasi,
- event/listener/notification,
- scheduler (`routes/console.php`),
- queue dengan driver `database`,
- Blade dan komponen anonim,
- fasilitas pengujian HTTP (`tests/Feature`).

Konfigurasi aplikasi berada di `config/` dan `bootstrap/app.php`. Berkas `bootstrap/app.php` memakai gaya konfigurasi Laravel modern: routing web, command, health check `/up`, middleware, dan penanganan exception. Tidak ada middleware kustom yang didaftarkan; middleware yang dipakai adalah bawaan Laravel (`auth`, `guest`) dan middleware otorisasi route (`can:...`).

## 2.3 MySQL dan Laragon

Database pengembangan menggunakan MySQL dengan konfigurasi pada `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=root
DB_PASSWORD=
```

Laragon menyediakan server MySQL, virtual host `task-manager.test`, dan PHP CLI yang dipakai untuk `php artisan`. Untuk pengujian, `phpunit.xml` menetapkan koneksi SQLite in-memory agar setiap tes berjalan cepat dan tidak mengganggu data pengembangan.

Perbedaan tipe data yang perlu diketahui: kolom `tasks.due_date` bertipe `date`, sedangkan `projects.start_date` dan `projects.end_date` juga `date`. Factory mengisi `due_date` sebagai string tanggal, dan model melakukan cast `'due_date' => 'date'`.

## 2.4 Blade

Blade dipakai untuk seluruh tampilan di `resources/views`. Pola yang digunakan:

- **Layout:** `layouts/app.blade.php` (halaman setelah login) dan `layouts/guest.blade.php` (login/registrasi).
- **Komponen anonim:** `resources/views/components/ui/*` (input, select, textarea, button, modal, confirm-dialog, badge, avatar, progress-bar, dll.), `components/dashboard/*`, `components/task/*`, `components/activity/*`, `components/layout/*`.
- **Partial:** `projects/partials/form.blade.php`, `tasks/partials/form.blade.php`, `tasks/partials/status-form.blade.php`.
- **Direktif otorisasi:** `@can`, `@cannot`, `@canany`, `@endcan` untuk menyembunyikan tombol yang tidak berhak dipakai.

Contoh komponen input yang dipakai di banyak form: `resources/views/components/ui/input.blade.php`. Komponen ini menggabungkan atribut, menandai error `aria-invalid`, dan menyusun `aria-describedby` otomatis.

## 2.5 Livewire

Livewire dipakai pada **satu komponen**: `app/Livewire/Tasks/Board.php` dengan tampilan `resources/views/livewire/tasks/board.blade.php`. Komponen ini menangani:

- filter papan (pencarian, penanggung jawab, prioritas, tenggat) dengan `wire:model.live`,
- pemindahan tugas antar kolom (`moveTask`) dan menu pindah status (`moveTaskToStatus`),
- otorisasi ulang pada setiap aksi melalui `$this->authorize(...)`.

Halaman `resources/views/tasks/board.blade.php` memanggil komponen tersebut dengan `<livewire:tasks.board :project="$project" />`.

Livewire **tidak** dipakai untuk CRUD proyek, CRUD tugas, komentar, autentikasi, atau dashboard; semuanya memakai alur HTTP controller biasa.

## 2.6 Tailwind CSS v4

Tailwind v4 dipakai dengan pendekatan CSS-first pada `resources/css/app.css`:

```css
@import 'tailwindcss';
@custom-variant dark (&:where(.dark, .dark *));
```

Token warna didefinisikan sebagai CSS variable (`--canvas`, `--surface`, `--line`, `--content`, `--brand`, `--status-*`, `--priority-*`) lalu dipetakan menjadi utilitas seperti `bg-canvas`, `text-content-muted`, `border-line`, `bg-status-completed-soft`. Mode gelap diaktifkan dengan kelas `.dark` pada `<html>`.

Alasan pemakaian: konsistensi visual dengan `DESIGN_SYSTEM (Task Management).md`, ukuran CSS lebih kecil karena hanya kelas yang dipakai yang dihasilkan, dan token semantik memudahkan penyesuaian tema terang/gelap.

## 2.7 Alpine.js

Alpine.js menangani interaksi ringan tanpa membuat komponen penuh:

- buka/tutup sidebar di layar kecil (`sidebarOpen`),
- sakelar tema gelap dan penyimpanan preferensi,
- menu dropdown pengguna di topbar,
- tab di halaman detail proyek (`tab`),
- modal dan confirm dialog (`components/ui/modal.blade.php`, `confirm-dialog.blade.php`),
- data kolom Kanban `kanbanColumn` yang menghubungkan SortableJS dengan Livewire.

Inisialisasi ada di `resources/js/app.js`:

```js
Alpine.data('kanbanColumn', ({ status }) => ({
    sortable: null,
    init() {
        this.sortable = new Sortable(this.$el, {
            group: 'kanban',
            draggable: '[data-draggable="true"]',
            onEnd: (event) => {
                this.$wire.moveTask(Number(event.item.dataset.taskId), targetStatus, event.newIndex);
            },
        });
    },
}));
```

## 2.8 SortableJS

SortableJS menyediakan drag-and-drop antar kolom Kanban. Setelah item dilepas, indeks baru dan status kolom tujuan dikirim ke `moveTask` Livewire, lalu `UpdateTaskStatusAction` menyimpan status, `completed_at`, dan posisi baru. Jika pengguna tidak bisa memindahkan tugas (misalnya Member yang bukan penanggung jawab), kartu ditandai `data-draggable="false"` sehingga tidak dapat diseret.

## 2.9 Vite

Vite membangun `resources/css/app.css` dan `resources/js/app.js` menjadi aset di `public/build`. Blade memuatnya lewat `@vite(['resources/css/app.css', 'resources/js/app.js'])`. Perintah yang tersedia pada `package.json`:

```json
"scripts": { "build": "vite build", "dev": "vite" }
```

## 2.10 Composer

Composer mengelola dependensi PHP dan autoload PSR-4:

```json
"autoload": {
  "psr-4": {
    "App\\": "app/",
    "Database\\Factories\\": "database/factories/",
    "Database\\Seeders\\": "database/seeders/"
  }
}
```

Perintah yang relevan: `composer install`, `composer require`, `vendor/bin/pint`, dan `php artisan`.

## 2.11 NPM

NPM mengelola dependensi frontend pada `package.json`: `tailwindcss`, `@tailwindcss/vite`, `vite`, `laravel-vite-plugin`, `alpinejs`, `@alpinejs/focus`, `sortablejs`, `concurrently`, serta dependensi opsional `@laravel/multiplex`.

## 2.12 Pengujian dan kualitas kode

- **PHPUnit 12.5** menjalankan tes di `tests/Feature` dan `tests/Unit`, dikonfigurasi lewat `phpunit.xml` dengan dua test suite (`Unit`, `Feature`). Tes menggunakan `RefreshDatabase` dan SQLite in-memory.
- **Laravel Pint** menjaga gaya kode PSR-12. Dijalankan dengan `vendor/bin/pint`.
- **FakerPHP** mengisi data palsu melalui `database/factories/*`.
- **Mockery** tersedia untuk mocking pada tes.
- **Collision** mempercantik keluaran error CLI.
- **Laravel Pail** untuk membaca log secara langsung (`php artisan pail`).
- **Laravel Pao** tercantum sebagai dependensi pengembangan pada `composer.json`; tidak ditemukan pemakaian langsung di kode aplikasi.

## 2.13 Git dan GitHub

Proyek memakai Git dengan branch `main` dan remote:

```
origin  https://github.com/KhoirunnisaM/task-management.git
```

Riwayat commit yang ada pada saat dokumentasi disusun:

```
b522c62 chore: checkpoint progress task final
3c8217c chore: checkpoint progress task 7
f6f39e3 chore: checkpoint progress task 5
```

Git dipakai untuk menyimpan checkpoint pekerjaan, sedangkan GitHub menjadi tempat repositori daring. Tidak ada konfigurasi CI/CD di dalam repositori ini.

## 2.14 Laragon, Visual Studio Code, dan OpenCode

- **Laragon** menyediakan PHP, MySQL, dan virtual host lokal sehingga aplikasi dapat diakses di `http://task-manager.test`.
- **Visual Studio Code** dipakai untuk menyunting kode PHP, Blade, CSS, dan JavaScript.
- **OpenCode** adalah agen pengembangan berbasis terminal yang dipakai untuk memeriksa struktur proyek, menjalankan tes, dan menyusun dokumentasi ini. OpenCode bukan bagian dari runtime aplikasi dan tidak menambah dependensi pada `composer.json` maupun `package.json`.

## 2.15 Perbedaan rencana PRD dan implementasi

| PRD menyebut | Implementasi aktual |
|---|---|
| Livewire v3 | `livewire/livewire: ^4.4` |
| Laravel Breeze (varian Livewire) | Tidak dipakai; autentikasi ditulis manual (`Auth\LoginController`, `Auth\RegisterController`) |
| `livewire-sortable` | Tidak dipakai; memakai `sortablejs` langsung |
| Reset password via email | Belum tersedia |
| Halaman profil | Belum tersedia |
| Email notifikasi | Notifikasi hanya channel `database`; `MAIL_MAILER=log` |
| Pest | Tidak dipakai; PHPUnit 12.5 |
| Laravel Debugbar/Telescope | Tidak terpasang |

Dokumen ini sengaja mencatat perbedaan tersebut agar tidak ada klaim teknologi yang tidak sesuai kode.
