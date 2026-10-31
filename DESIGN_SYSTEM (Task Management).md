# UI/UX Style Guide & Design System

Panduan visual dan interaksi untuk aplikasi *Task Management Proyek*. Implementasi memakai **Tailwind CSS (v4, CSS-first)**, **Blade components**, **Alpine.js**, dan **Livewire v3**.

Dokumen terkait: [`PRD.md`](./PRD.md) · [`AGENTS.md`](./AGENTS.md)

---

## 1. Arah Desain

**Subjek:** alat kerja harian untuk tim kecil-menengah yang membuka aplikasi ini puluhan kali sehari untuk satu tujuan: *tahu apa yang harus dikerjakan sekarang dan siapa yang menunggu*.

**Karakter:** tenang, rapat, mudah dipindai. Seperti papan kerja yang rapi, bukan landing page.

**Satu hal yang paling diingat:** kartu task dengan **pita status** di sisi kiri dan **indikator prioritas berbentuk batang**. Pengguna membaca status dan urgensi dari bentuk dan warna sebelum membaca judul. Semua elemen lain sengaja dibuat diam agar pita ini yang menonjol.

### Prinsip

1. **Informasi lebih penting dari dekorasi.** Garis, warna, dan ikon hanya hadir bila membawa makna (status, prioritas, tenggat).
2. **Satu radius per tingkat, bukan satu radius untuk semua.** Kontrol kecil, kartu, dan overlay punya radius berbeda.
3. **Warna tidak pernah menjadi satu-satunya pembawa makna.** Selalu dipasangkan dengan teks, titik, atau bentuk.
4. **Gerak menjawab aksi pengguna.** Tidak ada animasi masuk otomatis pada halaman.
5. **Rapat tapi bernapas.** Ritme 4px, kepadatan setara tabel kerja, target sentuh tetap 44px di layar sentuh.

### Yang sengaja dihindari

- Latar krem dengan aksen terakota, atau hitam pekat dengan aksen hijau neon.
- Deretan kartu identik dengan bayangan abu yang sama dan gradien dekoratif.
- Label huruf kapital semua di atas setiap judul, tanda panah `→` di setiap tombol.
- Satu kata beraksen warna di tengah judul.
- Animasi *fade-and-slide* pada setiap bagian atau efek hover pada setiap kartu.

---

## 2. Token Warna

Gunakan **token semantik** (nama peran), bukan nilai hex, di dalam komponen.

### 2.1 Palet inti (mode terang)

| Token | Nama | Hex | Penggunaan |
|---|---|---|---|
| `canvas` | Kanvas | `#F4F6FA` | Latar halaman |
| `surface` | Kertas | `#FFFFFF` | Kartu, panel, modal |
| `line` | Garis | `#E3E7EE` | Border, pemisah |
| `content` | Tinta | `#172033` | Teks utama |
| `content-muted` | Tinta redup | `#5F6B82` | Teks sekunder, metadata |
| `brand` | Ultramarine | `#3347C8` | Tombol utama, tautan, fokus |
| `brand-hover` | Ultramarine gelap | `#283AA3` | Hover/pressed tombol utama |
| `brand-soft` | Ultramarine pucat | `#E6E9FB` | Latar item terpilih, chip aktif |

Kontras teks `content` di atas `surface` ≈ 15:1; `content-muted` di atas `canvas` ≥ 4.5:1; putih di atas `brand` ≈ 7:1 (lolos WCAG AA).

### 2.2 Warna status task

| Status | Label UI | Teks/ikon | Latar lembut |
|---|---|---|---|
| `todo` | Belum dimulai | `#5F6B82` | `#EEF1F6` |
| `in_progress` | Dikerjakan | `#0B6E8A` | `#DDF3F9` |
| `in_review` | Ditinjau | `#6A47B8` | `#EDE7FA` |
| `completed` | Selesai | `#1B7A4B` | `#DEF4E7` |

### 2.3 Warna prioritas (hanya untuk indikator batang)

| Prioritas | Label UI | Warna | Jumlah batang terisi |
|---|---|---|---|
| `low` | Rendah | `#8792A6` | 1 dari 4 |
| `medium` | Sedang | `#C48A00` | 2 dari 4 |
| `high` | Tinggi | `#D9601A` | 3 dari 4 |
| `urgent` | Mendesak | `#C62F3E` | 4 dari 4 |

### 2.4 Warna fungsional

| Token | Hex (terang) | Penggunaan |
|---|---|---|
| `danger` | `#C62F3E` | Error, hapus, tenggat terlambat |
| `warning` | `#C48A00` | Peringatan, tenggat hari ini |
| `success` | `#1B7A4B` | Konfirmasi berhasil |
| `info` | `#0B6E8A` | Informasi netral |

### 2.5 Mode gelap

| Token | Hex (gelap) |
|---|---|
| `canvas` | `#0E1424` |
| `surface` | `#161E33` |
| `line` | `#27324D` |
| `content` | `#E7EBF4` |
| `content-muted` | `#9AA6BF` |
| `brand` (tombol) | `#4A5CE0` |
| `brand-hover` | `#6171F0` |
| `brand-soft` | `#222B55` |
| `brand-text` (tautan) | `#A5B1FF` |
| `danger` | `#F0616D` |

Status gelap: `todo` `#AAB4C8` / `#232C44` · `in_progress` `#5CC8E3` / `#12313D` · `in_review` `#B79DF2` / `#2A2347` · `completed` `#5FD39A` / `#12332A`.
Prioritas gelap: `low` `#8792A6` · `medium` `#E0A82E` · `high` `#F08A4B` · `urgent` `#F0616D`.

### 2.6 Implementasi Tailwind v4 (`resources/css/app.css`)

```css
@import "tailwindcss";

@custom-variant dark (&:where(.dark, .dark *));

:root {
  --canvas: #F4F6FA;
  --surface: #FFFFFF;
  --line: #E3E7EE;
  --content: #172033;
  --content-muted: #5F6B82;
  --brand: #3347C8;
  --brand-hover: #283AA3;
  --brand-soft: #E6E9FB;
  --brand-text: #3347C8;
  --danger: #C62F3E;
  --warning: #C48A00;
  --success: #1B7A4B;
  --info: #0B6E8A;

  --status-todo: #5F6B82;          --status-todo-soft: #EEF1F6;
  --status-in-progress: #0B6E8A;   --status-in-progress-soft: #DDF3F9;
  --status-in-review: #6A47B8;     --status-in-review-soft: #EDE7FA;
  --status-completed: #1B7A4B;     --status-completed-soft: #DEF4E7;

  --priority-low: #8792A6;
  --priority-medium: #C48A00;
  --priority-high: #D9601A;
  --priority-urgent: #C62F3E;
}

.dark {
  --canvas: #0E1424;
  --surface: #161E33;
  --line: #27324D;
  --content: #E7EBF4;
  --content-muted: #9AA6BF;
  --brand: #4A5CE0;
  --brand-hover: #6171F0;
  --brand-soft: #222B55;
  --brand-text: #A5B1FF;
  --danger: #F0616D;
  --warning: #E0A82E;
  --success: #5FD39A;
  --info: #5CC8E3;

  --status-todo: #AAB4C8;          --status-todo-soft: #232C44;
  --status-in-progress: #5CC8E3;   --status-in-progress-soft: #12313D;
  --status-in-review: #B79DF2;     --status-in-review-soft: #2A2347;
  --status-completed: #5FD39A;     --status-completed-soft: #12332A;

  --priority-medium: #E0A82E;
  --priority-high: #F08A4B;
  --priority-urgent: #F0616D;
}

@theme inline {
  --color-canvas: var(--canvas);
  --color-surface: var(--surface);
  --color-line: var(--line);
  --color-content: var(--content);
  --color-content-muted: var(--content-muted);
  --color-brand: var(--brand);
  --color-brand-hover: var(--brand-hover);
  --color-brand-soft: var(--brand-soft);
  --color-brand-text: var(--brand-text);
  --color-danger: var(--danger);
  --color-warning: var(--warning);
  --color-success: var(--success);
  --color-info: var(--info);

  --color-status-todo: var(--status-todo);
  --color-status-todo-soft: var(--status-todo-soft);
  --color-status-in-progress: var(--status-in-progress);
  --color-status-in-progress-soft: var(--status-in-progress-soft);
  --color-status-in-review: var(--status-in-review);
  --color-status-in-review-soft: var(--status-in-review-soft);
  --color-status-completed: var(--status-completed);
  --color-status-completed-soft: var(--status-completed-soft);

  --color-priority-low: var(--priority-low);
  --color-priority-medium: var(--priority-medium);
  --color-priority-high: var(--priority-high);
  --color-priority-urgent: var(--priority-urgent);

  --font-sans: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
  --font-mono: "JetBrains Mono", ui-monospace, monospace;

  --radius-control: 6px;
  --radius-card: 10px;
  --radius-overlay: 14px;

  --shadow-card: 0 1px 2px rgb(23 32 51 / 0.06);
  --shadow-raised: 0 6px 20px -6px rgb(23 32 51 / 0.18);
  --shadow-overlay: 0 16px 40px -12px rgb(23 32 51 / 0.28);
}

@layer base {
  body { @apply bg-canvas text-content font-sans antialiased; }
  :focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; }
}
```

Penggunaan: `bg-surface`, `text-content-muted`, `border-line`, `rounded-card`, `shadow-card`, `bg-status-in-review-soft`.

Mode gelap: tambahkan/hapus class `dark` pada `<html>` lewat Alpine (`x-data="{ dark: localStorage.theme === 'dark' }"`) dengan fallback ke `prefers-color-scheme`.

---

## 3. Tipografi

| Peran | Font | Catatan |
|---|---|---|
| UI dan judul | **Plus Jakarta Sans** | Bobot 400, 500, 600, 700 |
| Kode task (mis. `TSK-142`) | **JetBrains Mono** | Hanya untuk pengenal teknis, bukan label umum |

Muat lewat Bunny Fonts atau paket `@fontsource` agar tidak bergantung pada Google Fonts. Selalu sediakan fallback `ui-sans-serif, system-ui`.

### Skala

| Token | Ukuran / tinggi baris | Bobot | Penggunaan |
|---|---|---|---|
| `text-xs` | 12 / 16 | 500 | Badge, metadata kartu, timestamp |
| `text-sm` | 14 / 20 | 400-500 | **Teks default antarmuka**, tabel, form |
| `text-base` | 16 / 24 | 400 | Deskripsi task, komentar |
| `text-lg` | 18 / 28 | 600 | Judul panel, judul kolom |
| `text-xl` | 20 / 28 | 600 | Judul modal dan slide-over |
| `text-2xl` | 24 / 32 | 700 | Judul halaman |
| `text-3xl` | 30 / 36 | 700 | Judul proyek di header Detail Proyek |

Aturan:

- Judul memakai `tracking-tight` (−0.01em); teks kecil memakai tracking normal.
- Panjang baris teks bacaan (deskripsi, komentar) maksimal ±70 karakter (`max-w-prose`).
- Gunakan **sentence case** di seluruh UI. Hindari huruf kapital semua.
- Angka pada statistik dan tabel memakai `tabular-nums`.

---

## 4. Spasi, Radius, Elevasi

**Skala spasi (basis 4px):** 4, 8, 12, 16, 20, 24, 32, 48, 64. Gunakan kelipatan ini saja (`p-1` … `p-16`).

| Konteks | Nilai |
|---|---|
| Padding kartu task | 12px |
| Padding panel/kartu besar | 16-24px |
| Jarak antar kartu dalam kolom | 8px |
| Jarak antar kolom Kanban | 16px |
| Jarak antar bagian halaman | 24-32px |

**Radius**

| Token | Nilai | Dipakai untuk |
|---|---|---|
| `rounded-control` | 6px | Tombol, input, dropdown item, badge persegi |
| `rounded-card` | 10px | Kartu task, panel, kolom Kanban |
| `rounded-overlay` | 14px | Modal, slide-over, popover |
| `rounded-full` | - | Avatar, badge status, progress bar |

**Elevasi**

| Level | Token | Penggunaan |
|---|---|---|
| 0 | tanpa bayangan + `border-line` | Panel, tabel, kolom |
| 1 | `shadow-card` | Kartu task dalam keadaan diam |
| 2 | `shadow-raised` | Kartu saat di-drag, dropdown |
| 3 | `shadow-overlay` | Modal, slide-over, toast |

Mode gelap mengandalkan border dan perbedaan warna surface, bukan bayangan.

---

## 5. Layout dan Responsif

**Breakpoint:** `sm` 640 · `md` 768 · `lg` 1024 · `xl` 1280 · `2xl` 1536.

| Elemen | Ukuran |
|---|---|
| Sidebar (≥ `lg`) | 248px, tetap; ciut ke ikon 64px opsional |
| Topbar | 56px, tetap |
| Lebar konten maksimal | 1440px, rata kiri (bukan di tengah) |
| Kolom Kanban | lebar 304px, tinggi mengikuti isi, scroll vertikal internal |
| Slide-over task | 480px (≥ `md`), layar penuh di mobile |

**Perilaku responsif**

- **< `lg`:** sidebar menjadi drawer dari kiri (tombol menu di topbar).
- **Board di mobile:** kolom bergulir horizontal dengan `snap-x snap-mandatory`; satu kolom hampir memenuhi layar.
- **Tabel/List view di mobile:** berubah menjadi daftar baris dua tingkat (judul + metadata).
- Konten lebar (tabel, kode) bergulir di dalam wadahnya sendiri; halaman tidak boleh bergulir ke samping.

**Rata teks:** rata kiri untuk semua konten. Angka dan tanggal pada kolom tabel rata kanan.

### Sketsa halaman

**Dashboard**

```
┌────────┬───────────────────────────────────────────────────────┐
│Sidebar │ Topbar:  [Cari...]                   [🔔] [Avatar ▾]  │
│        ├───────────────────────────────────────────────────────┤
│Dashbo. │ Halo, Rina                                            │
│Proyek  │ ┌───────────────────────────────────────────────────┐ │
│Tugas   │ │ Proyek aktif │ Task saya │ Terlambat │ Selesai    │ │
│Anggota │ │      8       │    14     │     3     │ minggu ini │ │
│        │ └───────────────────────────────────────────────────┘ │
│        │ Task saya (urut tenggat)        │ Aktivitas terbaru   │
│        │ ▌ Rancang skema DB   Besok  ▮▮▯▯│  Dedi memindahkan…  │
│        │ ▌ Review PR #42  Terlambat  ▮▮▮▮│  Sari berkomentar…  │
│        │ Proyek terbaru                  │                     │
│        │ Website redesign ━━━━━━━━░░ 72% │                     │
└────────┴───────────────────────────────────────────────────────┘
```

Statistik ditampilkan sebagai **satu strip** dengan pemisah vertikal, bukan empat kartu terpisah.

**Detail Proyek (Board)**

```
┌────────┬───────────────────────────────────────────────────────┐
│Sidebar │ Website redesign   [Aktif]            [+ Task baru]   │
│        │ ━━━━━━━━━━━━░░░░ 72%   (Avatar stack) [Undang]        │
│        │ Board | Daftar | Anggota | Aktivitas                  │
│        │ [Cari task] [Assignee ▾] [Prioritas ▾] [Tenggat ▾]    │
│        │ ┌Belum dimulai 4┐┌Dikerjakan 3┐┌Ditinjau 2┐┌Selesai 9┐│
│        │ │▌Kartu         ││▌Kartu      ││▌Kartu    ││▌Kartu   ││
│        │ │▌Kartu         ││▌Kartu      ││          ││         ││
│        │ │ + Tambah task ││            ││          ││         ││
│        │ └───────────────┘└────────────┘└──────────┘└─────────┘│
└────────┴───────────────────────────────────────────────────────┘
```

---

## 6. Ikonografi

- Set ikon: **Heroicons outline** (paket `blade-ui-kit/blade-heroicons`), ukuran 20px (`size-5`), tebal garis 1.5.
- Ikon 16px (`size-4`) hanya untuk metadata di dalam kartu.
- Ikon fungsional selalu dengan label teks, kecuali tombol ikon yang punya `aria-label` dan tooltip.
- Warna ikon mengikuti teks (`currentColor`); jangan mewarnai ikon secara dekoratif.

---

## 7. Komponen

Letak: `resources/views/components/ui/*` dan `components/task/*`. Pemakaian: `<x-ui.button>`, `<x-ui.status-badge>`, `<x-task.card>`.

### 7.1 Tombol

| Varian | Gaya | Penggunaan |
|---|---|---|
| Primary | `bg-brand text-white hover:bg-brand-hover` | Satu aksi utama per tampilan |
| Secondary | `bg-surface border border-line hover:bg-canvas` | Aksi pendukung |
| Ghost | `text-content-muted hover:bg-brand-soft hover:text-brand-text` | Aksi dalam toolbar/kartu |
| Danger | `bg-danger text-white` | Aksi merusak (setelah konfirmasi) |

| Ukuran | Tinggi | Padding | Catatan |
|---|---|---|---|
| `sm` | 32px | `px-3` | Toolbar, kartu |
| `md` | 36px | `px-4` | Default desktop |
| `lg` | 44px | `px-5` | Default layar sentuh dan form mobile |

Semua tombol: `rounded-control text-sm font-medium`, tanpa ikon panah dekoratif. Status *loading* menampilkan spinner kecil dan menonaktifkan klik (`wire:loading.attr="disabled"`).

### 7.2 Form

- Label di atas input, `text-sm font-medium`; teks bantuan `text-xs text-content-muted` di bawah.
- Input: tinggi 36px (44px di sentuh), `rounded-control border border-line bg-surface`, fokus `ring-2 ring-brand`.
- Error: border `danger`, pesan `text-xs text-danger` di bawah input, **menyebutkan cara memperbaiki** ("Judul wajib diisi", bukan "Input tidak valid").
- Wajib vs opsional: tandai yang **opsional** dengan teks "(opsional)", bukan tanda bintang.
- Validasi inline saat `blur` untuk field penting; ringkasan error hanya jika form panjang.

### 7.3 Badge status

Pil dengan titik penuh dan label teks.

```blade
{{-- resources/views/components/ui/status-badge.blade.php --}}
@props(['status'])

@php
    $styles = [
        'todo'        => 'bg-status-todo-soft text-status-todo',
        'in_progress' => 'bg-status-in-progress-soft text-status-in-progress',
        'in_review'   => 'bg-status-in-review-soft text-status-in-review',
        'completed'   => 'bg-status-completed-soft text-status-completed',
    ];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium',
    $styles[$status->value],
]) }}>
    <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
    {{ $status->label() }}
</span>
```

### 7.4 Indikator prioritas

Empat batang vertikal (lebar 3px, tinggi naik 6/9/12/15px, jarak 2px). Batang terisi berwarna sesuai prioritas, sisanya `bg-line`. Selalu sertakan teks untuk pembaca layar (`<span class="sr-only">Prioritas tinggi</span>`) dan `title`.

```blade
{{-- resources/views/components/ui/priority-bars.blade.php --}}
@props(['priority'])

@php
    $level = ['low' => 1, 'medium' => 2, 'high' => 3, 'urgent' => 4][$priority->value];
    $color = [
        'low' => 'bg-priority-low', 'medium' => 'bg-priority-medium',
        'high' => 'bg-priority-high', 'urgent' => 'bg-priority-urgent',
    ][$priority->value];
@endphp

<span class="inline-flex items-end gap-0.5" title="Prioritas {{ strtolower($priority->label()) }}">
    @foreach ([6, 9, 12, 15] as $i => $h)
        <span class="w-[3px] rounded-sm {{ $i < $level ? $color : 'bg-line' }}" style="height: {{ $h }}px"></span>
    @endforeach
    <span class="sr-only">Prioritas {{ strtolower($priority->label()) }}</span>
</span>
```

### 7.5 Avatar dan tumpukan avatar

- Ukuran: 24 (kartu), 32 (daftar), 40 (profil/komentar).
- Tanpa foto: inisial 1-2 huruf di atas salah satu 6 warna redup yang dipilih deterministik dari `id` user. Teks harus lolos kontras 4.5:1.
- Tumpukan: tumpang tindih −8px, cincin `ring-2 ring-surface`, tampilkan maksimal 4 lalu `+N`.
- Task tanpa assignee menampilkan lingkaran garis putus-putus dengan ikon user dan tooltip "Belum ditugaskan".

### 7.6 Kartu task

Anatomi:

```
┌─────────────────────────────┐
▌ TSK-142                  ⋯  │   ← pita status 3px di kiri
▌ Rancang skema database      │   ← judul, maks. 2 baris
▌                             │
▌ ▮▮▯▯   📅 Besok      (AV)   │   ← prioritas · tenggat · assignee
└─────────────────────────────┘
```

```blade
{{-- resources/views/components/task/card.blade.php --}}
@props(['task'])

@php
    $edge = [
        'todo' => 'border-l-status-todo',
        'in_progress' => 'border-l-status-in-progress',
        'in_review' => 'border-l-status-in-review',
        'completed' => 'border-l-status-completed',
    ][$task->status->value];
@endphp

<article {{ $attributes->class([
    'rounded-card border border-line border-l-[3px] bg-surface p-3 shadow-card',
    'cursor-grab active:cursor-grabbing focus-within:ring-2 focus-within:ring-brand',
    $edge,
]) }}>
    <div class="flex items-start justify-between gap-2">
        <span class="font-mono text-xs text-content-muted">TSK-{{ $task->id }}</span>
        {{ $menu ?? '' }}
    </div>

    <button type="button" wire:click="$dispatch('open-task', { id: {{ $task->id }} })"
            class="mt-1 line-clamp-2 text-left text-sm font-semibold text-content hover:text-brand-text">
        {{ $task->title }}
    </button>

    <div class="mt-3 flex items-center justify-between">
        <div class="flex items-center gap-3 text-xs text-content-muted">
            <x-ui.priority-bars :priority="$task->priority" />
            @if ($task->due_date)
                <x-task.due-date :task="$task" />
            @endif
        </div>
        <x-ui.avatar :user="$task->assignee" size="sm" />
    </div>
</article>
```

Tenggat (`task.due-date`):

| Kondisi | Tampilan |
|---|---|
| Terlambat (belum selesai) | `text-danger font-medium` + ikon jam, "Terlambat 2 hari" |
| Hari ini | `text-warning font-medium`, "Hari ini" |
| Besok | teks normal, "Besok" |
| Dalam 7 hari | "Kamis" |
| Lebih jauh | "24 Okt" (tahun hanya bila bukan tahun berjalan) |
| Task selesai | Tanggal biasa, tanpa penekanan warna |

### 7.7 Kolom Kanban

- Lebar 304px, latar `bg-canvas` dengan `border border-line rounded-card`, padding 8px.
- Header: judul status `text-sm font-semibold` + jumlah task (`text-xs text-content-muted`) + tombol `+` ghost. Titik status berwarna di sebelah kiri judul.
- Area kartu: `space-y-2`, `min-h-[96px]` agar kolom kosong tetap bisa menjadi target drop.
- Kolom kosong: teks "Tidak ada task. Seret kartu ke sini." `text-xs text-content-muted`.
- Footer: tombol ghost "Tambah task" yang membuka form ringkas inline (judul saja).

**Drag and drop (SortableJS lewat Alpine)**

| Keadaan | Tampilan |
|---|---|
| Kartu diangkat | `shadow-raised`, rotasi 1.5°, opasitas 0.95 |
| Tempat jatuh | Kotak garis putus-putus `border-brand` dengan latar `bg-brand-soft` setinggi kartu |
| Setelah dijatuhkan | Kartu langsung di posisi baru (optimistic), Livewire menyimpan; bila gagal, kartu kembali dan toast error muncul |

**Alternatif keyboard (wajib):** menu `⋯` pada kartu berisi "Pindahkan ke → Belum dimulai / Dikerjakan / Ditinjau / Selesai". Ini juga cara utama di layar sentuh kecil.

### 7.8 Progress bar

Tinggi 6px, `rounded-full bg-line`, isi `bg-brand` (atau `bg-status-completed` bila 100%). Persentase ditampilkan sebagai teks (`72%`) dengan `tabular-nums`, dan atribut `role="progressbar"` beserta `aria-valuenow`.

### 7.9 Tab dan filter

- Tab: garis bawah 2px `brand` untuk tab aktif, teks `text-sm font-medium`; tab tidak aktif `text-content-muted`.
- Filter: chip `rounded-control border border-line`; saat aktif `bg-brand-soft text-brand-text border-transparent` dan tampilkan jumlah pilihan. Tersedia tombol "Reset filter" bila ada filter aktif.

### 7.10 Slide-over detail task

- Muncul dari kanan, lebar 480px, `rounded-l-overlay shadow-overlay`, latar redup `bg-content/30`.
- Susunan dari atas: kode + menu, **judul (edit inline)**, baris properti (status, assignee, prioritas, tenggat), deskripsi, komentar, riwayat aktivitas.
- Perubahan properti tersimpan otomatis dengan umpan balik halus ("Tersimpan"); tutup dengan `Esc`, tombol tutup, atau klik latar.
- Fokus berpindah ke judul saat dibuka dan kembali ke kartu pemicu saat ditutup (focus trap via Alpine `x-trap`).

### 7.11 Modal konfirmasi

- Lebar maksimal 440px, `rounded-overlay`.
- Judul menyebut objek: "Hapus task 'Rancang skema database'?". Isi menjelaskan akibat: "Task ini dan komentarnya akan dihapus."
- Dua tombol: Secondary "Batal" dan Danger "Hapus task". Fokus awal pada **Batal**.

### 7.12 Toast

- Pojok kanan bawah (desktop), atas layar (mobile), lebar maksimal 360px, `shadow-overlay rounded-card`.
- Sukses hilang otomatis setelah 5 detik; **error tetap tampil** sampai ditutup.
- Wadah memakai `aria-live="polite"` (error: `assertive`).
- Teks mengulang kata kerja aksi: tombol "Simpan perubahan" → toast "Perubahan disimpan".

### 7.13 Tabel (List view)

- Header `text-xs font-medium text-content-muted` (sentence case), baris tinggi 44px, pemisah `border-line`.
- Kolom: Task, Status, Assignee, Prioritas, Tenggat. Dapat diurutkan; kolom aktif menampilkan ikon panah arah urut.
- Baris hover `bg-canvas`; baris dapat diklik untuk membuka slide-over.

### 7.14 Empty state dan skeleton

- **Empty state** = ajakan bertindak: ikon sederhana 40px, satu kalimat judul, satu kalimat penjelas, satu tombol utama.
- **Skeleton** menggantikan konten saat memuat (`animate-pulse` rendah), dengan bentuk sama seperti kartu/baris asli. Hindari spinner layar penuh.

---

## 8. Interaksi dan Gerak

| Tujuan | Durasi | Easing |
|---|---|---|
| Hover/fokus, perubahan warna | 120ms | `ease-out` |
| Buka/tutup dropdown, tooltip | 150ms | `ease-out` |
| Slide-over, modal, toast | 200ms | `cubic-bezier(0.2, 0, 0, 1)` |
| Kartu menetap setelah drop | 180ms | `ease-out` |

- Gerak hanya sebagai **respons terhadap aksi**: buka, tutup, pindah, konfirmasi.
- Tidak ada animasi masuk otomatis pada kartu, kolom, atau bagian halaman.
- Hormati `prefers-reduced-motion`: matikan transisi non-esensial dan rotasi saat drag.

```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
}
```

- Livewire: gunakan `wire:loading` untuk status memuat per-aksi, `wire:navigate` untuk navigasi tanpa muat ulang penuh, dan `wire:key` untuk menjaga identitas elemen saat drag.

---

## 9. Aksesibilitas

- Kontras minimal WCAG AA (4.5:1 teks normal, 3:1 teks besar dan komponen UI).
- Fokus selalu terlihat: cincin 2px `brand` dengan offset 2px; jangan `outline-none` tanpa pengganti.
- Seluruh fungsi drag-and-drop punya alternatif keyboard (menu "Pindahkan ke").
- Status dan prioritas tidak mengandalkan warna saja (titik + teks, batang + `sr-only`).
- Target sentuh minimal 44×44px pada layar sentuh.
- Modal dan slide-over: focus trap, `role="dialog"`, `aria-modal="true"`, `aria-labelledby`, tutup dengan `Esc`.
- Tombol ikon wajib `aria-label`; gambar dekoratif `aria-hidden="true"`.
- Setiap `input` punya `label` terkait; pesan error dihubungkan lewat `aria-describedby`.
- Uji dengan keyboard saja dan pembaca layar pada alur utama: buat proyek → buat task → ubah status.

---

## 10. Konten dan UX Writing (Bahasa Indonesia)

**Gaya:** kalimat aktif, kata kerja jelas, sentence case, tanpa basa-basi. Sebut sesuatu dengan istilah yang dipahami pengguna, bukan istilah sistem.

### Kosakata baku

| Konsep | Gunakan | Hindari |
|---|---|---|
| Membuat | Buat proyek, Buat task | Tambah baru, Create |
| Menyimpan | Simpan perubahan | Submit, Kirim |
| Menghapus | Hapus task | Buang, Delete |
| Anggota | Anggota proyek | User, Member |
| Peran | Admin, Manajer, Anggota | Owner, Manager, Viewer |
| Status | Belum dimulai, Dikerjakan, Ditinjau, Selesai | Todo, In Progress |
| Prioritas | Rendah, Sedang, Tinggi, Mendesak | Low, Medium |

Satu aksi memakai **satu kata kerja** di seluruh alur: tombol "Simpan perubahan" → toast "Perubahan disimpan".

### Pesan error: sebutkan masalah dan cara memperbaikinya

| Situasi | Pesan |
|---|---|
| Judul kosong | "Judul wajib diisi." |
| Tenggat sudah lewat | "Pilih tanggal hari ini atau setelahnya." |
| Assignee bukan anggota | "Pilih anggota dari proyek ini." |
| Gagal menyimpan | "Perubahan belum tersimpan. Periksa koneksi lalu coba lagi." |
| Tidak berwenang | "Kamu tidak punya akses untuk mengubah task ini. Hubungi manajer proyek." |

Pesan error tidak meminta maaf dan tidak samar.

### Empty state

| Lokasi | Judul | Penjelas | Tombol |
|---|---|---|---|
| Daftar proyek | Belum ada proyek | Buat proyek pertama untuk mulai membagi tugas ke tim. | Buat proyek |
| Board kosong | Belum ada task di proyek ini | Tambahkan task dan tugaskan ke anggota tim. | Buat task |
| Task saya | Tidak ada task untukmu | Task yang ditugaskan kepadamu akan muncul di sini. | - |
| Aktivitas | Belum ada aktivitas | Perubahan pada task akan tercatat di sini. | - |

### Format tanggal dan angka

- Tanggal: "13 Okt 2026"; relatif untuk ≤ 7 hari ("Besok", "Kamis", "Terlambat 2 hari").
- Zona waktu mengikuti pengaturan aplikasi (`Asia/Jakarta`); lokal `id` untuk Carbon (`Carbon::setLocale('id')`).
- Jumlah: "3 task", "1 anggota" (tanpa pluralisasi rumit).

### Berkas bahasa

```php
// lang/id/tasks.php
return [
    'status' => [
        'todo' => 'Belum dimulai',
        'in_progress' => 'Dikerjakan',
        'in_review' => 'Ditinjau',
        'completed' => 'Selesai',
    ],
    'priority' => [
        'low' => 'Rendah',
        'medium' => 'Sedang',
        'high' => 'Tinggi',
        'urgent' => 'Mendesak',
    ],
    'created' => 'Task dibuat.',
    'deleted' => 'Task dihapus.',
];
```

---

## 11. Pola Halaman Utama

### Dashboard

1. Sapaan singkat dan satu aksi utama ("Buat proyek").
2. **Strip ringkasan** (proyek aktif, task saya, terlambat, selesai minggu ini). Angka `text-2xl font-bold tabular-nums`, label `text-xs text-content-muted`. Angka "terlambat" memakai warna `danger` hanya bila > 0.
3. Dua kolom: **Task saya** (daftar baris, urut tenggat) dan **Aktivitas terbaru**.
4. **Proyek terbaru** dengan progress bar.

### Detail Proyek

1. Header: judul (`text-3xl`), badge status proyek, progress bar, tumpukan avatar, tombol "Undang" dan "Buat task".
2. Tab: Board · Daftar · Anggota · Aktivitas.
3. Baris filter (cari, assignee, prioritas, tenggat) tetap menempel di bawah header saat menggulir.
4. Board 4 kolom sesuai bagian 7.7; klik kartu membuka slide-over.

---

## 12. Checklist Review UI

- [ ] Memakai token (tidak ada hex/ukuran bebas di Blade)
- [ ] Status/prioritas tidak bergantung pada warna saja
- [ ] Fokus keyboard terlihat; semua aksi dapat dijangkau tanpa mouse
- [ ] Teks UI lewat `lang/id`, sentence case, kosakata baku
- [ ] Tampilan benar pada 375px, 768px, dan 1280px
- [ ] Mode terang dan gelap sama-sama terbaca
- [ ] Ada state: memuat, kosong, error, dan tidak berwenang
- [ ] Tidak ada animasi otomatis; `prefers-reduced-motion` dihormati
