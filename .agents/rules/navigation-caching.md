# Navigation Architecture, Dynamic Routing & Sidebar Caching

## 1. Model-Driven Cache Invalidation
- Jangan hanya mengandalkan pemanggilan `SidebarCache::flushGlobal()` di dalam Controller. Perubahan data navigasi dapat terjadi via seeder, Tinker, model factories, atau penyesuaian langsung.
- Pastikan model navigasi (`Module`, `Menu`, `SubMenu`) memiliki lifecycle hook `booted()` dengan listener `saved` dan `deleted` yang memanggil `SidebarCache::flushGlobal()`.

## 2. Storage & Cache Permissions
- Hindari menjalankan perintah Artisan menggunakan `sudo` (misal `sudo php artisan ...`). Hal ini menyebabkan file cache di `storage/framework/cache/data/` dimiliki oleh `root:root`.
- Jika proses web server (user non-root) mencoba meng-update atau menaikkan versi cache, PHP akan gagal dengan peringatan `Permission denied` secara semi-senyap, menyebabkan cache basi (*stale cache*) tidak pernah diperbarui.
- Selalu pastikan kepemilikan direktori `storage/` dan `bootstrap/cache/` berada pada user pengembang:
  `sudo chown -R $USER:$USER storage bootstrap/cache`

## 3. Dynamic Route Resolution & Under-Development Fallback
- Model `Menu` dan `SubMenu` menggunakan method `getRouteUrlAttribute()` yang memeriksa ketersediaan rute via `Route::has($this->route_name)`.
- Jika nama rute tidak ditemukan di `routes/web.php`, sistem secara otomatis mengarahkan tautan ke rute fallback `system.under-development`.
- Jika pengguna mendapati tombol navigasi mengarah ke halaman "Status Fitur: Dalam Tahap Pengembangan" padahal rute baru sudah didaftarkan:
  1. Periksa apakah nama rute di database persis cocok dengan nama rute di `routes/web.php`.
  2. Periksa apakah cache sidebar masih menyimpan nama rute lama karena belum ter-flush.

## 4. Cache Versioning Implementation
- Pada cache driver `file`, hindari mengandalkan atomicity `Cache::increment()` jika key belum terinisialisasi dengan aman.
- Gunakan pola:
  ```php
  $current = (int) Cache::get(self::VERSION_KEY, 1);
  Cache::forever(self::VERSION_KEY, $current + 1);
  ```
  Pola ini menjamin nomor versi selalu naik secara persisten pada semua tipe cache driver.
