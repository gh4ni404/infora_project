# 🚀 Rencana Pengembangan Tingkat Lanjut (Multi-Sekolah & Ekosistem Terpusat)

Dokumen ini menampung rencana pengembangan tingkat lanjut untuk mendukung skenario **multi-sekolah (multi-unit yayasan / dinas / multi-tenancy)** di luar arsitektur operasional saat ini yang berjalan pada mode **Sekolah Tunggal (`1 Sekolah = 1 Database`)**.

---

## 🎯 Latar Belakang
Pada fase berjalan, INFORA memfokuskan kesederhanaan deployment, isolasi penuh basis data, dan kemudahan kolaborasi developer dengan pendekatan **1 Sekolah = 1 Database**:
- Halaman **Profil Sekolah** (`/master/data-sekolah`) hanya menampilkan dan mengelola 1 profil sekolah aktif (`edit`, `update`, `sync-single-admin`).
- Penambahan/penghapusan banyak sekolah via antarmuka web dinonaktifkan.

Untuk pengembangan skala yayasan, konsorsium sekolah, atau dinas pendidikan di masa mendatang, fitur **multi-sekolah** berikut dipisahkan ke dalam roadmap tingkat lanjut ini.

---

## 📋 Cakupan Fitur Multi-Sekolah (Rencana Lanjutan)

### 1. Registri Sekolah Multi-Record & Multi-Tenancy (`schools`)
- **Manajemen Banyak Unit Sekolah:** Mengaktifkan kembali pembuatan, pencarian, filter jenjang (`SMA`, `SMK`, `MA`, `SD`, `SMP`), dan paginasi daftar sekolah dalam satu instansi aplikasi/yayasan.
- **Isolasi Data Berbasis `school_id`:** Memanfaatkan relasi `school_id` yang sudah tersedia pada tabel operasional (`users`, `jurusan`, `tingkatan`, `mapel`, `gtks`, `murids`, `kelas`, dll.) untuk memisahkan data antar unit sekolah secara ketat.
- **Filter Konteks Unit Sekolah:** Menyediakan pemilih dropdown unit sekolah (`schoolFilterForm`) bagi akun pengelola tingkat yayasan/pusat di seluruh modul Master Data (Jurusan, Tingkatan, Mapel, GTK, Murid, Rombel).

### 2. Sinkronisasi Akun Admin Lintas Sekolah
- Penerbitan dan penyelarasan akun administrator sekolah secara massal (`syncAllAdmins`) untuk banyak unit sekolah sekaligus.

### 3. Kesiapan Bridging Ekosistem Nasional
- Integrasi API bridging dengan Dapodik, EMIS Kemenag, e-Rapor, dan layanan LMS/WhatsApp menggunakan `npsn` / `nism` sebagai *natural key* lintas instansi.
