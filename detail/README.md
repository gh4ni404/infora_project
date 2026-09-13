# 🚀 Rincian Lengkap Fitur Unggulan INFORA
> **Information Network for Organization, Records, & Accreditation (INFORA)**  
> *"Era Baru Sistem Informasi Sekolah Menengah (SMA & SMK) Terpadu & Siap Akreditasi"*

Dokumen ini memuat spesifikasi teknis mendalam, arsitektur sistem, dan rincian lengkap dari seluruh fitur unggulan platform **INFORA**. Untuk ringkasan umum dan panduan instalasi, silakan kembali ke **[README Utama](../README.md)**.

---

## 📚 Daftar Isi Fitur
1. [Manajemen Terpadu SMA & SMK](#-1-manajemen-terpadu-sma--smk)
2. [Modul Kemitraan Industri & PKL (Khusus SMK)](#-2-modul-kemitraan-industri--pkl-khusus-smk)
3. [Automasi Instrumen Akreditasi (BAN-SM / IASP)](#-3-automasi-instrumen-akreditasi-ban-sm--iasp)
4. [API-First Architecture & Data Bridging](#-4-api-first-architecture--data-bridging)
5. [Mobile-First & Dedicated Layout System](#-5-mobile-first--dedicated-layout-system)
6. [Modular Development & Pure JSON Data Response](#-6-modular-development--pure-json-data-response)
7. [Standar Styling: Reusable Global CSS Classes](#-7-standar-styling-reusable-global-css-classes-strict-no-ad-hoc-classes)
8. [Standar Upload Dokumentasi: Base64 & Naming Convention](#-8-standar-upload-dokumentasi-base64--naming-convention-maks-1mb)
9. [Sistem Navigasi Berbasis Data & Menu Dasar Bawaan Sistem](#-9-sistem-navigasi-berbasis-data--menu-dasar-bawaan-sistem-bootstrap-baseline)
10. [Prinsip Reduksi Batasan Unik](#-10-prinsip-reduksi-batasan-unik-zero-unique-constraints-on-entity-text-attributes)
11. [Halaman Placeholder "Fitur Dalam Pengembangan" & Dynamic Navigation Fallback](#-11-halaman-placeholder-fitur-dalam-pengembangan--dynamic-navigation-fallback-zero-dead-links)
12. [Sistem Tata Kelola Menu Akses & Dynamic Role Templates](#-12-sistem-tata-kelola-menu-akses--dynamic-role-templates-user-centric-granular-permissions)
13. [Sistem Cadangan & Pemulihan Sistem Lengkap](#-13-sistem-cadangan--pemulihan-sistem-lengkap-full-system-snapshot--server-migration-ready)
14. [Universal Smooth Real-Time Progressive Loading Screen](#-14-universal-smooth-real-time-progressive-loading-screen-windowinforaprogress)
15. [Master Data Sekolah](#-15-master-data-sekolah-multi-record-registry--kesiapan-bridging)
16. [Master Wilayah Administratif Kemendagri](#-16-master-wilayah-administratif-kemendagri-hierarki-4-tingkat)
17. [Tata Letak 3-Tier Docked Footer & Komponen Paginasi Responsif](#-17-tata-letak-3-tier-docked-footer--komponen-paginasi-responsif-global)

---

### 🏫 1. Manajemen Terpadu SMA & SMK
- **Struktur Akademik Fleksibel:** Mendukung Peminatan/Fase (SMA) dan Konsentrasi Keahlian/Jurusan (SMK).
- **Jurnal Digital KBM Guru:** Catatan materi harian, kendala siswa di kelas, dan dokumentasi KBM.
- **Modul Kesiswaan & Bimbingan Konseling (BK):** Rekap poin pelanggaran tata tertib dan portofolio prestasi siswa.

---

### 🏭 2. Modul Kemitraan Industri & PKL (Khusus SMK)
- **Database DUDI:** Manajemen data mitra Dunia Usaha dan Dunia Industri.
- **Monitoring Magang/PKL:** Penempatan siswa, plotting guru pembimbing, dan jurnal digital siswa di tempat magang.
- **Tracer Study & BKK:** Penelusuran keterserapan alumni di dunia kerja.

---

### 🏆 3. Automasi Instrumen Akreditasi (BAN-SM / IASP)
- **Dashboard Kesiapan Akreditasi:** Pemantauan progres pemenuhan 4 komponen mutu (Mutu Lulusan, Pembelajaran, Guru, dan Manajemen).
- **Bank Dokumen Digital:** Penyimpanan terstruktur dokumen SK, RPP/Modul Ajar, sertifikat guru, dan foto kegiatan.
- **Portal Asesor Audit:** Akses khusus bagi tim asesor akreditasi untuk memverifikasi bukti fisik secara terpusat.

---

### 🔌 4. API-First Architecture & Data Bridging
- **RESTful API Endpoints:** Seluruh modul dirancang terstandarisasi berbasis API untuk kemudahan konsumsi oleh aplikasi mobile, frontend modern, maupun portal pihak ketiga.
- **System Bridging & Interoperabilitas:** Mendukung jembatan integrasi (*bridging*) dengan ekosistem sistem lain (Dapodik, sistem akademik eksternal, LMS, hingga WhatsApp gateway).
- **Aman & Terproteksi:** Dilengkapi otorisasi berbasis token (Laravel Sanctum), rate limiting, dan enkripsi payload data.

---

### 📱 5. Mobile-First & Dedicated Layout System
- **Dedicated Layout Separation:** Memisahkan struktur layout antara tampilan Desktop dan Mobile secara khusus (bukan sekadar responsive CSS hiding biasa), menjaga ukuran DOM tetap ramping dan rendering super cepat.
- **Mobile Experience (App-Like):** Dioptimalkan untuk genggaman ponsel pintar dengan navigasi bawah (*bottom bar navigation*), gesture sentuhan yang intuitif, serta akses cepat untuk pengisian jurnal guru dan cek portofolio siswa.
- **Desktop Power-Dashboard:** Antarmuka layar lebar untuk produktivitas staf tata usaha dan pimpinan sekolah, dilengkapi navigasi sidebar fleksibel, tabel analitik bervolume besar, serta panel dokumen akreditasi.

---

### 📦 6. Modular Development & Pure JSON Data Response
- **Pengembangan Berbasis Modul (Modular Architecture):** Kode disusun secara modular per domain fitur (Akademik, Kesiswaan, Vokasi/PKL, Akreditasi, dan Integrasi Bridging) sehingga setiap modul mandiri, mudah dirawat, dan terisolasi (*high cohesion, low coupling*).
- **Pure JSON Response (Menghindari Response HTML):** Seluruh endpoint dan interaksi data menggunakan format **JSON murni** (`Content-Type: application/json`). Aplikasi secara ketat **menghindari pengembalian format HTML (HTML partials/fragments)** pada respons data, memastikan batas yang tegas antara data provider dan presentation layer.

---

### 🎨 7. Standar Styling: Reusable Global CSS Classes (Strict No Ad-Hoc Classes)
- **Nuansa Terang, Halus & Ramah Sivitas (Bright, Smooth & Clean):** Antarmuka dirancang dengan latar kanvas sejuk (`#F8FAFC`), permukaan kartu putih bersih (`#FFFFFF`), teks berkontras tinggi (`#0F172A`) yang mudah dibaca guru & siswa, serta aksen biru inspiratif (`#0284C7` & `#2563EB`).
- **Reusable Global Classes (Dilarang Keras Class Khusus/Ad-hoc):** Dilarang keras membuat class CSS khusus sekali pakai (*one-off / page-specific classes*) yang hanya dipakai pada elemen atau halaman tertentu. Seluruh styling antarmuka wajib memanfaatkan sistem class global yang bersifat dapat digunakan kembali (*reusable design tokens & utilities*).
- **Sistem Komponen UI Universal Terpusat:** Menyediakan sistem komponen siap pakai seperti Dropdown universal (`.dropdown`, `.dropdown-menu`, `.dropdown-item`), Kartu Pengguna (`.user-card-button`, `.user-avatar-circle`), serta Modal Dialog (`.modal-backdrop`, `.modal-dialog`).
- **Tanpa Inline Style (`style="..."`):** Dilarang keras menggunakan *inline style* langsung pada tag HTML untuk menjamin kebersihan kode (*clean markup*), kemudahan *maintenance*, dan konsistensi visual lintas halaman.
- **Tanpa Tag `<style>` pada View/Komponen:** Jika memerlukan UI kustom, seluruh definisi CSS wajib ditempatkan sebagai class reusable pada berkas stylesheet global (`resources/css/`), tanpa pernah menyisipkan tag `<style>` di dalam template Blade.

---

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

### 🗂️ 9. Sistem Navigasi Berbasis Data & Menu Dasar Bawaan Sistem (Bootstrap Baseline)
- **Hierarki Navigasi Dinamis (Modul ➔ Menu ➔ Sub-Menu):** Seluruh navigasi sidebar tidak lagi di-hardcode secara statis di template Blade, melainkan terstruktur secara dinamis di basis data.
- **Menu Dasar Bawaan Sistem (Bootstrap Baseline):** Sistem diawali dengan menu dasar bawaan/seeded di bawah modul "Pengaturan Sistem" (**Modul**, **Menu**, **Sub-Menu**, dan **Backup & Restore**). Menu-menu inilah yang menjadi pintu gerbang utama untuk mengelola navigasi serta memulihkan data sistem lama secara instan saat pertama kali instalasi baru dijalankan (`php artisan migrate:fresh --seed`).
- **Pemisah Modul Classic Minimalist:** Label modul berfungsi sebagai pemisah kategori yang rapi dan elegan (`.menu-category-label` dengan garis pembatas tipis atas dan tipografi uppercase) tanpa memerlukan ikon modul yang berlebihan.
- **Seksi Pengguna & Dropup Popover di Sidebar Footer:** Profil akun pengguna dan aksi logout ditempatkan di bagian bawah sidebar (`.sidebar-footer`) menggunakan kartu interaktif (`.user-card-button`) yang memunculkan popover menu melayang ke atas (*dropup*) berisi Pengaturan Profile, Ubah Password, Bantuan, dan Keluar.
- **Interaktivitas Sidebar Modern & Anchored Transition:**
  - View Composer `SidebarComposer` dengan *eager loading*, *caching versioning*, dan *safeguard* tabel.
  - Dropdown accordion sub-menu dengan rotasi ikon panah 180°, penutupan mulus saat ciut, dan `flex-shrink: 0` pada nav items untuk mencegah pemerasan teks.
  - Penataan penjangkaran simetris (*Anchored Symmetrical Transition*): Posisi avatar (17px margin) dan seluruh ikon menu (26px margin) terkunci presisi tanpa pergeseran horizontal (0px horizontal jump) baik saat terbuka (260px) maupun ciut (72px), menghilangkan efek *auto-centering* atau lompatan visual saat animasi berjalan.
  - Tombol Pencarian pada Mode Ciut: Kotak pencarian otomatis bertransformasi menjadi tombol ikon 40px yang elegan di atas Dashboard saat sidebar diciutkan; mengkliknya akan langsung membuka sidebar dan memfokuskan input pencarian.
  - Pencarian menu real-time multi-level (modul, menu, sub-menu) dengan auto-expand parent group dan shortcut global `Ctrl+K`.
- **Otomatisasi Urutan Tampil Mulai 1 & Dual Field Nama Rute Sub-Menu:**
  - Nilai default urutan (`order`) pada migrasi modul, menu, dan sub-menu dimulai dari 1 (menggantikan default 0).
  - Helper method `nextOrder()` pada model `Module`, `Menu`, dan `SubMenu` menghitung nomor urut selanjutnya secara otomatis untuk formulir tambah data.
  - Input dual-field nama rute pada formulir sub-menu (tampilan visual prefix rute menu induk terkunci + input sub-rute mandiri) guna menjamin konsistensi penamaan rute hierarkis.
- **Katalog Ikon Terkurasi & Penambahan Kategori Wilayah & Data:**
  - Visual Icon Picker (`<x-icon-picker>`) dan komponen SVG `<x-icon>` diperluas dengan ikon kategori Wilayah (`map`, `map-pin`, `landmark`, `building`, `building-2`, `globe`) dan kategori Data (`table`, dll.) berbasis Lucide Icons 100% free open-source.
- **Otomatisasi Kapitalisasi Teks Dua Lapis (Dual-Layer Text Transformation):**
  - **Nama Modul:** Selalu diformat **HURUF KAPITAL SEMUA (UPPERCASE)** secara otomatis saat diketik (`data-transform="uppercase"`) dan dijamin oleh Eloquent Attribute Mutator saat disimpan ke basis data (contoh: `NAVIGASI UTAMA`, `PENGATURAN SISTEM`).
  - **Nama Menu & Sub-Menu:** Selalu diformat **Capitalize Each Word (Title Case)** dengan pemeliharaan cerdas akronim standar pendidikan & teknologi (`SMK`, `SMA`, `SIM`, `PKL`, `KBM`, `GTK`, `BAN-SM`, `RPP`, `IT`, `TU`, `ID`, dll.) baik di sisi frontend maupun Model backend (contoh: `Dashboard`, `Sistem`, `Jurnal KBM`, `Rekam Jejak PKL SMK`, `Sub-Menu`). Pengguna cukup mengetik huruf kecil biasa, sistem otomatis memformatnya dengan rapi dan konsisten.

---

### 🛡️ 10. Prinsip Reduksi Batasan Unik (Zero 'unique' Constraints on Entity Text Attributes)
- **Rasionalisasi:** Penggunaan constraint `unique` pada kolom teks (seperti nama modul, nama menu, slug, atau route) dihindari dalam skema database maupun layer validasi Form Request. Pendekatan ini dipilih untuk mencegah kerumitan implementasi dan berbagai *edge cases* validasi CRUD (misalnya: konflik saat pembaruan data tanpa mengubah nama, isu duplikasi nama pada modul yang berbeda, atau rute bersyarat).
- **Integritas Berbasis Primary Key ID:** Seluruh identitas entitas dan relasi hierarki murni mengandalkan **Primary Key ID (Auto-Increment)** dan **Foreign Key dengan Cascade Delete**. Validasi berfokus pada tipe data, kelengkapan (*presence*), dan keberadaan relasi (*exists*), sehingga pengalaman pengelolaan data menjadi jauh lebih sederhana, fleksibel, dan minim galat operasional.

---

### 🚧 11. Halaman Placeholder "Fitur Dalam Pengembangan" & Dynamic Navigation Fallback (Zero Dead Links)
- **Eliminasi Tautan Mati (`href="#"`):** Seluruh item menu dan sub-menu yang dibuat oleh Super Admin namun belum memiliki rute implementasi aktif di `routes/web.php` tidak lagi menjadi link mati yang tidak responsif.
- **Dynamic Fallback Otomatis:** Model `Menu` dan `SubMenu` secara otomatis mengarahkan klik navigasi ke rute terpusat `/system/under-development?type={menu|submenu}&id={id}`.
- **Halaman Antarmuka Profesional & Informatif:** Menyajikan halaman bertema Infora yang elegan dengan breadcrumb hierarki navigasi (`MODUL ➔ MENU ➔ SUB-MENU`), status visibilitas, dan kartu detail entitas.
- **Blueprint & Scaffolding Developer:** Menampilkan panduan instan bagi pengembang untuk mengaktifkan fitur tersebut, mencakup contoh baris kode registrasi di `routes/web.php` dan perintah CLI `php artisan make:controller ...`.
- **Preservasi Status Aktif Sidebar:** Saat berada di halaman placeholder, accordion menu induk tetap terbuka dan item sub-menu bersangkutan disorot aktif secara visual.

---

### 🔐 12. Sistem Tata Kelola Menu Akses & Dynamic Role Templates (User-Centric Granular Permissions)
- **Otorisasi Berbasis Pengguna (User-Centric):** Setiap akun pengguna (Guru, Staf TU, Siswa) dapat dikonfigurasi hak akses navigasinya secara independen tanpa dibatasi satu role kaku.
- **Wewenang Aksi Granular (Lihat, Tambah, Ubah, Hapus):** Hak akses diatur mendalam per-menu dan sub-menu dengan 4 pilar izin: **Lihat** (tampil di sidebar & buka halaman), **Tambah** (buat data), **Ubah** (edit data), dan **Hapus** (delete data).
- **Template Peran Dinamis (Dikelola Super Admin):** Menyediakan sistem template peran bawaan (Guru Pengajar, Wali Kelas, Wakasek Kurikulum, Wakasek Kesiswaan, Staf TU, Siswa Reguler, Siswa PKL) yang menu standarnya dapat diubah dan disesuaikan oleh Super Admin kapan saja di tab *Template Peran Sistem*.
- **Penerapan Template Cepat (1-Click Preset):** Saat mengatur akun guru atau siswa, Super Admin cukup memilih template peran untuk mengisi seluruh centang izin secara instan dalam 1 detik, lalu dapat menambah/mengurangi izin secara personal.
- **Filtering Dinamis pada Sidebar (`SidebarComposer`):** Sidebar hanya menampilkan modul, menu, dan sub-menu yang diizinkan (`can_view = true`) untuk pengguna yang sedang login. Akun `super_admin` secara permanen mempertahankan akses penuh (*root bypass*).
- **Akses Rute Resmi:** Tersedia di URL `/sistem/user` dengan nama rute `sistem.user`.

---

### 💾 13. Sistem Cadangan & Pemulihan Sistem Lengkap (Full System Snapshot & Server Migration Ready)
- **Snapshot Portabel Satu-Klik (.zip):** Mengekspor seluruh struktur skema basis data (`database.sql`) dan seluruh berkas/gambar unggahan pengguna (`storage/app/public/`) ke dalam satu arsip ZIP mandiri berformat standar yang dilengkapi `manifest.json`.
- **Mesin Mandiri Pure Native PHP/PDO & ZipArchive:** Dirancang tanpa ketergantungan paket berat pihak ketiga, menjamin kompatibilitas tinggi lintas platform (Docker, server staging, dan hosting produksi) dengan proteksi penanganan foreign keys otomatis.
- **Pengecekan & Reparasi Cerdas Symlink (*Zero Broken Images / Anti-404*):** Saat proses restore di server baru, sistem memeriksa validitas symlink `public/storage`; jika sudah terhubung sehat ke path target lokal maka dilanjutkan langsung, dan jika belum ada atau rusak/putus (*broken link* dari server lama), sistem otomatis membuat ulang symlink (`Artisan::call('storage:link')`).
- **Tata Kelola Arsip & Unduhan Lokal:** Memantau daftar berkas cadangan tersimpan, kapasitas penyimpanan yang terpakai, serta menyediakan tombol unduh aman ke komputer lokal maupun penghapusan berkas dengan sanitasi *anti-directory traversal*.
- **Pemulihan Fleksibel (*Full ZIP & SQL Dump*):** Mendukung pemulihan dari berkas cadangan di server maupun dari unggahan berkas eksternal hingga 250 MB via formulir unggah.
- **Protokol Keamanan Ketat (*Danger Confirmation Modal*):** Mengingat proses pemulihan bersifat destruktif terhadap data aktif, sistem memproteksi aksi restore dengan dialog modal interaktif yang mewajibkan pengetikan kata kunci verifikasi `"PULIHKAN"` sebelum tombol eksekusi terbuka.
- **Akses Rute Mandiri:** Berada pada URL `/backup-restore` dengan nama rute `backup-restore` (sesuai nama rute pada menu mandiri sistem).

---

### ⚡ 14. Universal Smooth Real-Time Progressive Loading Screen (`window.InforaProgress`)
- **Indikator Global Lintas Modul:** Mesin indikator loading universal yang dapat dipanggil oleh seluruh modul dan fitur aplikasi (impor siswa, ekspor laporan, kalkulasi akreditasi, backup & restore, dll.) via API JavaScript global `window.InforaProgress`.
- **Desain Minimalis & Tanpa Elemen Kaku:** Menggantikan tampilan kaku dan teknis dengan kartu melayang modern berbalut *backdrop blur*, *ambient status orb* berdenyut lembut, judul aktivitas dinamis, serta bar progres ramping (*sleek pill bar*) 9px.
- **Animasi Sangat Halus (*Buttery Smooth Motion & Gradient Shimmer*):** Bar progres dilengkapi sapuan gradien bercahaya Royal Blue (`#2563EB`) ke Cyan (`#06B6D4`) yang mengalir kontinu (`@keyframes progressGradientShimmer`), transisi pergerakan kurva `cubic-bezier(0.25, 1, 0.5, 1)`, serta interpolasi persentase mulus dengan `requestAnimationFrame`.
- **Dukungan Streaming SSE Terintegrasi:** Dilengkapi pembaca stream *Server-Sent Events* (`InforaProgress.stream(url, formData)`) untuk menyajikan progres riil langsung dari backend secara otomatis.
- **100% Bebas Inline Styles & Bebas Tag `<style>`:** Seluruh styling visual terpusat di `resources/css/app.css` tanpa satupun atribut `style="..."`.

---

### 🏫 15. Master Data Sekolah (Multi-Record Registry & Kesiapan Bridging)
- **Registri Sekolah Fleksibel (SMA & SMK):** Mendukung pengelolaan daftar banyak sekolah (multi-unit yayasan) dengan atribut lengkap: identitas resmi (Nama Sekolah, NPSN, NSS, Jenis Sekolah SMA/SMK, Status Negeri/Swasta, Akreditasi A/B/C/Belum), alamat komprehensif, kontak, pimpinan sekolah & NIP, serta yayasan naungan.
- **Arsitektur Modal Dialog Tambah & Edit Modular (`create.blade.php` & `edit.blade.php`):** Baik formulir tambah (*create*) maupun ubah (*edit*) beroperasi secara interaktif via dialog modal (`#modalCreateSchool` dan `#modalEditSchool`) langsung di atas tabel data tanpa reload halaman. Berkas `create.blade.php` dan `edit.blade.php` distandarisasi sebagai komponen modular yang di-include ke dalam halaman indeks (`index.blade.php`).
- **Kesiapan Bridging Ekosistem Dapodik:** Kolom `npsn` di-index secara optimal sebagai kunci unik alternatif (*secondary natural key*) untuk integrasi API bridging ekosistem nasional pada fase lanjutan, tanpa mengunci constraint unik kaku pada level DBMS sesuai prinsip reduksi batasan teks unik.
- **Manajemen Visual & Upload Logo Base64 (Maks. 1MB):** Dilengkapi sistem unggah logo format Base64 terintegrasi langsung di form modal create dan edit, live preview instan, validasi ukuran 1MB, dan penyimpanan otomatis ke disk publik dengan tata nama collision-proof: `sekolah_u{user_id}_{timestamp}_{random8}.{ext}`.
- **Pencarian Dinamis, Filter Jenis & Paginasi Responsif:** Pencarian instan berdasarkan nama sekolah, NPSN, maupun kota/kabupaten dengan filter dropdown jenis sekolah (Semua, SMA, SMK), toolbar tabel adaptif, dan integrasi komponen paginasi global `<x-pagination>`.
- **Akses Rute Resmi:** Dikelola pada rute `/master/data-sekolah` (`master.data-sekolah.*`) di bawah modul Administrasi ➔ Master ➔ Data Sekolah.

---

### 🗺️ 16. Master Wilayah Administratif Kemendagri (Hierarki 4 Tingkat)
- **Kewajiban Standar Resmi Kemendagri (Bukan BPS):** Seluruh kode wilayah administratif dalam platform INFORA wajib menggunakan standar resmi Kementerian Dalam Negeri RI dalam format string numerik murni tanpa tanda titik (Provinsi 2 digit misal `73`, Kabupaten/Kota 4 digit misal `7308`, Kecamatan 6/7 digit misal `730801`, Kelurahan/Desa 10 digit misal `7308011001`). Secara ketat menolak kode wilayah statistik BPS (Wilkerstat) yang memiliki ketidakcocokan signifikan pada level kabupaten/kota (misal: Kab. Bone Kemendagri = `7308`, BPS = `7311`). Pedoman resmi dicatat dalam `.agents/rules/regional-codes-standard.md`.
- **Relasi Berjenjang 4 Tingkat & Integritas Relasional:**
  - Struktur hierarki berjenjang (`Provinsi` ➔ `Kabupaten` ➔ `Kecamatan` ➔ `Kelurahan/Desa`) dengan relasi *has-many-through* dan relasi langsung ke entitas sekolah (`sekolah()`).
  - Mutator Title Case otomatis (`TextFormatter::titleCase()`) pada nama wilayah, mutator UPPERCASE pada singkatan provinsi, dan scope query `aktif()`.
  - Proteksi integritas relasi: data kelurahan/desa yang masih terhubung dengan registri data sekolah diproteksi dari penghapusan (*restricted deletion guard*).
- **Filter Cascading Dinamis 3 Tingkat:** Antarmuka pencarian dan formulir modal dilengkapi filter dropdown cascading reaktif (pilih Provinsi ➔ memuat Kabupaten terkait ➔ memuat Kecamatan terkait) tanpa reload halaman.
- **Arsitektur Modal Dialog CRUD Modular:** Formulir tambah (`create.blade.php`) dan ubah (`edit.blade.php`) terstandarisasi sebagai komponen modal modular yang di-include pada `index.blade.php`, dilengkapi sticky header scroll tabel (`.table-responsive-scroll`) dan paginasi responsif.
- **Dataset Baseline Resmi Kemendagri (Seeder):**
  - 38 Provinsi se-Indonesia (`ProvinsiSeeder`).
  - 24 Kabupaten/Kota se-Sulawesi Selatan dan kota-kota percontohan nasional (`KabupatenSeeder`).
  - 27 Kecamatan se-Kabupaten Bone (`KecamatanSeeder`).
  - 372 Kelurahan & Desa se-Kabupaten Bone (`KelurahanSeeder`).
- **Akses Rute Resmi:**
  - Provinsi: `/wilayah/provinsi` (`wilayah.provinsi.*`)
  - Kabupaten: `/wilayah/kabupaten` (`wilayah.kabupaten.*`)
  - Kecamatan: `/wilayah/kecamatan` (`wilayah.kecamatan.*`)
  - Kelurahan: `/wilayah/kelurahan` (`wilayah.kelurahan.*`)

---

### 📐 17. Tata Letak 3-Tier Docked Footer & Komponen Paginasi Responsif Global
- **Arsitektur Antarmuka 3-Tier (Docked Footer & Internal Scroll):**
  - Membagi tata letak aplikasi menjadi 3 zona fungsional: Topbar (*header*) tetap di bagian atas, footer docked (*docked bottom dock*) permanen di bawah viewport layar, serta scrolling vertikal independen pada area konten utama (`.app-content`) dan navigasi sidebar (`.sidebar-content`).
  - Menghilangkan *double scrollbar* pada jendela browser dan memastikan seluruh tombol aksi serta footer sistem selalu berada dalam jangkauan pandang pengguna.
  - Penguncian `flex-shrink: 0` pada elemen navigasi sidebar dan penghalusan kurva transisi accordion menu dengan *cubic-bezier* serta auto-scroll cerdas.
- **Komponen Paginasi Responsif Global (`<x-pagination>`):**
  - Komponen universal di `resources/views/components/pagination.blade.php` yang didaftarkan secara global pada `AppServiceProvider` via `Paginator::defaultView('components.pagination')`.
  - Menyajikan badge informasi rentang data, tautan nomor halaman yang rapi, dan tombol navigasi mobile-friendly yang tidak rusak pada resolusi sempit.
- **Toolbar Tabel Responsif & Sticky Header Scroll:**
  - Penataan toolbar tabel (`.table-toolbar-responsive`) yang fleksibel membungkus search box, filter dropdown, dan tombol tambah data di berbagai ukuran layar.
  - Wadah tabel data dengan modifier `.table-responsive-scroll` dengan batas ketinggian maksimal 440px dan sticky header `th`, menjaga label kolom tetap terlihat saat pengguna menelusuri data tabel panjang.

---

## 🔗 Navigasi Dokumen Terkait
- 🏠 **[README Utama](../README.md):** Gambaran umum, prasyarat, dan panduan menjalankan proyek.
- 🎨 **[DESIGN.md](DESIGN.md):** Spesifikasi arsitektur teknis, sistem peran (RBAC), skema database, dan UI/UX.
- 📋 **[TODO.md](TODO.md):** Roadmap tahapan pengembangan (Fase 1 sampai Fase 6).
- 🖼️ **[BRANDING.md](docs/BRANDING.md):** Filosofi penamaan brand, aset logo lockup, dan app icon.
- 🛠️ **[TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md):** Solusi kendala teknis dan instalasi container.
