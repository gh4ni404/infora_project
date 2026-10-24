# 📋 INFORA Development Roadmap (TODO.md)
> **Information Network for Organization, Records, dan Accreditation (INFORA)**  
> Target: Sistem Informasi Manajemen (SIM) Sekolah Menengah Atas (SMA), Sekolah Menengah Kejuruan (SMK), dan Madrasah Aliyah (MA)

---

## 📌 Status Ringkasan Proyek
- [x] Konsep dan Penamaan Platform (**INFORA**)
- [x] Filosofi Brand, Desain Logo dan App Icon ([BRANDING.md](detail/docs/BRANDING.md))
- [x] Spesifikasi Desain dan Arsitektur Sistem ([DESIGN.md](detail/DESIGN.md))
- [x] Inisialisasi Project Laravel dan Docker Stack Multi-Platform ([README.md](README.md))
- [x] Standarisasi Layanan Unggah Gambar Base64 (`ImageUploader` dan `<x-image-upload>`, Maks. 1MB)
- [x] Arsitektur Navigasi Dinamis dan Pengaturan Sistem (Modul, Menu, Sub-Menu)
- [x] Arsitektur Modal Dialog CRUD Modular (Create dan Edit as Components in Index via `InforaModals`)
- [x] Tata Kelola Menu Akses dan Dynamic Role Templates (User-Centric Granular Permissions via `PermissionMatrix`)
- [x] Sistem Cadangan dan Pemulihan Sistem Lengkap (Full System Snapshot dan Server Migration Ready)
- [x] Master Data Sekolah (Multi-Record Registry SMA, SMK, dan MA)
- [x] Master Data Wilayah Administratif Pemerintahan Berbasis Kode Kemendagri (Provinsi, Kabupaten, Kecamatan, Kelurahan dan Desa)
- [x] Arsitektur Tata Letak 3-Tier (Docked Footer dan Scroll Internal) dan Komponen Paginasi Responsif Global
- [x] Master Data Jurusan / Konsentrasi Keahlian dan Peminatan (SMA, SMK, dan MA)
- [x] Master Data Tingkatan Kelas (X, XI, XII, Fase Kurikulum Merdeka, dan Urutan Alami)
- [x] Master Data Pendidik dan Tenaga Kependidikan (GTK) (Multi-Role JSON, Sinkronisasi Akun Otomatis, dan Manajemen Kredensial)
- [x] Pengaturan Profil Pengguna Mandiri & Keamanan Akun (Dedicated 2-Column Settings Layout, 4 Tab GTK & 2 Tab Non-GTK, Konsolidasi Informasi Pribadi & Kredensial, Dirty-Form Modal, Dashboard Onboarding Alert, Auto-Sync GTK)
- [x] Konfigurasi Sekolah: Tahun Ajaran dan Manajemen Semester
- [x] Konfigurasi Sekolah: Kalender Akademik Terpadu dan Agenda Multi-Day (Grid Bulanan dan Tabel Agenda via `<x-tab-pills>`)
- [x] Layanan Universal Spreadsheet (`SpreadsheetService`) & Impor / Ekspor Excel Data GTK via `<x-crud-button>`
- [x] Ekosistem 25 Anonymous Blade Components dan Test Suite 371 Tests Lulus 100% (1.728 Assertions)
- [ ] Arsitektur Akun Sivitas (4 Tipe Akun: Siswa, Guru, Staff, Admin dengan profil relasional 1-to-1)
- [ ] Dedicated Layout Separation (Desktop dan Mobile)
- [ ] Implementasi Modul Akademik Lanjutan dan Tata Kelola Utama

---

## 🚀 Fase 1: Inisialisasi Environment dan Docker Architecture
- [x] **1.1. Inisialisasi Project Laravel (Versi Terbaru / Latest Version)**
  - [x] Generate project Laravel versi terbaru dengan struktur direktori modern.
  - [x] Konfigurasi arsitektur modern (streamlined `bootstrap/app.php`, Vite asset pipeline, Pest testing).
  - [x] Konfigurasi `.env.example`, `.env.docker.example`, dan `.env` untuk environment development dan production.
- [x] **1.2. Setup Docker Containerization (Native Multi-Platform)**
  - [x] Buat `Dockerfile` teroptimasi untuk PHP 8.4-FPM (ekstensi `pdo_mysql`, `bcmath`, `gd`, `zip`, `redis`, `opcache`, `pcntl`, Composer, Node.js 22).
  - [x] Buat `docker-compose.yml` (Services: Nginx, App PHP-FPM, MySQL 8.0, Node.js Vite).
  - [x] Buat konfigurasi virtual host Nginx `docker/nginx/default.conf` teroptimasi untuk routing Laravel terbaru.
  - [x] Buat skrip helper automasi lintas OS (`./dev` untuk Linux/macOS/WSL dan `dev.ps1` untuk Windows).

---

## 🔐 Fase 2: Design System, Arsitektur Akun dan User-Centric Menu Access
- [ ] **2.1. Desain Sistem dan Arsitektur Dedicated Layout (Desktop dan Mobile)**
  - [x] Setup palet warna INFORA Terang dan Ramah Sivitas (Canvas `#F8FAFC`, Surface `#FFFFFF`, Sky/Royal Blue `#0284C7`/`#2563EB`, Teks Kontras Tinggi `#0F172A`) dan tipografi `Plus Jakarta Sans`.
  - [x] Standarisasi class CSS global terpusat di `resources/css/app.css` (reusable global utility dan component classes, zero inline styles, zero `<style>` tags).
  - [x] Master layout shell minimalis awal: `layouts/auth.blade.php` dan `layouts/app.blade.php`.
  - [x] Implementasi Tata Letak 3-Tier dengan Docked Footer dan scroll internal independen (`.app-content` dan `.sidebar-content`) untuk mengeliminasi double window scroll.
  - [x] Komponen Paginasi Responsif Global (`<x-pagination>`) terdaftar universal via `AppServiceProvider` dan Toolbar Tabel Responsif.
  - [ ] Implementasi layout lanjutan khusus: Mobile App-like (`layouts/mobile.blade.php`) dan Desktop Power-Dashboard (`layouts/desktop.blade.php`).
- [ ] **2.2. Manajemen Akun Sivitas dan Autentikasi Fleksibel (4 Tipe Akun)**
  - [x] Migrasi kolom sistem tabel `users` (`username`, `user_type`, `is_active`, `avatar_path`).
  - [x] Seeder default Super Admin (entitas pengembang platform: `superadmin` / `password`).
  - [x] Login Controller multi-identifier (login via **Username** maupun **Email**).
  - [x] SuperAdminMiddleware untuk proteksi route dashboard.
  - [x] Dashboard Super Admin minimalis awal (1 menu tunggal: **Dashboard**).
  - [x] Pengaturan Profil Pengguna Mandiri (`/profile`, layout 2-kolom navigasi vertikal, 4 tab GTK dan 2 tab non-GTK, integrasi avatar dan kredensial akun login ke Informasi Pribadi, dirty-form unsaved changes confirmation modal, ubah kata sandi dengan auto-logout).
  - [x] Banner Onboarding / Alert Kelengkapan Profil di Dashboard untuk akun baru dengan kata sandi default.
  - [x] Automated Pest test suite `ProfileTest.php` (total test suite melonjak menjadi 330 tests lulus 100%, 1.493 assertions).
  - [ ] Skema database tabel profil sivitas sekolah (`student_profiles`, `teacher_profiles`, `staff_profiles`).
  - [ ] Setup Laravel Sanctum untuk RESTful API Tokens.
  - [ ] Skema database *Single User Table + Specific Profile Relations* (1-to-1):
    - `student_profiles`: NISN, NIS, rombel_id, angkatan, kontak orang tua.
    - `teacher_profiles`: NIP, NUPTK, gelar_depan, gelar_belakang, no_hp.
    - `staff_profiles`: NIP, unit_kerja, jabatan_tu.
  - [ ] Autentikasi Fleksibel (Auto-Detect Login Identifier):
    - Siswa login via **NISN** / NIS.
    - Guru login via **NIP / NUPTK** atau Email.
    - Admin dan Super Admin login via **Username** atau Email.
  - [ ] Setup Laravel Sanctum untuk otentikasi ganda (Web Session dan RESTful API Tokens).
- [x] **2.3. Fondasi Hierarki Navigasi dan Pengaturan Sistem (Modul, Menu, Sub-Menu)**
  - [x] Skema database hierarki 3 level: tabel `modules`, `menus`, `sub_menus` tanpa constraint `unique` (bebas bentrok penamaan, mengandalkan Primary Key ID).
  - [x] Model Eloquent, Relasi Berjenjang (`hasMany`/`belongsTo`), Casts, dan Factories (`Module`, `Menu`, `SubMenu`).
  - [x] Database Seeder `SystemMenuSeeder` (Modul "Navigasi Utama" dan "Pengaturan Sistem" berisi 3 menu dasar: Modul, Menu, Sub-Menu).
  - [x] Integrasi Frontend Sidebar Dinamis:
    - [x] View Composer `SidebarComposer` dengan *safeguard* `Schema::hasTable('modules')`.
    - [x] Dropdown Accordion sub-menu interaktif (`.nav-group-item`, `.nav-group-trigger`, `.nav-arrow-icon`, `.nav-submenu-list`).
    - [x] Sub-menu bersih dan rapi: bullet dot dihapus, digantikan indentasi bertingkat dan left accent indicator bar saat hover dan aktif.
    - [x] Pencarian menu real-time multi-level (modul, menu, sub-menu dengan auto-expand parent).
    - [x] Collapsible sidebar dengan anchor kiri presisi 26px (tanpa pergeseran horizontal ikon / 0px jump).
    - [x] Resolusi rute fleksibel pada model `Menu` dan `SubMenu`: mendukung format hierarki sederhana `modul.menu` / `modul.menu.submenu` tanpa kewajiban akhiran `.index`.
  - [x] Backend CRUD Lengkap Pengaturan Sistem:
    - [x] Resource routes `/system/modules`, `/system/menus`, `/system/sub-menus` di bawah proteksi `['auth', 'super_admin']`.
    - [x] Form Request validation layer berbahasa Indonesia (`StoreModuleRequest`, `UpdateModuleRequest`, dll).
    - [x] Controllers: `ModuleController`, `MenuController`, `SubMenuController`.
    - [x] Antarmuka manajemen lengkap (9 Blade views) dengan standardisasi komponen tabel data, form control, alerts, dan tombol aksi (100% bebas *inline styles*).
    - [x] Visual Icon Picker Component (`<x-icon-picker>`): dialog katalog visual dengan live preview, live search (Indonesia), filter kategori, dan 100% free Lucide Icons (~38 SVG icons).
    - [x] Perluasan Katalog Ikon Lucide Kategori Wilayah (`map`, `map-pin`, `landmark`, `building`, `building-2`, `globe`) dan Data (`table`).
    - [x] Panduan informatif nama rute dengan quick suggestion pills dan datalist rute terdaftar.
    - [x] Otomatisasi urutan tampil mulai 1 (`nextOrder()`) pada model Modul, Menu, dan Sub-Menu serta validasi order `min:1`.
    - [x] Dual field input nama rute pada formulir sub-menu (prefix rute menu induk terkunci + input sub-rute mandiri).
    - [x] Standarisasi komponen metrik universal (`.stats-grid`, `.stat-card`, `.callout-danger`, `.grid-split-2-1`) dan utilitas dropzone.
    - [x] Penyempurnaan interaksi sidebar: `flex-shrink: 0` pada nav items, kurva animasi accordion cubic-bezier, dan auto-scroll saat submenu dibuka di bagian bawah.
    - [x] Modal Interaktif Tambah dan Edit Data: Tombol Tambah dan Edit pada Modul, Menu, dan Sub-Menu memunculkan modal dialog interaktif langsung di atas tabel data (tanpa redirect halaman) dengan auto-reopen pada validasi error serta komponen modular `edit.blade.php`.
    - [x] Classic Minimalist Sidebar Divider: Label modul berfungsi sebagai pemisah kategori yang rapi dan elegan (`.menu-category-label` dengan garis pembatas tipis atas dan tipografi uppercase) serta eliminasi kolom/opsi icon modul yang redundan.
    - [x] Relokasi Profil Pengguna dan Logout ke Sidebar Footer: Topbar dibersihkan dan difokuskan, seksi akun pengguna dipindahkan ke bawah sidebar (`.sidebar-footer`).
    - [x] Dropup Popover Menu Interaktif: Kartu profil pengguna (`#userProfileTrigger`) memicu popover menu melayang ke atas (*dropup*) berisi Pengaturan Profile, Ubah Password, Bantuan, dan Keluar.
    - [x] Standarisasi Sistem Class Global Modular Dropdown: Merefaktor styling ke class universal reusable (`.dropdown`, `.dropdown-menu`, `.dropdown-item`, `.user-card-button`) sesuai standar *Strict No Ad-Hoc Classes*.
    - [x] Penjangkaran Simetris Animasi Sidebar (*Anchored Symmetrical Transition*): Posisi avatar 38px (17px margin) dan seluruh ikon menu (26px margin) terkunci presisi tanpa pergeseran horizontal (0px horizontal jump) baik saat expanded (260px) maupun collapsed (72px), menghilangkan glitch *auto-centering*.
    - [x] Tombol Pencarian Interaktif Mode Ciut dan Shortcut `Ctrl+K`: Kotak pencarian otomatis berubah menjadi tombol ikon 40px di atas Dashboard saat sidebar diciutkan; mengkliknya otomatis membuka sidebar dan memfokuskan input pencarian.
    - [x] Otomatisasi Kapitalisasi Teks Input dan Integritas Data: Frontend real-time auto-transform via `[data-transform]` + helper `TextFormatter` dan Eloquent mutators (UPPERCASE untuk Modul, Title Case / Capitalize Each Word untuk Menu dan Sub-Menu dengan preservasi akronim pendidikan dan teknologi).
    - [x] Halaman Placeholder "Fitur Dalam Pengembangan" dan Dynamic Navigation Fallback:
      - [x] Eliminasi tautan mati (`href="#"`) di sidebar untuk menu/sub-menu tanpa rute terdaftar.
      - [x] Fallback otomatis model `Menu` dan `SubMenu` ke rute `system.under-development?type={menu|submenu}&id={id}` jika rute belum diimplementasikan di `routes/web.php`.
      - [x] Controller `UnderDevelopmentController` dan View `resources/views/system/under-development.blade.php` dengan breadcrumb hierarki, kartu status, dan developer scaffolding blueprint (`php artisan make:controller ...`).
      - [x] Preservasi state aktif sidebar (accordion induk tetap terbuka dan item disorot aktif saat berada di halaman placeholder).
    - [x] Automated Pest test suite (total 59 tests lulus 100%, 261 assertions).
- [x] **2.4. Tata Kelola Menu Akses dan Dynamic Role Templates (User-Centric Granular Permissions)**
  - [x] Skema database: tabel `user_menu_permissions` dan `menu_access_templates` tanpa constraint `unique` pada teks.
  - [x] Model Eloquent `UserMenuPermission`, `MenuAccessTemplate`, serta relasi dan helper otorisasi di `User.php` (`hasMenuAccess()`, `hasSubMenuAccess()`).
  - [x] Seeder awal `MenuAccessTemplateSeeder` untuk preset beragam peran guru (Pengajar, Wali Kelas, Wakasek) dan siswa (Reguler, PKL).
  - [x] Form Requests dan Controller: `MenuAccessController`, `UpdateUserPermissionsRequest`, `UpdateTemplatePermissionsRequest`.
  - [x] Antarmuka manajemen lengkap di `resources/views/system/menu-access/` (Tab Hak Akses per Pengguna, Tab Template Peran Sistem, Form Matriks Izin User, Form Matriks Template Peran).
  - [x] Dukungan tab filter peran Staf (Tendik) dan tombol aktivasi instan akun nonaktif langsung dari baris tabel (`PATCH /sistem/user/{user}/activate`).
  - [x] Standardisasi antarmuka tabel menu akses menggunakan komponen resmi `<x-page-header>`, `<x-tab-pills>`, `<x-tab-pill>`, `<x-data-table>`, dan `<x-pagination-summary>`.
  - [x] Fitur 1-Klik Salin Template Peran ke Akun Pengguna (*Apply Preset*).
  - [x] Integrasi filtering dinamis pada `SidebarComposer` (non-super admin hanya melihat menu yang diizinkan, super admin root bypass).
  - [x] Automated Pest test suite `MenuAccessTest.php` (total 67 tests lulus 100%, 299 assertions).
- [x] **2.5. Cadangan dan Pemulihan Sistem Lengkap (*Full System Snapshot dan Server Migration Ready*)**
  - [x] Arsitektur rute mandiri `/backup-restore` (`backup-restore`) di bawah middleware proteksi Super Admin.
  - [x] Mesin backup mandiri (*pure native PHP/PDO dan ZipArchive*) tanpa dependensi pihak ketiga (`DatabaseBackupService`) dengan dukungan multi-driver (MySQL/MariaDB dan SQLite).
  - [x] Pembuatan paket arsip `.zip` portabel berisi `database.sql`, berkas aset `storage/app/public/`, dan `manifest.json`.
  - [x] Manajemen berkas arsip di `storage/app/backups/`: pembuatan dump ZIP/SQL satu-klik, pengunduhan aman, dan penghapusan aman dari path traversal.
  - [x] Pemulihan sistem (*restore*) dari berkas server maupun dari unggahan berkas eksternal hingga 250 MB via `RestoreBackupRequest`.
  - [x] Pengecekan cerdas kesehatan symlink storage (`ensureStorageLink()`): jika sudah terhubung valid dilanjutkan, jika hilang/rusak dibuat ulang via `Artisan::call('storage:link')` (mencegah aset gambar 404).
  - [x] Dialog modal bahaya (*Danger Confirmation Modal*) dengan kata kunci `"PULIHKAN"` untuk mencegah eksekusi tidak sengaja.
  - [x] Mesin Universal Smooth Real-Time Progressive Loading Screen (`window.InforaProgress` dan `<x-progress-modal />`) dengan animasi gradien shimmer mengalir halus, ambient status orb, dan arsitektur reusable lintas modul.
  - [x] Tampilan antarmuka estetik minimalis menggunakan elemen native HTML5 `<progress>` dan 100% reusable class di `resources/css/app.css` (bebas elemen kaku/stepper, bebas inline styles, dan bebas tag style).
  - [x] Sinkronisasi zona waktu server dan aplikasi ke `Asia/Makassar` (WITA, GMT+8) serta konfigurasi upload hingga 250 MB.
  - [x] Automated Pest test suite `BackupRestoreTest.php` (total 86 tests aplikasi lulus 100%, 398 assertions).

---

## 🏫 Fase 3: Modul Master Data Sekolah dan Manajemen Akademik (SMA, SMK, dan MA)
- [x] **3.1. Master Data Sekolah (Multi-Record Registry dan Kesiapan Bridging)**
  - [x] Skema database tabel `schools`: kolom identitas resmi (nama, NPSN non-unique terindeks, NSS, jenjang SMA/SMK/MA, status Negeri/Swasta, akreditasi A/B/C/Belum), alamat lengkap, kontak telepon/email/web, pimpinan/NIP, yayasan, dan status aktif.
  - [x] Model `School` dengan Title Case auto-mutator (`TextFormatter::titleCase()` preservasi akronim), boolean casting, dan helper query.
  - [x] Migrasi antarmuka penuh ke Anonymous Blade Components (`<x-data-table>`, `<x-form-input>`, `<x-form-select>`, `<x-image-upload>`, `<x-modal>`).
  - [x] Integrasi service mandiri `App\Support\ImageUploader` untuk penamaan semantik Base64 terstandarisasi `{modul}_u{user_id}_{timestamp}_{random8}.{ext}` dan pembersihan aman berkas fisik lama.
  - [x] Form Requests dan Controller CRUD lengkap (`Master\SchoolController`, `StoreSchoolRequest`, `UpdateSchoolRequest`) dengan validasi bahasa Indonesia dan dukungan jenjang MA.
  - [x] Antarmuka manajemen terpadu: Data Table dengan pencarian (nama/NPSN/kota), filter jenjang sekolah (Semua, SMA, SMK, MA), paginasi, thumbnail logo, modal interaktif tambah sekolah (`create.blade.php`), dan modal interaktif edit sekolah (komponen modular `edit.blade.php`).
  - [x] Standarisasi sistem dialog modal: arsitektur flexbox (eliminasi footer clipping), normalisasi margin/padding, divider `var(--infora-border)` standar desain, dan badge seksi edukasi.
  - [x] Automated Pest test suite `SchoolTest.php` (15 skenario pengujian feature test lulus 100%).
- [x] **3.2. Master Data Wilayah Administratif Indonesia (Standar Resmi Kemendagri)**
  - [x] Penetapan kewajiban standar kode wilayah resmi Kementerian Dalam Negeri RI (Kemendagri) numerik murni tanpa titik (2 digit provinsi, 4 digit kabupaten/kota, 6/7 digit kecamatan, 10 digit kelurahan/desa) dan penolakan kode statistik BPS/Wilkerstat (`.agents/rules/regional-codes-standard.md`).
  - [x] Skema database relasional berjenjang 4 tingkat (`provinsi`, `kabupaten`, `kecamatan`, `kelurahan`) dengan indeks kode, cascade delete, dan proteksi integritas relasi sekolah.
  - [x] Model Eloquent `Provinsi`, `Kabupaten`, `Kecamatan`, dan `Kelurahan` lengkap dengan relasi hierarkis, relasi `sekolah()`, scope `aktif()`, dan mutator Title Case (`TextFormatter::titleCase()`).
  - [x] Form Requests dan Controllers CRUD lengkap: `ProvinsiController`, `KabupatenController`, `KecamatanController`, `KelurahanController` dengan validasi hierarki bertingkat.
  - [x] Antarmuka Blade terstandarisasi dengan arsitektur modal CRUD modular (`index.blade.php`, `create.blade.php`, dan `edit.blade.php`), filter cascading dinamis 3 tingkat, sticky header scroll tabel, dan paginasi responsif.
  - [x] Seeder resmi Kemendagri: 38 Provinsi se-Indonesia, 24 Kabupaten/Kota se-Sulawesi Selatan, 27 Kecamatan se-Kabupaten Bone, 372 Kelurahan dan Desa se-Kabupaten Bone.
  - [x] Automated Pest test suite (66 skenario feature tests di `ProvinsiTest.php`, `KabupatenTest.php`, `KecamatanTest.php`, dan `KelurahanTest.php`).
- [ ] **3.3. Master Data Akademik dan Sivitas**
  - [x] CRUD Data Tingkatan Kelas (`Tingkatan`) terhubung ke unit sekolah (`school_id`), model dengan mutator TextFormatter, rute resource `/master/data-tingkatan` (`master.data-tingkatan.*`), Form Requests validasi tenancy, kartu metrik KPI, 1-klik toggle status, antarmuka modal modular (`index.blade.php`, `create.blade.php`, `edit.blade.php`), dan automated test suite `TingkatanTest.php` (16 feature tests, 100% lulus).
  - [x] CRUD Data Jurusan / Konsentrasi Keahlian (SMK), Peminatan/Fase (SMA), dan Program Keagamaan (MA) terhubung ke unit sekolah (`school_id`).

  - [x] Model `Jurusan`, mutator otomatis Title Case, standardisasi rute resource `/master/data-jurusan`, toggle status 1-klik, dan integrasi modal interaktif `InforaModals`.
  - [x] Automated Pest test suite `JurusanTest.php`.
  - [x] Master Data Pendidik dan Tenaga Kependidikan (GTK) / Guru dan Pegawai (Multi-Role `jenis_ptk` JSON, Sinkronisasi Akun Otomatis `GtkAccountManager`, Quick Actions Toggle/Reset, Profil Modal, dan Manajemen Kredensial).
  - [x] Automated Pest test suite `GtkTest.php` (total test suite melonjak menjadi 312 tests lulus 100%, 1.400 assertions).
  - [x] Standardisasi UI/UX & Kepatuhan Arsitektur Data GTK:
    - Eliminasi 100% inline style dan integrasi utility class standar Tailwind/INFORA.
    - Standardisasi komponen form: penggantian native select dengan `<x-form-select>` (dilengkapi alias `:value`) dan `<x-searchable-select>`.
    - Integrasi penuh 4-tier Kemendagri Wilayah Cascades (`provinsi_id`, `kabupaten_id`, `kecamatan_id`, `kelurahan_id`) pada modal Tambah & Ubah dengan `InforaWilayah.bindCascade`, `populateCascade`, dan `restoreOnError`.
    - Eager loading relasi wilayah dan accessor `alamat_lengkap` pada model `Gtk`.
    - Standardisasi modal konfirmasi Reset Password dan Toggle Status menggunakan `<x-modal name="confirm">` dan `InforaModals.bindConfirmButtons`.
    - Standardisasi tombol aksi preview profil menggunakan `<x-crud-button action="view">`.
    - Unifikasi arsitektur penetapan kata sandi `GtkAccountManager::resolveDefaultPassword()` dengan preservasi spasi sandi kustom, evaluasi `filled()`, hierarki fallback konsisten (**NIP** ➔ **NUPTK** ➔ **NIK** ➔ `'infora123'`), dan feedback password pada flash notification.
    - Penanganan route shadowing / collision rute eksplisit `data-gtk/semua` terhadap resource wildcard `{gtk}`.
    - Ekspansi test suite: **334 tests passed, 1.515 assertions (100% lulus)**.
    - [x] **Impor Massal Excel, Ekspor Terfilter & Unduh Template GTK (Layanan Universal `SpreadsheetService`):**
      - Layanan terpusat universal `App\Services\SpreadsheetService` dengan styling template Royal Blue `#1E3A8A`, auto-width, freeze panes, streaming export `.xlsx`/`.csv` via `php://output`, proteksi string data-type eksplisit (`DataType::TYPE_STRING`) untuk NIK/NIP/NUPTK/telepon bebas notasi ilmiah, dan sanitasi formula injection.
      - **Penyederhanaan Onboarding Cepat & Pengisian Mandiri Profil (*Quick Onboarding & Self-Service Completion*):**
        - Rampingkan berkas template impor menjadi 6 kolom esensial pokok (`Nama Lengkap*`, `Jenis Kelamin*`, `Status Kepegawaian*`, `Jenis PTK*`, `NIP`, `NIK`, `No HP / WhatsApp`, `Email`) dengan Excel Data Validation dropdown dan lembar referensi kamus penugasan, membebaskan admin dari beban entri manual biodata detail.
        - Rampingkan modal tambah GTK (`resources/views/master/data-gtk/create.blade.php`) berfokus pada data pokok dan penugasan serta banner panduan mandiri via **Pengaturan Profil**.
      - Form Request validasi `ImportGtkRequest` (.xlsx, .xls, .csv, maks 5MB, tenancy isolation) dengan preservasi kompatibilitas mundur terhadap berkas lama (17 kolom).
      - Logika Upsert data GTK cerdas berbasis NIK atau NIP dengan auto-create/sync akun user via `GtkAccountManager`.
      - Antarmuka terintegrasi pada `resources/views/master/data-gtk/`: tombol Import dan Export menggunakan komponen resmi `<x-crud-button action="create">`, serta modal dialog `import.blade.php` dan `create.blade.php`.
      - Penataan urutan route sebelum `Route::resource('data-gtk', ...)` untuk mencegah route collision.
      - Automated test suite `tests/Feature/GtkImportExportTest.php`, `tests/Feature/GtkTest.php`, `tests/Feature/ProfileTest.php`, dan `tests/Feature/System/MenuAccessTest.php` (total test suite aplikasi mencapai **355 tests passed, 1.668 assertions, 100% lulus**).
  - [ ] *(Ditangguhkan / Backlog)* Penyederhanaan Formulir Entri Data GTK:
    - **Opsi A (Mode Cepat vs Lengkap):** Toggle switch antara mode entri ringkas (hanya nama, JK, status, NIP/NIK, peran PTK) vs mode lengkap.
    - **Opsi B (Wizard 3 Langkah):** Stepper terstruktur (1: Identitas & Kepegawaian ➔ 2: Penugasan & Template Hak Akses ➔ 3: Kontak & Pasfoto).
    - **Opsi C (Seksi Kolapsibel / Accordion):** Field wajib di atas selalu terbuka; seksi Biodata Tambahan, Kontak, Kredensial, dan Pasfoto terlipat rapi.
    - [x] **Opsi D (Impor Massal Excel & Ekspor Terfilter serta Modal Tambah Ringkas):** *(Selesai diimplementasikan via `SpreadsheetService` dan `create.blade.php` dengan filosofi Quick Onboarding 6-Kolom Esensial & Self-Service Completion)* Pengunggahan berkas template spreadsheet dan modal tambah ringkas untuk pendaftaran GTK cepat.
  - [ ] CRUD Data Rombel/Kelas dan Ruangan Belajar.
  - [ ] Manajemen Data Siswa terhubung ke `student_profiles`.
- [ ] **3.4. Kalender dan Jadwal Pelajaran**
  - [x] Manajemen Tahun Ajaran dan Semester (`TahunAjaran` dan `Semester`, relasi berjenjang, tanggal pelaksanaan, aktivasi 1-klik, modal interaktif di `/konfigurasi-sekolah/tahun-ajaran`).
  - [x] Kalender Akademik Terpadu dan Agenda Multi-Day (tampilan ganda Kalender Bulanan Grid dan Tabel Agenda via `<x-tab-pills>`, rentang kegiatan multi-hari, tag kategori warna, feed JSON `/events`, modal interaktif di `/konfigurasi-sekolah/kalender-akademik`).
  - [x] Automated Pest test suite `TahunAjaranTest.php` dan `KalenderAkademikTest.php`.
  - [ ] Alokasi Jam Mengajar Guru dan Penyusunan Jadwal Mingguan per Kelas.
- [ ] **3.5. Jurnal KBM Digital Guru**
  - [ ] Form input jurnal mengajar harian guru per jam tatap muka.
  - [ ] Pencatatan topik/materi, kendala siswa di kelas, dan upload foto dokumentasi KBM via `ImageUploader` / `<x-image-upload>`.
- [ ] **3.6. Kesiswaan dan Bimbingan Konseling (BK)**
  - [ ] Buku catatan pelanggaran tata tertib dan kalkulasi poin disiplin.
  - [ ] Portofolio prestasi, sertifikat, dan kejuaraan siswa.

---

## 🏭 Fase 4: Modul Vokasi dan Kemitraan Industri / PKL (Khusus SMK)
- [ ] **4.1. Master Data Kemitraan DUDI**
  - [ ] Database Rekanan Dunia Usaha dan Dunia Industri.
  - [ ] Profil MoU, kuota magang, dan bidang kompetensi keahlian terkait.
- [ ] **4.2. Manajemen PKL / Magang Siswa**
  - [ ] Ploting penempatan siswa magang dan penetapan guru pembimbing sekolah.
  - [ ] Jurnal Harian PKL Digital bagi siswa di lokasi magang.
  - [ ] Form penilaian dan evaluasi kompetensi oleh instruktur industri.
- [ ] **4.3. Penelusuran Tamatan dan BKK**
  - [ ] Modul Tracer Study keterserapan alumni di dunia kerja/kuliah.
  - [ ] Papan pengumuman informasi lowongan kerja Bursa Kerja Khusus (BKK).

---

## 🏆 Fase 5: Modul Akreditasi dan Bank Dokumen Mutu (BAN-SM / IASP)
- [ ] **5.1. Dashboard Kesiapan Akreditasi**
  - [ ] Pemetaan 4 Komponen IASP (Mutu Lulusan, Proses Pembelajaran, Mutu Guru, Manajemen Sekolah).
  - [ ] Progress bar kelengkapan bukti fisik tiap butir instrumen akreditasi.
- [ ] **5.2. Bank Dokumen Digital**
  - [ ] Manajemen folder terstruktur per butir akreditasi (SK, Modul Ajar, Notula, Foto Kegiatan).
  - [ ] Otomasi integrasi data sekolah (Rekap jurnal KBM, prestasi siswa, dan data PKL langsung terhubung ke butir mutu).
  - [ ] Portal Guest Asesor (Akses audit read-only saat visitasi akreditasi).

---

## ⚡ Fase 6: System Bridging, Notifikasi, PWA dan Finalisasi
- [ ] **6.1. System Bridging dan Interoperabilitas API**
  - [ ] Standardisasi Eloquent API Resources untuk konsumsi client frontend/mobile dan pihak ketiga (format Pure JSON murni, menghindari format HTML pada response data).
  - [ ] API Bridging Gateway untuk sinkronisasi data Dapodik dan e-Rapor.
  - [ ] Integrasi Gateway WhatsApp (Notifikasi pengumuman sekolah dan agenda kegiatan ke wali murid).
  - [ ] API Documentation (OpenAPI / Swagger / Postman Collection).
- [ ] **6.2. PWA (Progressive Web App)**
  - [ ] Service Worker untuk akses offline ringan.
  - [ ] Web App Manifest dan App Icon INFORA untuk install di homescreen smartphone.
- [ ] **6.3. Testing, Security Hardening dan Finalisasi**
  - [ ] Unit dan Feature Testing (Pest / PHPUnit).
  - [ ] Rate limiting pada endpoint otentikasi/API dan proteksi SQL Injection/XSS.
  - [ ] Panduan Penggunaan Guru, Siswa, dan Administrator.

---

## 📚 Rujukan Dokumen Terkait
- 📖 **[README.md](README.md):** Gambaran umum proyek, ringkasan fitur, dan panduan Docker.
- 🚀 **[README.md Detail](detail/README.md):** Rincian lengkap seluruh fitur unggulan dan spesifikasi teknis mendalam.
- 📝 **[CHANGELOG.md](CHANGELOG.md):** Catatan riwayat perubahan dan versi rilis platform.
- 🎨 **[DESIGN.md](detail/DESIGN.md):** Spesifikasi arsitektur teknis, sistem peran (RBAC), skema basis data, dan UI/UX.
- 🖼️ **[BRANDING.md](detail/docs/BRANDING.md):** Filosofi penamaan brand, aset logo lockup, dan app icon.
