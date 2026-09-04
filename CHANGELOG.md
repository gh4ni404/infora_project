# 📝 Changelog

Semua perubahan penting pada proyek **INFORA (Information Network for Organization, Records, & Accreditation)** akan dicatat dalam berkas ini.

Format berkas ini mengacu pada [Keep a Changelog](https://keepachangelog.com/id/1.1.0/), dan proyek ini mematuhi [Semantic Versioning (SemVer)](https://semver.org/lang/id/).

---

## [Unreleased]

### Planned (Fase 2)
- Master Dedicated Layouts: `layouts/desktop.blade.php` & `layouts/mobile.blade.php`.
- Sistem styling global terpusat di `resources/css/` (reusable utility & component tokens).
- Migrasi database 4 tipe akun sivitas (`users.user_type`: `super_admin`, `admin`, `guru`, `siswa`).
- Skema tabel profil relasional 1-to-1 (`student_profiles`, `teacher_profiles`, `staff_profiles`).
- Autentikasi fleksibel berbasis deteksi otomatis (NISN, NIP, Username, Email).
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
