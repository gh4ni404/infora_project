# Prinsip Reduksi Batasan Unik pada Kolom Teks Entitas

Aturan ini mengikat perancangan skema basis data migrasi dan validasi entitas sistem sesuai `detail/DESIGN.md` Seksi 6 poin 4 dan 5.

---

## 1. Rasionalisasi Desain: Mengapa Menghindari Constraint `unique` pada Kolom Teks?
1. **Fleksibilitas Operasional dan Penanganan Data Riil:**
   - Dalam tata kelola sekolah, sering kali terdapat kebutuhan menginput nama entitas yang identik pada konteks atau waktu yang berbeda (misalnya: nama menu yang sama di bawah modul berbeda, nama rombel/jurusan lintas sekolah, atau pembaruan draft).
   - Menetapkan database constraint `unique` pada kolom teks (seperti `name`, `route_name`, `npsn`, `kode`) kerap memicu kegagalan transaksi fatal (*duplicate entry SQL error*) saat pengguna melakukan pengeditan atau restoring data.
2. **Integritas Relasional Berbasis ID & Foreign Key:**
   - Integritas data dijamin melalui Primary Key ID (`id`) dan relasi Foreign Key terindeks (`foreignId()->constrained()->cascadeOnDelete()`), bukan melalui batasan teks unik yang kaku di DBMS.

---

## 2. Aturan Perancangan Migrasi & Validasi
1. **Migrasi:**
   - Dilarang menambahkan `->unique()` pada kolom teks entitas administratif (`modules.name`, `menus.name`, `sub_menus.name`, `sub_menus.route_name`, `schools.npsn`).
   - Gunakan `->index()` jika kolom teks tersebut memerlukan optimasi pencarian / bridging nasional (misal kolom `npsn` pada tabel `schools`).
2. **Validasi Form Request:**
   - Hindari aturan validasi `Rule::unique(...)` pada nama entitas yang membatasi fleksibilitas operasional admin, kecuali diminta secara eksplisit atau dibatasi spesifik per-scope (misal: per `school_id`).
   - Penanganan duplikasi sebaiknya diarahkan pada level peringatan antarmuka pengguna atau scope per unit sekolah.
