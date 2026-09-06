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

### Penyebab

Laravel menggunakan directive `@vite` di Blade template untuk memuat aset frontend. Mekanisme kerjanya:

1. Jika file **`public/hot`** ada → Laravel menganggap **Vite dev server sedang aktif** dan mencoba memuat semua aset langsung dari `http://0.0.0.0:5173` (alamat Vite di dalam Docker container).
2. Jika file **`public/hot`** tidak ada → Laravel memuat aset dari hasil build di **`public/build/manifest.json`**.

Masalah terjadi saat:
- Vite dev server pernah dijalankan sebelumnya (file `public/hot` terbuat).
- Kemudian container di-restart atau dev server dihentikan **tanpa** menghapus file `hot`.
- Akibatnya, browser diarahkan ke `0.0.0.0:5173` yang tidak bisa diakses dari luar container → **`ERR_ADDRESS_INVALID`**.

---

### ✅ Solusi

#### Langkah 1 — Build aset frontend (jika belum)

```bash
# Git Bash / WSL / Linux / macOS
./dev npm run build

# Windows PowerShell
.\dev.ps1 npm run build
```

Tunggu hingga proses selesai. Output akhir akan menampilkan daftar file yang dihasilkan di `public/build/`.

#### Langkah 2 — Hapus file `public/hot`

```bash
# Git Bash / WSL / Linux / macOS
./dev exec app rm -f public/hot

# Windows PowerShell
.\dev.ps1 exec app rm -f public/hot

# Alternatif: langsung di dalam container
docker exec infora_project-app-1 rm -f /var/www/html/public/hot
```

> **Catatan:** Nama container bisa berbeda. Jalankan `docker ps` untuk melihat nama container yang aktif, lalu ganti `infora_project-app-1` dengan nama yang sesuai.

#### Langkah 3 — Hard refresh browser

Tekan **`Ctrl + Shift + R`** (Windows/Linux) atau **`Cmd + Shift + R`** (macOS) untuk memuat ulang halaman dan mengabaikan cache browser.

---

### 🔄 Cara Kerja yang Benar: Dev Server vs. Production Build

| Mode | Perintah | Deskripsi |
|---|---|---|
| **Development (HMR)** | `./dev npm run dev` | Vite dev server aktif di `localhost:5173`. File `public/hot` dibuat otomatis. Perubahan kode langsung tampil di browser tanpa reload. |
| **Production Build** | `./dev npm run build` | Aset di-compile ke `public/build/`. **Tidak ada** file `public/hot`. Laravel membaca manifest dari `public/build/manifest.json`. |

> ⚠️ **Jangan jalankan keduanya sekaligus.** Jika kamu menjalankan `npm run dev` lalu menghentikannya dan tidak menghapus `public/hot`, Laravel akan tetap mencoba menghubungi dev server yang sudah tidak aktif.

---

## ❌ Halaman Kosong Setelah `./dev start` (Tanpa Error Jelas)

### Gejala

- Container berjalan, tapi mengakses `localhost:8000` menampilkan halaman kosong atau error 500.

### Penyebab & Solusi

| Penyebab | Solusi |
|---|---|
| `.env` belum dibuat | `cp .env.docker.example .env` (Linux/macOS) atau `copy .env.docker.example .env` (Windows CMD) |
| Migrasi database belum dijalankan | `./dev artisan migrate --seed` |
| `APP_KEY` kosong | `./dev artisan key:generate` |
| Storage symlink belum ada | `./dev artisan storage:link --relative` |

---

## ❌ Error: `The [public/storage] link already exists`

### Gejala

Saat menjalankan `./dev artisan storage:link`, muncul pesan error merah:
```
ERROR  The [public/storage] link already exists.
```

### Penyebab

Symlink `public/storage` sudah pernah dibuat sebelumnya.

### Solusi

Ini **bukan error fatal** — symlink sudah ada dan berfungsi. Abaikan pesan ini dan lanjutkan ke langkah berikutnya.

---

## ❌ Aset Masih Tidak Muncul Setelah Build & Hapus `hot`

### Kemungkinan Penyebab

1. **Cache browser** — Lakukan hard refresh: `Ctrl + Shift + R`.
2. **Manifest tidak terbaca** — Pastikan folder `public/build/` berisi file `manifest.json`:
   ```bash
   ./dev exec app ls public/build/
   ```
3. **`APP_ASSET_URL` salah di `.env`** — Pastikan tidak ada nilai `APP_ASSET_URL` yang mengarah ke alamat yang salah. Hapus atau kosongkan entri tersebut.
4. **Nginx cache** — Restart container Nginx:
   ```bash
   docker compose restart nginx
   ```

---

## 🧰 Perintah Diagnostik Berguna

```bash
# Cek status semua container
docker compose ps

# Lihat log container app (Laravel)
./dev logs app

# Lihat log container nginx
./dev logs nginx

# Masuk ke shell container app
./dev exec app bash

# Cek apakah file hot ada
./dev exec app ls -la public/hot

# Cek isi manifest build
./dev exec app cat public/build/manifest.json
```

---

## 📌 Checklist Setup Awal (Windows dengan Docker Desktop)

Ikuti urutan ini saat pertama kali menjalankan proyek:

```powershell
# 1. Clone repositori
git clone https://github.com/gh4ni404/infora_project.git
cd infora_project

# 2. Salin konfigurasi environment
copy .env.docker.example .env

# 3. Jalankan semua container
.\dev.ps1 start

# 4. Jalankan migrasi & seeder database
.\dev.ps1 artisan migrate --seed

# 5. Buat storage symlink
.\dev.ps1 artisan storage:link --relative

# 6. Build aset frontend
.\dev.ps1 npm run build

# 7. Buka browser dan akses aplikasi
# http://localhost:8000
```

> **Untuk pengguna Git Bash / WSL:** Ganti `.\dev.ps1` dengan `./dev` di setiap perintah di atas.
