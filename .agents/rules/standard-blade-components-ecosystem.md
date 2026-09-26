# Standard Anonymous Blade Components Ecosystem (22 Components)

Aturan ini mengikat seluruh pembuatan dan modifikasi tampilan antarmuka (Blade views) di proyek INFORA sesuai dokumentasi resmi di `docs/COMPONENTS.md`.

---

## 1. Kewajiban Menggunakan Komponen Blade Resmi
1. **Dilarang Menuliskan Elemen HTML Form & UI Mentah:**
   - Dilarang menuliskan struktur `<div class="form-group"><label>...<input class="form-control">...` secara manual berulang-ulang.
   - Wajib memanfaatkan **22 Anonymous Blade Components** yang telah disediakan di `resources/views/components/`.
2. **Katalog 22 Komponen Resmi:**
   - **Form & Input:**
     - `<x-form-input>` (input teks, angka, email, password, tanggal, hidden dengan binding `old()`, label bintang, error handling otomatis).
     - `<x-form-select>` (dropdown standar dengan koleksi opsi dan atribut dinamis).
     - `<x-form-textarea>` (textarea dengan autosize dan error handling).
     - `<x-form-checkbox>` (checkbox toggle/switch ramah touch).
     - `<x-searchable-select>` (dropdown searchable dengan filtering cepat).
     - `<x-icon-picker>` (dialog visual pemilih ikon Lucide interaktif).
     - `<x-image-upload>` (upload gambar modern terintegrasi live base64 preview dan service `ImageUploader`).
   - **Halaman, Navigasi & Metrik:**
     - `<x-page-header>` (judul halaman, breadcrumb, subtitle, action buttons).
     - `<x-tab-pills>` & `<x-tab-pill>` (tab switcher berbasis pil interaktif).
     - `<x-stats-grid>` & `<x-stat-card>` (kartu metrik statistik dengan tren dan aksen).
     - `<x-alert>` (notifikasi alert kontekstual dengan icon).
   - **Tabel & Penyajian Data:**
     - `<x-data-table>` (tabel data responsif dengan styling INFORA).
     - `<x-search-toolbar>` (toolbar pencarian dan filter cepat).
     - `<x-table-actions>` (aksi baris tabel: edit modal, toggle status, hapus).
     - `<x-empty-state>` (tampilan kosong elegan saat data belum ada atau pencarian nihil).
     - `<x-pagination-summary>` & `<x-pagination>` (ringkasan rentang dan paginasi adaptif).
   - **Dialog & Modal:**
     - `<x-modal>` (modal flexbox terstandarisasi dengan auto-scroll dan zero clipping).
     - `<x-progress-modal>` (modal indikator loading bertingkat halus).
   - **Grafis & Ikon:**
     - `<x-icon>` (render native Lucide SVG open source).

---

## 2. Integrasi Modul JavaScript Pendamping
Komponen UI Blade bekerja selaras dengan 5 modul controller client-side native:
1. `window.InforaModals` → Mengontrol buka/tutup modal dialog (`InforaModals.open(id)`, `InforaModals.close(id)`).
2. `window.SearchableSelect` → Inisialisasi dropdown dengan live query search.
3. `window.ImageUpload` → Handler FileReader base64 dan pratinjau instan.
4. `window.InforaProgress` → Manajemen progress bar loading screen global.
5. `window.PermissionMatrix` → Manajemen RBAC dinamis dan bulk selection.
