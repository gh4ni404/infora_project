# 📝 Changelog

Semua perubahan penting pada proyek **INFORA (Information Network for Organization, Records, & Accreditation)** akan dicatat dalam berkas ini.

Format berkas ini mengacu pada [Keep a Changelog](https://keepachangelog.com/id/1.1.0/), dan proyek ini mematuhi [Semantic Versioning (SemVer)](https://semver.org/lang/id/).

---

## [Unreleased]

### Added
- **Sistem Cadangan & Pemulihan Sistem Lengkap (*Full System Snapshot & Server Migration Ready*):**
  - Meningkatkan fitur Backup & Restore pada rute `/backup-restore` (`backup-restore`) di bawah modul PENGATURAN SISTEM dari sekadar dump database menjadi snapshot sistem terpadu berformat `.zip`.
  - Mengemas basis data (`database.sql`), seluruh berkas/gambar unggahan pengguna (`storage/app/public/`), dan berkas `manifest.json` metadata ke dalam satu arsip portabel mandiri via native PHP `ZipArchive`.
  - Pengecualian cerdas pada folder `vendor/`, `node_modules/`, `.git/`, dan `storage/framework/` agar ukuran arsip tetap ringkas dan bebas konflik binary OS antar server.
  - **Pencegahan Otomatis Gambar 404 (*Symlink Health Check & Auto-Repair*)**: Saat pemulihan (*restore*), sistem memeriksa kesehatan symlink `public/storage`; jika sudah terhubung valid ke target lokal, sistem melanjutkan tanpa operasi berlebih; jika belum ada atau rusak (*broken link* dari server lama), sistem otomatis membuat ulang symlink (`Artisan::call('storage:link')`).
  - **Menu Dasar Bawaan Sistem (*Baseline System Seeder*)**: Menu mandiri **Backup & Restore** (`route: backup-restore`, `icon: folder`, `order: 2`) resmi dijadikan menu sistem dasar di [SystemMenuSeeder.php](file:///home/gania/infora_project/database/seeders/SystemMenuSeeder.php) di bawah modul PENGATURAN SISTEM, menjamin menu ini langsung aktif di sidebar saat *fresh installation* (`php artisan migrate:fresh --seed`) agar proses pemulihan data dapat langsung dieksekusi seketika.
  - Test suite Pest diperluas dengan [SystemBaselineSeederTest.php](file:///home/gania/infora_project/tests/Feature/System/SystemBaselineSeederTest.php) (total 83 tests aplikasi lulus 100%, 377 assertions).
- **Sistem Tata Kelola Menu Akses & Dynamic Role Templates (User-Centric Granular Permissions):**
  - Mengimplementasikan otorisasi navigasi berbasis pengguna pada rute `/sistem/menu-akses` (`sistem.menu-akses`) lengkap dengan 4 hak aksi granular: Lihat (`can_view`), Tambah (`can_create`), Ubah (`can_edit`), dan Hapus (`can_delete`).
  - Sistem template peran dinamis (`menu_access_templates`) yang dapat dikonfigurasi penuh oleh Super Admin untuk beragam peran (Guru Pengajar, Wali Kelas, Wakasek Kurikulum, Wakasek Kesiswaan, Staf TU, Siswa Reguler, Siswa PKL) melalui antarmuka khusus.
  - Fitur 1-Klik Salin Template Peran (*Apply Preset*) saat mengelola izin pengguna untuk pengisian hak akses instan dalam 1 detik.
  - Antarmuka manajemen lengkap di `resources/views/system/menu-access/` dengan kepatuhan arsitektur 100% bebas *inline style* dan bebas tag `<style>`.
  - Integrasi filtering dinamis pada `SidebarComposer` dengan proteksi *root bypass* permanen untuk Super Admin.
  - Test suite terintegrasi Pest `MenuAccessTest.php` (total 67 tests aplikasi lulus 100%, 299 assertions).
- **Halaman Placeholder "Fitur Dalam Pengembangan" & Dynamic Fallback (Zero Dead Links):**
  - Mengeliminasi tautan mati (`href="#"`) di sidebar: Setiap menu atau sub-menu yang dibuat oleh Super Admin namun belum memiliki rute implementasi aktif di `routes/web.php` secara otomatis mengarah ke halaman pratinjau `/system/under-development?type={menu|submenu}&id={id}`.
  - Implementasi `UnderDevelopmentController` yang mengekstrak informasi hierarki entitas navigasi (Modul, Menu, Sub-Menu) dan menghasilkan saran cetak biru (*blueprint*) pengembang berupa snippet rute Laravel dan perintah CLI `php artisan make:controller ...`.
  - Tampilan antarmuka profesional `resources/views/system/under-development.blade.php` dengan tema visual Infora, breadcrumbs navigasi, badge status *Under Development*, kartu detail entitas, dan tombol aksi cepat (*Edit Konfigurasi* & *Kembali ke Dashboard*).
  - Preservasi state aktif sidebar: Saat halaman placeholder diakses, accordion menu induk otomatis tetap terbuka (`is-open active-parent`) dan sub-menu bersangkutan disorot aktif (`active`).
  - Suite pengujian fitur otomatis (`UnderDevelopmentTest.php`) dan penyesuaian assertions pada `SidebarFrontendTest.php`, seluruh 59 test suite aplikasi lulus 100% (261 assertions).
- **Ikon Pencarian Interaktif pada Sidebar Ciut & Shortcut Keyboard (`Ctrl+K`):**
  - Mengubah kotak pencarian (`.sidebar-search .search-box`) menjadi tombol ikon 40px x 40px interaktif saat sidebar dalam keadaan ciut (72px), mengisi ruang kosong di atas menu Dashboard dengan fungsi yang produktif dan estetik.
  - Mengklik tombol pencarian pada mode ciut akan langsung membuka sidebar secara otomatis dan memfokuskan kursor ke input pencarian (`#sidebarMenuSearch`).
  - Dukungan shortcut keyboard global `Ctrl+K` atau `/` (saat tidak sedang mengetik di input/textarea) untuk membuka sidebar dan memfokuskan pencarian menu dari mana saja di dalam aplikasi.
- **Seksi Profil Pengguna & Dropup Popover Interaktif di Sidebar Footer:**
  - Memindahkan informasi profil pengguna dan aksi keluar dari topbar ke bagian bawah sidebar (*sidebar footer*), menghadirkan topbar yang bersih, fokus, dan lapang.
  - Kartu pengguna interaktif (`#userProfileTrigger`) yang memicu popover menu melayang ke atas (*dropup*) saat diklik, dilengkapi indikator panah *chevron* yang berputar 180° saat terbuka.
  - Menu popover lengkap: Header ringkasan akun dengan avatar inisial, opsi **Pengaturan Profile**, **Ubah Password**, **Bantuan** (Pusat Bantuan INFORA), serta tombol **Keluar / Logout** aman berbalut aksi destruktif (*danger styling*).
  - Interaksi aksesibel: Menutup otomatis via tombol `Escape`, menutup saat mengklik area di luar (*click outside*), dan notifikasi melayang (*floating toast*) non-intrusif pada fitur yang sedang dipersiapkan.
  - Responsif pada sidebar ciut: Saat sidebar diciutkan (72px), menu otomatis melayang ke arah samping kanan (`left: 76px; bottom: 8px; width: 230px`) sehingga seluruh navigasi tetap dapat dijangkau dengan mudah.

### Changed & Refactored
- **Penyempurnaan Animasi Menu Ikon & Penjangkaran 26px (*Buttery Smooth Anchored Transition*):**
  - Mengeliminasi efek hentakan visual (*abrupt jump*) pada ikon-ikon navigasi menu saat sidebar diciutkan atau dibuka.
  - Menjaga koordinat horizontal ikon terkunci stabil pada margin 26px dari tepi kiri (`16px padding container + 10px padding tombol = 26px`), baik saat terbuka (260px) maupun ciut (72px) tanpa mengubah padding menjadi 0 atau memaksakan `justify-content: center`.
  - Menggantikan `display: none` pada teks menu, badge jumlah, dan panah accordion dengan transisi kurva halus `opacity: 0.2s` dan `transform: translateX(-10px)`.
  - Penutupan halus accordion submenu yang sedang terbuka (`max-height: 0; opacity: 0; padding: 0`) saat sidebar diciutkan.
  - Transformasi halus pemisah modul (`.menu-category-label`) menjadi garis pembatas tipis 1px yang rapi di mode ciut.
- **Standarisasi Sistem Class Global Universal Dropdown (`resources/css/app.css`):**
  - Merefaktor seluruh penamaan class ad-hoc (seperti `.user-dropup-*`) ke sistem komponen dropdown modular universal: `.dropdown`, `.dropdown.dropdown-full`, `.dropdown-menu`, `.dropdown-menu-up`, `.dropdown-menu-end`, `.dropdown-menu-full`, `.dropdown-header`, `.dropdown-divider`, `.dropdown-list`, `.dropdown-item`, dan `.dropdown-item.is-danger`.
  - Mengimplementasikan komponen kartu profil universal `.user-card-button` dan standarisasi avatar global `.user-avatar-circle` (38px) serta varian `.user-avatar-circle.avatar-sm` (28px).
  - Menegakkan kepatuhan 100% pada aturan arsitektur *Strict No Ad-Hoc Classes* (bebas class sekali pakai, tanpa inline styles, dan tanpa tag `<style>`).
- **Perbaikan Penjangkaran Animasi Transisi Sidebar (*Anchored Symmetrical Transition*):**
  - Mengeliminasi bug transisi di mana elemen pengguna sempat memusatkan diri ke tengah lalu bergeser ke kiri (*auto ketengah baru ke kiri*) saat toggle sidebar ciut/buka.
  - Mengunci posisi avatar pada koordinat horizontal tetap 17px dari tepi kiri layar (`padding: 0.75rem 12px` pada footer + `padding: 0.375rem 5px` pada kartu = 17px), identik dengan margin emblem logo header (`padding: 1.25rem 17px`).
  - Menjaga alignment selalu `justify-content: flex-start` (tanpa pernah berganti ke `center`).
  - Menerapkan transisi halus `opacity: 0.2s ease` dan `transform: translateX(-10px)` pada nama pengguna dan ikon chevron, sehingga teks memudar mulus selaras dengan penyusutan lebar sidebar (260px ke 72px).
- **Pemisah Modul Classic Minimalist & Eliminasi Opsi Icon Modul:**
  - Menerapkan desain pemisah kategori modul *Classic Minimalist* (`.menu-category-label` dengan garis pembatas tipis atas dan tipografi uppercase elegan) yang rapi dan profesional.
  - Menghapus atribut dan kolom `icon` dari tabel database `modules`, model, factory, seeder, Form Requests, dan views modul karena kategori modul berfungsi sebagai header pengelompokan yang tidak menampilkan icon di sidebar.

- **Modal Interaktif Formulir Tambah (Modul, Menu, & Sub-Menu):**
  - Alih-alih berpindah/redirect ke halaman baru saat mengklik tombol "+ Tambah", sistem kini memunculkan modal dialog interaktif langsung pada halaman index (`system/modules`, `system/menus`, `system/sub-menus`).
  - Arsitektur styling modal global reusable di `resources/css/app.css` (`.modal-backdrop`, `.modal-dialog`, `.modal-header`, `.modal-body`, `.modal-footer`).
  - Layering Z-Index berlapis: Modal form berada pada `z-index: 9000` dan Modal Icon Picker berada pada `z-index: 10000`, memungkinkan katalog visual ikon dibuka di atas modal formulir secara mulus.
  - Penanganan validasi cerdas: Modal otomatis terbuka kembali (*auto-reopen*) ketika terdapat kesalahan input dari server (`$errors->any()`).
- **Visual Icon Picker Interaktif & Katalog Ikon Terkurasi (100% Free - Lucide Icons):**
  - Komponen Blade reusable `<x-icon-picker>` di `resources/views/components/icon-picker.blade.php` dengan live preview card terpilih, pencarian instan (nama/kata kunci Indonesia), filter kategori (Navigasi, Akademik, Data, Pengguna, Sistem, Umum), dan modal katalog visual yang mudah diklik tanpa perlu mengetik manual.
  - Konfigurasi katalog ikon resmi di `config/icons.php` berisi ~38 ikon Lucide SVG 100% gratis dan open-source (berlisensi ISC tanpa biaya/lisensi berbayar).
  - Perluasan komponen `<x-icon>` dengan dukungan SVG path lengkap untuk seluruh koleksi ikon (`school`, `graduation-cap`, `book-open`, `award`, `database`, `users`, `compass`, `settings`, dll).
  - Integrasi Icon Picker pada formulir Tambah/Edit Modul dan Menu.
- **Resolusi Rute Fleksibel & Panduan Informatif Nama Rute:**
  - Metode cerdas pada model `Menu` dan `SubMenu` (`getRouteUrlAttribute()`, `isRouteActive()`, `hasActiveSubMenu()`) yang otomatis mencocokkan format hierarki sederhana `modul.menu` atau `modul.menu.submenu` tanpa mewajibkan pengguna menambahkan akhiran teknis `.index`.
  - Kotak panduan informatif (`.route-guide-box`) pada formulir Menu dan Sub-Menu yang menjelaskan kapan rute harus dikosongkan (menu induk dropdown) dan kapan harus diisi, dilengkapi *quick suggestion pills* dan `<datalist>` rute terdaftar.
- **Penyempurnaan UI/UX Sub-Menu Sidebar:**
  - Menghapus titik (*bullet dot*) pada sub-menu sidebar demi visual yang lebih bersih dan rapi.
  - Mengganti bullet dengan indentasi bertingkat elegan dan indikator garis vertikal aksen (*left accent indicator bar*) saat di-hover dan aktif.
- **Modul Autentikasi Super Admin:**
  - Login multi-identifier fleksibel via **Username** maupun **Email** dengan proteksi session fixation.
  - Verifikasi status keaktifan akun (`is_active`).
  - Fitur logout aman (session invalidation & token CSRF regeneration).
  - Middleware `SuperAdminMiddleware` untuk membatasi akses khusus akun bertipe `super_admin`.
- **Database & Model:**
  - Migrasi penambahan kolom sistem pada tabel `users`: `username`, `user_type`, `is_active`, `avatar_path`.
  - Seeder default Super Admin (`superadmin` / `password` / `superadmin@infora.test`).
  - Helper methods pada model `User`: `isSuperAdmin()`, `isAdmin()`, `isGuru()`, `isSiswa()`.
- **Design System & Antarmuka Awal:**
  - Standardisasi class CSS global reusable di `resources/css/app.css` (bebas class ad-hoc, tanpa *inline styles*, dan tanpa tag `<style>`).
  - Master layout `layouts/auth.blade.php` dan `layouts/app.blade.php`.
  - Tampilan Dashboard awal Super Admin dengan **1 menu tunggal: Dashboard**.
  - Fitur UX interaktif Lihat/Sembunyikan Kata Sandi (*Show/Hide Password Toggle*) dengan ikon SVG ramah aksesibilitas.
  - Indikator loading interaktif dan proteksi *anti-spam click* pada tombol "Masuk ke Dashboard" (`#btnLogin`) dengan SVG spinner berputar halus dan transisi teks "Memproses...".
  - Fitur pencarian menu real-time pada sidebar Dashboard dengan standardisasi komponen reusable `.search-box`, `.search-icon`, `.search-input`, `.search-clear` serta `.empty-state` untuk feedback saat menu tidak ditemukan.
  - Fitur Collapsible Sidebar interaktif pada layout dashboard: tombol toggle panel (`#sidebarToggle`) dengan class reusable `.btn-icon` di topbar, transisi lebar halus (260px ke 72px), tampilan branding kompak, animasi rotasi ikon panah, dan persistensi status via `localStorage`.
  - Integrasi identitas brand visual resmi pada header sidebar menggunakan aset emblem badge simetris presisi (`public/images/infora-emblem-badge.png`) berpadu dengan tipografi tajam Plus Jakarta Sans (Opsi 1 Hybrid).
- **Fondasi Menu Hirarki Dinamis (Modul, Menu, Sub-Menu Tanpa Unique Constraint):**
  - Migrasi skema database terstruktur: tabel `modules`, `menus`, dan `sub_menus` tanpa kolom unik yang membebani (bebas bentrok penamaan, murni mengandalkan Primary Key ID).
  - Model Eloquent `Module`, `Menu`, `SubMenu` lengkap dengan relasi berjenjang `hasMany`/`belongsTo`, casts, dan factories pendukung.
  - Seeder default `SystemMenuSeeder` yang mendaftarkan 3 menu sistem dasar (*Modul*, *Menu*, *Sub-Menu*) pada modul "Pengaturan Sistem" serta menu "Dashboard" pada modul "Navigasi Utama".
- **Integrasi Sidebar Frontend Dinamis & Interaktif:**
  - View Composer `SidebarComposer` yang menginjeksi daftar modul, menu, dan sub-menu aktif ke `layouts.app` dengan proteksi tabel `Schema::hasTable('modules')`.
  - Dukungan render hierarki 3 tingkat (*Modules* ➔ *Menus* ➔ *Sub-Menus*) dengan komponen ikon `<x-icon>` yang mendukung ikon Lucide SVG (`layout-dashboard`, `layers`, `menu`, `list-tree`, `shield-check`, `settings`, dll).
  - Dropdown Accordion sub-menu interaktif (`.nav-group-item`, `.nav-group-trigger`, `.nav-arrow-icon`, `.nav-submenu-list`) dengan rotasi panah halus 180° dan auto-uncollapse sidebar saat menu ber-sub-menu diklik dari status *collapsed*.
  - Pencarian menu real-time multi-level cerdas yang mencakup nama modul, menu, hingga sub-menu, otomatis membuka parent accordion jika sub-menu cocok, dan menyembunyikan kategori modul yang tidak relevan.
  - Presisi koordinat anchor kiri 26px pada seluruh ikon navigasi, baik pada mode expanded (260px) maupun collapsed (72px), menghasilkan animasi transisi lipat sidebar yang mulus tanpa pergeseran horizontal (0px horizontal jump).
- **Backend CRUD Lengkap Pengaturan Sistem (Modul, Menu, Sub-Menu):**
  - Rute resource RESTful terproteksi: `/system/modules`, `/system/menus`, `/system/sub-menus` di bawah middleware `['auth', 'super_admin']`.
  - Form Request Validation Layer dengan pesan bahasa Indonesia: `StoreModuleRequest`, `UpdateModuleRequest`, `StoreMenuRequest`, `UpdateMenuRequest`, `StoreSubMenuRequest`, `UpdateSubMenuRequest` (bebas aturan *unique* untuk fleksibilitas maksimal sesuai kebutuhan user).
  - Controller CRUD lengkap di namespace `App\Http\Controllers\System`:
    - `ModuleController`: daftar modul dengan hitungan relasi menu (`menus_count`), pencarian, form tambah/edit, dan cascade delete.
    - `MenuController`: daftar menu dengan relasi modul dan hitungan sub-menu (`sub_menus_count`), filter dropdown modul, dan cascade delete.
    - `SubMenuController`: daftar sub-menu dengan relasi induk menu & modul, filter dropdown menu, dan form tambah/edit.
  - Komponen Desain Global & Antarmuka Manajemen (9 views di `resources/views/system/`):
    - Komponen tabel data elegan (`.table-card`, `.table-toolbar`, `.data-table`, `.table-actions`, `.table-footer`).
    - Alert notifikasi sukses/gagal (`.alert-success`, `.alert-danger`).
    - Komponen form input, select, checkbox, hint, dan tombol aksi (`.btn-edit`, `.btn-delete`, `.btn-primary`, `.btn-secondary`).
    - 100% mematuhi aturan strict arsitektur: **Zero inline styles (`style=""`) dan Zero tag `<style>`**.
- **Sistem Otomatisasi Kapitalisasi Teks Input (UPPERCASE Modul & Title Case Menu/Sub-Menu):**
  - Helper terpusat `App\Support\TextFormatter` untuk pemformatan teks konsisten: metode `upper()` dan `titleCase()` yang cerdas mempertahankan akronim standar pendidikan & teknologi (`SMK`, `SMA`, `SIM`, `PKL`, `KBM`, `GTK`, `BAN-SM`, `RPP`, `IT`, `TU`, dll.).
  - Eloquent Attribute Mutators pada Model `Module` (nama otomatis tersimpan dalam format **UPPERCASE**) serta `Menu` dan `SubMenu` (nama otomatis tersimpan dalam format **Capitalize Each Word / Title Case** dengan preservasi akronim).
  - Integrasi Frontend Reaktif & CSS Utilitas:
    - Atribut `data-transform="uppercase"` pada formulir modul dengan auto-uppercase instan saat mengetik tanpa merusak posisi kursor (*caret range*).
    - Atribut `data-transform="title-case"` pada formulir menu & sub-menu dengan pemformatan otomatis saat event `blur` dan sebelum `submit`.
    - Kelas utilitas CSS `.text-uppercase` dan `.text-capitalize` di `resources/css/app.css` untuk feedback visual seketika.
  - Pembaruan seeder baseline `SystemMenuSeeder` dengan nama modul berformat kapital (`NAVIGASI UTAMA` & `PENGATURAN SISTEM`).
  - Unit test baru `TextFormatterTest` dan peningkatan test suite hingga **54 tests lulus 100% (242 assertions)**.
- **Automated Testing & Jaminan Kualitas:**
  - Penambahan feature dan unit tests baru (`TextFormatterTest`, `SidebarFrontendTest`, `ModuleCrudTest`, `MenuCrudTest`, `SubMenuCrudTest`).
  - Total test suite mencapai **54 tests lulus 100% (242 assertions)**.
  - 100% lulus standardisasi formatting Laravel Pint (PSR-12).

### Changed
- **Transformasi Desain Sistem ke Nuansa Terang, Ramah & Keterbacaan Tinggi:**
  - Pembaruan tema visual global di `resources/css/app.css` dari nuansa gelap ke tema terang (*bright, smooth, clean modern*).
  - Peningkatan kontras teks (`Slate-900 #0F172A` di atas latar kanvas sejuk `#F8FAFC` dan kartu `#FFFFFF`) untuk menjamin keterbacaan maksimal (WCAG AAA) yang nyaman bagi guru dan siswa.
  - Harmonisasi palet warna aksen pendidikan: Sky Blue (`#0284C7`) dan Royal Blue (`#2563EB`) dengan latar navigasi aktif *soft blue* (`#EFF6FF`).
  - Penyesuaian elevasi kartu putih bersih dengan batas presisi halus (`#E2E8F0`) dan bayangan lembut melayang.

### Planned (Tahap Berikutnya)
- Skema tabel profil relasional 1-to-1 (`student_profiles`, `teacher_profiles`, `staff_profiles`).
- Dedicated Layout lanjutan: Mobile App-like (`layouts/mobile.blade.php`) & Desktop Power-Dashboard (`layouts/desktop.blade.php`).
- Registry menu terpusat (`config/menu.php`) dengan alokasi hak akses *User-Centric* & de-duplikasi otomatis.

---

## [0.1.0] - 2026-09-05

### Added
- **Docker Multi-Platform Environment (Non-Sail):**
  - Containerization terisolasi dengan Docker Compose: PHP 8.4-FPM (`infora_app`), Nginx Alpine (`infora_web`), MySQL 8.0 (`infora_mysql`), dan Node.js 22 LTS (`infora_node`).
  - Skrip automasi serbaguna lintas platform: `./dev` (Bash untuk Linux/macOS/WSL) dan `dev.ps1` (PowerShell untuk Windows).
  - Konfigurasi environment `.env.docker.example` dan template koneksi database lokal/remote.
- **Standar Arsitektur Inti Platform:**
  - **API-First & System Bridging:** Arsitektur modular siap bridging dengan Dapodik, e-Rapor, LMS, dan WhatsApp Gateway.
  - **Pure JSON Response Contract:** Seluruh endpoint dan interaksi data wajib mengembalikan format `application/json` murni; secara ketat menghindari format HTML pada respons data.
  - **Mobile-First & Dedicated Layouts:** Pemisahan struktur fisik template antara Desktop (power-dashboard analitik) dan Mobile (app-like bottom navigation & card drawer).
  - **Standar Reusable Global CSS:** Seluruh styling antarmuka wajib menggunakan class global reusable; dilarang keras membuat class khusus/ad-hoc, tanpa *inline styles* (`style="..."`), dan tanpa tag `<style>` pada view.
  - **Standar Media Upload Base64:** Seluruh pengunggahan dokumentasi foto (KBM, PKL, Akreditasi) menggunakan string Base64 maksimal 1MB per berkas dengan preservasi kualitas visual.
  - **Pola Penamaan Berkas Semantik:** Standarisasi nama file otomatis `{modul}_u{user_id}_{YYYYMMDD_His}_{random_8char}.{ekstensi}` untuk audit trail dan pencegahan tabrakan nama berkas.
  - **Arsitektur Akun Sivitas (4 Tipe Akun):** Klasifikasi tingkat pengguna `super_admin`, `admin`, `guru`, dan `siswa` dengan profil relasional terpisah dan login fleksibel (NISN/NIP/Username/Email).
  - **Sistem Menu User-Centric:** Alokasi menu berbasis pengguna untuk mengakomodasi peran majemuk guru tanpa duplikasi menu.
- **Dokumentasi Proyek Komprehensif:**
  - `README.md`: Gambaran platform, badges arsitektur, dan panduan quick start Docker.
  - `TODO.md`: Roadmap tahapan implementasi dari Fase 1 hingga Fase 6.
  - `detail/DESIGN.md`: Spesifikasi teknis, diagram arsitektur bridging, skema data, dan keamanan.
  - `detail/docs/BRANDING.md`: Filosofi penamaan brand INFORA, palet warna, dan aset logo.

### Removed
- Seluruh referensi modul presensi digital / absensi / RFID / geofencing dari template lama yang tidak relevan dengan fokus SIM SMA & SMK.
- Berkas dokumentasi redundan `detail/README.md`.
