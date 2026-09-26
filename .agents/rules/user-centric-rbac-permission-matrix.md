# Tata Kelola Izin Granular Pengguna & Matriks Hak Akses (PermissionMatrix)

Aturan ini mengikat seluruh perancangan otorisasi dan kontrol akses menu navigasi di platform INFORA sesuai `detail/DESIGN.md` Seksi 4.4 dan 2.4 poin 18.

---

## 1. Arsitektur Otorisasi Berbasis Pengguna (User-Centric Granular RBAC)
1. **Single User Table + Granular Permissions:**
   - Otorisasi menu navigasi dikelola secara user-centric pada tabel `user_menu_permissions` per baris menu/submenu.
   - Menggunakan 4 hak aksi granular independen:
     - `can_view` (Hak melihat menu/halaman)
     - `can_create` (Hak menambah data baru)
     - `can_edit` (Hak mengubah data)
     - `can_delete` (Hak menghapus data)
2. **Template Peran Dinamis (Role Access Templates):**
   - Template hak akses peran disimpan pada tabel `menu_access_templates` untuk memudahkan Super Admin menerapkan preset izin 1-klik ke akun pengguna (`applyTemplateToUser`).
3. **Super Admin Root Bypass:**
   - Akun bertipe `super_admin` (`User::isSuperAdmin()`) memiliki status root bypass permanen untuk mengakses seluruh modul dan menu tanpa terikat batasan di tabel permission.

---

## 2. Pengendali Matriks Hak Akses Client-Side: `PermissionMatrix`
Modul JavaScript terpusat `resources/js/permission-matrix.js` mengendalikan antarmuka tabel matriks izin:
1. **Dependensi Aksi Baris (Row Action Dependencies):**
   - Mematikan centang *Lihat* (`can_view`) otomatis mematikan dan menonaktifkan seluruh centang aksi CRUD (`can_create`, `can_edit`, `can_delete`).
   - Mencentang salah satu aksi CRUD otomatis mengaktifkan centang *Lihat*.
2. **Sinkronisasi Hierarki Menu (Parent-Child Hierarchy Sync):**
   - Mencentang hak *Lihat* pada suatu sub-menu otomatis mencentang hak *Lihat* pada menu induknya.
   - Menonaktifkan semua sub-menu di bawah suatu menu tidak otomatis mematikan menu induk (dikelola fleksibel).
3. **Toolbar Aksi Cepat Massal:**
   - Toolbar menyediakan tombol *Centang Semua Lihat*, *Centang Semua CRUD*, *Kosongkan Semua*, serta *Toggle Centang per Modul*.

---

## 3. Filtering Sidebar Bebas N+1 (In-Memory Filtering)
- View Composer `SidebarComposer` memuat struktur modul navigasi melalui `SidebarCache::get(auth()->user())`.
- Filter otorisasi menu dan submenu dieksekusi secara in-memory melalui koleksi relasi yang telah di-eager-load, menjamin render sidebar tetap instan dan bebas dari masalah N+1 database queries.
