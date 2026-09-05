# 📝 Changelog

Semua perubahan penting pada proyek **INFORA (Information Network for Organization, Records, & Accreditation)** akan dicatat dalam berkas ini.

Format berkas ini mengacu pada [Keep a Changelog](https://keepachangelog.com/id/1.1.0/), dan proyek ini mematuhi [Semantic Versioning (SemVer)](https://semver.org/lang/id/).

---

## [Unreleased]

### Added
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
- **Automated Testing:**
  - Feature test suite `SuperAdminAuthTest` mencakup 11 pengujian otomatis via Pest (100% lulus).

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
