# 📘 Panduan Resmi Pengembangan Modul SIM Sekolah & Target Akreditasi (SD, SMP, SMA, SMK/MA)

Dokumen ini menjadi **rujukan tunggal (Master Guide)** bagi pengembang **INFORA** dalam merancang, memprioritaskan, dan mengeksekusi penambahan modul Sistem Informasi Manajemen (SIM) Sekolah yang terintegrasi langsung dengan kesiapan akreditasi (**BAN-PDM / IASP**) untuk jenjang **SD, SMP, SMA, SMK, dan Madrasah (MI, MTs, MA)**.

---

## 1. Alur Kerja Developer (*Guide ➔ DESIGN & TODO ➔ Implementasi ➔ CHANGELOG*)

Setiap penambahan modul wajib mengikuti siklus dokumentasi dan rekayasa berikut (selaras dengan [Protokol Sinkronisasi Dokumentasi](../.agents/rules/project-documentation-sync-protocol.md)):

1. **Pilih Target Modul dari Panduan Ini:**
   - Ambil satu unit modul/sub-modul dari daftar target pada Bagian 4 di bawah sesuai prioritas lapisan/fase.
2. **Catat Rencana ke `DESIGN.md` & `TODO.md`:**
   - Daftarkan rancangan tabel dan relasinya di [DESIGN.md](DESIGN.md) (bagian Arsitektur & Skema Basis Data Inti) dengan **mematuhi prinsip Zero `unique` Constraints dan MariaDB-First** ([.ai/rules/migrations.md](../.ai/rules/migrations.md)).
   - Tambahkan checklist item tugasnya ke [TODO.md](../TODO.md) dan [detail/TODO.md](TODO.md).
3. **Bangun Fitur Sesuai Standar Codebase INFORA:**
   - Baca [.ai/rules/index.md](../.ai/rules/index.md) sebelum membuat kode baru.
   - Terapkan mode tunggal sekolah (`Controller::resolveSchoolContext()`, `authorizeSchool()`).
   - Daftarkan rute dengan konvensi standar `.index` pada `routes/web.php`.
   - Gunakan komponen Blade resmi di [docs/COMPONENTS.md](../docs/COMPONENTS.md) tanpa CSS ad-hoc maupun inline style.
   - Catat perkembangan fitur melalui kolom `dev_progress` & `dev_stage` jika fitur dirilis bertahap.
4. **Verifikasi & Catat ke `CHANGELOG.md`:**
   - Tambahkan **Pest Feature Tests** di `tests/Feature/` (`RefreshDatabase` SQLite `:memory:`), lalu jalankan `./dev pint --dirty --format agent` dan `./dev test --compact`.
   - Centang `[x]` pada [TODO.md](../TODO.md) & [detail/TODO.md](TODO.md), ringkas di [detail/README.md](README.md), dan tulis catatan rilis di bawah `## [Unreleased]` pada [CHANGELOG.md](../CHANGELOG.md).

---

## 2. Kesiapan & Penyesuaian Lintas Jenjang (SD, SMP, SMA, SMK, MA)

| Aspek Teknis | SD / MI | SMP / MTs | SMA / MA | SMK / MAK |
| :--- | :--- | :--- | :--- | :--- |
| **`school_type` di `Referensi`** | Tambah `SD` & `MI` (`is_madrasah: true` untuk MI) | Tambah `SMP` & `MTs` (`is_madrasah: true` untuk MTs) | Sudah mendukung `SMA` & `MA` (`is_madrasah: true` untuk MA) | Sudah mendukung `SMK` & `MAK` |
| **Penjenjangan (`Tingkatan`)** | Kelas 1–6 (Fase A: 1–2, Fase B: 3–4, Fase C: 5–6) | Kelas 7–9 (Fase D) | Kelas 10–12 (Fase E: 10, Fase F: 11–12) | Kelas 10–12 / 13 (Fase E & F) |
| **Jurusan / Peminatan** | Tidak dipakai (`jurusan_id = null`) | Tidak dipakai (`jurusan_id = null`) | Opsional (Fase/Peminatan Kelas XI–XII) | Wajib (Konsentrasi Keahlian) |
| **Identitas Murid (NIS/NISM)** | SD: NIS & NISN; MI: NISM 18-digit (`isMadrasah()`) | SMP: NIS & NISN; MTs: NISM 18-digit (`isMadrasah()`) | SMA: NIS & NISN; MA: NISM 18-digit (`isMadrasah()`) | SMK: NIS & NISN; MAK: NISM 18-digit |
| **Modul Vokasi / PKL** | Tidak aktif / opsional | Tidak aktif / opsional | Tidak aktif / opsional | Aktif penuh untuk sekolah SMK |

---

## 3. Peta Pilar Akreditasi (BAN-PDM / IASP) ke Bukti Digital Otomatis

Agar sekolah tidak menumpuk dokumen manual menjelang visitasi, modul operasional INFORA dirancang otomatis memasok bukti ke 4 pilar akreditasi utama:

1. **Kinerja Pendidik dalam Proses Pembelajaran (Mutu Guru & Proses Belajar):**
   - Dipasok otomatis oleh modul **Penugasan Mengajar & Jadwal**, **Bahan & Perangkat Ajar Guru** (ATP, Modul Ajar/RPP, bahan bacaan), dan **Jurnal KBM Digital** (keterlaksanaan tatap muka, refleksi, foto kegiatan).
2. **Kepemimpinan Kepala Sekolah & Manajemen Sekolah (Tata Kelola):**
   - Dipasok oleh **Kalender Akademik**, **Tahun Ajaran & Semester**, **Tata Kelola Hak Akses**, **Sarpras**, serta **Dokumen Manajemen** (RKAS/RKT, SK pembagian tugas, notula rapat, supervisi).
3. **Iklim Lingkungan Belajar & Layanan Kesiswaan:**
   - Dipasok oleh **Absensi Murid** (kiosk wajah + mapel), **Kesiswaan, Ekstrakurikuler & Layanan BK**, serta catatan lingkungan aman/inklusif.
4. **Mutu Hasil Belajar & Capaian Lulusan:**
   - Dipasok oleh **Penilaian & Rekap Hasil Belajar** (formatif/sumatif/leger), **Portofolio Prestasi Murid**, dan **Tracer Study / BKK** *(khusus SMA/SMK/MA)*.

---

## 4. Peta Modul Wajib & Target Pengembangan Bertahap

### Lapisan A — Fondasi Sistem & Master Data *(Sudah Tersedia)*
- Tata Kelola Sistem (`system.modules.index`, `system.menus.index`, `system.sub-menus.index`, `system.referensi.index`, `sistem.user.index`, `backup-restore.index`).
- Master Data Inti (`master.data-sekolah.index`, `wilayah.*`, `master.data-tingkatan.index`, `master.data-jurusan.index`, `master.data-mapel.index`, `master.data-gtk.index`, `master.data-murid.index`, `rombel.kelas.index`).
- Konfigurasi & Presensi Awal (`konfigurasi-sekolah.tahun-ajaran.index`, `konfigurasi-sekolah.kalender-akademik.index`, `pendaftaran_muka.index`, `absen_murid.index`).

---

### Lapisan B — Inti Akademik & KBM *(Prioritas Wajib Tahap 1)*

| Modul / Menu | Sub-Menu & Rute Target (`.index`) | Entitas / Tabel Utama (Non-Unique) | Kontribusi Akreditasi |
| :--- | :--- | :--- | :--- |
| **Kurikulum & Jadwal** | `Penugasan Mengajar` (`akademik.penugasan-mengajar.index`) | `teaching_assignments` (`school_id`, `tahun_ajaran_id`, `semester_id`, `kelas_id`, `mapel_id`, `gtk_id`, `jp_per_minggu`) | Pembagian beban mengajar & SK tugas guru |
| **Kurikulum & Jadwal** | `Jadwal Pelajaran` (`akademik.jadwal-pelajaran.index`) | `lesson_schedules` (`teaching_assignment_id`, `hari`, `jam_ke`, `jam_mulai`, `jam_selesai`, `ruangan`) | Keteraturan jadwal KBM mingguan |
| **Bahan Ajar Guru** | `Perangkat & Bahan Ajar` (`akademik.bahan-ajar.index`) | `teaching_materials` (`school_id`, `gtk_id`, `mapel_id`, `tingkatan_id`, `tahun_ajaran_id`, `kategori` [`atp`, `modul_ajar`, `materi`, `lkpd`], `judul`, `file_path`, `external_url`, `status_validasi`) | Kelengkapan perencanaan pembelajaran guru |
| **Jurnal KBM & Presensi** | `Jurnal KBM Guru` (`akademik.jurnal-kbm.index`) | `teaching_journals` (`lesson_schedule_id`, `teaching_assignment_id`, `tanggal`, `materi_pokok`, `aktivitas`, `kendala`, `foto_path`) | Bukti pelaksanaan pembelajaran harian |
| **Jurnal KBM & Presensi** | `Presensi Mapel` (`akademik.presensi-mapel.index`) | `subject_attendances` (`teaching_journal_id`, `murid_id`, `status` [`H`, `S`, `I`, `A`], `catatan`) | Kehadiran peserta didik per mata pelajaran |
| **Penilaian Akademik** | `Input Penilaian` (`akademik.penilaian.index`) | `assessments` & `assessment_scores` (`teaching_assignment_id`, `jenis` [`formatif`, `sumatif`, `pts`, `pas`], `judul`, `bobot`, `murid_id`, `skor`, `deskripsi`) | Asesmen formatif, sumatif & capaian belajar |
| **Penilaian Akademik** | `Rekap & Leger Nilai` (`akademik.leger-nilai.index`) | Agregasi `assessment_scores` per `kelas_id` & `semester_id` + ekspor Excel via `SpreadsheetService` | Dokumen rekap hasil belajar & kesiapan e-Rapor |

> [!TIP]
> **Ketergantungan Teknis Wajib:** Bangun **Penugasan Mengajar (`teaching_assignments`)** terlebih dahulu sebelum Bahan Ajar, Jurnal KBM, dan Penilaian, karena keempat modul tersebut merujuk ke guru, mapel, kelas, tahun ajaran, dan semester yang sama.

---

### Lapisan C — Kesiswaan, Tata Kelola & Akreditasi *(Prioritas Wajib Tahap 2)*

| Modul / Menu | Sub-Menu & Rute Target (`.index`) | Entitas / Tabel Utama (Non-Unique) | Kontribusi Akreditasi |
| :--- | :--- | :--- | :--- |
| **Kepegawaian Harian** | `Absensi GTK` (`kepegawaian.absensi-gtk.index`) | `gtk_attendances` (`school_id`, `gtk_id`, `tanggal`, `jam_masuk`, `jam_pulang`, `status`, `keterangan`) | Disiplin kehadiran pendidik & tenaga kependidikan |
| **Kesiswaan & BK** | `Ekstrakurikuler` (`kesiswaan.ekstrakurikuler.index`) | `extracurriculars` & `extracurricular_members` (`school_id`, `nama`, `pembina_gtk_id`, `murid_id`, `predikat`) | Pengembangan minat, bakat & karakter murid |
| **Kesiswaan & BK** | `Prestasi & Penghargaan` (`kesiswaan.prestasi.index`) | `student_achievements` (`school_id`, `murid_id`, `tingkat`, `peringkat`, `penyelenggara`, `tanggal`, `sertifikat_path`) | Bukti capaian prestasi akademik/non-akademik |
| **Kesiswaan & BK** | `Disiplin & Layanan BK` (`kesiswaan.bk.index`) | `counseling_records` (`school_id`, `murid_id`, `konselor_gtk_id`, `kategori`, `tanggal`, `tindak_lanjut`) | Iklim keamanan sekolah & pendampingan murid |
| **Sarpras & Tata Kelola** | `Inventaris & Ruangan` (`tata-kelola.sarpras.index`) | `school_rooms` & `school_assets` (`school_id`, `nama`, `kategori`, `jumlah`, `kondisi` [`baik`, `rusak_ringan`, `rusak_berat`]) | Kesiapan sarana prasarana pembelajaran |
| **Tata Kelola** | `Supervisi & Rapat` (`tata-kelola.supervisi.index`) | `supervision_logs` & `meeting_minutes` (`school_id`, `gtk_id`, `tanggal`, `topik`, `catatan`, `dokumen_path`) | Bukti kepemimpinan kepala sekolah & evaluasi |
| **Akreditasi Sekolah** | `Dashboard Kesiapan` (`akreditasi.dashboard.index`) | `accreditation_instruments` & `accreditation_items` (`jenjang` [`ALL`, `SD`, `SMP`, `SMA`, `SMK`], `komponen`, `kode_butir`, `judul`, `bobot`) | Persentase kesiapan per komponen BAN-PDM/IASP |
| **Akreditasi Sekolah** | `Bank Dokumen Mutu` (`akreditasi.bank-dokumen.index`) | `accreditation_evidences` (`school_id`, `accreditation_item_id`, `tahun_ajaran_id`, `judul`, `file_path`, `source_type`, `verified_by`) | Repository bukti fisik terstruktur + tautan data otomatis |
| **Akreditasi Sekolah** | `Portal Audit Asesor` (`akreditasi.asesor.index`) | Mode read-only terkurasi untuk akun/token asesor saat visitasi + ekspor paket ZIP bukti per butir | Kemudahan audit asesor tanpa akses ubah data |

---

### Lapisan D — Modul Spesifik Jenjang & Ekosistem *(Prioritas Tahap 3)*

| Modul / Menu | Rute Target (`.index`) | Target Jenjang | Catatan Implementasi |
| :--- | :--- | :--- | :--- |
| **SPMB / PPDB** | `ppdb.pendaftaran.index` | SD, SMP, SMA, SMK, MA | Seleksi & verifikasi calon murid baru ➔ konversi otomatis menjadi record `Murid` |
| **Vokasi & PKL** | `vokasi.dudi.index`, `vokasi.pkl.index`, `vokasi.bkk.index` | Khusus SMK / MAK | Khusus jenjang SMK / MAK |
| **Perpustakaan** | `layanan.perpustakaan.index` | Opsional SD, SMP, SMA, SMK | Katalog koleksi buku & sirkulasi peminjaman murid/GTK |
| **Bridging Eksternal** | `bridging.dapodik.index` | Semua Jenjang | Ekspor/sinkronisasi format Dapodik & e-Rapor berbasis Eloquent API Resources |

---

## 5. Checklist Teknis Sebelum Mengklaim Modul Selesai

Setiap developer yang menyelesaikan satu modul dari panduan ini wajib memastikan seluruh poin berikut terpenuhi:
- [ ] Spesifikasi tabel & relasi tercatat di [DESIGN.md](DESIGN.md), tanpa constraint `unique` pada kolom teks/komposit, dan kompatibel dengan **MariaDB (`utf8mb4_unicode_ci`)**, **MySQL**, dan **SQLite `:memory:`**.
- [ ] Semua input/update memakai Form Request khusus di `app/Http/Requests/<Domain>/` dengan pesan error Bahasa Indonesia.
- [ ] Rute terdaftar di `routes/web.php` dengan konvensi `.index`, melewati `SuperAdminMiddleware`, dan menghormati isolasi sekolah (`authorizeSchool()`).
- [ ] Antarmuka memakai komponen Blade resmi ([docs/COMPONENTS.md](../docs/COMPONENTS.md)) bebas tag `<style>` dan inline `style="..."`.
- [ ] Pengujian **Pest Feature Tests** di `tests/Feature/` lulus 100%, format PHP lulus `./dev pint --dirty --format agent`, serta [TODO.md](../TODO.md), [detail/TODO.md](TODO.md), dan [CHANGELOG.md](../CHANGELOG.md) telah diperbarui.
