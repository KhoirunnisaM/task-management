# TaskFlow — Branding dan Panduan UI/UX

Dokumen ini menjelaskan identitas visual **TaskFlow**, aset branding, struktur layout, komponen UI, perubahan halaman, dan implementasi responsivitas yang benar-benar diterapkan pada aplikasi.

Acuan utama tetap [`DESIGN_SYSTEM (Task Management).md`](../DESIGN_SYSTEM%20(Task%20Management).md). Perubahan pada tahap ini tidak mengganti design system, melainkan menerapkannya secara konsisten dan menambahkan identitas TaskFlow.

---

## 1. Nama dan Identitas Aplikasi

| Atribut | Nilai |
|---|---|
| Nama aplikasi | **TaskFlow** |
| Tagline | **Plan • Organize • Achieve** |
| Karakter | Modern, minimalis, profesional, bersih, mudah dipindai |
| Konteks | Aplikasi manajemen proyek dan tugas untuk tim kecil–menengah |

Nama aplikasi disimpan pada `lang/id/common.php` sebagai `common.app_name`, sehingga judul halaman, sidebar, layout autentikasi, dan metadata aplikasi memakai satu sumber yang sama.

## 2. Makna Nama dan Tagline

- **Task** — tugas, pekerjaan, dan tanggung jawab yang harus diselesaikan.
- **Flow** — alur kerja yang terorganisasi dan berjalan lancar dari perencanaan hingga penyelesaian.
- **Plan • Organize • Achieve** — tiga tahap utama penggunaan aplikasi: merencanakan proyek, mengatur tugas dan tim, lalu mencapai target.

## 3. Konsep dan Makna Logo

Logo TaskFlow adalah kombinasi **checklist** dan **papan/folder tugas**:

- Kotak membulat melambangkan papan atau folder pekerjaan yang rapi.
- Tiga baris checklist melambangkan daftar tugas yang dipantau dan diselesaikan.
- Gradasi biru–indigo (`#4A5CE0` → `#283AA3`) memberi kesan modern dan profesional tanpa keluar dari palet brand.
- Pada logo lengkap, kata **Task** berwarna navy gelap (`#172033`) dan **Flow** berwarna biru brand (`#3347C8`).
- Tagline ditampilkan pada halaman autentikasi dan pada logo lengkap SVG.

Logo dibuat sebagai SVG agar tajam di semua ukuran dan mudah diedit.

## 4. Lokasi File Aset Logo

| File | Kegunaan |
|---|---|
| `public/images/taskflow-icon.svg` | Ikon saja; dipakai untuk favicon dan komponen logo in-app |
| `public/images/taskflow-logo.svg` | Logo lengkap dengan wordmark dan tagline; dapat dipakai untuk materi eksternal |
| `public/favicon.ico` | Favicon fallback lama |

Favicon SVG dipasang melalui `layouts/app.blade.php` dan `layouts/guest.blade.php`:

```blade
<link rel="icon" type="image/svg+xml" href="{{ asset('images/taskflow-icon.svg') }}">
<link rel="alternate icon" href="{{ asset('favicon.ico') }}">
```

## 5. Warna dan Tipografi

Warna diambil dari token yang sudah ada di `resources/css/app.css` dan `DESIGN_SYSTEM (Task Management).md`.

| Elemen | Token | Nilai terang |
|---|---|---|
| Background | `canvas` | `#F4F6FA` |
| Surface / card | `surface` | `#FFFFFF` |
| Border | `line` | `#E3E7EE` |
| Teks utama | `content` | `#172033` |
| Teks sekunder | `content-muted` | `#5F6B82` |
| Brand utama | `brand` | `#3347C8` |
| Brand hover | `brand-hover` | `#283AA3` |
| Brand soft | `brand-soft` | `#E6E9FB` |
| Brand text | `brand-text` | `#3347C8` |

Warna status dan prioritas tetap memakai token yang sudah ditetapkan:

- Status: `status-todo`, `status-in-progress`, `status-in-review`, `status-completed` beserta varian `-soft`.
- Prioritas: `priority-low`, `priority-medium`, `priority-high`, `priority-urgent`.
- Semantik: `danger`, `warning`, `success`, `info`.

Mode gelap memakai token yang sama melalui kelas `.dark`; tidak ada palet baru.

Tipografi:

- **Plus Jakarta Sans** untuk UI dan judul (bobot 400, 500, 600, 700).
- **JetBrains Mono** hanya untuk pengenal teknis seperti `TSK-142`.
- Radius: `rounded-control` 6px, `rounded-card` 10px, `rounded-overlay` 14px.
- Spasi mengikuti ritme 4px dan skala Tailwind yang sudah dipakai proyek.

## 6. Struktur Layout Aplikasi

### Layout autentikasi — `resources/views/layouts/guest.blade.php`

- Dua kolom pada desktop: panel brand di kiri dan form di kanan.
- Panel brand memuat logo TaskFlow, tagline, headline, tiga poin nilai aplikasi, dan tahun berjalan.
- Pada mobile panel brand disembunyikan; logo ringkas tampil di atas form.
- Form dibatasi sekitar 420px agar tetap nyaman dibaca.

### Layout aplikasi — `resources/views/layouts/app.blade.php`

- Sidebar tetap 248px pada `lg` ke atas, menjadi drawer pada layar lebih kecil.
- Topbar setinggi 56px dan sticky.
- Konten utama maksimal 1440px, rata kiri, dengan padding responsif.
- Skip-link disediakan untuk pengguna keyboard.

### Sidebar — `resources/views/components/layout/sidebar.blade.php`

- Logo TaskFlow di bagian atas.
- Label bagian **Ruang kerja**.
- Navigasi Dashboard dan Projects dengan ikon.
- State aktif memakai latar `brand-soft`, teks `brand-text`, dan indikator batang brand.
- Kartu pengguna di bagian bawah berisi avatar, nama, dan role.
- Tombol logout yang mudah ditemukan.

Menu hanya memuat route yang sudah ada. Visibilitas konten dan aksi tetap mengikuti policy dan role yang berlaku.

### Topbar — `resources/views/components/layout/topbar.blade.php`

- Tombol drawer pada layar kecil.
- Judul halaman dari `@section('title')`.
- Nama dan role pengguna sebagai metadata.
- Toggle mode gelap/terang.
- Dropdown profil berisi avatar, nama, email, role, dan aksi logout.
- `aria-expanded`, `aria-haspopup`, dan penutupan dengan Escape/klik di luar.

## 7. Komponen UI yang Diperbarui

| Komponen | File | Perubahan |
|---|---|---|
| Logo brand | `components/brand/logo.blade.php` | Komponen logo reusable dengan ikon, wordmark dua warna, tagline opsional, ukuran `sm`/`md`/`lg` |
| Button | `components/ui/button.blade.php` | `whitespace-nowrap`, state disabled yang jelas |
| Input | `components/ui/input.blade.php` | Hover, focus ring, dan border error yang konsisten |
| Select | `components/ui/select.blade.php` | Komponen baru untuk menyeragamkan seluruh select |
| Textarea | `components/ui/textarea.blade.php` | Komponen baru untuk textarea form dan komentar |
| Form field | `components/ui/form-field.blade.php` | Label, hint, dan pesan error konsisten |
| Stat strip | `components/dashboard/stat-strip.blade.php` | Dukungan ikon dan nada warna untuk angka penting |
| Project card | `components/dashboard/project-card.blade.php` | Owner, tanggal selesai, progress, avatar stack |
| Empty state | `components/ui/empty-state.blade.php` | Ikon, judul, deskripsi, dan slot aksi |
| Flash | `components/ui/flash.blade.php` | Notifikasi sukses/gagal dengan ikon dan auto-dismiss |
| Modal / slide-over | `components/ui/modal.blade.php`, `slide-over.blade.php` | Overlay, fokus, dan animasi yang halus |
| Confirm dialog | `components/ui/confirm-dialog.blade.php` | Konfirmasi sebelum aksi hapus |
| Badge status | `components/ui/status-badge.blade.php`, `task-status-badge.blade.php` | Warna status dari token |
| Priority bars | `components/ui/priority-bars.blade.php` | Indikator prioritas berbasis bentuk dan warna |
| Activity timeline | `components/activity/timeline.blade.php` | Avatar, perubahan nilai, dan empty state |

Semua tombol, input, select, textarea, dan kartu memakai radius, border, serta warna dari design system.

## 8. Perubahan pada Masing-masing Halaman

### Login — `resources/views/auth/login.blade.php`

- Kartu form dengan ikon header.
- Logo dan tagline TaskFlow melalui layout guest.
- Tombol utama selebar kartu.
- Link ke halaman daftar.
- Panel akun demo tetap dipertahankan.

### Register — `resources/views/auth/register.blade.php`

- Gaya yang sama dengan login.
- Empat field dengan hint password.
- Link kembali ke login.

### Dashboard — `resources/views/dashboard/*.blade.php`

- Header sapaan dengan badge role dan aksi sesuai role.
- Statistik dengan ikon.
- Ringkasan status tugas.
- Kartu proyek dengan owner, tenggat, progress, dan anggota.
- Daftar tugas dan tenggat terdekat.
- Aktivitas terbaru.
- Empty state informatif.
- Cakupan data Admin, Manager, dan Member tetap berasal dari `DashboardService` yang sudah ada; tidak ada data dummy.

### Daftar Proyek — `resources/views/projects/index.blade.php`

- Header halaman dan tombol tambah proyek.
- Filter pencarian dan status yang seragam.
- Kartu proyek berisi status, owner, deskripsi, progress, rentang tanggal, jumlah task/anggota, avatar stack, dan aksi detail/edit/hapus.
- Hover halus pada kartu.
- Empty state khusus kondisi filter dan kondisi kosong.

### Detail Proyek — `resources/views/projects/show.blade.php`

- Header dengan judul, status, owner, rentang tanggal, jumlah anggota, progress, dan avatar stack.
- Tab Overview, Anggota, dan Aktivitas.
- Ringkasan jumlah task, task selesai, anggota, dan persentase progress.
- Daftar anggota dengan role, pengaturan role, dan konfirmasi hapus anggota.
- Form penambahan anggota memakai select yang konsisten.

### Daftar Task — `resources/views/tasks/index.blade.php`

- Filter pencarian, status, prioritas, assignee, dan tenggat.
- Grup task per status dengan garis atas berwarna status dan count pill.
- Kartu task tetap mempertahankan pita status di sisi kiri.
- Empty state dengan aksi tambah task.

### Kanban Board — `resources/views/livewire/tasks/board.blade.php`

- Empat kolom: Todo, In Progress, In Review, Completed.
- Garis atas kolom memakai warna status dan count pill.
- Horizontal scroll dengan snap pada layar kecil.
- Filter search, assignee, prioritas, dan tenggat.
- Drag and drop, perubahan status, dan penyimpanan tetap memakai implementasi Sortable + Livewire yang sudah ada.

### Detail Task — `resources/views/tasks/show.blade.php`

- Header dengan ID task, status, penanda overdue, judul, dan proyek.
- Panel deskripsi, form ubah status, dan detail task (prioritas, tenggat, assignee, pembuat, tanggal dibuat).
- Komentar dengan avatar, waktu, aksi edit/hapus, modal edit, dan konfirmasi hapus.
- Timeline aktivitas dan empty state komentar.

### Form Proyek dan Task

- `resources/views/projects/partials/form.blade.php`
- `resources/views/tasks/partials/form.blade.php`
- `resources/views/tasks/partials/status-form.blade.php`

Memakai input, select, dan textarea yang seragam, label opsional, hint, pesan error, serta tombol simpan/batal yang konsisten.

### Halaman Profil, Lupa Password, dan Reset Password

Halaman profil, lupa password, dan reset password **belum tersedia** di proyek ini (tidak ada route, controller, maupun view). Karena itu halaman tersebut tidak ditambahkan atau diubah pada tahap branding ini agar tidak mengubah struktur route dan logika autentikasi yang sudah berjalan.

## 9. Implementasi Responsivitas

| Area | Perilaku |
|---|---|
| Sidebar | Tetap 248px pada `lg`; drawer dari kiri dengan overlay pada layar kecil |
| Topbar | Judul dan metadata menyusut/terpotong dengan aman; drawer trigger muncul di bawah `lg` |
| Auth layout | Dua kolom pada `lg`; satu kolom dan logo ringkas pada mobile |
| Dashboard | Statistik 2 kolom → 3/4/5/6/7 kolom sesuai breakpoint; panel menumpuk pada mobile |
| Project cards | Satu kolom pada mobile, dua kolom pada `sm` ke atas |
| Filter | Menumpuk vertikal pada mobile, sejajar pada layar lebih besar |
| Task list | Grup status tetap satu kolom; metadata dibungkus agar tidak meluber |
| Kanban | Kolom 304px dengan `max-w-[calc(100vw-2rem)]`, horizontal scroll dan `snap-x` pada mobile |
| Form | Grid field menyesuaikan lebar; tombol tetap dapat disentuh |
| Modal / slide-over | Lebar penuh pada mobile, ukuran tetap pada desktop |
| Tabel/daftar | Konten lebar bergulir di dalam wadahnya; halaman tidak bergulir horizontal |

Breakpoint mengikuti design system: `sm` 640, `md` 768, `lg` 1024, `xl` 1280, `2xl` 1536.

## 10. Cara Menggunakan Kembali Logo dan Komponen

### Logo

```blade
{{-- Logo dengan link dan tagline --}}
<x-brand.logo :href="route('dashboard')" tagline size="md" />

{{-- Logo tanpa tagline --}}
<x-brand.logo size="sm" />
```

Ukuran yang tersedia: `sm`, `md`, `lg`. Komponen memakai `public/images/taskflow-icon.svg` dan menyusun wordmark dengan HTML agar tetap terbaca pada mode gelap.

### Select

```blade
<x-ui.select id="status" name="status" required>
    @foreach ($statuses as $status)
        <option value="{{ $status->value }}">{{ $status->label() }}</option>
    @endforeach
</x-ui.select>
```

### Textarea

```blade
<x-ui.textarea
    name="description"
    rows="5"
    :value="old('description', $model->description)"
    placeholder="Tulis deskripsi..."
/>
```

### Tombol, input, dan form field

```blade
<x-ui.button type="submit">{{ __('common.save') }}</x-ui.button>
<x-ui.button :href="route('projects.index')" variant="secondary">Batal</x-ui.button>

<x-ui.form-field :label="__('projects.title_field')" name="title">
    <x-ui.input name="title" :value="old('title', $project->title)" required />
</x-ui.form-field>
```

### Komponen dashboard

```blade
<x-dashboard.header :role="$role" />

<x-dashboard.stat-strip :items="[
    ['label' => 'Proyek', 'value' => 8, 'icon' => $iconPath],
]" />

<x-dashboard.panel title="Proyek terbaru" :empty="$projects->isEmpty()">
    ...
</x-dashboard.panel>
```

---

## Verifikasi

- `npm run build` berhasil (Vite + Tailwind).
- `vendor/bin/pint` lulus.
- `php artisan test` lulus: **108 tes, 524 assertion**.
- Favicon SVG dan aset logo tersedia di `public/images/`.
- Tidak ada route, controller, model, skema database, atau dependency baru yang diubah untuk branding ini.
