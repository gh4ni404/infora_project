# Codex conversation

## User

halo saya mau bertanya nih, pada modul akademik di sistem informasi sekolah itu biasanya punya menu apa saja ya? tolong lakukan riset terlebih dahulu berbasis kebutuhan akreditasi sekolah yang baik

## Assistant

Saya akan menelusuri instrumen akreditasi sekolah yang berlaku di Indonesia, lalu memetakan kebutuhan buktinya ke menu modul akademik. Saya juga akan membedakan menu inti dengan fitur pendukung agar hasilnya bisa dipakai untuk merancang sistem.

## Assistant

Untuk cakupan **SD, SMP, SMA, SMK, MI, MTs, dan MA, termasuk sekolah swasta**, saya menyarankan **12 kelompok menu akademik**, ditambah fitur khusus sesuai jenjang.

Saya sudah menelaah sumber resmi BAN-PDM: [Kepmen 246/O/2024 tentang Instrumen Akreditasi](https://ban-pdm.id/documents/download/1720674890_81659f281e6bee599f2a.pdf), [Panduan Penjelasan IA2024 versi 2025](https://ban-pdm.id/documents/download/1756890580_7aed8e14ada8b94fca13.pdf), panduan khusus SMK/MAK, serta panduan pengajuan akreditasi ulang 2026.

Temuan utamanya: **sistem perlu membantu sekolah menunjukkan hubungan antara rencana pembelajaran, pelaksanaan, hasil belajar, dan perbaikan yang dilakukan.** Akreditasi memeriksa kinerja melalui dokumen, wawancara, dan observasi. Daftar menu berikut adalah rekomendasi desain berdasarkan kebutuhan tersebut.

Acuan akreditasinya mencakup empat area:

1. **Kinerja pendidik:** pembelajaran yang berpusat pada murid, asesmen, umpan balik, dan dukungan belajar.
2. **Kepemimpinan kepala sekolah/madrasah:** pengembangan kurikulum, refleksi, evaluasi, serta pengelolaan sumber daya.
3. **Iklim lingkungan belajar:** kebinekaan, inklusivitas, keamanan, keselamatan, dan kesehatan.
4. **Kompetensi hasil pembelajaran:** perkembangan kompetensi murid dan lulusan.

Menu yang saya rekomendasikan:

| Menu utama | Submenu atau fungsi penting | Manfaat untuk mutu dan akreditasi |
|---|---|---|
| **1. Dashboard Akademik** | Agenda, ringkasan kehadiran, perkembangan belajar, siswa yang memerlukan pendampingan | Membantu pimpinan dan guru menentukan prioritas tindakan |
| **2. Pengaturan Akademik** | Tahun ajaran, semester, jenjang, mata pelajaran, alokasi jam, kebijakan penilaian | Menjadi dasar penyelenggaraan akademik yang konsisten |
| **3. Kelas dan Penugasan Guru** | Rombel, anggota kelas, wali kelas, guru pengampu, pembagian tugas mengajar | Memperjelas layanan belajar dan tanggung jawab guru |
| **4. Kalender dan Jadwal** | Kalender pendidikan, jadwal pelajaran, asesmen, praktikum, kegiatan akademik | Menghubungkan program yang direncanakan dengan pelaksanaannya |
| **5. Kurikulum dan Perencanaan Pembelajaran** | Kurikulum satuan pendidikan, capaian/tujuan pembelajaran, alur atau silabus, rencana pembelajaran, bahan ajar | Menunjukkan keselarasan tujuan, kegiatan, dan asesmen |
| **6. Jurnal Pembelajaran** | Materi dan kegiatan yang terlaksana, metode, kendala, refleksi guru, dokumentasi | Mencatat praktik pembelajaran dan bahan evaluasinya |
| **7. Presensi** | Kehadiran harian atau per pelajaran, izin, keterlambatan, rekap, tindak lanjut ketidakhadiran | Membantu mengenali hambatan partisipasi belajar |
| **8. Asesmen dan Portofolio** | Asesmen awal, formatif, sumatif, rubrik, nilai, umpan balik, karya siswa | Menunjukkan perkembangan kompetensi beserta bukti pencapaiannya |
| **9. Pendampingan Belajar** | Remedial, pengayaan, penyesuaian pembelajaran, dukungan kebutuhan belajar, evaluasi tindak lanjut | Membuktikan bahwa hasil asesmen digunakan untuk membantu murid |
| **10. Rapor dan Keputusan Akademik** | Rapor deskriptif, catatan wali kelas, komunikasi hasil belajar, kenaikan kelas, kelulusan | Menyampaikan perkembangan murid dan rekomendasi pendampingan |
| **11. Kokurikuler, Ekstrakurikuler, dan Prestasi** | Program, peserta, pembimbing, kegiatan, karya, perkembangan kompetensi dan karakter | Mendokumentasikan pengalaman belajar di luar pelajaran reguler |
| **12. Supervisi dan Evaluasi Mutu Akademik** | Observasi pembelajaran, umpan balik, refleksi guru, evaluasi kurikulum, analisis hasil belajar, rencana perbaikan | Mendukung kepemimpinan pembelajaran dan perbaikan berkelanjutan |

**Menu asesmen, pendampingan, rapor, dan supervisi perlu saling terhubung.** Misalnya, ketika asesmen menunjukkan sebagian murid belum memahami materi, guru bisa mencatat pendampingan, menilai kembali perkembangannya, lalu menggunakan hasilnya dalam refleksi pembelajaran.

Dalam panduan 2025, laporan hasil belajar dikomunikasikan kepada murid dan orang tua **minimum sekali per semester**. Laporan yang baik memuat perkembangan, umpan balik, dan rekomendasi pendampingan. Karena itu, menu rapor sebaiknya juga mencatat komunikasi dan tindak lanjut.

Ada beberapa penyesuaian untuk cakupan sekolah Anda:

| Jenis sekolah | Penyesuaian yang disarankan |
|---|---|
| **SD/MI** | Mendukung pengelolaan guru kelas serta perkembangan kemampuan dasar murid |
| **SMP/MTs dan SMA/MA** | Mendukung guru mata pelajaran, pilihan pelajaran/program sesuai kurikulum, serta pendampingan studi lanjutan |
| **SMK** | Tambahkan program/konsentrasi keahlian, PKL, pembimbing industri, pembelajaran berbasis industri, uji kompetensi, sertifikasi, dan penelusuran lulusan |
| **Madrasah** | Sesuaikan struktur mata pelajaran, program keagamaan, format rapor, dan kebutuhan pelaporan Kemenag |
| **Sekolah swasta** | Menu inti tetap sama; dukung program akademik khas yayasan dan evaluasinya bila ada |

Tambahan SMK memiliki dasar yang kuat dalam [Panduan IA2024 versi 2025 untuk SMK/MAK](https://ban-pdm.id/documents/download/1760361959_819fe74bbb57f26f989c.pdf), yang membahas relevansi pembelajaran dengan dunia kerja, portofolio kompetensi, serta kondisi lulusan yang bekerja, berwirausaha, atau melanjutkan pendidikan.

Saya juga menyarankan **menu “Mutu dan Akreditasi” yang mengambil bukti dari seluruh modul sekolah**. Isinya dapat mencakup pemetaan bukti ke instrumen, Deskripsi Kinerja Asesi, evaluasi diri, temuan asesor, dan tindak lanjut perbaikan.

Dokumen utama yang disebut dalam [Panduan Asesi 2025](https://ban-pdm.id/documents/download/1756911475_02a67651b220740f28b9.pdf) antara lain kurikulum satuan pendidikan, rencana kerja tahunan, rencana kegiatan dan anggaran, kalender akademik, contoh rencana pembelajaran, serta dokumentasi lingkungan belajar. Bukti anggaran, sarpras, kesehatan, dan keamanan perlu berasal dari modul terkait.

**Rapor siswa juga perlu dibedakan dari Rapor Pendidikan.** Rapor siswa menjelaskan perkembangan individu; Rapor Pendidikan memuat indikator mutu satuan pendidikan. Untuk SD/MI, SMP/MTs, dan SMA/MA, instrumen menyebut komponen hasil pembelajaran diperhitungkan dari analisis Asesmen Nasional. Nilai rapor internal tetap berguna sebagai bahan evaluasi sekolah.

Untuk pembangunan bertahap, prioritaskan pengaturan akademik, kelas, jadwal, perencanaan, jurnal, presensi, asesmen, pendampingan, dan rapor. Setelah itu, lengkapi supervisi, analisis mutu, dan pengelolaan bukti. **Riwayat perbaikan antartahun perlu disimpan**, karena [panduan pengajuan akreditasi ulang 2026](https://ban-pdm.id/documents/download/1779253891_0692da8bfb84f682ebf2.pdf) meminta sekolah menjelaskan peningkatan kinerja sejak visitasi terakhir beserta bukti pendukungnya.
