# Menu Semua Anggota dan Navigasi Kembali

Dokumen ini menjelaskan implementasi menu **Semua anggota** khusus Admin dan tombol **Kembali** yang dipakai di seluruh halaman TaskFlow.

---

## 1. Fungsi Menu Semua Anggota

Menu **Semua anggota** adalah halaman pengelolaan pengguna yang hanya dapat diakses oleh Admin global. Halaman ini menampilkan seluruh akun yang terdaftar di TaskFlow, bukan hanya anggota pada satu proyek.

Fitur yang tersedia:

- Daftar seluruh pengguna dari tabel `users`.
- Pencarian berdasarkan nama atau email.
- Filter berdasarkan role global (`admin`, `manager`, `member`).
- Pengurutan berdasarkan nama atau tanggal bergabung.
- Pagination 10 pengguna per halaman.
- Empty state untuk kondisi kosong dan kondisi hasil filter kosong.
- Halaman detail pengguna berisi informasi akun dan daftar proyek yang diikuti beserta role proyeknya.

Menu ini tidak menyediakan penghapusan pengguna atau perubahan role karena fitur tersebut tidak termasuk ruang lingkup PRD saat ini.

## 2. Perbedaan Pengguna Global dan Role Anggota Proyek

TaskFlow memakai dua tingkat role:

| Tingkat | Lokasi | Nilai | Fungsi |
|---|---|---|---|
| Role global | `users.role` | `admin`, `manager`, `member` | Menentukan akses tingkat aplikasi, termasuk akses menu Semua anggota |
| Role proyek | `project_user.role` | `manager`, `member` | Menentukan peran pengguna pada proyek tertentu |

Konsekuensinya:

- Admin adalah role global dan dapat melihat seluruh pengguna serta seluruh proyek.
- Manager dan Member pada tabel `users` bukan penentu hak akses proyek.
- Satu pengguna dapat menjadi **Manager** di satu proyek dan **Member** di proyek lain.
- Role proyek dibaca dari pivot `project_user`, bukan disimpulkan dari role global.

Karena itu halaman detail pengguna menampilkan role proyek per baris proyek, bukan satu role proyek global.

## 3. Alur Menampilkan Daftar Pengguna

1. Admin membuka menu **Semua anggota** dari sidebar.
2. Route `admin.members.index` dijalankan.
3. Middleware `auth` memastikan pengguna sudah login.
4. Middleware `can:viewAny,App\Models\User` memanggil `UserPolicy@viewAny`.
5. `IndexUserRequest` memvalidasi parameter `search`, `role`, dan `sort`, sekaligus memeriksa izin `viewAny`.
6. `UserQueryService@paginate` membangun query ke tabel `users`.
7. Query memakai `select` kolom yang diperlukan, filter, sorting, dan pagination.
8. Hasil dikirim ke `resources/views/admin/members/index.blade.php`.
9. Halaman detail diakses melalui `admin.members.show` dan memanggil `UserPolicy@view`.

Query daftar memakai pencarian `LIKE` pada nama/email, filter role, serta `orderBy` yang sudah divalidasi. Tidak ada N+1 query karena daftar hanya membutuhkan kolom `users`; detail proyek dimuat melalui relasi `projects()` pada halaman detail.

## 4. Route, Controller, Model, dan View

### Route

| Method | URI | Name | Middleware |
|---|---|---|---|
| GET | `/admin/members` | `admin.members.index` | `auth`, `can:viewAny,App\Models\User` |
| GET | `/admin/members/{user}` | `admin.members.show` | `auth`, `can:viewAny,App\Models\User` |

Route didefinisikan di `routes/web.php` di dalam grup `auth`.

### Controller

`app/Http/Controllers/AdminMemberController.php`

- `index(IndexUserRequest $request, UserQueryService $query)` — menampilkan daftar pengguna terpaginasikan.
- `show(User $user)` — mengotorisasi `view` lalu menampilkan detail pengguna dan proyek yang diikuti.

### Request

`app/Http/Requests/User/IndexUserRequest.php`

- `authorize()` memeriksa `viewAny` pada `User`.
- `rules()` memvalidasi `search`, `role`, dan `sort`.

### Service

`app/Services/UserQueryService.php`

- Memetakan pilihan sort ke kolom dan arah yang aman.
- Menjalankan pencarian nama/email, filter role, sorting, dan pagination.
- Menggunakan `withQueryString()` agar filter tetap terbawa saat berpindah halaman.

### Policy

`app/Policies/UserPolicy.php`

- `viewAny(User $user)` — hanya Admin global.
- `view(User $user, User $member)` — hanya Admin global.

### Model

`app/Models/User.php`

- `role` di-cast ke `App\Enums\UserRole`.
- `projects()` adalah relasi `belongsToMany` ke `Project` melalui `project_user` dengan pivot `role`.
- `isAdmin()` dipakai oleh policy dan sidebar.

### View

- `resources/views/admin/members/index.blade.php`
- `resources/views/admin/members/show.blade.php`
- `resources/views/components/layout/sidebar.blade.php` — menu Admin.
- `resources/views/components/layout/nav-item.blade.php` — komponen item navigasi reusable.

### Bahasa

- `lang/id/admin_members.php`
- `lang/id/common.php` — label `user_management` dan `all_members`.

## 5. Pencarian, Filter, Sorting, dan Pagination

- **Pencarian:** parameter `search`, mencocokkan `name` atau `email` dengan `LIKE`.
- **Filter role:** parameter `role`, divalidasi dengan `Rule::enum(UserRole::class)`.
- **Sorting:** parameter `sort` dengan nilai:
  - `joined_desc` — terbaru bergabung (default)
  - `joined_asc` — terlama bergabung
  - `name_asc` — nama A–Z
  - `name_desc` — nama Z–A
- **Pagination:** 10 pengguna per halaman, memakai `paginate(10)->withQueryString()`.
- **Empty state:** membedakan kondisi belum ada pengguna dan kondisi filter tidak menemukan hasil.

## 6. Proteksi Akses Admin

Proteksi dilakukan berlapis:

1. **Sidebar** hanya menampilkan menu jika `auth()->user()->isAdmin()`.
2. **Route middleware** `can:viewAny,App\Models\User` menolak Manager dan Member dengan HTTP 403.
3. **Form Request** `IndexUserRequest@authorize` memeriksa ulang izin `viewAny`.
4. **Controller detail** memanggil `$this->authorize('view', $user)`.
5. **UserPolicy** menjadi satu sumber aturan: hanya Admin global yang boleh melihat daftar maupun detail pengguna.

Pengguna yang belum login diarahkan ke halaman login oleh middleware `auth`. Manager dan Member yang membuka URL secara langsung mendapatkan respons **403 Forbidden**.

## 7. Cara Kerja Tombol Kembali

Komponen reusable: `resources/views/components/ui/back-button.blade.php`.

```blade
<x-ui.back-button :fallback="route('projects.index')" class="mb-4" />
```

Perilaku:

1. Setiap halaman wajib memberikan URL `fallback` yang sesuai konteks.
2. Tanpa JavaScript, tombol tetap berfungsi sebagai link biasa ke `fallback`.
3. Dengan JavaScript, komponen memeriksa `document.referrer`.
4. Navigasi `history.back()` hanya dijalankan jika:
   - referrer tersedia,
   - berasal dari origin yang sama,
   - bukan halaman yang sama dengan halaman saat ini.
5. Jika referrer tidak tersedia, berasal dari situs lain, atau tidak valid, pengguna diarahkan ke `fallback`.
6. Referrer eksternal tidak pernah dipakai untuk `history.back()` sehingga pengguna tidak keluar dari TaskFlow secara tidak sengaja.

Karena `fallback` ditentukan per halaman, tidak ada satu URL fallback yang dipakai untuk semua konteks. Halaman juga tetap bekerja setelah refresh atau saat dibuka langsung melalui URL karena link `href` selalu tersedia.

## 8. Komponen Reusable yang Dibuat

| Komponen | File | Fungsi |
|---|---|---|
| Tombol Kembali | `resources/views/components/ui/back-button.blade.php` | Link kembali dengan ikon panah, fallback eksplisit, dan pengecekan referrer |
| Item navigasi sidebar | `resources/views/components/layout/nav-item.blade.php` | Menyeragamkan tampilan menu sidebar, termasuk state aktif |

Komponen tombol memakai gaya outline/secondary design system: border `line`, latar `surface`, teks sekunder, radius `control`, dan hover `canvas`. Ukurannya kecil (`h-8`, `text-xs`) agar tidak mendominasi judul halaman.

## 9. Halaman yang Sudah Menggunakan Tombol Kembali

| Halaman | Fallback |
|---|---|
| Dashboard | `dashboard` |
| Daftar proyek | `dashboard` |
| Detail proyek | `projects.index` |
| Form tambah proyek | `projects.index` |
| Form edit proyek | `projects.show` |
| Anggota proyek (tab pada detail proyek) | `projects.index` |
| Daftar tugas | `projects.show` |
| Kanban Board | `projects.show` |
| Detail tugas | `projects.tasks.index` |
| Form tambah tugas | `projects.tasks.index` |
| Form edit tugas | `projects.tasks.show` |
| Semua anggota (Admin) | `dashboard` |
| Detail anggota (Admin) | `admin.members.index` |
| Register | `login` |

Catatan:

- **Login** tidak memakai tombol Kembali karena merupakan titik masuk aplikasi; tidak ada halaman sebelumnya yang aman dan relevan untuk dituju.
- **Lupa password** dan **reset password** belum tersedia di proyek ini.
- **Halaman profil** belum tersedia di proyek ini.
- Komentar task ditampilkan inline pada detail task, bukan halaman terpisah.

## 10. Hasil Pengujian

Pengujian otomatis:

- `vendor/bin/pint` — lulus.
- `php artisan test` — **118 tes, 579 assertion, semuanya lulus**.

Cakupan pengujian fitur Semua anggota:

- Admin melihat menu dan dapat membuka halaman.
- Admin melihat seluruh pengguna terdaftar.
- Pencarian berdasarkan nama dan email.
- Filter role global.
- Sorting nama naik/turun.
- Pagination 10 data per halaman.
- Detail pengguna menampilkan proyek dan role proyek.
- Password tidak ditampilkan.
- Manager dan Member tidak melihat menu.
- Manager dan Member mendapat 403 saat membuka daftar maupun detail.
- Guest diarahkan ke login.

Cakupan pengujian tombol Kembali:

- Register memiliki link kembali ke login.
- Dashboard, daftar proyek, detail proyek, form proyek, daftar tugas, Kanban, detail tugas, form tugas, Semua anggota, dan detail anggota memiliki `href` fallback yang sesuai konteks.
