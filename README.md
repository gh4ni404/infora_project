# 🌐 INFORA — Sistem Informasi Manajemen SMA, SMK, dan MA

<p align="center">
  <strong>Information Network for Organization, Records, dan Accreditation</strong><br>
  <em>"Era Baru Sistem Informasi Sekolah Menengah Terpadu dan Siap Akreditasi"</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-Latest_Stable-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Latest">
  <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4">
  <img src="https://img.shields.io/badge/Docker-Enabled-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Vite-HMR-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
  <img src="https://img.shields.io/badge/Tests-371_Passed_%7C_1728_Assertions-10B981?style=for-the-badge&logo=pest&logoColor=white" alt="Pest Tests">
  <img src="https://img.shields.io/badge/Architecture-API--First_&_Bridging-00D2FF?style=for-the-badge&logo=fastapi&logoColor=white" alt="API-First dan Bridging">
  <img src="https://img.shields.io/badge/UI--UX-Mobile--First_&_Dedicated_Layouts-10B981?style=for-the-badge&logo=pwa&logoColor=white" alt="Mobile-First dan Dedicated Layouts">
  <img src="https://img.shields.io/badge/Data_Contract-Modular_&_Pure_JSON-F59E0B?style=for-the-badge&logo=json&logoColor=white" alt="Modular dan Pure JSON">
</p>

---

## 📖 Tentang INFORA

**INFORA** adalah platform Sistem Informasi Manajemen (SIM) sekolah menengah generasi baru yang dirancang untuk menjawab kebutuhan operasional **SMA**, **SMK**, dan **Madrasah Aliyah (MA)**. 

Dibangun dengan arsitektur **API-First dan System Bridging**, **Pengembangan Berbasis Modul (Modular Architecture)**, serta filosofi **Mobile-First Development dengan Pemisahan Layout Dedicated (Desktop dan Mobile)**, INFORA mengintegrasikan seluruh lini tata kelola sekolah. Seluruh komunikasi data dirancang menggunakan format **JSON murni** (*zero HTML in response data*) untuk memastikan integrasi yang bersih, terstandarisasi, dan mudah dikonsumsi oleh aplikasi mobile, frontend modern, maupun sistem eksternal.

---

## 🚀 Fitur Unggulan

INFORA menghadirkan ekosistem terpadu yang dirancang khusus untuk memenuhi standar tata kelola dan akreditasi sekolah menengah:

- 🏫 **Manajemen Terpadu SMA, SMK, dan MA:** Registri multi-sekolah, struktur akademik fleksibel (Konsentrasi Keahlian SMK, Peminatan/Fase SMA, dan Program Peminatan/Keagamaan MA), Jurnal Digital KBM Guru harian, dan modul BK.
- 🎓 **Master Data Jurusan dan Peminatan:** Tata kelola program keahlian/peminatan berbasis unit sekolah, toggle status aktif 1-klik, dan dialog modal CRUD interaktif.
- 📚 **Master Data Tingkatan Kelas:** Penjenjangan tingkatan kelas (X, XI, XII / 1-13) terhubung unit sekolah, pemetaan Fase Kurikulum Merdeka (Fase E & F), toggle status aktif 1-klik, kartu metrik ringkasan KPI, dan modal dialog CRUD modular.
- 👨‍🏫 **Master Data Pendidik dan Tenaga Kependidikan (GTK):** Pengelolaan sivitas pendidik & tendik dengan dukungan tugas majemuk (*multi-role* JSON), sinkronisasi akun otomatis, reset password, dan profil modal interaktif.
- 👤 **Pengaturan Profil Pengguna Mandiri & Keamanan:** Halaman khusus 2-kolom dengan navigasi vertikal ramping (4 tab GTK dan 2 tab non-GTK), integrasi terpadu pasfoto avatar, identitas diri, kredensial login (username), dan ubah kata sandi pada Informasi Pribadi, dirty-form unsaved changes confirmation modal, serta auto-logout pasca ganti password.
- 📅 **Konfigurasi Sekolah (Tahun Ajaran dan Semester):** Manajemen siklus tahun ajaran dan semester aktif dengan tanggal pelaksanaan terpadu serta aktivasi 1-klik.
- 📆 **Kalender Akademik Terpadu dan Agenda Multi-Day:** Tampilan ganda via `<x-tab-pills>` (Grid Bulanan Interaktif dan Tabel Agenda), rentang kegiatan multi-hari, filter kategori, dan feed JSON real-time.
- 📊 **Layanan Universal Spreadsheet & Impor Ekspor Data:** Mesin terpadu `SpreadsheetService` untuk unduh template (.xlsx) profesional bergaya Royal Blue, ekspor streaming data terfilter (.xlsx/.csv), proteksi tipe teks numerik panjang bebas scientific notation (NIK/NIP/NUPTK/No HP), dan modal dialog impor massal GTK berbasis `<x-crud-button action="create">`.
- 🧩 **25 Anonymous Blade Components dan Client Engines:** Katalog komponen UI universal (`<x-data-table>`, `<x-searchable-select>`, `<x-image-upload>`, `<x-file-upload>`, `<x-modal>`, `<x-tab-pills>`) berpadu dengan controller JavaScript terpadu (`InforaModals`, `SearchableSelect`, `ImageUpload`, `PermissionMatrix`).
- 📸 **Layanan Unggah Gambar Terpusat (`ImageUploader`):** Pipeline Base64 native PHP tanpa library pihak ketiga, penamaan semantik collision-proof `{modul}_u{user_id}_{timestamp}_{random8}.{ext}`, dan validasi ketat 1MB.
- 🏭 **Kemitraan Industri dan Magang PKL (Khusus SMK):** Database mitra DUDI, monitoring penempatan magang, plotting guru pembimbing, dan rekap Tracer Study alumni.
- 🏆 **Automasi Instrumen Akreditasi (BAN-SM / IASP):** Dashboard kesiapan 4 komponen mutu, penyimpanan terstruktur bank dokumen digital, serta portal audit khusus asesor.
- 🔌 **API-First Architecture dan System Bridging:** RESTful API terstandarisasi, jembatan integrasi (*bridging*) dengan ekosistem Dapodik/LMS/WhatsApp, otorisasi Sanctum, dan kontrak respons *Pure JSON*.
- 📱 **Mobile-First dan Dedicated Layout System:** Pemisahan struktur layout native antara Desktop Power-Dashboard dan Mobile App-Like (bukan sekadar responsive CSS hiding).
- 🗂️ **Navigasi Dinamis dan Tata Kelola Menu Akses:** Hierarki navigasi sistem (Modul ➔ Menu ➔ Sub-Menu) berbasis data, otorisasi granular *user-centric*, modul `PermissionMatrix`, template peran dinamis, dan fallback aman *Zero Dead Links*.
- 💾 **Cadangan dan Pemulihan Sistem Lengkap:** Snapshot portabel satu-klik (.zip), mesin backup mandiri native PHP/PDO tanpa dependensi luar, dan auto-repair symlink storage.
- 🗺️ **Master Wilayah Administratif Kemendagri:** Hierarki 4 tingkat (Provinsi, Kabupaten/Kota, Kecamatan, Kelurahan dan Desa) berbasis standar resmi Kemendagri RI numerik murni, cascading filter 3 tingkat dinamis, proteksi relasi sekolah, dan seeder resmi lengkap.
- 📐 **Tata Letak 3-Tier dan Paginasi Responsif:** Arsitektur antarmuka 3-tier (fixed topbar, docked footer, dan scroll internal independen) berpadu dengan komponen paginasi universal `<x-pagination>` dan toolbar tabel adaptif.
- ⚡ **Universal Smooth Progressive Loading Screen:** Mesin indikator progres global (`window.InforaProgress`) beranimasi gradien halus (*buttery smooth motion*) untuk seluruh operasi sistem, navigasi link, dan form submissions.

> 💡 **Dokumentasi Lengkap dan Spesifikasi Arsitektur:**  
> - Untuk penjelasan mendalam mengenai rasionalisasi desain sistem, standar upload Base64, reduksi batasan unik, dan rincian teknis fitur, silakan baca **[detail/README.md](detail/README.md)**.  
> - Untuk panduan lengkap katalog komponen Blade UI dan contoh implementasinya, silakan baca **[docs/COMPONENTS.md](docs/COMPONENTS.md)**.

---

## 💻 Tech Stack dan Arsitektur

- **Backend:** Laravel (Versi Terbaru) — *Modular Monolith, RESTful API dan Bridging Core*
- **Automated Testing:** Pest PHP — **371 Tests Passed dan 1.728 Assertions (100% Pass Rate)**
- **UI Components:** 25 Anonymous Blade Components ([`docs/COMPONENTS.md`](docs/COMPONENTS.md)) dan Reusable Global CSS Tokens (`resources/css/app.css`, Zero Inline Styles)
- **JavaScript Client Engines:** `InforaModals` (CRUD modals), `SearchableSelect` (cascading dynamic dropdowns), `ImageUpload` (Base64 file pipeline), `PermissionMatrix` (RBAC access matrix), `InforaProgress` (global smooth loading indicator)
- **Navigation dan Hierarchy:** System-Driven Dynamic Navigation (Modul ➔ Menu ➔ Sub-Menu), 3 Baseline Bootstrap Menus, User-Centric Menu Access Control, Configurable Role Templates, Dynamic Under-Development Fallback (Zero Dead Links), Non-Unique Entity Convention, Automatic Order dan Dual-Field Routing
- **School Master dan Academic:** Multi-Record School Management (SMA, SMK, dan MA, Public dan Private), Unit School Filter, Jurusan/Konsentrasi Keahlian/Peminatan Registry, Tingkatan / Grade Levels Registry, GTK / Educator Multi-Role Management, Tahun Ajaran dan Semester Management, Academic Calendar with Multi-Day Event Spanning
- **Regional Administrative Master:** Kemendagri 4-Tier Standard (Provinsi, Kabupaten/Kota, Kecamatan, Kelurahan/Desa), Pure Numeric Codes (Non-BPS), 3-Tier Cascading Filter, School Relation Integrity Guard
- **Layout dan Pagination Architecture:** 3-Tier Docked Layout (Fixed Topbar, Docked Bottom Footer, Independent Internal Scrolling), Universal Responsive Pagination Component (`<x-pagination>`, Registered Global via `Paginator::defaultView`)
- **Global Indicator Engine:** Universal Smooth Real-Time Progressive Indicator (`window.InforaProgress`, `<x-progress-modal />`, SSE Stream Reader, Zero Inline Styles)
- **Data dan Response Contract:** Pure JSON Responses (`application/json`), Eloquent API Resources, Zero HTML in Data Payloads
- **Media dan File Handling:** Unified `ImageUploader` Service, Base64 Uploads (Maks. 1MB/file, Preservasi Kualitas, Pola Nama: `{modul}_u{user_id}_{timestamp}_{random8}.{ext}`)
- **Styling dan UI Convention:** Reusable Global CSS Classes (Light, Smooth dan Clean Theme, Strict No Ad-Hoc/Specific Classes), Zero Inline Styles (`style=""`), Zero `<style>` Tags in Views
- **Database dan Timezone:** MySQL 8.0 dan Redis Cache (Server Timezone: `Asia/Makassar` / WITA, GMT+8)
- **Web Server:** Nginx (Alpine Linux, `client_max_body_size 250M`)
- **Frontend / Client:** Blade, Alpine.js, TailwindCSS — *Mobile-First Architecture dan Dedicated Layouts (Desktop dan Mobile)*
- **Containerization:** Docker dan Docker Compose (Native Multi-Platform)

---

## 🛠️ Panduan Memulai Cepat (Quick Start)

### Prasyarat
- [Docker](https://docs.docker.com/get-docker/) dan [Docker Compose](https://docs.docker.com/compose/) (atau Docker Desktop) terpasang di komputer Anda.
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

### 4. Setup Aplikasi dan Migrasi Database
```bash
# Linux / macOS / WSL
./dev artisan migrate

# Windows (PowerShell)
.\dev.ps1 artisan migrate
```
*(Catatan: Symbolic link storage publik relatif sudah dibuat otomatis saat `./dev start`. Anda juga dapat menjalankannya manual jika diperlukan via `./dev artisan storage:link --relative`)*

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
