# 🌐 INFORA — Sistem Informasi Manajemen SMA & SMK

<p align="center">
  <strong>Information Network for Organization, Records, & Accreditation</strong><br>
  <em>"Era Baru Sistem Informasi Sekolah Menengah Terpadu & Siap Akreditasi"</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-Latest_Stable-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Latest">
  <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4">
  <img src="https://img.shields.io/badge/Docker-Enabled-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Vite-HMR-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
  <img src="https://img.shields.io/badge/Architecture-API--First_&_Bridging-00D2FF?style=for-the-badge&logo=fastapi&logoColor=white" alt="API-First & Bridging">
  <img src="https://img.shields.io/badge/UI--UX-Mobile--First_&_Dedicated_Layouts-10B981?style=for-the-badge&logo=pwa&logoColor=white" alt="Mobile-First & Dedicated Layouts">
  <img src="https://img.shields.io/badge/Data_Contract-Modular_&_Pure_JSON-F59E0B?style=for-the-badge&logo=json&logoColor=white" alt="Modular & Pure JSON">
</p>

---

> 🛑 **STATUS PROYEK: DIARSIPKAN SEBAGAI REFERENSI TEKNIS (ARCHIVED REFERENCE BLUEPRINT)**  
> Repositori ini resmi ditutup dan dialihkan fungsinya sebagai **Arsip & Basis Referensi** untuk pengembangan ulang **INFORA Generasi Baru (Next-Gen INFORA)** yang mengusung arsitektur ramping (*Lean MVC*), hemat memori, dan ramah konteks AI. Seluruh kode, modul, dan dokumentasi di repositori ini menjadi rujukan spesifikasi fitur dan analisis evaluasi (*post-mortem*).

---

## 📖 Tentang INFORA

**INFORA** adalah platform Sistem Informasi Manajemen (SIM) sekolah menengah generasi baru yang dirancang untuk menjawab kebutuhan operasional **SMA** dan **SMK**. 

Dibangun dengan arsitektur **API-First & System Bridging**, **Pengembangan Berbasis Modul (Modular Architecture)**, serta filosofi **Mobile-First Development dengan Pemisahan Layout Dedicated (Desktop & Mobile)**, INFORA mengintegrasikan seluruh lini tata kelola sekolah. Seluruh komunikasi data dirancang menggunakan format **JSON murni** (*zero HTML in response data*) untuk memastikan integrasi yang bersih, terstandarisasi, dan mudah dikonsumsi oleh aplikasi mobile, frontend modern, maupun sistem eksternal.

---

## 🚀 Fitur Unggulan

INFORA menghadirkan ekosistem terpadu yang dirancang khusus untuk memenuhi standar tata kelola dan akreditasi sekolah menengah:

- 🏫 **Manajemen Terpadu SMA & SMK:** Struktur akademik fleksibel (Peminatan/Fase SMA & Konsentrasi Keahlian SMK), Jurnal Digital KBM Guru harian, dan modul Kesiswaan & Bimbingan Konseling (BK).
- 🎓 **Master Data Jurusan:** Manajemen konsentrasi keahlian (SMK) dan peminatan (SMA) terhubung ke unit sekolah, ringkasan KPI, filter sekolah, dan quick status toggle.
- 📅 **Tahun Ajaran & Semester:** Konfigurasi master-detail kanan-kiri (1:1 split), banner status semester aktif, dan helper aktivasi atomik transaksional.
- 📆 **Kalender Akademik Sekolah:** Manajemen agenda sekolah mode ganda (*Interactive Month Grid & Agenda Table*), filter kategori event, badge warna semantik, dan endpoint API events JSON.
- 🏭 **Kemitraan Industri & Magang PKL (Khusus SMK):** Database mitra DUDI, monitoring penempatan magang, plotting guru pembimbing, dan rekap Tracer Study alumni.
- 🏆 **Automasi Instrumen Akreditasi (BAN-SM / IASP):** Dashboard kesiapan 4 komponen mutu, penyimpanan terstruktur bank dokumen digital, serta portal audit khusus asesor.
- 🔌 **API-First Architecture & System Bridging:** RESTful API terstandarisasi, jembatan integrasi (*bridging*) dengan ekosistem Dapodik/LMS/WhatsApp, otorisasi Sanctum, dan kontrak respons *Pure JSON*.
- 📱 **Mobile-First & Dedicated Layout System:** Pemisahan struktur layout native antara Desktop Power-Dashboard dan Mobile App-Like (bukan sekadar responsive CSS hiding).
- 🗂️ **Navigasi Dinamis & Tata Kelola Menu Akses:** Hierarki navigasi sistem (Modul ➔ Menu ➔ Sub-Menu) berbasis data, otorisasi granular *user-centric*, template peran dinamis, dan fallback aman *Zero Dead Links*.
- 💾 **Cadangan & Pemulihan Sistem Lengkap:** Snapshot portabel satu-klik (.zip), mesin backup mandiri native PHP/PDO tanpa dependensi luar, auto-repair symlink storage, dan aksi *batch purge* cadangan.
- 🏫 **Master Data Sekolah:** Registri multi-sekolah SMA & SMK, dropdown berjenjang Kemendagri 4 tingkat via AJAX, alur kerja interaktif modal (Create & Edit) tanpa reload halaman, dan upload logo Base64.
- 🗺️ **Master Wilayah Administratif Kemendagri:** Hierarki 4 tingkat (Provinsi, Kabupaten/Kota, Kecamatan, Kelurahan & Desa) berbasis standar resmi Kemendagri RI, komponen `<x-searchable-select>`, cascading filter 3 tingkat dinamis, proteksi relasi sekolah, dan seeder resmi lengkap.
- 📐 **Tata Letak 3-Tier & Paginasi Responsif:** Arsitektur antarmuka 3-tier (fixed topbar, docked footer, dan scroll internal independen) berpadu dengan komponen paginasi universal `<x-pagination>` dan toolbar tabel adaptif.
- ⚡ **Universal Smooth Progressive Loading Screen:** Mesin indikator progres global (`window.InforaProgress`) beranimasi gradien halus (*buttery smooth motion*) untuk seluruh operasi sistem.

> 💡 **Dokumentasi Lengkap & Spesifikasi Arsitektur:**  
> Untuk rincian teknis mendalam dari seluruh 23 fitur, evaluasi arsitektur (*post-mortem*), dan pedoman arsitektur baru, silakan baca **[detail/README.md](detail/README.md)** dan **[detail/DESIGN.md](detail/DESIGN.md)**.

---

## 💻 Tech Stack & Arsitektur

- **Backend:** Laravel (Versi Terbaru) — *Modular Monolith, RESTful API & Bridging Core*
- **Navigation & Hierarchy:** System-Driven Dynamic Navigation (Modul ➔ Menu ➔ Sub-Menu), 3 Baseline Bootstrap Menus, User-Centric Menu Access Control, Configurable Role Templates, Dynamic Under-Development Fallback (Zero Dead Links), Non-Unique Entity Convention, Automatic Order & Dual-Field Routing
- **School Master Registry:** Multi-Record School Management (SMA & SMK, Public & Private), Dynamic Filtering, Base64 Logo Pipeline, Title-Case Auto Formatting, Bridging-Ready Architecture
- **Regional Administrative Master:** Kemendagri 4-Tier Standard (Provinsi, Kabupaten/Kota, Kecamatan, Kelurahan/Desa), Pure Numeric Codes (Non-BPS), 3-Tier Cascading Filter, School Relation Integrity Guard
- **Layout & Pagination Architecture:** 3-Tier Docked Layout (Fixed Topbar, Docked Bottom Footer, Independent Internal Scrolling), Universal Responsive Pagination Component (`<x-pagination>`, Registered Global via `Paginator::defaultView`)
- **Global Indicator Engine:** Universal Smooth Real-Time Progressive Indicator (`window.InforaProgress`, `<x-progress-modal />`, SSE Stream Reader, Zero Inline Styles)
- **Universal Modal & Form UI:** Flexbox-Driven Modal Architecture (Zero-Clipping Sticky Footers, Dynamic Scrolling Body), Interactive Modular Modal CRUD (`create.blade.php` & `edit.blade.php` as Components in `index`), Margin Normalization, Standard Design Border Dividers, Accessible Slim Scrollbar
- **Data & Response Contract:** Pure JSON Responses (`application/json`), Eloquent API Resources, Zero HTML in Data Payloads
- **Media & File Handling:** Base64 Uploads (Maks. 1MB/file, Preservasi Kualitas, Pola Nama: `{modul}_u{user_id}_{timestamp}_{random8}.{ext}`)
- **Styling & UI Convention:** Reusable Global CSS Classes (Light, Smooth & Clean Theme, Strict No Ad-Hoc/Specific Classes), Zero Inline Styles (`style=""`), Zero `<style>` Tags in Views
- **Database & Timezone:** MySQL 8.0 & Redis Cache (Server Timezone: `Asia/Makassar` / WITA, GMT+8)
- **Web Server:** Nginx (Alpine Linux, `client_max_body_size 250M`)
- **Frontend / Client:** Blade, Alpine.js, TailwindCSS — *Mobile-First Architecture & Dedicated Layouts (Desktop & Mobile)*
- **Containerization:** Docker & Docker Compose (Native Multi-Platform)

---

## 🛠️ Panduan Memulai Cepat (Quick Start)

### Prasyarat
- [Docker](https://docs.docker.com/get-docker/) & [Docker Compose](https://docs.docker.com/compose/) (atau Docker Desktop) terpasang di komputer Anda.
- Git.

### 1. Kloning Repositori
```bash
git clone https://github.com/gh4ni404/infora_project.git
cd infora_project
```

### 2. Salin Konfigurasi Environment
```bash
cp .env.docker.example .env
```

### 3. Jalankan Container Docker
Gunakan helper script bawaan untuk kemudahan lintas OS:
```bash
# Linux / macOS / WSL / Git Bash
./dev start

# Windows (PowerShell)
.\dev.ps1 start
```

### 4. Setup Aplikasi & Migrasi Database
```bash
# Linux / macOS / WSL
./dev artisan migrate

# Windows (PowerShell)
.\dev.ps1 artisan migrate
```
*(Wajib dijalankan saat instalasi awal: `./dev artisan storage:link --relative` untuk membuat symbolic link storage publik yang portabel dan kompatibel antara host dan container Docker)*

### 5. Akses Aplikasi
- **Aplikasi Web INFORA:** [http://localhost:8000](http://localhost:8000)
- **Vite Dev Server (HMR):** [http://localhost:5173](http://localhost:5173)

---

## 📂 Struktur Dokumentasi Proyek

Dokumentasi detail mengenai arsitektur, rencana kerja, riwayat rilis, dan identitas brand:
- 🚀 **[detail/README.md](detail/README.md):** Rincian lengkap seluruh fitur unggulan, arsitektur sistem, dan spesifikasi teknis.
- 📋 **[TODO.md](detail/TODO.md):** Roadmap pengembangan fase demi fase (Fase 1 sampai Fase 6).
- 📝 **[CHANGELOG.md](CHANGELOG.md):** Riwayat perubahan dan rilis versi platform.
- 🎨 **[DESIGN.md](detail/DESIGN.md):** Spesifikasi arsitektur sistem, identitas visual, palet warna, dan skema database.
- 🖼️ **[BRANDING.md](detail/docs/BRANDING.md):** Filosofi penamaan brand, aset logo lockup, dan app icon.
- 🛠️ **[TROUBLESHOOTING.md](detail/docs/TROUBLESHOOTING.md):** Panduan mengatasi masalah umum saat setup Docker (halaman tanpa styling, file `hot`, dan lainnya).

---

## 📄 Lisensi
Proyek ini dikembangkan di bawah lisensi open-source [MIT License](LICENSE).
