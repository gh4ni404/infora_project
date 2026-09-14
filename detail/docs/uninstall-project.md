Masalah **`Permission denied`** saat membersihkan folder proyek berbasis Docker di Linux biasanya terjadi karena 2 hal:
1. **Container masih aktif**, sehingga file dan direktori masih di-*mount* / dikunci oleh Docker daemon.
2. **Ada file/folder yang dibuat oleh container sebagai `root`** (UID 0), sehingga user Linux biasa Anda (`gania`, UID 1000) tidak diizinkan menghapusnya secara langsung dengan `rm -rf`.

Berikut adalah panduan langkah demi langkah untuk membersihkannya secara tuntas hingga bersih seperti baru di-clone:

---

### 🧹 Skenario 1: Reset Bersih ke Kondisi Awal (Seperti Baru Kloning)

Gunakan langkah ini jika Anda ingin menguji kembali apakah pengembang baru yang baru men-clone proyek benar-benar bisa langsung jalan:

#### Langkah 1: Hentikan dan lepas semua container Docker
Jalankan perintah ini untuk menghentikan container dan melepaskan semua *volume/mount*:
```bash
./dev down -v
```
*(atau `docker compose down -v --remove-orphans`)*

---

#### Langkah 2: Kembalikan kepemilikan seluruh file ke user Anda
Untuk menghapus file-file yang sempat dimiliki oleh `root` tanpa terhalang *Permission Denied*, Anda bisa menggunakan **trik container sementara** berikut (tidak perlu password `sudo`):
```bash
docker run --rm -v $(pwd):/work -w /work alpine chown -R 1000:1000 .
```
> **Mengapa ini bekerja?** Docker menjalankan container Alpine sebagai `root`, lalu mengubah seluruh hak milik file di folder proyek menjadi UID 1000 (user Linux Anda). Setelah ini, user Anda memiliki hak penuh atas semua file.

*(Alternatif jika biasa memakai sudo:* `sudo chown -R $USER:$USER .`*)*

---

#### Langkah 3: Hapus artefak hasil generate (Vendor, Node Modules, .env, Log)
Ada 2 cara:

**Cara Praktis (via Git):**
Git dapat menghapus semua file/folder yang masuk dalam `.gitignore` (termasuk `vendor/`, `node_modules/`, `.env`, cache, dan log):
```bash
git clean -fdX
```
> ⚠️ **Catatan:** Perintah `git clean -fdX` akan menghapus file `.env`. Pastikan Anda sudah punya cadangan kredensial jika menggunakan database kustom.

**Atau Cara Manual (Spesifik):**
```bash
rm -rf vendor/
rm -rf node_modules/
rm -f .env
rm -f public/hot
rm -f public/storage
rm -f storage/logs/*.log
```

---

#### Langkah 4 (Opsional): Bersihkan Image Docker Lokal
Jika Anda ingin Docker me-rebuild image PHP dari nol:
```bash
docker rmi infora_project-app

# atau bisa menggunakan ini
docker image prune -a

```