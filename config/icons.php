<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Katalog Ikon Resmi INFORA (100% Free & Open Source - Lucide Icons)
    |--------------------------------------------------------------------------
    |
    | Seluruh ikon di bawah ini berasal dari keluarga Lucide Icons berlisensi
    | open-source (ISC License), bebas digunakan tanpa lisensi berbayar.
    | Ikon dirender secara native berbasis SVG oleh komponen Blade <x-icon>.
    |
    */

    'categories' => [
        'all' => 'Semua Ikon',
        'navigasi' => 'Navigasi & Tampilan',
        'akademik' => 'Sekolah & Akademik',
        'data' => 'Data & Arsip',
        'pengguna' => 'Pengguna & SDM',
        'sistem' => 'Sistem & Keamanan',
        'umum' => 'Komunikasi & Umum',
    ],

    'icons' => [
        // Navigasi & Tampilan
        [
            'name' => 'compass',
            'label' => 'Kompas / Penjelajah',
            'category' => 'navigasi',
            'keywords' => 'kompas penjelajah arah navigasi modul peta',
        ],
        [
            'name' => 'layers',
            'label' => 'Layer / Modul Sistem',
            'category' => 'navigasi',
            'keywords' => 'layer modul tumpukan paket sistem kategori',
        ],
        [
            'name' => 'menu',
            'label' => 'Menu Bar Navigasi',
            'category' => 'navigasi',
            'keywords' => 'menu bar navigasi daftar opsi hamburger',
        ],
        [
            'name' => 'list-tree',
            'label' => 'Hierarki / Sub-Menu',
            'category' => 'navigasi',
            'keywords' => 'pohon hierarki sub-menu cabang relasi tree',
        ],
        [
            'name' => 'layout-dashboard',
            'label' => 'Dashboard Utama',
            'category' => 'navigasi',
            'keywords' => 'dashboard beranda ringkasan statistik panel utama',
        ],
        [
            'name' => 'grid',
            'label' => 'Grid Aplikasi',
            'category' => 'navigasi',
            'keywords' => 'grid kotak menu kumpulan aplikasi modul',
        ],

        // Sekolah & Akademik
        [
            'name' => 'school',
            'label' => 'Gedung Sekolah',
            'category' => 'akademik',
            'keywords' => 'sekolah gedung madrasah kampus instansi pendidikan',
        ],
        [
            'name' => 'graduation-cap',
            'label' => 'Topi Toga / Kelulusan',
            'category' => 'akademik',
            'keywords' => 'toga kelulusan alumni wisuda akademik jenjang',
        ],
        [
            'name' => 'book-open',
            'label' => 'Buku Terbuka / Kurikulum',
            'category' => 'akademik',
            'keywords' => 'buku terbuka kurikulum pelajaran materi baca silabus',
        ],
        [
            'name' => 'book',
            'label' => 'Buku / Modul Belajar',
            'category' => 'akademik',
            'keywords' => 'buku modul perpustakaan literatur referensi',
        ],
        [
            'name' => 'award',
            'label' => 'Prestasi & Nilai',
            'category' => 'akademik',
            'keywords' => 'prestasi penghargaan medali piagam sertifikat nilai raport ranking',
        ],
        [
            'name' => 'calculator',
            'label' => 'Kalkulator / Hitung Nilai',
            'category' => 'akademik',
            'keywords' => 'kalkulator hitung nilai matematika statistik bobot',
        ],

        // Data & Arsip
        [
            'name' => 'database',
            'label' => 'Basis Data / Master Data',
            'category' => 'data',
            'keywords' => 'database basis data master penyimpanan data server sql tabel',
        ],
        [
            'name' => 'table',
            'label' => 'Tabel Data / Rekapitulasi',
            'category' => 'data',
            'keywords' => 'tabel data baris kolom matriks rekap spreadsheet excel',
        ],
        [
            'name' => 'folder',
            'label' => 'Folder Berkas',
            'category' => 'data',
            'keywords' => 'folder direktori berkas arsip dokumen kelompok',
        ],
        [
            'name' => 'file-text',
            'label' => 'Dokumen / Laporan',
            'category' => 'data',
            'keywords' => 'dokumen laporan lembaran surat teks formulir cetak pdf',
        ],
        [
            'name' => 'clipboard-list',
            'label' => 'Catatan / Checklist',
            'category' => 'data',
            'keywords' => 'catatan checklist tugas inventaris agenda presensi daftar',
        ],
        [
            'name' => 'calendar',
            'label' => 'Kalender / Jadwal',
            'category' => 'data',
            'keywords' => 'kalender jadwal waktu agenda tanggal kegiatan semester',
        ],
        [
            'name' => 'building',
            'label' => 'Fasilitas & Sarpras',
            'category' => 'data',
            'keywords' => 'sarana prasarana gedung ruangan fasilitas kelas laboratorium',
        ],

        // Pengguna & SDM
        [
            'name' => 'users',
            'label' => 'Grup Siswa / Pengguna',
            'category' => 'pengguna',
            'keywords' => 'pengguna siswa rombel santri murid kelompok orang tim',
        ],
        [
            'name' => 'user',
            'label' => 'Pengguna Tunggal / Profil',
            'category' => 'pengguna',
            'keywords' => 'pengguna user akun profil biodata individu',
        ],
        [
            'name' => 'user-check',
            'label' => 'Verifikasi / Presensi',
            'category' => 'pengguna',
            'keywords' => 'presensi hadir absensi verifikasi konfirmasi ceklis izin',
        ],
        [
            'name' => 'user-plus',
            'label' => 'Registrasi / Siswa Baru',
            'category' => 'pengguna',
            'keywords' => 'ppdb registrasi daftar tambah siswa baru pendaftaran calon',
        ],
        [
            'name' => 'briefcase',
            'label' => 'Pegawai / Guru / Tenaga Pendidik',
            'category' => 'pengguna',
            'keywords' => 'guru tenaga pendidik staf karyawan tata usaha pegawai sdm',
        ],

        // Sistem & Keamanan
        [
            'name' => 'shield-check',
            'label' => 'Keamanan & Hak Akses',
            'category' => 'sistem',
            'keywords' => 'keamanan hak akses permission proteksi role peran firewall aman',
        ],
        [
            'name' => 'shield',
            'label' => 'Perlindungan Sistem',
            'category' => 'sistem',
            'keywords' => 'tameng pelindung proteksi keamanan enkripsi',
        ],
        [
            'name' => 'settings',
            'label' => 'Pengaturan Konfigurasi',
            'category' => 'sistem',
            'keywords' => 'pengaturan konfigurasi setting opsi gear preferensi kontrol',
        ],
        [
            'name' => 'sliders',
            'label' => 'Parameter Sistem',
            'category' => 'sistem',
            'keywords' => 'parameter tuning pengaturan filter opsi opsi-lanjutan',
        ],
        [
            'name' => 'key',
            'label' => 'Kunci / Token Otorisasi',
            'category' => 'sistem',
            'keywords' => 'kunci token api akses otentikasi lisensi sandi',
        ],
        [
            'name' => 'lock',
            'label' => 'Kunci Gembok',
            'category' => 'sistem',
            'keywords' => 'gembok privasi rahasia kunci proteksi keamanan',
        ],
        [
            'name' => 'activity',
            'label' => 'Log Aktivitas / Audit',
            'category' => 'sistem',
            'keywords' => 'log aktivitas detak audit riwayat pemantauan monitor',
        ],
        [
            'name' => 'bar-chart',
            'label' => 'Statistik & Grafik',
            'category' => 'sistem',
            'keywords' => 'statistik grafik performa visualisasi data metrik diagram',
        ],

        // Komunikasi & Umum
        [
            'name' => 'bell',
            'label' => 'Notifikasi & Pengumuman',
            'category' => 'umum',
            'keywords' => 'lonceng notifikasi pengumuman pemberitahuan info peringatan',
        ],
        [
            'name' => 'mail',
            'label' => 'Surel / Kotak Pesan',
            'category' => 'umum',
            'keywords' => 'surat surel email pesan inbox kirim korespondensi',
        ],
        [
            'name' => 'credit-card',
            'label' => 'Keuangan / Pembayaran SPP',
            'category' => 'umum',
            'keywords' => 'keuangan spp bayar cicilan kas tagihan transaksi biaya invoice',
        ],
        [
            'name' => 'globe',
            'label' => 'Portal Publik / Web',
            'category' => 'umum',
            'keywords' => 'portal website situs publik internet online domain',
        ],
        [
            'name' => 'help-circle',
            'label' => 'Pusat Bantuan / FAQ',
            'category' => 'umum',
            'keywords' => 'bantuan panduan tanya jawab faq dokumen tutorial informasi',
        ],
        [
            'name' => 'sparkles',
            'label' => 'Fitur Unggulan / AI',
            'category' => 'umum',
            'keywords' => 'unggulan bintang baru kecerdasan kilau rekomendasi',
        ],
    ],
];
