# Migration Schema Synchronization, In-Place Modification & Backup Traps

## 1. Larangan Membuat File Migrasi Baru Tanpa Persetujuan
- Jika rencana awal telah menyepakati modifikasi skema secara *in-place* pada file migrasi yang ada (misal `create_schools_table.php`), **JANGAN PERNAH** secara sepihak membuat file migrasi baru (seperti `add_wilayah_columns_to_schools_table.php`).
- Selalu patuhi struktur dan urutan timestamp migrasi yang sudah disepakati.

## 2. Menghindari `migrate:fresh` yang Menghilangkan Data Pengguna
- Dilarang keras menyarankan atau mengeksekusi `migrate:fresh` jika pengguna telah memiliki data kerja (menu, sub-menu, hak akses, template, atau data master sekolah) di database, kecuali diminta secara eksplisit oleh pengguna.
- Menghormati progres pengguna adalah prioritas utama.

## 3. Perangkap Database Hasil Restore Cadangan (Backup Trap) & Migrasi Idempoten
- Ketika sistem menjalankan pemulihan (*restore*) dari file SQL/ZIP cadangan:
  1. Tabel `migrations` di database ikut tertimpa ke kondisi saat cadangan tersebut dibuat.
  2. Jika ada migrasi baru setelah tanggal backup tersebut, status migrasi akan menjadi `Pending`.
  3. Jika migrasi tidak ditulis secara idempoten, `php artisan migrate` akan crash dengan error `Table already exists`.
  4. Aplikasi dapat mengalami error fatal (misal `Unknown column 'is_master_enabled'`) saat perintah sistem seperti `./dev clear` dipanggil sebelum migrasi dieksekusi.
- **Standar Migrasi Idempoten Wajib**:
  - Semua pembuatan tabel baru wajib diawali `if (Schema::hasTable('table_name')) { return; }`.
  - Semua penambahan kolom baru wajib dibungkus `if (! Schema::hasColumn('table_name', 'column_name'))`.
- **Otomatisasi Sinkronisasi Pasca Restore**:
  - Layanan restore (`DatabaseBackupService::restoreDatabaseFromSql`) WAJIB secara otomatis mengeksekusi `Artisan::call('migrate', ['--force' => true])` dan `SidebarCache::flushGlobal()` di akhir proses pemulihan sebelum menyelesaikan transaksi restore.
- **Prosedur Sinkronisasi Aman Manual (Non-Destructive)**:
  - Gunakan `Schema::table()` untuk menambahkan kolom baru secara aman jika belum ada (`if (! Schema::hasColumn(...))`).
  - Sinkronkan nama catatan di tabel `migrations` melalui query update agar cocok dengan nama file migrasi terkini.
  - Dengan cara ini, status migrasi menjadi `Ran` dan 100% data pengguna tetap aman.

## 4. Standar Eksekusi Perintah
- Selalu gunakan perintah helper `./dev artisan ...` sesuai lingkungan Docker proyek, bukan perintah langsung pada host atau docker exec mentah.

