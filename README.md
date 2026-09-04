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

## 📖 Tentang INFORA

**INFORA** adalah platform Sistem Informasi Manajemen (SIM) sekolah menengah generasi baru yang dirancang untuk menjawab kebutuhan operasional **SMA** dan **SMK**. 

Dibangun dengan arsitektur **API-First & System Bridging**, **Pengembangan Berbasis Modul (Modular Architecture)**, serta filosofi **Mobile-First Development dengan Pemisahan Layout Dedicated (Desktop & Mobile)**, INFORA mengintegrasikan seluruh lini tata kelola sekolah. Seluruh komunikasi data dirancang menggunakan format **JSON murni** (*zero HTML in response data*) untuk memastikan integrasi yang bersih, terstandarisasi, dan mudah dikonsumsi oleh aplikasi mobile, frontend modern, maupun sistem eksternal.

---

## 🚀 Fitur Unggulan

### 🏫 1. Manajemen Terpadu SMA & SMK
- **Struktur Akademik Fleksibel:** Mendukung Peminatan/Fase (SMA) dan Konsentrasi Keahlian/Jurusan (SMK).
- **Jurnal Digital KBM Guru:** Catatan materi harian, kendala siswa di kelas, dan dokumentasi KBM.
- **Modul Kesiswaan & Bimbingan Konseling (BK):** Rekap poin pelanggaran tata tertib dan portofolio prestasi siswa.

### 🏭 2. Modul Kemitraan Industri & PKL (Khusus SMK)
- **Database DUDI:** Manajemen data mitra Dunia Usaha dan Dunia Industri.
- **Monitoring Magang/PKL:** Penempatan siswa, plotting guru pembimbing, dan jurnal digital siswa di tempat magang.
- **Tracer Study & BKK:** Penelusuran keterserapan alumni di dunia kerja.

### 🏆 3. Automasi Instrumen Akreditasi (BAN-SM / IASP)
- **Dashboard Kesiapan Akreditasi:** Pemantauan progres pemenuhan 4 komponen mutu (Mutu Lulusan, Pembelajaran, Guru, dan Manajemen).
- **Bank Dokumen Digital:** Penyimpanan terstruktur dokumen SK, RPP/Modul Ajar, sertifikat guru, dan foto kegiatan.
- **Portal Asesor Audit:** Akses khusus bagi tim asesor akreditasi untuk memverifikasi bukti fisik secara terpusat.

### 🔌 4. API-First Architecture & Data Bridging
- **RESTful API Endpoints:** Seluruh modul dirancang terstandarisasi berbasis API untuk kemudahan konsumsi oleh aplikasi mobile, frontend modern, maupun portal pihak ketiga.
- **System Bridging & Interoperabilitas:** Mendukung jembatan integrasi (*bridging*) dengan ekosistem sistem lain (Dapodik, sistem akademik eksternal, LMS, hingga WhatsApp gateway).
- **Aman & Terproteksi:** Dilengkapi otorisasi berbasis token (Laravel Sanctum), rate limiting, dan enkripsi payload data.

### 📱 5. Mobile-First & Dedicated Layout System
- **Dedicated Layout Separation:** Memisahkan struktur layout antara tampilan Desktop dan Mobile secara khusus (bukan sekadar responsive CSS hiding biasa), menjaga ukuran DOM tetap ramping dan rendering super cepat.
- **Mobile Experience (App-Like):** Dioptimalkan untuk genggaman ponsel pintar dengan navigasi bawah (*bottom bar navigation*), gesture sentuhan yang intuitif, serta akses cepat untuk pengisian jurnal guru dan cek portofolio siswa.
- **Desktop Power-Dashboard:** Antarmuka layar lebar untuk produktivitas staf tata usaha dan pimpinan sekolah, dilengkapi navigasi sidebar fleksibel, tabel analitik bervolume besar, serta panel dokumen akreditasi.

### 📦 6. Modular Development & Pure JSON Data Response
- **Pengembangan Berbasis Modul (Modular Architecture):** Kode disusun secara modular per domain fitur (Akademik, Kesiswaan, Vokasi/PKL, Akreditasi, dan Integrasi Bridging) sehingga setiap modul mandiri, mudah dirawat, dan terisolasi (*high cohesion, low coupling*).
- **Pure JSON Response (Menghindari Response HTML):** Seluruh endpoint dan interaksi data menggunakan format **JSON murni** (`Content-Type: application/json`). Aplikasi secara ketat **menghindari pengembalian format HTML (HTML partials/fragments)** pada respons data, memastikan batas yang tegas antara data provider dan presentation layer.

### 🎨 7. Standar Styling: Reusable Global CSS Classes (Strict No Ad-Hoc Classes)
- **Reusable Global Classes (Dilarang Keras Class Khusus/Ad-hoc):** Dilarang keras membuat class CSS khusus sekali pakai (*one-off / page-specific classes*) yang hanya dipakai pada elemen atau halaman tertentu. Seluruh styling antarmuka wajib memanfaatkan sistem class global yang bersifat dapat digunakan kembali (*reusable design tokens & utilities*).
- **Tanpa Inline Style (`style="..."`):** Dilarang keras menggunakan *inline style* langsung pada tag HTML untuk menjamin kebersihan kode (*clean markup*), kemudahan *maintenance*, dan konsistensi visual lintas halaman.
- **Tanpa Tag `<style>` pada View/Komponen:** Jika memerlukan UI kustom, seluruh definisi CSS wajib ditempatkan sebagai class reusable pada berkas stylesheet global (`resources/css/`), tanpa pernah menyisipkan tag `<style>` di dalam template Blade.

### 📸 8. Standar Upload Dokumentasi: Base64 & Naming Convention (Maks. 1MB)
- **Format Upload Base64:** Seluruh pengunggahan foto dokumentasi (kegiatan KBM, berkas bukti akreditasi, sertifikat siswa, dan jurnal PKL) wajib menggunakan format string **Base64** di dalam payload request JSON, selaras dengan arsitektur *Pure JSON*.
- **Batasan Ukuran Maksimal 1MB:** Ukuran payload foto per berkas dibatasi maksimal **1MB** untuk menjaga efisiensi bandwidth dan responsivitas API.
- **Preservasi Kualitas Gambar:** Wajib menerapkan optimasi/kompresi cerdas sebelum konversi base64 sehingga ukuran berkas tetap di bawah 1MB tanpa mengorbankan ketajaman resolusi, keterbacaan teks dokumen, maupun fidelitas visual foto.
- **Pola Penamaan File Semantik (Naming Convention):** Berkas hasil upload wajib dinamai secara otomatis mengikuti pola terstandarisasi:
  ```text
  {modul}_u{user_id}_{YYYYMMDD_His}_{random_8char}.{ekstensi}
  ```
  *(Contoh: `kbm_u7_20260905_143022_a7b9c1d2.webp`, `pkl_u142_20260905_152011_3c4d5e6f.webp`, `akr_u3_20260905_110545_f83e2a1b.pdf`)*. Format ini menjamin berkas bebas tabrakan nama (*collision-proof*), aman, serta dapat diaudit dan ditelusuri kepemilikannya secara instan.

---

## 💻 Tech Stack & Arsitektur

- **Backend:** Laravel (Versi Terbaru) — *Modular Monolith, RESTful API & Bridging Core*
- **Data & Response Contract:** Pure JSON Responses (`application/json`), Eloquent API Resources, Zero HTML in Data Payloads
- **Media & File Handling:** Base64 Uploads (Maks. 1MB/file, Preservasi Kualitas, Pola Nama: `{modul}_u{user_id}_{timestamp}_{random8}.{ext}`)
- **Styling & UI Convention:** Reusable Global CSS Classes (Strict No Ad-Hoc/Specific Classes), Zero Inline Styles (`style=""`), Zero `<style>` Tags in Views
- **Database:** MySQL 8.0 & Redis Cache
- **Web Server:** Nginx (Alpine Linux)
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
*(Opsional: `./dev artisan storage:link` untuk menghubungkan storage publik)*

### 5. Akses Aplikasi
- **Aplikasi Web INFORA:** [http://localhost:8000](http://localhost:8000)
- **Vite Dev Server (HMR):** [http://localhost:5173](http://localhost:5173)

---

## 📂 Struktur Dokumentasi Proyek

Dokumentasi detail mengenai arsitektur, rencana kerja, riwayat rilis, dan identitas brand:
- 📋 **[TODO.md](TODO.md):** Roadmap pengembangan fase demi fase (Fase 1 sampai Fase 6).
- 📝 **[CHANGELOG.md](CHANGELOG.md):** Riwayat perubahan dan rilis versi platform.
- 🎨 **[DESIGN.md](detail/DESIGN.md):** Spesifikasi arsitektur sistem, identitas visual, palet warna, dan skema database.
- 🖼️ **[BRANDING.md](detail/docs/BRANDING.md):** Filosofi penamaan brand, aset logo lockup, dan app icon.

---

## 📄 Lisensi
Proyek ini dikembangkan di bawah lisensi open-source [MIT License](LICENSE).
