# 📋 INFORA Development Roadmap (TODO.md)
> **Information Network for Organization, Records, & Accreditation (INFORA)**  
> Target: Sistem Informasi Manajemen (SIM) Sekolah Menengah Atas (SMA) & Sekolah Menengah Kejuruan (SMK)

---

## 📌 Status Ringkasan Proyek
- [x] Konsep & Penamaan Platform (**INFORA**)
- [x] Filosofi Brand, Desain Logo & App Icon ([BRANDING.md](docs/BRANDING.md))
- [x] Spesifikasi Desain & Arsitektur Sistem ([DESIGN.md](DESIGN.md))
- [x] Inisialisasi Project Laravel & Docker Stack Multi-Platform ([README.md](../README.md))
- [x] Standarisasi Format Upload Base64 (Maks. 1MB) & Pola Penamaan Berkas Semantik
- [ ] Arsitektur Akun Sivitas (4 Tipe Akun) & Sistem Menu Berbasis User (User-Centric & Anti-Duplicate)
- [ ] Dedicated Layout Separation (Desktop & Mobile) & Reusable Global CSS System
- [ ] Implementasi Modul Akademik & Tata Kelola Utama

---

## 🚀 Fase 1: Inisialisasi Environment & Docker Architecture
- [x] **1.1. Inisialisasi Project Laravel (Versi Terbaru / Latest Version)**
  - [x] Generate project Laravel versi terbaru dengan struktur direktori modern.
  - [x] Konfigurasi arsitektur modern (streamlined `bootstrap/app.php`, Vite asset pipeline, Pest testing).
  - [x] Konfigurasi `.env.example`, `.env.docker.example`, dan `.env` untuk environment development & production.
- [x] **1.2. Setup Docker Containerization (Native Multi-Platform)**
  - [x] Buat `Dockerfile` teroptimasi untuk PHP 8.4-FPM (ekstensi `pdo_mysql`, `bcmath`, `gd`, `zip`, `redis`, `opcache`, `pcntl`, Composer, Node.js 22).
  - [x] Buat `docker-compose.yml` (Services: Nginx, App PHP-FPM, MySQL 8.0, Node.js Vite).
  - [x] Buat konfigurasi virtual host Nginx `docker/nginx/default.conf` teroptimasi untuk routing Laravel terbaru.
  - [x] Buat skrip helper automasi lintas OS (`./dev` untuk Linux/macOS/WSL dan `dev.ps1` untuk Windows).

---

## 🔐 Fase 2: Design System, Arsitektur Akun & User-Centric Menu Access
- [ ] **2.1. Desain Sistem & Arsitektur Dedicated Layout (Desktop & Mobile)**
  - [x] Setup palet warna INFORA Terang & Ramah Sivitas (Canvas `#F8FAFC`, Surface `#FFFFFF`, Sky/Royal Blue `#0284C7`/`#2563EB`, Teks Kontras Tinggi `#0F172A`) & tipografi `Plus Jakarta Sans`.
  - [x] Standarisasi class CSS global terpusat di `resources/css/app.css` (reusable global utility & component classes, zero inline styles, zero `<style>` tags).
  - [x] Master layout shell minimalis awal: `layouts/auth.blade.php` dan `layouts/app.blade.php`.
  - [ ] Implementasi layout lanjutan khusus: Mobile App-like (`layouts/mobile.blade.php`) & Desktop Power-Dashboard (`layouts/desktop.blade.php`).
- [ ] **2.2. Manajemen Akun Sivitas & Autentikasi Fleksibel (4 Tipe Akun)**
  - [x] Migrasi kolom sistem tabel `users` (`username`, `user_type`, `is_active`, `avatar_path`).
  - [x] Seeder default Super Admin (entitas pengembang platform: `superadmin` / `password`).
  - [x] Login Controller multi-identifier (login via **Username** maupun **Email**).
  - [x] SuperAdminMiddleware untuk proteksi route dashboard.
  - [x] Dashboard Super Admin minimalis awal (1 menu tunggal: **Dashboard**).
  - [ ] Skema database tabel profil sivitas sekolah (`student_profiles`, `teacher_profiles`, `staff_profiles`).
  - [ ] Setup Laravel Sanctum untuk RESTful API Tokens.
  - [ ] Skema database *Single User Table + Specific Profile Relations* (1-to-1):
    - `student_profiles`: NISN, NIS, rombel_id, angkatan, kontak orang tua.
    - `teacher_profiles`: NIP, NUPTK, gelar_depan, gelar_belakang, no_hp.
    - `staff_profiles`: NIP, unit_kerja, jabatan_tu.
  - [ ] Autentikasi Fleksibel (Auto-Detect Login Identifier):
    - Siswa login via **NISN** / NIS.
    - Guru login via **NIP / NUPTK** atau Email.
    - Admin & Super Admin login via **Username** atau Email.
  - [ ] Setup Laravel Sanctum untuk otentikasi ganda (Web Session & RESTful API Tokens).
- [x] **2.3. Fondasi Hierarki Navigasi & Pengaturan Sistem (Modul, Menu, Sub-Menu)**
  - [x] Skema database hierarki 3 level: tabel `modules`, `menus`, `sub_menus` tanpa constraint `unique` (bebas bentrok penamaan, mengandalkan Primary Key ID).
  - [x] Model Eloquent, Relasi Berjenjang (`hasMany`/`belongsTo`), Casts, dan Factories (`Module`, `Menu`, `SubMenu`).
  - [x] Database Seeder `SystemMenuSeeder` (Modul "Navigasi Utama" & "Pengaturan Sistem" berisi 3 menu dasar: Modul, Menu, Sub-Menu).
  - [x] Integrasi Frontend Sidebar Dinamis:
    - [x] View Composer `SidebarComposer` dengan *safeguard* `Schema::hasTable('modules')`.
    - [x] Dropdown Accordion sub-menu interaktif (`.nav-group-item`, `.nav-group-trigger`, `.nav-arrow-icon`, `.nav-submenu-list`).
    - [x] Sub-menu bersih & rapi: bullet dot dihapus, digantikan indentasi bertingkat dan left accent indicator bar saat hover & aktif.
    - [x] Pencarian menu real-time multi-level (modul, menu, sub-menu dengan auto-expand parent).
    - [x] Collapsible sidebar dengan anchor kiri presisi 26px (tanpa pergeseran horizontal ikon / 0px jump).
    - [x] Resolusi rute fleksibel pada model `Menu` & `SubMenu`: mendukung format hierarki sederhana `modul.menu` / `modul.menu.submenu` tanpa kewajiban akhiran `.index`.
  - [x] Backend CRUD Lengkap Pengaturan Sistem:
    - [x] Resource routes `/system/modules`, `/system/menus`, `/system/sub-menus` di bawah proteksi `['auth', 'super_admin']`.
    - [x] Form Request validation layer berbahasa Indonesia (`StoreModuleRequest`, `UpdateModuleRequest`, dll).
    - [x] Controllers: `ModuleController`, `MenuController`, `SubMenuController`.
    - [x] Antarmuka manajemen lengkap (9 Blade views) dengan standardisasi komponen tabel data, form control, alerts, dan tombol aksi (100% bebas *inline styles*).
    - [x] Visual Icon Picker Component (`<x-icon-picker>`): dialog katalog visual dengan live preview, live search (Indonesia), filter kategori, dan 100% free Lucide Icons (~38 SVG icons).
    - [x] Panduan informatif nama rute dengan quick suggestion pills dan datalist rute terdaftar.
    - [x] Modal Interaktif Tambah Data: Tombol Tambah Modul, Tambah Menu, dan Tambah Sub-Menu memunculkan modal dialog interaktif langsung di atas tabel data (tanpa redirect halaman) dengan auto-reopen pada validasi error.
    - [x] Classic Minimalist Sidebar Divider: Label modul berfungsi sebagai pemisah kategori yang rapi dan elegan (`.menu-category-label` dengan garis pembatas tipis atas dan tipografi uppercase) serta eliminasi kolom/opsi icon modul yang redundan.
    - [x] Relokasi Profil Pengguna & Logout ke Sidebar Footer: Topbar dibersihkan dan difokuskan, seksi akun pengguna dipindahkan ke bawah sidebar (`.sidebar-footer`).
    - [x] Dropup Popover Menu Interaktif: Kartu profil pengguna (`#userProfileTrigger`) memicu popover menu melayang ke atas (*dropup*) berisi Pengaturan Profile, Ubah Password, Bantuan, dan Keluar.
    - [x] Standarisasi Sistem Class Global Modular Dropdown: Merefaktor styling ke class universal reusable (`.dropdown`, `.dropdown-menu`, `.dropdown-item`, `.user-card-button`) sesuai standar *Strict No Ad-Hoc Classes*.
    - [x] Penjangkaran Simetris Animasi Sidebar (*Anchored Symmetrical Transition*): Posisi avatar 38px (17px margin) dan seluruh ikon menu (26px margin) terkunci presisi tanpa pergeseran horizontal (0px horizontal jump) baik saat expanded (260px) maupun collapsed (72px), menghilangkan glitch *auto-centering*.
    - [x] Tombol Pencarian Interaktif Mode Ciut & Shortcut `Ctrl+K`: Kotak pencarian otomatis berubah menjadi tombol ikon 40px di atas Dashboard saat sidebar diciutkan; mengkliknya otomatis membuka sidebar dan memfokuskan input pencarian.
    - [x] Otomatisasi Kapitalisasi Teks Input & Integritas Data: Frontend real-time auto-transform via `[data-transform]` + helper `TextFormatter` & Eloquent mutators (UPPERCASE untuk Modul, Title Case / Capitalize Each Word untuk Menu & Sub-Menu dengan preservasi akronim pendidikan & teknologi).
    - [x] Halaman Placeholder "Fitur Dalam Pengembangan" & Dynamic Navigation Fallback:
      - [x] Eliminasi tautan mati (`href="#"`) di sidebar untuk menu/sub-menu tanpa rute terdaftar.
      - [x] Fallback otomatis model `Menu` & `SubMenu` ke rute `system.under-development?type={menu|submenu}&id={id}` jika rute belum diimplementasikan di `routes/web.php`.
      - [x] Controller `UnderDevelopmentController` & View `resources/views/system/under-development.blade.php` dengan breadcrumb hierarki, kartu status, dan developer scaffolding blueprint (`php artisan make:controller ...`).
      - [x] Preservasi state aktif sidebar (accordion induk tetap terbuka dan item disorot aktif saat berada di halaman placeholder).
    - [x] Automated Pest test suite (total 59 tests lulus 100%, 261 assertions).
- [x] **2.4. Tata Kelola Menu Akses & Dynamic Role Templates (User-Centric Granular Permissions)**
  - [x] Skema database: tabel `user_menu_permissions` & `menu_access_templates` tanpa constraint `unique` pada teks.
  - [x] Model Eloquent `UserMenuPermission`, `MenuAccessTemplate`, serta relasi dan helper otorisasi di `User.php` (`hasMenuAccess()`, `hasSubMenuAccess()`).
  - [x] Seeder awal `MenuAccessTemplateSeeder` untuk preset beragam peran guru (Pengajar, Wali Kelas, Wakasek) dan siswa (Reguler, PKL).
  - [x] Form Requests & Controller: `MenuAccessController`, `UpdateUserPermissionsRequest`, `UpdateTemplatePermissionsRequest`.
  - [x] Antarmuka manajemen lengkap di `resources/views/system/menu-access/` (Tab Hak Akses per Pengguna, Tab Template Peran Sistem, Form Matriks Izin User, Form Matriks Template Peran).
  - [x] Fitur 1-Klik Salin Template Peran ke Akun Pengguna (*Apply Preset*).
  - [x] Integrasi filtering dinamis pada `SidebarComposer` (non-super admin hanya melihat menu yang diizinkan, super admin root bypass).
  - [x] Automated Pest test suite `MenuAccessTest.php` (total 67 tests lulus 100%, 299 assertions).
- [x] **2.5. Cadangan & Pemulihan Sistem Lengkap (*Full System Snapshot & Server Migration Ready*)**
  - [x] Arsitektur rute mandiri `/backup-restore` (`backup-restore`) di bawah middleware proteksi Super Admin.
  - [x] Mesin backup mandiri (*pure native PHP/PDO & ZipArchive*) tanpa dependensi pihak ketiga (`DatabaseBackupService`) dengan dukungan multi-driver (MySQL/MariaDB & SQLite).
  - [x] Pembuatan paket arsip `.zip` portabel berisi `database.sql`, berkas aset `storage/app/public/`, dan `manifest.json`.
  - [x] Manajemen berkas arsip di `storage/app/backups/`: pembuatan dump ZIP/SQL satu-klik, pengunduhan aman, dan penghapusan aman dari path traversal.
  - [x] Pemulihan sistem (*restore*) dari berkas server maupun dari unggahan berkas eksternal hingga 250 MB via `RestoreBackupRequest`.
  - [x] Pengecekan cerdas kesehatan symlink storage (`ensureStorageLink()`): jika sudah terhubung valid dilanjutkan, jika hilang/rusak dibuat ulang via `Artisan::call('storage:link')` (mencegah aset gambar 404).
  - [x] Dialog modal bahaya (*Danger Confirmation Modal*) dengan kata kunci `"PULIHKAN"` untuk mencegah eksekusi tidak sengaja.
  - [x] Mesin Universal Smooth Real-Time Progressive Loading Screen (`window.InforaProgress` & `<x-progress-modal />`) dengan animasi gradien shimmer mengalir halus, ambient status orb, dan arsitektur reusable lintas modul.
  - [x] Tampilan antarmuka estetik minimalis menggunakan elemen native HTML5 `<progress>` dan 100% reusable class di `resources/css/app.css` (bebas elemen kaku/stepper, bebas inline styles, dan bebas tag style).
  - [x] Sinkronisasi zona waktu server & aplikasi ke `Asia/Makassar` (WITA, GMT+8) serta konfigurasi upload hingga 250 MB.
  - [x] Automated Pest test suite `BackupRestoreTest.php` (total 86 tests aplikasi lulus 100%, 398 assertions).

---

## 🏫 Fase 3: Modul Master Data Sekolah & Manajemen Akademik (SMA & SMK)
- [ ] **3.1. Master Data Sekolah & Sivitas**
  - [ ] CRUD Data Jurusan/Konsentrasi Keahlian (SMK) & Peminatan/Fase (SMA).
  - [ ] CRUD Data Rombel/Kelas & Ruangan Belajar.
  - [ ] Manajemen Data Siswa terhubung ke `student_profiles`.
  - [ ] Manajemen Data Guru & Pegawai terhubung ke `teacher_profiles` & `staff_profiles`.
- [ ] **3.2. Kalender & Jadwal Pelajaran**
  - [ ] Manajemen Tahun Ajaran, Semester, & Kalender Akademik.
  - [ ] Alokasi Jam Mengajar Guru & Penyusunan Jadwal Mingguan per Kelas.
- [ ] **3.3. Jurnal KBM Digital Guru**
  - [ ] Form input jurnal mengajar harian guru per jam tatap muka.
  - [ ] Pencatatan topik/materi, kendala siswa di kelas, dan upload foto dokumentasi KBM (format Base64, maksimal 1MB per berkas dengan kualitas visual terjaga, pola penamaan semantik: `{modul}_u{user_id}_{YYYYMMDD_His}_{random_8char}.{ekstensi}`).
- [ ] **3.4. Kesiswaan & Bimbingan Konseling (BK)**
  - [ ] Buku catatan pelanggaran tata tertib & kalkulasi poin disiplin.
  - [ ] Portofolio prestasi, sertifikat, dan kejuaraan siswa.

---

## 🏭 Fase 4: Modul Vokasi & Kemitraan Industri / PKL (Khusus SMK)
- [ ] **4.1. Master Data Kemitraan DUDI**
  - [ ] Database Rekanan Dunia Usaha & Dunia Industri.
  - [ ] Profil MoU, kuota magang, dan bidang kompetensi keahlian terkait.
- [ ] **4.2. Manajemen PKL / Magang Siswa**
  - [ ] Ploting penempatan siswa magang & penetapan guru pembimbing sekolah.
  - [ ] Jurnal Harian PKL Digital bagi siswa di lokasi magang.
  - [ ] Form penilaian & evaluasi kompetensi oleh instruktur industri.
- [ ] **4.3. Penelusuran Tamatan & BKK**
  - [ ] Modul Tracer Study keterserapan alumni di dunia kerja/kuliah.
  - [ ] Papan pengumuman informasi lowongan kerja Bursa Kerja Khusus (BKK).

---

## 🏆 Fase 5: Modul Akreditasi & Bank Dokumen Mutu (BAN-SM / IASP)
- [ ] **5.1. Dashboard Kesiapan Akreditasi**
  - [ ] Pemetaan 4 Komponen IASP (Mutu Lulusan, Proses Pembelajaran, Mutu Guru, Manajemen Sekolah).
  - [ ] Progress bar kelengkapan bukti fisik tiap butir instrumen akreditasi.
- [ ] **5.2. Bank Dokumen Digital**
  - [ ] Manajemen folder terstruktur per butir akreditasi (SK, Modul Ajar, Notula, Foto Kegiatan).
  - [ ] Otomasi integrasi data sekolah (Rekap jurnal KBM, prestasi siswa, dan data PKL langsung terhubung ke butir mutu).
  - [ ] Portal Guest Asesor (Akses audit read-only saat visitasi akreditasi).

---

## ⚡ Fase 6: System Bridging, Notifikasi, PWA & Finalisasi
- [ ] **6.1. System Bridging & Interoperabilitas API**
  - [ ] Standardisasi Eloquent API Resources untuk konsumsi client frontend/mobile & pihak ketiga (format Pure JSON murni, menghindari format HTML pada response data).
  - [ ] API Bridging Gateway untuk sinkronisasi data Dapodik & e-Rapor.
  - [ ] Integrasi Gateway WhatsApp (Notifikasi pengumuman sekolah & agenda kegiatan ke wali murid).
  - [ ] API Documentation (OpenAPI / Swagger / Postman Collection).
- [ ] **6.2. PWA (Progressive Web App)**
  - [ ] Service Worker untuk akses offline ringan.
  - [ ] Web App Manifest & App Icon INFORA untuk install di homescreen smartphone.
- [ ] **6.3. Testing, Security Hardening & Finalisasi**
  - [ ] Unit & Feature Testing (Pest / PHPUnit).
  - [ ] Rate limiting pada endpoint otentikasi/API dan proteksi SQL Injection/XSS.
  - [ ] Panduan Penggunaan Guru, Siswa, dan Administrator.

---

## 📚 Rujukan Dokumen Terkait
- 📖 **[README.md](../README.md):** Gambaran umum proyek, standar arsitektur, dan panduan Docker.
- 📝 **[CHANGELOG.md](../CHANGELOG.md):** Catatan riwayat perubahan dan versi rilis platform.
- 🎨 **[DESIGN.md](DESIGN.md):** Spesifikasi arsitektur teknis, sistem peran (RBAC), skema basis data, dan UI/UX.
- 🖼️ **[BRANDING.md](docs/BRANDING.md):** Filosofi penamaan brand, aset logo lockup, dan app icon.
