# Standar Penulisan Pesan Git Commit (Conventional Commits Bahasa Indonesia)

Aturan ini mengikat seluruh penulisan pesan commit Git di repositori INFORA sesuai panduan resmi di `commit_rules.md` dan riwayat commit aktif.

---

## 1. Bahasa & Format Pesan
1. **Wajib Bahasa Indonesia Penuh:**
   - Semua pesan commit wajib ditulis dalam **Bahasa Indonesia** mengikuti bahasa resmi proyek dan antarmuka INFORA.
   - Hindari mencampur bahasa (Inggris + Indonesia) kecuali untuk kata kunci tipe Conventional Commits (`feat`, `fix`, `refactor`, dll) dan nama class/token teknis.
2. **Format Conventional Commits:**
   ```text
   <type>(<scope>): <ringkasan>
   ```
   - `<type>`: jenis perubahan (lihat tabel di bawah).
   - `<scope>`: modul atau komponen yang disentuh (misal: `sekolah`, `jurusan`, `wilayah`, `rules`, `sidebar`, `components`, `migration`, `seed`, `route`).
   - `<ringkasan>`: kalimat pendek imperatif, maksimal **±72 karakter**, dan **tidak berakhiran titik**.

---

## 2. Tabel Tipe Perubahan (`type`)

| Tipe | Penggunaan | Contoh Subjek |
| :--- | :--- | :--- |
| `feat` | Fitur baru | `feat(sekolah): tambah filter pencarian unit sekolah` |
| `fix` | Perbaikan bug | `fix(wilayah): perbaiki cascade dropdown kabupaten ke kecamatan` |
| `refactor` | Pengubahan struktur kode tanpa mengubah fungsi | `refactor(kalender-akademik): selaraskan komponen form-select pada modal` |
| `style` | Format / kerapian kode (hasil Pint), tanpa ubah logika | `style: rapikan format model User via pint` |
| `test` | Penambahan atau pembaruan test otomatis | `test(jurusan): tambah feature test validasi status aktif` |
| `docs` | Perubahan dokumentasi / aturan (`.md`) | `docs(rules): tambah aturan konvensi git commit ke .agents/rules` |
| `chore` | Tugas rutin (config, migration, seed, aset) | `chore(seed): tambahkan data awal modul dan menu navigasi` |
| `perf` | Optimasi performa dan query | `perf(sidebar): optimasi in-memory filtering menu akses` |
| `build` | Perubahan dependensi dan build (`composer.json`, `package.json`) | `build: perbarui pustaka tailwindcss ke v4` |

---

## 3. Aturan Penulisan Ringkasan (Subjek)
1. **Awali dengan Kata Kerja Bentuk Dasar (Imperatif):**
   - Gunakan: `tambah`, `perbaiki`, `ubah`, `perbarui`, `hapus`, `pindahkan`, `ganti`, `bersihkan`, `selaraskan`.
   - Hindari kata kerja berimbuhan pasif/lampau (misal: *menambahkan*, *memperbaiki*, *added*, *fixed*).
2. **Tanpa Titik di Akhir Subjek:**
   - Subjek commit tidak boleh diakhiri tanda titik (`.`).

---

## 4. Prinsip Pemisahan Komit (Atomic Commits)
- Dilarang menggabungkan banyak perubahan yang tidak berhubungan dalam satu commit.
- Terapkan prinsip **satu commit = satu tujuan logis**:
  - Pisahkan perapian formatting (`style`) dari penambahan fitur (`feat`).
  - Pisahkan migrasi/skema database (`chore` / `feat`) dari logic antarmuka.

---

## 5. Protokol Wajib Sebelum Menjalankan Commit
1. **Pemeriksaan Linter & Code Style:**
   - Jalankan `./dev pint` (atau `.\dev.ps1 pint`).
2. **Verifikasi Test Suite:**
   - Jalankan `./dev test` (atau `.\dev.ps1 test`).
3. **Pemeriksaan Status File:**
   - Jalankan `git status` dan `git diff` untuk memastikan tidak ada berkas yang tidak diinginkan terbawa.
4. **Proteksi File Rahasia:**
   - **Dilarang keras** meng-commit file kredensial/rahasia (seperti `.env`).
