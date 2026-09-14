# 🛠️ INFORA — Panduan Troubleshooting

Dokumen ini menghimpun masalah umum yang sering ditemui saat setup atau pengembangan INFORA di lingkungan Docker, beserta langkah penyelesaiannya.

---

## ❌ Halaman Tampil Tanpa Styling (CSS/JS Tidak Dimuat)

### Gejala

- Halaman login atau halaman lain tampil sebagai **HTML polos tanpa styling** (tidak ada warna, layout, atau font).
- Browser DevTools Console menampilkan error seperti:
  ```
  Failed to load resource: net::ERR_ADDRESS_INVALID
  GET https://0.0.0.0:5173/resources/css/app.css
  GET https://0.0.0.0:5173/resources/js/app.js
  ```

### Penyebab & Solusi Permanen

Masalah ini terjadi ketika Vite berjalan di dalam Docker container dengan host `0.0.0.0`, sehingga plugin Vite Laravel secara default menulis alamat internal container `http://0.0.0.0:5173` ke dalam file `public/hot`.

> ✅ **Solusi Permanen (Sudah Terpasang di `vite.config.js`):**
> Konfigurasi `server.origin: process.env.VITE_ORIGIN || 'http://localhost:5173'` telah dipasang pada `vite.config.js`. File `public/hot` kini selalu menulis URL `http://localhost:5173` sehingga browser dapat mengakses server Vite secara live tanpa error `ERR_ADDRESS_INVALID`.

### 🔄 Jika Dev Server Vite Terhenti & Ingin Menggunakan Mode Production Build

Jika container dev server dihentikan atau Anda ingin menggunakan build statis produksi:

```bash
# Git Bash / WSL / Linux / macOS
./dev build
rm -f public/hot

# Windows PowerShell
.\dev.ps1 build
Remove-Item -Force public/hot -ErrorAction SilentlyContinue
```

---

## ❌ Halaman Kosong Setelah `./dev start` (Tanpa Error Jelas)

### Penyebab & Solusi

Entrypoint container `infora_app` sudah otomatis mengurus `.env`, `composer install`, `APP_KEY`, dan `storage:link`. Jika masih menemukan kendala:

| Penyebab | Solusi |
|---|---|
| Konfigurasi Database Belum Sesuai | Pastikan kredensial `DB_*` di `.env` sudah benar (remote cPanel atau `host.docker.internal`). |
| Migrasi database belum dijalankan | `./dev artisan migrate --seed` atau `.\dev.ps1 fresh` |
| Cache Laravel usang | `./dev clear` atau `.\dev.ps1 clear` |

---

## ❌ Error: `The [public/storage] link already exists`

### Gejala

Saat menjalankan `./dev artisan storage:link`, muncul pesan:
```
ERROR  The [public/storage] link already exists.
```

### Penyebab & Solusi

Ini **bukan error fatal** — symlink storage sudah dibuat otomatis saat container pertama kali start. Anda dapat mengabaikan pesan ini.

---

## 🧰 Perintah Diagnostik Berguna

```bash
# Cek status semua container
docker compose ps

# Lihat log container app (Laravel PHP-FPM)
./dev logs app

# Lihat log container web (Nginx)
./dev logs web

# Lihat log container node (Vite HMR)
./dev logs node

# Masuk ke shell container app
./dev bash

# Masuk ke shell root container app
./dev root-bash

# Bersihkan seluruh cache aplikasi
./dev clear
```

---

## 📌 Checklist Setup Awal (Developer Baru)

Pengembang baru hanya memerlukan Docker & Docker Compose:

```bash
# 1. Clone repositori
git clone https://github.com/gh4ni404/infora_project.git
cd infora_project

# 2. Jalankan semua container (otomatis setup .env, vendor, key, & storage link)
# Linux / macOS / WSL:
./dev start

# Windows (PowerShell):
.\dev.ps1 start

# 3. Jalankan migrasi database
./dev artisan migrate --seed
# atau pada PowerShell:
.\dev.ps1 fresh

# 4. Akses aplikasi di browser
# Web App  : http://localhost:8000
# Vite HMR : http://localhost:5173
```
