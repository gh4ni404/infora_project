# Project Documentation Synchronization Protocol

Aturan ini mengikat seluruh siklus pengembangan fitur, refactoring kode, dan perancangan antarmuka di proyek INFORA agar dokumentasi dan tracker selalu mencerminkan status riil proyek.

---

## 1. Peta Dokumen Utama Proyek
Setiap aspek proyek didokumentasikan pada berkas khusus yang saling melengkapi:

| Berkas Dokumentasi | Peran & Kandungan Dokumen |
| :--- | :--- |
| **`detail/DESIGN.md`** | Dokumen induk spesifikasi desain, arsitektur sistem, palet warna, dan prinsip perancangan layout. |
| **`docs/COMPONENTS.md`** | Katalog teknis lengkap 22 Anonymous Blade Components dan modul pendamping JavaScript. |
| **`CHANGELOG.md`** | Catatan riwayat versi terstruktur mengikuti standar [Keep a Changelog](https://keepachangelog.com/id/1.1.0/) dan [SemVer](https://semver.org/lang/id/). |
| **`TODO.md` & `detail/TODO.md`** | Roadmap implementasi fitur dari Fase 1 hingga Fase 6 beserta checkbox status penyelesaian. |
| **`README.md` & `detail/README.md`** | Ringkasan proyek, quick start panduan Docker, serta penjelasan rinci fitur unggulan. |
| **`dev` & `dev.ps1`** | Skrip helper resmi automasi perintah container Docker lintas OS. |

---

## 2. Protokol Sinkronisasi Wajib Saat Pengembangan Fitur
1. **Fitur Baru Selesai Diimplementasikan:**
   - Tambahkan entri ringkas di bawah seksi `## [Unreleased]` pada `CHANGELOG.md` (kategori `Added` atau `Changed`).
   - Centang checkbox `[x]` pada task yang relevan di `TODO.md` dan `detail/TODO.md`.
   - Perbarui ringkasan fitur di `README.md` dan `detail/README.md` bila fitur tersebut merupakan kapabilitas inti baru.
2. **Pembuatan Komponen Blade Baru:**
   - Daftarkan komponen pada `docs/COMPONENTS.md` lengkap dengan props, slots, contoh Blade view, dan interaksi JavaScript-nya.
3. **Perubahan Arsitektur atau Standar Desain:**
   - Perbarui spesifikasi terkait di `detail/DESIGN.md` dan pastikan sinkron dengan aturan yang tersimpan di `.agents/rules/`.
4. **Verifikasi Pengujian:**
   - Setiap fitur baru wajib disertai feature test Pest di `tests/Feature/` dan dipastikan lulus 100% via `./dev test` sebelum menandai tugas selesai pada dokumentasi.
