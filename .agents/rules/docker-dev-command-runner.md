# Docker Development CLI Wrapper (./dev & dev.ps1)

Aturan ini mengikat seluruh eksekusi perintah pengembang (*development tooling*) dalam proyek INFORA sesuai dengan skrip helper container Docker di root proyek.

---

## 1. Larangan Eksekusi CLI Langsung pada Host
1. **Dilarang Menjalankan Tooling Langsung di Host:**
   - Dilarang menjalankan perintah langsung `php artisan ...`, `composer ...`, `npm ...`, `vendor/bin/pest`, `vendor/bin/pint`, atau `mysql` secara langsung di terminal host mesin pengembang.
   - Dilarang menjalankan `docker compose exec` atau `docker compose run` mentah secara manual jika perintah tersebut sudah disediakan oleh helper script.
2. **Kewajiban Menggunakan Skrip Helper Resmi:**
   - **Linux / macOS / WSL:** Wajib menggunakan `./dev <command>`
   - **Windows PowerShell:** Wajib menggunakan `.\dev.ps1 <command>`

---

## 2. Pemetaan Perintah Helper Resmi

| Kategori | Perintah Helper (Linux/WSL) | Perintah Helper (PowerShell) | Eksekusi di Container |
| :--- | :--- | :--- | :--- |
| **Artisan** | `./dev artisan <args>` / `./dev art` | `.\dev.ps1 artisan <args>` | `app` (as `www-data`) |
| **Composer** | `./dev composer <args>` / `./dev comp` | `.\dev.ps1 composer <args>` | `app` (as `www-data`) |
| **NPM** | `./dev npm <args>` | `.\dev.ps1 npm <args>` | `node` |
| **Testing** | `./dev test <args>` | `.\dev.ps1 test <args>` | `app` (menjalankan `php artisan test`) |
| **Formatter**| `./dev pint <args>` | `.\dev.ps1 pint <args>` | `app` (menjalankan `vendor/bin/pint`) |
| **Build Asset**| `./dev build` | `.\dev.ps1 build` | `node` (menjalankan `npm run build`) |
| **Reset DB** | `./dev fresh` | `.\dev.ps1 fresh` | `app` (`migrate:fresh --seed`) |
| **Cache Clear**| `./dev clear` | `.\dev.ps1 clear` | `app` (`optimize:clear`) |
| **DB Shell** | `./dev mysql` | `.\dev.ps1 mysql` | `app` (`php artisan db`) |
| **Bash Shell** | `./dev bash` | `.\dev.ps1 bash` | `app` (interactive bash as `www-data`) |

---

## 3. Isolasi Hak Akses dan User Container
1. **Eksekusi sebagai `www-data`:**
   - Helper `./dev` dan `.\dev.ps1` secara otomatis mengeksekusi perintah PHP/Artisan/Composer di dalam container `app` menggunakan user non-root `www-data`.
   - Ini mencegah timbulnya konflik kepemilikan file (`permission denied` oleh user `root:root`) pada direktori `storage/` dan `bootstrap/cache/`.
2. **Auto-fallback Container:**
   - Helper secara otomatis mendeteksi apakah container sedang berjalan (`docker compose ps`). Jika belum aktif, skrip akan melakukan `docker compose run --rm` secara transparan.
