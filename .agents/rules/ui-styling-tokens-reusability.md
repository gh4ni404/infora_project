# UI Styling Standards: Global Reusability, Token Architecture & Zero Ad-Hoc Classes

Aturan ini mengikat seluruh pengembangan antarmuka (Blade Views & Stylesheets) di proyek INFORA sesuai dengan `detail/DESIGN.md` poin 6 dan konvensi desain yang telah disepakati.

---

## 1. Larangan Mutlak Class CSS Ad-Hoc & Inline Styles
1. **Dilarang Membuat Class Ad-Hoc / Spesifik Satu Fitur:**
   - Jangan pernah membuat class CSS dengan prefix modul tunggal, seperti `.calendar-*`, `.jurnal-*`, `.absen-*`, atau `.rekap-*`.
   - Seluruh penamaan class wajib bersifat universal, modular, dan dapat digunakan lintas modul:
     - Gunakan `.schedule-grid`, `.schedule-header-cell`, `.schedule-day-cell`, `.schedule-pill` (bisa dipakai untuk Kalender Akademik, Jadwal KBM, Logbook PKL).
     - Gunakan `.legend-bar`, `.legend-item`, `.legend-dot`, `.dot-*` (untuk legenda kalender, status tabel, chart).
     - Gunakan `.tab-pills`, `.tab-pill` (untuk navigasi filter / view switcher).
     - Gunakan `.table-card`, `.table-toolbar`, `.toolbar-nav`, `.btn-icon` (untuk wrapper kartu dan kontrol toolbar).
     - Gunakan `.card-list-item`, `.card-list-item-*` (untuk daftar item di dalam modal atau kartu ringkasan).
2. **Zero Inline Styles (`style="..."`):**
   - Dilarang keras menuliskan atribut `style="..."` pada elemen HTML Blade.
   - Dilarang menyisipkan tag `<style>` di dalam berkas Blade view.
   - Semua penyesuaian jarak, tata letak, atau warna wajib memanfaatkan class reusable yang telah terdaftar di `resources/css/app.css`.

---

## 2. Arsitektur Token Warna Terpusat (Single Source of Truth)
1. **Wajib Menggunakan CSS Variables (`var(--infora-*)`):**
   - Dilarang menuliskan kode warna HEX mentah (seperti `#2563EB`, `#DC2626`, `#D97706`, `#16A34A`, `#1E40AF`) langsung di dalam komponen CSS.
   - Semua warna wajib merujuk ke token semantik yang didefinisikan di `:root` pada `resources/css/app.css`.
2. **Larangan Alias Bayangan (No Redundant Shadow Aliases):**
   - Jangan melakukan overengineering dengan membuat alias yang menduplikasi variabel (misalnya jangan membuat `--infora-amber` hanya untuk meng-alias `--infora-warning`, atau `--infora-rose` untuk `--infora-danger`).
   - Gunakan langsung variabel semantik utama yang ada di sistem desain:
     - `--infora-brand-primary`, `--infora-brand-soft`, `--infora-brand-border`, `--infora-brand-text`
     - `--infora-success`, `--infora-success-bg`, `--infora-success-border`, `--infora-success-text`
     - `--infora-warning`, `--infora-warning-bg`, `--infora-warning-border`, `--infora-warning-text`
     - `--infora-danger`, `--infora-danger-bg`, `--infora-danger-border`, `--infora-danger-text`
     - `--infora-info`, `--infora-info-bg`, `--infora-info-border`, `--infora-info-text`
     - Modul khusus: `--infora-purple-*` (Rapat/Dinas), `--infora-indigo-*` (Khusus)
3. **Penambahan Token Baru:**
   - Jika suatu elemen memerlukan token baru (seperti warna teks kontras brand `#1E40AF`), daftarkan satu kali di seksi terkait di `:root` (`--infora-brand-text: #1E40AF;`), lalu gunakan via `var(--infora-brand-text)`.

---

## 3. Konsolidasi Sistem Badges, Status Pills & Legend Dots
1. **Satu Lokasi Terpusat:**
   - Seluruh definisi `.badge-*`, `.pill-*`, dan `.dot-*` wajib ditempatkan dalam satu seksi tunggal terpusat di `resources/css/app.css` (area komponen UI dasar, sekitar baris 400).
   - Dilarang membuat deklarasi ulang (*re-declaration*) di bagian bawah stylesheet.
2. **Signature Accent Border (3px Left Accent):**
   - Setiap varian warna wajib mengimplementasikan bahasa visual khas INFORA:
     ```css
     .badge-emerald, .pill-emerald, .dot-emerald {
         background: var(--infora-success-bg);
         border: 1px solid var(--infora-success-border);
         border-left: 3px solid var(--infora-success);
         color: var(--infora-success-text);
     }
     ```
   - Pada indikator lingkaran `.legend-dot`, kombinasi border ini membentuk aksen sabit (*half-moon accent*) yang identik dan konsisten dengan badge akreditasi data sekolah.

---

## 4. Standarisasi Tampilan Tanggal Akhir Pekan (Weekend & Libur)
1. **Kotak Merah Halus (*Smooth Red Box*):**
   - Tanggal hari Sabtu dan Minggu pada grid jadwal/kalender wajib menggunakan kelas `.schedule-day-num.is-weekend`.
   - Menggunakan latar lembut `var(--infora-danger-bg)`, border halus `1px solid var(--infora-danger-border)`, dan teks tegas `var(--infora-danger)` dengan sudut melengkung `border-radius: 0.375rem` dan bayangan lembut.
   - Pada saat di-hover, gunakan transisi halus dengan latar `var(--infora-danger-border)` dan sedikit elevasi `translateY(-1px)`.
2. **Prioritas Tanggal Hari Ini (`is-today`):**
   - Jika hari Sabtu/Minggu bertepatan dengan hari ini, status `.is-today` harus memprioritaskan warna biru primer `var(--infora-brand-primary)` dengan kontras putih bersih (`#FFFFFF`).
