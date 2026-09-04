# 📋 INFORA Development Roadmap (TODO.md)
> **Information Network for Organization, Records, & Accreditation (INFORA)**  
> Target: Sistem Informasi Manajemen (SIM) Sekolah Menengah Atas (SMA) & Sekolah Menengah Kejuruan (SMK)

---

## 📌 Status Ringkasan Proyek
- [x] Konsep & Penamaan Platform (**INFORA**)
- [x] Filosofi Brand, Desain Logo & App Icon ([BRANDING.md](detail/docs/BRANDING.md))
- [x] Spesifikasi Desain & Arsitektur Sistem ([DESIGN.md](detail/DESIGN.md))
- [x] Inisialisasi Project Laravel & Docker Stack Multi-Platform ([README.md](README.md))
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
  - [ ] Integrasikan font `Plus Jakarta Sans` & `Inter`.
  - [ ] Setup palet warna INFORA (Slate Dark `#0B132B`, Electric Cyan `#00D2FF`, Royal Blue `#3A7BD5`).
  - [ ] Implementasi master layout terpisah: Desktop (`layouts/desktop.blade.php`) & Mobile (`layouts/mobile.blade.php`).
  - [ ] Siapkan komponen UI master: Bottom Navigation Bar & Card Grid Launcher (Mobile), Sidebar adaptif collapsible & Topbar (Desktop).
  - [ ] Standarisasi class CSS global terpusat di `resources/css/` (penerapan aturan reusable class global, larangan keras class khusus/ad-hoc, tanpa *inline style*, dan tanpa tag `<style>` pada view).
- [ ] **2.2. Manajemen Akun Sivitas & Autentikasi Fleksibel (4 Tipe Akun)**
  - [ ] Implementasi 4 Tipe Akun Utama (`user_type`):
    - `super_admin`: Akses root/developer/yayasan, bypass permission, kelola konfigurasi global & log audit.
    - `admin`: Staf Tata Usaha / Operator sekolah, kelola data sivitas, plotting, dan alokasi menu guru.
    - `guru`: Pendidik/staf pengajar, wali kelas, pembimbing PKL, tim akreditasi.
    - `siswa`: Peserta didik aktif (dan portal orang tua), profil akademik, jurnal mandiri PKL (SMK).
  - [ ] Skema database *Single User Table + Specific Profile Relations* (1-to-1):
    - `student_profiles`: NISN, NIS, rombel_id, angkatan, kontak orang tua.
    - `teacher_profiles`: NIP, NUPTK, gelar_depan, gelar_belakang, no_hp.
    - `staff_profiles`: NIP, unit_kerja, jabatan_tu.
  - [ ] Autentikasi Fleksibel (Auto-Detect Login Identifier):
    - Siswa login via **NISN** / NIS.
    - Guru login via **NIP / NUPTK** atau Email.
    - Admin & Super Admin login via **Username** atau Email.
  - [ ] Setup Laravel Sanctum untuk otentikasi ganda (Web Session & RESTful API Tokens).
- [ ] **2.3. User-Centric Menu Access & De-Duplication System**
  - [ ] Registrasi katalog menu terpusat (`config/menu.php`) dengan atribut `key`, `title`, `icon`, `route`, `group`, dan filter tipe sekolah `school_type: ['sma', 'smk']`.
  - [ ] Alokasi menu berbasis pengguna (*User-Centric Access Control*): mengizinkan satu guru memegang berbagai penugasan tanpa batasan role kaku.
  - [ ] Mekanisme *De-Duplication* otomatis (`unique('key')`) agar menu di sidebar/mobile drawer tidak pernah muncul ganda.
  - [ ] Fitur *Role Presets* (Template menu awal untuk mempercepat admin TU saat membuat akun guru/staf baru).

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
- 📖 **[README.md](README.md):** Gambaran umum proyek, standar arsitektur, dan panduan Docker.
- 🎨 **[DESIGN.md](detail/DESIGN.md):** Spesifikasi arsitektur teknis, sistem peran (RBAC), skema basis data, dan UI/UX.
- 🖼️ **[BRANDING.md](detail/docs/BRANDING.md):** Filosofi penamaan brand, aset logo lockup, dan app icon.
