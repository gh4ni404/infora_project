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
- **Nuansa Terang, Halus & Ramah Sivitas (Bright, Smooth & Clean):** Antarmuka dirancang dengan latar kanvas sejuk (`#F8FAFC`), permukaan kartu putih bersih (`#FFFFFF`), teks berkontras tinggi (`#0F172A`) yang mudah dibaca guru & siswa, serta aksen biru inspiratif (`#0284C7` & `#2563EB`).
- **Reusable Global Classes (Dilarang Keras Class Khusus/Ad-hoc):** Dilarang keras membuat class CSS khusus sekali pakai (*one-off / page-specific classes*) yang hanya dipakai pada elemen atau halaman tertentu. Seluruh styling antarmuka wajib memanfaatkan sistem class global yang bersifat dapat digunakan kembali (*reusable design tokens & utilities*).
- **Sistem Komponen UI Universal Terpusat:** Menyediakan sistem komponen siap pakai seperti Dropdown universal (`.dropdown`, `.dropdown-menu`, `.dropdown-item`), Kartu Pengguna (`.user-card-button`, `.user-avatar-circle`), serta Modal Dialog (`.modal-backdrop`, `.modal-dialog`).
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

### 🗂️ 9. Sistem Navigasi Berbasis Data & Menu Dasar Bawaan Sistem (Bootstrap Baseline)
- **Hierarki Navigasi Dinamis (Modul ➔ Menu ➔ Sub-Menu):** Seluruh navigasi sidebar tidak lagi di-hardcode secara statis di template Blade, melainkan terstruktur secara dinamis di basis data.
- **Menu Dasar Bawaan Sistem (Bootstrap Baseline):** Sistem diawali dengan menu dasar bawaan/seeded di bawah modul "Pengaturan Sistem" (**Modul**, **Menu**, **Sub-Menu**, dan **Backup & Restore**). Menu-menu inilah yang menjadi pintu gerbang utama untuk mengelola navigasi serta memulihkan data sistem lama secara instan saat pertama kali instalasi baru dijalankan (`php artisan migrate:fresh --seed`).
- **Pemisah Modul Classic Minimalist:** Label modul berfungsi sebagai pemisah kategori yang rapi dan elegan (`.menu-category-label` dengan garis pembatas tipis atas dan tipografi uppercase) tanpa memerlukan ikon modul yang berlebihan.
- **Seksi Pengguna & Dropup Popover di Sidebar Footer:** Profil akun pengguna dan aksi logout ditempatkan di bagian bawah sidebar (`.sidebar-footer`) menggunakan kartu interaktif (`.user-card-button`) yang memunculkan popover menu melayang ke atas (*dropup*) berisi Pengaturan Profile, Ubah Password, Bantuan, dan Keluar.
- **Interaktivitas Sidebar Modern & Anchored Transition:**
  - View Composer `SidebarComposer` dengan *eager loading* dan *safeguard* tabel.
  - Dropdown accordion sub-menu dengan rotasi ikon panah 180° dan penutupan mulus saat ciut.
  - Penataan penjangkaran simetris (*Anchored Symmetrical Transition*): Posisi avatar (17px margin) dan seluruh ikon menu (26px margin) terkunci presisi tanpa pergeseran horizontal (0px horizontal jump) baik saat terbuka (260px) maupun ciut (72px), menghilangkan efek *auto-centering* atau lompatan visual saat animasi berjalan.
  - Tombol Pencarian pada Mode Ciut: Kotak pencarian otomatis bertransformasi menjadi tombol ikon 40px yang elegan di atas Dashboard saat sidebar diciutkan; mengkliknya akan langsung membuka sidebar dan memfokuskan input pencarian.
  - Pencarian menu real-time multi-level (modul, menu, sub-menu) dengan auto-expand parent group dan shortcut global `Ctrl+K`.
- **Otomatisasi Kapitalisasi Teks Dua Lapis (Dual-Layer Text Transformation):**
  - **Nama Modul:** Selalu diformat **HURUF KAPITAL SEMUA (UPPERCASE)** secara otomatis saat diketik (`data-transform="uppercase"`) dan dijamin oleh Eloquent Attribute Mutator saat disimpan ke basis data (contoh: `NAVIGASI UTAMA`, `PENGATURAN SISTEM`).
  - **Nama Menu & Sub-Menu:** Selalu diformat **Capitalize Each Word (Title Case)** dengan pemeliharaan cerdas akronim standar pendidikan & teknologi (`SMK`, `SMA`, `SIM`, `PKL`, `KBM`, `GTK`, `BAN-SM`, `RPP`, `IT`, `TU`, `ID`, dll.) baik di sisi frontend maupun Model backend (contoh: `Dashboard`, `Sistem`, `Jurnal KBM`, `Rekam Jejak PKL SMK`, `Sub-Menu`). Pengguna cukup mengetik huruf kecil biasa, sistem otomatis memformatnya dengan rapi dan konsisten.

### 🛡️ 10. Prinsip Reduksi Batasan Unik (Zero 'unique' Constraints on Entity Text Attributes)
- **Rasionalisasi:** Penggunaan constraint `unique` pada kolom teks (seperti nama modul, nama menu, slug, atau route) dihindari dalam skema database maupun layer validasi Form Request. Pendekatan ini dipilih untuk mencegah kerumitan implementasi dan berbagai *edge cases* validasi CRUD (misalnya: konflik saat pembaruan data tanpa mengubah nama, isu duplikasi nama pada modul yang berbeda, atau rute bersyarat).
- **Integritas Berbasis Primary Key ID:** Seluruh identitas entitas dan relasi hierarki murni mengandalkan **Primary Key ID (Auto-Increment)** dan **Foreign Key dengan Cascade Delete**. Validasi berfokus pada tipe data, kelengkapan (*presence*), dan keberadaan relasi (*exists*), sehingga pengalaman pengelolaan data menjadi jauh lebih sederhana, fleksibel, dan minim galat operasional.

### 🚧 11. Halaman Placeholder "Fitur Dalam Pengembangan" & Dynamic Navigation Fallback (Zero Dead Links)
- **Eliminasi Tautan Mati (`href="#"`):** Seluruh item menu dan sub-menu yang dibuat oleh Super Admin namun belum memiliki rute implementasi aktif di `routes/web.php` tidak lagi menjadi link mati yang tidak responsif.
- **Dynamic Fallback Otomatis:** Model `Menu` dan `SubMenu` secara otomatis mengarahkan klik navigasi ke rute terpusat `/system/under-development?type={menu|submenu}&id={id}`.
- **Halaman Antarmuka Profesional & Informatif:** Menyajikan halaman bertema Infora yang elegan dengan breadcrumb hierarki navigasi (`MODUL ➔ MENU ➔ SUB-MENU`), status visibilitas, dan kartu detail entitas.
- **Blueprint & Scaffolding Developer:** Menampilkan panduan instan bagi pengembang untuk mengaktifkan fitur tersebut, mencakup contoh baris kode registrasi di `routes/web.php` dan perintah CLI `php artisan make:controller ...`.
- **Preservasi Status Aktif Sidebar:** Saat berada di halaman placeholder, accordion menu induk tetap terbuka dan item sub-menu bersangkutan disorot aktif secara visual.

### 🔐 12. Sistem Tata Kelola Menu Akses & Dynamic Role Templates (User-Centric Granular Permissions)
- **Otorisasi Berbasis Pengguna (User-Centric):** Setiap akun pengguna (Guru, Staf TU, Siswa) dapat dikonfigurasi hak akses navigasinya secara independen tanpa dibatasi satu role kaku.
- **Wewenang Aksi Granular (Lihat, Tambah, Ubah, Hapus):** Hak akses diatur mendalam per-menu dan sub-menu dengan 4 pilar izin: **Lihat** (tampil di sidebar & buka halaman), **Tambah** (buat data), **Ubah** (edit data), dan **Hapus** (delete data).
- **Template Peran Dinamis (Dikelola Super Admin):** Menyediakan sistem template peran bawaan (Guru Pengajar, Wali Kelas, Wakasek Kurikulum, Wakasek Kesiswaan, Staf TU, Siswa Reguler, Siswa PKL) yang menu standarnya dapat diubah dan disesuaikan oleh Super Admin kapan saja di tab *Template Peran Sistem*.
- **Penerapan Template Cepat (1-Click Preset):** Saat mengatur akun guru atau siswa, Super Admin cukup memilih template peran untuk mengisi seluruh centang izin secara instan dalam 1 detik, lalu dapat menambah/mengurangi izin secara personal.
- **Filtering Dinamis pada Sidebar (`SidebarComposer`):** Sidebar hanya menampilkan modul, menu, dan sub-menu yang diizinkan (`can_view = true`) untuk pengguna yang sedang login. Akun `super_admin` secara permanen mempertahankan akses penuh (*root bypass*).
- **Akses Rute Resmi:** Tersedia di URL `/sistem/menu-akses` dengan nama rute `sistem.menu-akses`.

### 💾 13. Sistem Cadangan & Pemulihan Sistem Lengkap (Full System Snapshot & Server Migration Ready)
- **Snapshot Portabel Satu-Klik (.zip):** Mengekspor seluruh struktur skema basis data (`database.sql`) dan seluruh berkas/gambar unggahan pengguna (`storage/app/public/`) ke dalam satu arsip ZIP mandiri berformat standar yang dilengkapi `manifest.json`.
- **Mesin Mandiri Pure Native PHP/PDO & ZipArchive:** Dirancang tanpa ketergantungan paket berat pihak ketiga, menjamin kompatibilitas tinggi lintas platform (Docker, server staging, dan hosting produksi) dengan proteksi penanganan foreign keys otomatis.
- **Pengecekan & Reparasi Cerdas Symlink (*Zero Broken Images / Anti-404*):** Saat proses restore di server baru, sistem memeriksa validitas symlink `public/storage`; jika sudah terhubung sehat ke path target lokal maka dilanjutkan langsung, dan jika belum ada atau rusak/putus (*broken link* dari server lama), sistem otomatis membuat ulang symlink (`Artisan::call('storage:link')`).
- **Tata Kelola Arsip & Unduhan Lokal:** Memantau daftar berkas cadangan tersimpan, kapasitas penyimpanan yang terpakai, serta menyediakan tombol unduh aman ke komputer lokal maupun penghapusan berkas dengan sanitasi *anti-directory traversal*.
- **Pemulihan Fleksibel (*Full ZIP & SQL Dump*):** Mendukung pemulihan dari berkas cadangan di server maupun dari unggahan berkas eksternal hingga 250 MB via formulir unggah.
- **Protokol Keamanan Ketat (*Danger Confirmation Modal*):** Mengingat proses pemulihan bersifat destruktif terhadap data aktif, sistem memproteksi aksi restore dengan dialog modal interaktif yang mewajibkan pengetikan kata kunci verifikasi `"PULIHKAN"` sebelum tombol eksekusi terbuka.
- **Akses Rute Mandiri:** Berada pada URL `/backup-restore` dengan nama rute `backup-restore` (sesuai nama rute pada menu mandiri sistem).

### ⚡ 14. Universal Smooth Real-Time Progressive Loading Screen (`window.InforaProgress`)
- **Indikator Global Lintas Modul:** Mesin indikator loading universal yang dapat dipanggil oleh seluruh modul dan fitur aplikasi (impor siswa, ekspor laporan, kalkulasi akreditasi, backup & restore, dll.) via API JavaScript global `window.InforaProgress`.
- **Desain Minimalis & Tanpa Elemen Kaku:** Menggantikan tampilan kaku dan teknis dengan kartu melayang modern berbalut *backdrop blur*, *ambient status orb* berdenyut lembut, judul aktivitas dinamis, serta bar progres ramping (*sleek pill bar*) 9px.
- **Animasi Sangat Halus (*Buttery Smooth Motion & Gradient Shimmer*):** Bar progres dilengkapi sapuan gradien bercahaya Royal Blue (`#2563EB`) ke Cyan (`#06B6D4`) yang mengalir kontinu (`@keyframes progressGradientShimmer`), transisi pergerakan kurva `cubic-bezier(0.25, 1, 0.5, 1)`, serta interpolasi persentase mulus dengan `requestAnimationFrame`.
- **Dukungan Streaming SSE Terintegrasi:** Dilengkapi pembaca stream *Server-Sent Events* (`InforaProgress.stream(url, formData)`) untuk menyajikan progres riil langsung dari backend secara otomatis.
- **100% Bebas Inline Styles & Bebas Tag `<style>`:** Seluruh styling visual terpusat di `resources/css/app.css` tanpa satupun atribut `style="..."`.

### 🏫 15. Master Data Sekolah (Multi-Record Registry & Kesiapan Bridging)
- **Registri Sekolah Fleksibel (SMA & SMK):** Mendukung pengelolaan daftar banyak sekolah (multi-unit yayasan) dengan atribut lengkap: identitas resmi (Nama Sekolah, NPSN, NSS, Jenis Sekolah SMA/SMK, Status Negeri/Swasta, Akreditasi A/B/C/Belum), alamat komprehensif, kontak, pimpinan sekolah & NIP, serta yayasan naungan.
- **Kesiapan Bridging Ekosistem Dapodik:** Kolom `npsn` di-index secara optimal sebagai kunci unik alternatif (*secondary natural key*) untuk integrasi API bridging ekosistem nasional pada fase lanjutan, tanpa mengunci constraint unik kaku pada level DBMS sesuai prinsip reduksi batasan teks unik.
- **Manajemen Visual & Upload Logo Base64 (Maks. 1MB):** Dilengkapi sistem unggah logo format Base64 terintegrasi langsung di form modal create dan edit terpisah, live preview instan, validasi ukuran 1MB, dan penyimpanan otomatis ke disk publik dengan tata nama collision-proof: `sekolah_u{user_id}_{timestamp}_{random8}.{ext}`.
- **Pencarian Dinamis & Filter Jenis:** Pencarian instan berdasarkan nama sekolah, NPSN, maupun kota/kabupaten dengan filter dropdown jenis sekolah (Semua, SMA, SMK) dan paginasi 15 data per halaman.
- **Akses Rute Resmi:** Dikelola pada rute `/master/data-sekolah` (`master.data-sekolah.*`) di bawah modul Administrasi ➔ Master ➔ Data Sekolah.

---

## 💻 Tech Stack & Arsitektur

- **Backend:** Laravel (Versi Terbaru) — *Modular Monolith, RESTful API & Bridging Core*
- **Navigation & Hierarchy:** System-Driven Dynamic Navigation (Modul ➔ Menu ➔ Sub-Menu), 3 Baseline Bootstrap Menus, User-Centric Menu Access Control, Configurable Role Templates, Dynamic Under-Development Fallback (Zero Dead Links), Non-Unique Entity Convention
- **School Master Registry:** Multi-Record School Management (SMA & SMK, Public & Private), Dynamic Filtering, Base64 Logo Pipeline, Title-Case Auto Formatting, Bridging-Ready Architecture
- **Global Indicator Engine:** Universal Smooth Real-Time Progressive Indicator (`window.InforaProgress`, `<x-progress-modal />`, SSE Stream Reader, Zero Inline Styles)
- **Universal Modal & Form UI:** Flexbox-Driven Modal Architecture (Zero-Clipping Sticky Footers, Dynamic Scrolling Body), Margin Normalization, Standard Design Border Dividers, Accessible Slim Scrollbar
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
- 📋 **[TODO.md](TODO.md):** Roadmap pengembangan fase demi fase (Fase 1 sampai Fase 6).
- 📝 **[CHANGELOG.md](CHANGELOG.md):** Riwayat perubahan dan rilis versi platform.
- 🎨 **[DESIGN.md](detail/DESIGN.md):** Spesifikasi arsitektur sistem, identitas visual, palet warna, dan skema database.
- 🖼️ **[BRANDING.md](detail/docs/BRANDING.md):** Filosofi penamaan brand, aset logo lockup, dan app icon.
- 🛠️ **[TROUBLESHOOTING.md](detail/docs/TROUBLESHOOTING.md):** Panduan mengatasi masalah umum saat setup Docker (halaman tanpa styling, file `hot`, dan lainnya).

---

## 📄 Lisensi
Proyek ini dikembangkan di bawah lisensi open-source [MIT License](LICENSE).
