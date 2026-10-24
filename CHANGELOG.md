# 📝 Changelog

Semua perubahan penting pada proyek **INFORA (Information Network for Organization, Records, dan Accreditation)** akan dicatat dalam berkas ini.

Format berkas ini mengacu pada [Keep a Changelog](https://keepachangelog.com/id/1.1.0/), dan proyek ini mematuhi [Semantic Versioning (SemVer)](https://semver.org/lang/id/).

---

## [Unreleased]

### Added dan Improved (Master Data Tingkatan Sekolah, Desain Empty-State Terpusat & Standardisasi Komponen UI)
- **Master Data Tingkatan Kelas (`master/data-tingkatan`):**
  - *CRUD Penjenjangan Kelas Multi-Tenancy:* Mengimplementasikan pengelolaan penjenjangan tingkatan kelas (X, XI, XII / 1-13) berbasis unit sekolah (`school_id`), pemetaan Fase Kurikulum Merdeka (Fase E, Fase F, dll.), serta integrasi mutator teks otomatis (`UPPERCASE` untuk notasi kode dan `Title Case` untuk nama serta fase).
  - *Skema Basis Data & Integritas Tenancy:* Membuat migrasi `tingkatan` dengan indeks unik majemuk per unit sekolah (`['school_id', 'kode']` dan `['school_id', 'tingkat_angka']`) serta scope query bawaan `ordered()` (`tingkat_angka ASC`).
  - *Kartu Metrik Ringkasan (KPI Cards):* Menghadirkan 4 kartu indikator metrik berbasis `<x-stats-grid>` dan `<x-stat-card>`: Total Tingkatan, Tingkatan Aktif, Non-Aktif, dan Total Fase Kurikulum.
  - *Aksi Cepat & Modal Modular:* Toggle status keaktifan 1-klik (`POST /master/data-tingkatan/{tingkatan}/toggle-status`), antarmuka modal modular (`create.blade.php`, `edit.blade.php`) berbasis `<x-modal>`, dan dialog konfirmasi hapus universal.
  - *Pengujian Otomatis Komprehensif:* Menambahkan 16 skenario feature test di `tests/Feature/TingkatanTest.php` (mencakup proteksi otentikasi tamu, otorisasi RBAC, isolasi multi-sekolah, validasi keunikan berjenjang, toggle status, dan operasi CRUD).

### Fixed dan Refactored (Audit Kepatuhan Blade Components, Eliminasi Raw HTML Input & Overengineering)
- **Standardisasi Ekosistem Komponen Blade UI & Eliminasi Raw HTML Input:**
  - *Pemusatan Vertikal dan Horizontal Empty State Tabel:* Memperbarui styling CSS pada `resources/css/app.css` (`.table-card:has(.empty-state) .table-responsive`, `.data-table:has(.empty-state)`, dan `.data-table .empty-state`) untuk memastikan pesan kosong beserta ikonnya berada tepat di tengah area tabel baik secara vertikal maupun horizontal.
  - *Perapian Komponen `<x-empty-state>`:* Membersihkan redundansi kelas `empty-state-icon` pada SVG internal dan menyederhanakan evaluasi slot aksi menjadi `@if ($action || (isset($slot) && $slot->isNotEmpty()))`.
  - *Eliminasi Raw HTML `<input type="hidden">`:* Mengganti seluruh raw input tersembunyi manual pada modal Tambah/Ubah Data GTK (`create.blade.php`, `edit.blade.php`), Data Tingkatan (`index.blade.php`), Data Jurusan (`create.blade.php`, `index.blade.php`), Data Sekolah (`create.blade.php`, `edit.blade.php`), Tab Pengaturan Profil Pengguna (`personal`, `employment`, `contact`), Cadangan & Pemulihan Sistem, Kalender Akademik, Filter Navigasi Akses Menu, dan Komponen Modal Universal (`components/modal.blade.php`) ke komponen resmi `<x-form-input type="hidden">`.
  - *Standardisasi Komponen `<x-alert>` pada Validasi PTK:* Mengganti raw alert manual pada `master/data-gtk/edit.blade.php` dengan komponen resmi `<x-alert type="danger">`.
  - *Refactoring `UpdateTingkatanRequest`:* Mengeliminasi kode overengineering, parameter guessing (`?? $this->route('data_tingkatan')`), dan defensive programming berlebih pada pengambilan model instance tingkatan dari route parameter.
  - *Sinkronisasi Aturan Katalog Komponen:* Memperbarui aturan `.agents/rules/standard-blade-components-ecosystem.md` dari 22 menjadi 25 komponen resmi dengan mencantumkan `<x-file-upload>`, `<x-status-banner>`, dan `<x-crud-button>`.
  - *Ekspansi Test Suite Keseluruhan:* Total test suite aplikasi terverifikasi 100% lulus dengan **371 tests passed, 1.728 assertions**.

### Added dan Improved (Tata Kelola Menu Akses, Onboarding Cepat GTK & Pengisian Mandiri Profil)
- **Pembaruan Tata Kelola Menu Akses & Otorisasi Pengguna (`system/menu-access`):**
  - *Dukungan Filter Peran Staf (Tendik):* Menambahkan tab filter peran `staf` pada daftar pengguna tata kelola menu akses, memungkinkan Super Admin memfilter khusus akun tenaga kependidikan (TU, laboran, pustakawan, dll).
  - *Aktivasi Cepat Akun Nonaktif:* Menambahkan tombol aksi aktifkan akun langsung dari baris tabel (`PATCH /sistem/user/{user}/activate`) untuk akun nonaktif, mempermudah onboarding pengguna baru.
  - *Indikator Status Akun:* Menambahkan kolom Status Akun dengan badge semantik (`badge-success` untuk Aktif, `badge-neutral` untuk Nonaktif).
  - *Standardisasi Ekosistem Komponen UI Blade:* Merefaktor seluruh antarmuka `resources/views/system/menu-access/index.blade.php` ke komponen resmi sistem: `<x-page-header>`, `<x-tab-pills>`, `<x-tab-pill>`, `<x-data-table>`, dan `<x-pagination-summary>`, mengeliminasi raw markup tabel bespoke.
- **Penyederhanaan Onboarding GTK Cepat & Pengisian Mandiri Profil (*Quick Onboarding & Self-Service Completion*):**
  - **Latar Belakang & Identifikasi Kebutuhan:**
    - Sebelumnya, berkas template impor Excel memuat 17 kolom mendalam (gelar, tempat/tanggal lahir, agama, riwayat pendidikan, alamat domisili teks, dll.). Hal ini membebani administrator sekolah sebagai "juru ketik" massal dan rentan galat format (misal format tanggal Excel serial vs string, serta alamat teks yang tidak terhubung dengan foreign key 4-tingkat wilayah Kemendagri `provinsi_id`, `kabupaten_id`, `kecamatan_id`, `kelurahan_id`).
  - **Solusi & Implementasi Teknis:**
    1. *Template Esensial 6 Kolom (`GtkController@template`):* Memangkas kolom pendaftaran template impor menjadi 6 kolom pokok: **Nama Lengkap\***, **Jenis Kelamin (L/P)\***, **Status Kepegawaian\***, **Jenis PTK (pisahkan koma jika jamak)\***, **NIP**, **NIK**, **No HP / WhatsApp**, dan **Email**.
    2. *Preservasi Validasi Dropdown & Multi-Sheet:* Mempertahankan validasi Excel Data Validation dropdown untuk `Jenis Kelamin` (`L,P`) dan `Status Kepegawaian` resmi, serta lembar kerja Sheet 2 `[Panduan & Referensi]` yang memuat kamus penugasan pendidik dan tendik.
    3. *Pengisian Mandiri via Pengaturan Profil:* Membagi tanggung jawab secara optimal: Admin mendaftarkan identitas pokok dan hak akses, sementara biodata mendalam (pasfoto, riwayat pendidikan/gelar, tanggal lahir, dan domisili terhubung wilayah Kemendagri) dilengkapi secara mandiri oleh masing-masing guru melalui menu **Pengaturan Profil**.
    4. *Preservasi Kompatibilitas Mundur (Backward Compatibility):* Parser `GtkController@import` tetap mendukung pengunggahan berkas 17-kolom lama secara transparan tanpa galat atau *undefined index warning*.
    5. *Pembaruan Antarmuka Modal Dialog Impor:* Memperbarui panduan `:guidelines` pada `resources/views/master/data-gtk/import.blade.php` berbasis `<x-modal name="import">` dan dropzone universal `<x-file-upload>`.
    6. *Perampingan Modal Tambah GTK & Otomasi Kredensial dari Nama Lengkap (`resources/views/master/data-gtk/create.blade.php`):* Menyederhanakan formulir modal penambahan GTK baru menjadi hanya memuat 4 data pokok: **Nama Lengkap**, **Jenis Kelamin**, **Status Kepegawaian**, dan **Jenis PTK (Penugasan)**. Menghapus input manual username dan password; sistem otomatis meng-generate username dan kata sandi awal dari nama lengkap (`Str::slug($nama_lengkap, '')`, e.g. `budisantoso`), serta mengintegrasikannya ke `User::needsProfileCompletion()` untuk mewajibkan guru memperbarui kata sandi dan melengkapi biodata (NIP, NIK, kontak, gelar, dan domisili Kemendagri) via menu **Pengaturan Profil**.
    7. *Eliminasi Inline Styles & Kode Spekulatif:* Menghapus seluruh inline styles pada kartu peran `edit.blade.php` dialihkan ke utilitas token CSS terpusat `.ptk-role-card` dan `.ptk-role-scroll` di `app.css`, memangkas event listener dead code wilayah pada `index.blade.php`, membersihkan query tidak terpakai (`Provinsi::query()`) pada `GtkController@index`, serta memangkas varian modal export spekulatif pada `<x-modal>`.
    8. *Ekspansi Test Suite:* Memperbarui `tests/Feature/GtkImportExportTest.php`, `tests/Feature/GtkTest.php`, `tests/Feature/ProfileTest.php`, dan `tests/Feature/System/MenuAccessTest.php`. Total test suite aplikasi meningkat menjadi **355 tests passed, 1.668 assertions (100% lulus)**.

### Fixed dan Refactored (Audit Kepatuhan Komponen, Arsitektur Kredensial GTK & Standardisasi Wilayah)
- **Audit Standarisasi Komponen & Kepatuhan Arsitektur UI pada Master Data GTK (`master/data-gtk`):**
  - **Kesalahan / Gap yang Ditemukan:**
    1. *Pelanggaran Strict No Inline Styles:* Ditemukan atribut inline `style="..."` pada elemen tombol dan form di `resources/views/master/data-gtk/index.blade.php`, yang melanggar prinsip desain global INFORA.
    2. *Bypassing Ekosistem Komponen Form:* Penggunaan elemen `<select>` native biasa untuk filter jenis kelamin, status kepegawaian, dan status aktif alih-alih memanfaatkan komponen resmi `<x-form-select>` yang telah tersedia.
    3. *Desinkronisasi Foreign Key Wilayah Kemendagri:* Skema tabel `gtks` telah memiliki 4 foreign key wilayah Kemendagri (`provinsi_id`, `kabupaten_id`, `kecamatan_id`, `kelurahan_id`), namun form input modal Tambah dan Edit sebelumnya hanya menyediakan sebuah `<textarea name="alamat">`, sehingga mengabaikan modul client-side `window.InforaWilayah`.
    4. *Bespoke Modals & Pelanggaran Standar Konfirmasi:* Dialog konfirmasi aksi Reset Password dan Toggle Status Aktif dibuat secara manual (*bespoke*) dengan raw HTML `<div class="modal">` dan injeksi manipulasi `.innerHTML` manual via JavaScript, alih-alih memanfaatkan komponen reusable `<x-modal name="confirm">` dan helper universal `InforaModals.bindConfirmButtons`.
    5. *Bypassing Otorisasi Tombol RBAC:* Tombol aksi preview profil GTK menggunakan tag `<button>` biasa alih-alih komponen otorisasi `<x-crud-button action="view">`.
    6. *Kelas CSS Legacy Bootstrap:* Tampilan detail profil GTK (`show.blade.php`) menggunakan kelas utilitas Bootstrap (`d-flex`, `align-items-center`, `small`, dll.) yang tidak standar dengan CSS tokens INFORA / Tailwind.
    7. *Bentrokan Rute (Route Collision):* Rute alias `Route::get('data-gtk/semua', ...)->name('data-gtk.index')` dideklarasikan setelah `Route::resource('data-gtk', ...)`, menyebabkan rute `data-gtk/semua` tertelan oleh wildcard `{gtk}` dari resource route dan menghasilkan galat 404 (ModelNotFoundException).
  - **Bagaimana Memperbaikinya (Solusi dan Implementasi Teknis):**
    1. *Eliminasi 100% Inline Styles:* Menghapus seluruh inline styles dan menggantinya dengan utilitas Tailwind dan class tokens pada `resources/css/app.css`.
    2. *Migrasi ke `<x-form-select>` & Penambahan Alias `:value`:* Mengganti seluruh `<select>` biasa dengan `<x-form-select>`, serta menambahkan alias prop `:value` pada `form-select.blade.php` agar kompatibel dan konsisten dengan penulisan `<x-searchable-select>`.
    3. *Integrasi Cascading Wilayah 4-Tingkat Kemendagri:* Memasang `<x-searchable-select>` untuk Provinsi, Kabupaten/Kota, Kecamatan, dan Kelurahan/Desa pada `create.blade.php` dan `edit.blade.php`. Mengikat alur berantai secara dinamis melalui `window.InforaWilayah.bindCascade`, mengisi otomatis data saat edit via `populateCascade`, serta memulihkan data gagal validasi via `restoreOnError`.
    4. *Penyediaan Data Wilayah & Eager Loading Bebas N+1:* Menyediakan `$provinsiList` pada `GtkController@index` dan menerapkan eager loading relasi `['provinsi', 'kabupaten', 'kecamatan', 'kelurahan']` pada controller untuk performa query optimal.
    5. *Accessor Alamat Lengkap (`alamat_lengkap`):* Menambahkan accessor `getAlamatLengkapAttribute()` pada Model `Gtk` yang menyusun alamat domisili lengkap dan rapi (Alamat, Desa/Kelurahan, Kecamatan, Kabupaten/Kota, Provinsi).
    6. *Standarisasi Modal Konfirmasi Universal:* Menggantikan modal bespoke dengan komponen resmi `<x-modal name="confirm">` untuk `modalResetPasswordGtk` dan `modalToggleStatusGtk`, diikat rapi melalui `InforaModals.bindConfirmButtons`.
    7. *Standarisasi Tombol Preview Profil:* Menggantikan tombol biasa dengan `<x-crud-button action="view">` yang terintegrasi matriks perizinan RBAC.
    8. *Sanitasi CSS Tokens:* Membersihkan class Bootstrap legacy pada `show.blade.php` menjadi utility classes standar Tailwind/INFORA (`flex`, `items-center`, `text-sm`, dll.).
    9. *Penataan Urutan Rute Bebas Collision:* Memindahkan deklarasi rute eksplisit `Route::get('data-gtk/semua', ...)->name('data-gtk.index')` tepat **sebelum** resource route `Route::resource('data-gtk', ...)` sehingga URL `/master/data-gtk/semua` dikenali dengan sempurna.

- **Unifikasi & Perbaikan Arsitektur Kredensial Kata Sandi GTK (`App\Support\GtkAccountManager`):**
  - **Kesalahan / Gap yang Ditemukan:**
    1. *Pemotongan Spasi Sandi Kustom (Regex Bug):* Penggunaan regex agresif `preg_replace('/\s+/', '', $password)` secara keliru memotong karakter spasi yang sengaja dimasukkan pengguna pada kata sandi kustom (*passphrase*).
    2. *Pemeriksaan Truthy Lemah pada String Spasi:* Penggunaan evaluasi truthy biasa (`!empty($gtk->nip)`) berisiko memperlakukan string yang hanya berisi spasi kosong sebagai data valid alih-alih melewatinya.
    3. *Ketidakkonsistenan Fallback Password:* Adanya ketidaksinkronan nilai fallback password antara alur pembuatan akun baru (`createOrUpdateUserForGtk` menggunakan fallback `'infora123'`) dengan alur reset password (`resetPassword` menggunakan fallback `$user->username`), membingungkan pengguna jika GTK tidak memiliki NIP, NUPTK, maupun NIK.
    4. *Ketiadaan Informasi Kata Sandi yang Ditetapkan:* Saat administrator menambahkan GTK baru dengan mengosongkan kolom kata sandi, sistem tidak memberikan informasi password awal apa yang di-generate pada notifikasi flash.
  - **Bagaimana Memperbaikinya (Solusi dan Implementasi Teknis):**
    1. *Metode Arsitektur Terpusat `resolveDefaultPassword()`:* Membangun method terpadu `resolveDefaultPassword(Gtk $gtk, ?string $customPassword = null): string` pada `GtkAccountManager` sebagai single source of truth penetapan kata sandi akun GTK.
    2. *Preservasi Karakter Spasi Frasa Sandi:* Mengganti regex agresif dengan `trim()` murni pada kata sandi kustom, menjaga keutuhan karakter spasi yang sah di dalam kata sandi.
    3. *Evaluasi Ketat dengan Helper `filled()`:* Menggunakan helper bawaan Laravel `filled($gtk->nip)`, `filled($gtk->nuptk)`, dan `filled($gtk->nik)` untuk memastikan string kosong atau whitespace tidak diproses.
    4. *Hierarki Fallback Default Password Seragam:* Menetapkan hierarki penentuan kata sandi default secara tegas dan konsisten di seluruh alur: **NIP** ➔ **NUPTK** ➔ **NIK** ➔ `'infora123'`.
    5. *Feedback Transparan pada Flash Message:* Menambahkan pemberitahuan kata sandi default akun secara transparan pada flash notification saat berhasil menambahkan data GTK baru melalui `GtkController@store`.

- **Peningkatan Test Suite & Jaminan Kualitas:**
  - Seluruh pengujian otomatis Pest feature dan unit tests berjalan 100% sukses: **334 tests lulus tanpa kegagalan (1.515 assertions)**.
  - 100% kode terformat rapi sesuai panduan proyek dengan Laravel Pint (`vendor/bin/pint --dirty --format agent`).

### Added
- **Komponen Universal Pengunggahan Berkas (`<x-file-upload>`) & Elevasi UI/UX Modal Impor (`<x-modal name="import">`):**
  - **Latar Belakang & Identifikasi Masalah UI/UX:**
    - Sebelumnya, modal impor masih menggunakan elemen native browser `<input type="file">` standar yang kaku, memiliki tampilan tidak konsisten lintas peramban (*"Choose File No file chosen"*), serta tata letak panduan yang belum rapi.
  - **Solusi & Implementasi Teknis:**
    1. *Komponen Reusable `<x-file-upload>` (Komponen Blade ke-25):* Menyediakan area *drag-and-drop* berkas dokumen dan spreadsheet interaktif dengan deteksi event `dragover`/`dragleave`/`drop`, kartu pratinjau berkas terpilih (nama berkas, ukuran MB otomatis, badge status siap impor, dan tombol bersihkan berkas), serta arsitektur JavaScript *event delegation* yang sangat ringan, efisien, dan bebas overengineering.
    2. *Varian Modal Mandiri Bebas Slot (`<x-modal name="import">`):* Mengintegrasikan secara otomatis kartu unduh template resmi `.template-download-card`, komponen `<x-file-upload>`, injeksi input hidden unit sekolah (`:school-id`), dan kotak panduan berformat modern `.guideline-box` dengan bullet point terstandarisasi tanpa memerlukan slot tambahan (*slotless*).
    3. *Varian Modal Ekspor Terstandarisasi (`<x-modal name="export">`):* Menyediakan modal pemilih format (.xlsx atau .csv) dan catatan filter aktif secara deklaratif.
    4. *Kepatuhan Strict No Inline Styles:* Seluruh komponen menggunakan class CSS global terpusat pada `resources/css/app.css` (`.file-dropzone`, `.template-download-card`, `.guideline-box`) tanpa tag `<style>` maupun inline styles `style="..."`.
    5. *Pencegahan Freeze InforaProgress pada Unduh Berkas & Ekspor (Fix Stuck 70%):* Menambahkan atribut `data-no-progress` dan `download` pada tautan unduh template serta memperbarui interceptor global pada `resources/js/app.js` agar secara otomatis mengabaikan URL berkas (`/download`, `/template`, `/export`). Tindakan ini mencegah overlay progres memblokir layar karena respons unduhan binary attachment tidak memicu *unload* halaman oleh browser.
- **Layanan Universal Spreadsheet (`App\Services\SpreadsheetService`) & Fitur Impor / Ekspor Excel Data GTK (`master/data-gtk`):**
  - **Latar Belakang & Kebutuhan Arsitektur Global:**
    - Awalnya fitur impor dan ekspor direncanakan khusus untuk data GTK (`GtkSpreadsheetService`). Namun untuk menjaga efisiensi, konsistensi reusabilitas, dan kepatuhan arsitektur modular INFORA, layanan ini direkayasa menjadi layanan global universal `App\Services\SpreadsheetService`. Seluruh modul lain di masa mendatang (Data Siswa, Rombel, Nilai, Presensi, Pelanggaran, dll.) dapat langsung memanfaatkan satu mesin spreadsheet yang sama tanpa duplikasi kode (*DRY*).
  - **Fitur Unggulan Layanan Universal `SpreadsheetService`:**
    1. *Pembuatan Template Spreadsheet Universal (`template`):* Menghasilkan berkas `.xlsx` dengan styling profesional (Header Royal Blue `#1E3A8A` dengan teks putih tebal, *freeze panes* pada baris judul, *auto-fit column width* dengan margin kenyamanan baca, dan baris data contoh yang realistis).
    2. *Streaming Ekspor Terproteksi (`export`):* Mendukung ekspor format `.xlsx` dan `.csv` secara streaming langsung via `php://output` dengan header HTTP download yang tepat, menghemat penggunaan RAM pada server.
    3. *Proteksi Preservasi Teks Numerik Panjang:* Menggunakan penetapan tipe data teks eksplisit (`DataType::TYPE_STRING`) via `setCellValueExplicit()` untuk data pengenal numerik panjang seperti NIK (16 digit), NIP (18 digit), NUPTK (16 digit), dan nomor telepon. Ini mengeliminasi risiko bawaan Microsoft Excel / Google Sheets yang memotong digit atau mengonversinya menjadi format notasi ilmiah (`7.308E+15`).
    4. *Universal Robust Row Reader (`readRows`):* Pembaca berkas spreadsheet cerdas yang mendukung format `.xlsx`, `.xls`, dan `.csv` dengan slugifikasi otomatis nama kolom (menghilangkan spasi, karakter khusus, dan konversi ke snake_case), eliminasi baris kosong otomatis, serta proteksi sanitasi terhadap serangan *CSV / Spreadsheet Formula Injection* (`stripFormulaInjection` untuk karakter `=`, `+`, `-`, `@`, `\t`, `\r`).
  - **Fitur Impor, Ekspor, dan Unduh Template Data GTK (`master/data-gtk`):**
    1. *Unduh Template Excel GTK (`GET /master/data-gtk/template`):* Menyediakan berkas unduhan template resmi `template_import_gtk.xlsx` lengkap dengan baris contoh data nyata pendidik dan tenaga kependidikan.
    2. *Ekspor Data GTK Terfilter (`GET /master/data-gtk/export`):* Mendukung ekspor data GTK secara dinamis mengikuti query filter aktif (unit sekolah, kata kunci pencarian, jenis PTK, status kepegawaian, dan status keaktifan).
    3. *Impor Data GTK Massal (`POST /master/data-gtk/import`):*
       - Validasi form request terdedikasi `ImportGtkRequest` dengan batasan tipe berkas (`.xlsx`, `.xls`, `.csv`), ukuran maksimal 5MB, serta isolasi tenancy unit sekolah.
       - Logika *Upsert Cerdas*: Memperbarui data profil GTK yang telah ada berdasarkan kecocokan NIK atau NIP tanpa menduplikasi record.
       - Sinkronisasi Akun User Otomatis: Setiap data baru otomatis dibuatkan akun login via `GtkAccountManager` lengkap dengan resolusi username cerdas, template hak akses peran, dan hierarki kata sandi default terpadu.
       - Pesan feedback terperinci yang menginformasikan jumlah baris berhasil ditambahkan, jumlah data diperbarui, dan peringatan baris yang dilewati.
  - **Kepatuhan Komponen UI & Desain Sistem INFORA:**
    1. *Kepatuhan Strict Komponen CRUD Button:* Tombol "Import Excel" dan "Export Excel" pada toolbar `resources/views/master/data-gtk/index.blade.php` wajib dan telah mengimplementasikan komponen resmi `<x-crud-button action="create">` dengan atribut `icon="upload"` dan `icon="download"`.
    2. *Modal Dialog Impor Modern (`resources/views/master/data-gtk/import.blade.php`):* Komponen modal terstandarisasi dengan kartu aksi unduh template, dropzone interaktif unggah berkas, panduan pengisian kolom, dan tombol submit terotorisasi.
  - **Pencegahan Bentrokan Rute (Route Collision Fix):**
    - Mendeklarasikan rute eksplisit `data-gtk/template`, `data-gtk/export`, dan `data-gtk/import` tepat **sebelum** deklarasi `Route::resource('data-gtk', ...)` pada `routes/web.php`. Hal ini mencegah parameter URL `'template'` atau `'export'` terintersepsi oleh wildcard parameter `{gtk}` dari rute resource yang memicu galat 404 (`ModelNotFoundException`).
  - **Automated Test Suite Expansion:**
    - Membuat berkas pengujian fitur terpadu `tests/Feature/GtkImportExportTest.php` berisi 10 skenario pengujian komprehensif (29 assertions) mencakup autentikasi tamu, isolasi hak akses peran RBAC, unduh template, ekspor dengan dan tanpa filter, impor spreadsheet valid dengan pembuatan akun user otomatis, impor upsert pada NIK/NIP yang sudah ada, serta validasi berkas upload.
    - Total test suite aplikasi meningkat menjadi **344 tests lulus 100% tanpa kegagalan (1.544 assertions)**.
**
  - Mengimplementasikan halaman dedicated mandiri pada rute `/profile` (`profile.index`, `profile.update`, `profile.password.update`) untuk seluruh tipe pengguna (Super Admin, Admin Sekolah, Guru/GTK, Murid).
  - **Tata Letak 2-Kolom dengan Navigasi Vertikal (`.grid-split-sidebar` & `.tab-pills-vertical`):**
    - Sisi kiri: Mini Profile Card (avatar berukuran 72px, nama lengkap bergelar, username, badge tipe akun, dan unit sekolah) serta navigasi tab vertikal yang rapi.
    - Sisi kanan: Panel konten formulir yang beralih secara dinamis (*Informasi Pribadi*, *Klasifikasi & Pendidikan*, *Kontak & Domisili*, dan *Status Keaktifan*).
  - **Konsolidasi Formulir Informasi Pribadi & Kredensial:** Mengintegrasikan pengelolaan Foto Avatar profil, Identitas & Biodata Diri, Kredensial Username, serta Keamanan & Ubah Kata Sandi secara terpadu di tab *Informasi Pribadi*.
  - **Validasi Dirty-Form & Modal Konfirmasi Pindah Tab:** Mencegah kehilangan input data formulir yang belum disimpan saat berpindah tab dengan konfirmasi simpan atau abaikan.
  - **Single View dengan Sub-Komponen Modular (`resources/views/profile/partials/*`):** Mencegah duplikasi kode (*DRY*) dan menjaga struktur template tetap bersih (*zero bloat*).
  - **Deteksi Akun Belum Lengkap Secara Heuristik (`User::needsProfileCompletion()`):**
    - Mendeteksi secara otomatis apakah akun masih menggunakan kata sandi default sistem (`infora123`, `password`, `admin`, atau NIP/NUPTK/NIK pada akun GTK) atau belum melengkapi kontak dan foto profil tanpa memerlukan migrasi kolom baru.
  - **Dashboard Onboarding Alert Banner:** Menampilkan banner peringatan ramah di atas dashboard Super Admin dan Admin Sekolah lengkap dengan tombol aksi *"Perbarui Profil Sekarang"*.
  - **Proteksi Integritas Data & Sinkronisasi Relasional:**
    - Pembaruan kontak dan pasfoto otomatis menyinkronkan data profil [`Gtk`](app/Models/Gtk.php) terkait secara transparan.
  - **Alur Logout Otomatis Pasca Pembaruan Kata Sandi:** Mengakhiri sesi aktif secara aman dan mengarahkan kembali ke halaman login dengan flash message sukses setelah kata sandi berhasil diubah.
  - **Integrasi Dropup Sidebar:** Mengaktifkan tautan *Pengaturan Profile* dan *Ubah Password* di footer sidebar [`layouts/app.blade.php`](resources/views/layouts/app.blade.php) serta menampilkan foto avatar nyata jika tersedia.
  - **Pest Automated Feature Test Suite (`tests/Feature/ProfileTest.php`):** 18 skenario pengujian komprehensif, membawa total test suite melonjak menjadi **330 tests lulus 100% (1.493 assertions)**.
- **Master Data Pendidik dan Tenaga Kependidikan (GTK) / Guru dan Pegawai (Multi-Role `jenis_ptk` JSON Architecture):**
  - Mengimplementasikan CRUD lengkap data guru dan tenaga kependidikan pada rute `/master/data-gtk` (`master.data-gtk.*`).
  - **Arsitektur Peran Majemuk (*Multi-Role Duties*):** Kolom `jenis_ptk` bertipe JSON array pada tabel `gtks` (`['Guru Mapel', 'Wali Kelas', 'Pembimbing PKL']`) untuk mengakomodasi fleksibilitas tugas majemuk pendidik di sekolah tanpa beban kompleksitas tabel pivot.
  - **Model `Gtk` & Relasi Berjenjang:** Dilengkapi `SoftDeletes`, array casting untuk `jenis_ptk`, mutator nama bergelar otomatis (`nama_bergelar`), helper status penugasan (`is_pendidik`, `is_tendik`), scopes query (`scopeAktif`, `scopePendidik`, `scopeTendik`), serta relasi `school()` (`belongsTo`) dan `user()` (`belongsTo`).
  - **Sinkronisasi Akun Otomatis (`App\Support\GtkAccountManager`):**
    - Otomatisasi pembuatan dan pembaruan akun `User` yang tertaut dengan profil GTK.
    - Resolusi username hierarkis cerdas: NIP ➔ NUPTK ➔ NIK ➔ slug nama.
    - Pembuatan kredensial awal (default password) dan penugasan template hak akses otomatis (`MenuAccessTemplate` Peran Guru/Pegawai).
    - Aksi cepat 1-klik: Reset Password (`POST /master/data-gtk/{gtk}/reset-password`) dan Toggle Status Aktif terpadu (`POST /master/data-gtk/{gtk}/toggle-status`).
    - Penonaktifan akun user secara otomatis saat profil GTK dihapus (soft delete).
  - **Antarmuka Responsif & Interaktif di `resources/views/master/data-gtk/`:**
    - Kartu metrik KPI (Total GTK, Pendidik/Guru, Tenaga Kependidikan/Tendik, dan GTK Aktif).
    - Toolbar filter komprehensif: pemilih unit sekolah (`schoolFilterForm`), pencarian teks bebas (nama, NIP, NUPTK, NIK), filter Jenis PTK multi-role, Status Kepegawaian, dan Status Aktif.
    - Tabel data modern dengan badge chips peran majemuk (`+N lagi`), tombol aksi terotorisasi, dan dialog konfirmasi hapus aman.
    - Dialog modal Create dan Edit terstruktur dengan pengelompokan penugasan (Tugas Pendidik vs Tugas Tenaga Kependidikan) dan form credentials.
    - Dialog modal Preview Profil GTK interaktif via endpoint JSON `GET /master/data-gtk/{gtk}`.
    - Integrasi komponen unggah pasfoto `<x-image-upload>` dan standardisasi service `ImageUploader`.
  - **Pest Feature Test Suite (`tests/Feature/GtkTest.php`):** 12 skenario pengujian komprehensif mencakup autentikasi, isolasi tenancy unit sekolah, operasi CRUD, validasi data, mutator, toggle status, reset password, dan soft delete.
  - **Ekspansi Test Suite: 312 Tests Lolos 100% (1.400 Assertions):** Total pengujian otomatis Pest meningkat menjadi **312 tests (1.400 assertions) lulus 100% tanpa risiko/kegagalan**.
- **Modul Client-Side Wilayah Terpadu (`resources/js/infora-wilayah.js` / `window.InforaWilayah`):**
  - Mengimplementasikan modul mandiri berkinerja tinggi untuk mengelola *cascading dropdown* wilayah administratif Indonesia 4 tingkat (Provinsi ➔ Kabupaten/Kota ➔ Kecamatan ➔ Kelurahan/Desa ➔ Kode Pos).
  - Menyediakan *in-memory cache* untuk mereduksi request HTTP jaringan ke server pada data wilayah yang statis.
  - Menstandarkan event binding (`bindCascade`), populasi berantai modal edit (`populateCascade`), dan pemulihan opsi error validasi (`restoreOnError`).
  - Mengurangi 274 baris kode boilerplate di `master/data-sekolah/index.blade.php`, serta mempercepat fetch pada modul Master Wilayah Kecamatan dan Kelurahan.
- **Master Data Jurusan / Konsentrasi Keahlian dan Peminatan (SMA, SMK, dan MA):**
  - Mengimplementasikan CRUD lengkap data jurusan dan program peminatan pada rute `/master/data-jurusan` (`master.data-jurusan.*`).
  - Mendukung struktur program keahlian lintas jenjang: Konsentrasi Keahlian (SMK), Peminatan/Fase Kurikulum Merdeka (SMA), dan Program Keagamaan/Peminatan (Madrasah Aliyah - MA).
  - Isolasi data per unit sekolah dengan filter pemilih unit (`schoolFilterForm`).
  - Tombol toggle status aktif/non-aktif cepat 1-klik: `POST /master/data-jurusan/{jurusan}/toggle-status`.
  - Integrasi dialog modal interaktif `InforaModals` (`modalCreateJurusan`, `modalEditJurusan`, `modalDeleteJurusan`) langsung di atas tabel data tanpa reload halaman.
  - Automated Pest test suite di [`tests/Feature/JurusanTest.php`](tests/Feature/JurusanTest.php).
- **Konfigurasi Sekolah: Manajemen Tahun Ajaran dan Semester:**
  - Mengimplementasikan tata kelola siklus akademik pada rute `/konfigurasi-sekolah/tahun-ajaran` (`konfigurasi-sekolah.tahun-ajaran.*`).
  - Entitas induk `TahunAjaran` dengan rentang tanggal dan status aktif, serta sub-entitas `Semester` (Ganjil dan Genap) dengan rentang tanggal pelaksanaan.
  - Fitur aktivasi 1-klik (`POST /konfigurasi-sekolah/tahun-ajaran/{tahunAjaran}/semester/{semester}/activate`) dengan proteksi semester aktif tunggal.
  - Arsitektur dialog modal modular: `modal-tahun-ajaran.blade.php` dan `modal-semester.blade.php`.
  - Automated Pest test suite di [`tests/Feature/TahunAjaranTest.php`](tests/Feature/TahunAjaranTest.php).
- **Konfigurasi Sekolah: Kalender Akademik Terpadu dan Agenda Kegiatan Multi-Day:**
  - Mengimplementasikan Kalender Akademik interaktif pada rute `/konfigurasi-sekolah/kalender-akademik` (`konfigurasi-sekolah.kalender-akademik.*`).
  - Tampilan ganda yang dapat dialihkan via komponen `<x-tab-pills>`:
    1. **Tampilan Kalender Bulanan Interaktif (*Monthly Grid View*):** Kotak hari kalender, navigasi antar bulan, indikator hari ini (*today badge*), dan strip penanda kegiatan.
    2. **Tampilan Tabel Daftar Agenda (*Tabular Agenda View*):** Tabel pencarian cepat seluruh kegiatan dengan filter kategori.
  - Dukungan rentang kegiatan multi-hari (*multi-day event spanning*) dengan kalkulasi badge durasi hari otomatis (*"3 hari"*).
  - Standarisasi pewarnaan kategori agenda (Libur Nasional/Keagamaan, Penilaian/Ujian, KBM, Ekstrakurikuler, Rapat Dinas).
  - Endpoint JSON `GET /konfigurasi-sekolah/kalender-akademik/events` untuk memuat acara kalender secara asinkron berdasarkan unit sekolah yang aktif.
  - Automated Pest test suite di [`tests/Feature/KalenderAkademikTest.php`](tests/Feature/KalenderAkademikTest.php).
- **Standarisasi Service `ImageUploader` (`App\Support\ImageUploader`) dan Komponen `<x-image-upload>`:**
  - Service mandiri murni PHP untuk validasi ketat MIME type (`png`, `jpg`, `jpeg`, `webp`, `gif`, `svg`), batasan ukuran maks. 1MB (`DEFAULT_MAX_BYTES = 1048576`), dan penamaan semantik collision-proof `{modul}_u{user_id}_{timestamp}_{random8}.{ext}`.
  - Komponen Blade `<x-image-upload>` dengan dropzone modern, live base64 conversion di browser via FileReader API, pratinjau bentuk lingkaran/membulat, flag penghapusan foto, dan modul JavaScript pendamping `window.ImageUpload`.
  - Migrasi pengolahan logo sekolah pada `Master\SchoolController` ke `ImageUploader::uploadBase64()`.
  - Unit test suite di [`tests/Unit/ImageUploaderTest.php`](tests/Unit/ImageUploaderTest.php).
- **Pengendali Matriks Hak Akses Terprogram (`PermissionMatrix`):**
  - Modul client-side terpusat `resources/js/permission-matrix.js` untuk mengelola tabel matriks RBAC pada Hak Akses Pengguna dan Template Peran.
  - Otomatisasi dependensi baris (menonaktifkan *Lihat* otomatis menonaktifkan aksi CRUD; memilih aksi CRUD otomatis mengaktifkan *Lihat*).
  - Sinkronisasi hierarki (mengaktifkan *Lihat* pada sub-menu otomatis mencentang *Lihat* pada menu induk).
  - Toolbar aksi cepat: *Centang Semua Lihat*, *Centang Semua CRUD*, *Kosongkan Semua*, serta *Toggle per Modul*.
- **Ekosistem 22 Anonymous Blade Components dan Dokumentasi Lengkap (`docs/COMPONENTS.md`):**
  - Dokumentasi resmi 22 komponen Blade Anonymous dan 5 modul JavaScript pendamping (`InforaModals`, `SearchableSelect`, `ImageUpload`, `PermissionMatrix`, `InforaProgress`).
  - Standarisasi props, fallback error bag yang aman dari unit-testing, dan pengujian regresi di `BladeComponentsTest.php`.
- **Dukungan Jenjang Madrasah Aliyah (MA):**
  - Memperluas cakupan platform dengan menambahkan jenjang MA di samping SMA dan SMK pada entitas Data Sekolah (`School`) dan Data Jurusan (`Jurusan`).
- **Pembersihan View Legacy dan Refactoring Desain:**
  - Membersihkan seluruh class lebar kolom legacy dan inline styling pada modul sistem, wilayah administratif, master data, dan konfigurasi sekolah.
- **Ekspansi Test Suite: 250 Tests Lolos 100% (1.123 Assertions):**
  - Total pengujian otomatis Pest melonjak menjadi **250 feature dan unit tests lulus 100% tanpa risiko/kegagalan (1.123 assertions)**.
- **Master Data Wilayah Administratif Pemerintahan Berbasis Kode Kemendagri (Hierarki 4 Tingkat):**
  - Mengimplementasikan CRUD lengkap untuk 4 tingkat hierarki wilayah: **Provinsi** (`/wilayah/provinsi`), **Kabupaten/Kota** (`/wilayah/kabupaten`), **Kecamatan** (`/wilayah/kecamatan`), dan **Kelurahan/Desa** (`/wilayah/kelurahan`) di bawah modul Master Wilayah Administratif.
  - **Standardisasi Kode Wilayah Resmi Kemendagri (Bukan BPS)**:
    - Kewajiban penggunaan standar resmi Kementerian Dalam Negeri RI dalam format numerik murni tanpa tanda titik: 2 digit Provinsi (`73`), 4 digit Kabupaten/Kota (`7308`), 6/7 digit Kecamatan (`730801`), dan 10 digit Kelurahan/Desa (`7308011001`).
    - Penegakan aturan konsistensi: menolak kode wilayah statistik BPS (Wilkerstat) yang kerap berbeda signifikan (misal: Kab. Bone Kemendagri = `7308`, bukan BPS `7311`). Aturan terdokumentasi permanen di `.agents/rules/regional-codes-standard.md`.
  - **Model, Relasi Berjenjang dan Mutator**:
    - Model `Provinsi`, `Kabupaten`, `Kecamatan`, dan `Kelurahan` dilengkapi relasi hierarki langsung dan *has-many-through* (`provinsi` ➔ `kabupaten` ➔ `kecamatan` ➔ `kelurahan/desa`), serta relasi `sekolah()` pada masing-masing entitas.
    - Mutator otomatis `TextFormatter::titleCase()` untuk preservasi huruf kapital dan akronim pada nama wilayah, mutator UPPERCASE pada singkatan provinsi, serta scope query `aktif()`.
    - Proteksi integritas relasional: Kelurahan yang masih dirujuk oleh data sekolah tidak dapat dihapus (*restricted deletion guard*).
  - **Filter Cascading Dinamis 3 Tingkat**:
    - Antarmuka tabel dan modal dilengkapi filter bertingkat dinamis (Provinsi ➔ Kabupaten ➔ Kecamatan) yang saling terhubung secara reaktif tanpa reload halaman.
  - **Seeder Resmi Kemendagri**:
    - `ProvinsiSeeder`: 38 Provinsi resmi Republik Indonesia.
    - `KabupatenSeeder`: 24 Kabupaten/Kota se-Sulawesi Selatan dan kota-kota percontohan nasional.
    - `KecamatanSeeder`: Seluruh 27 Kecamatan se-Kabupaten Bone.
    - `KelurahanSeeder`: Seluruh 372 Kelurahan dan Desa resmi se-Kabupaten Bone.
  - **66 Automated Feature Tests**: Test suite `ProvinsiTest.php`, `KabupatenTest.php`, `KecamatanTest.php`, dan `KelurahanTest.php` mencakup otentikasi super admin, listing dan pencarian, filter cascading, validasi format kode dan hierarki wilayah, mutator title case, operasi CRUD, dan proteksi integritas relasi. Total suite pengujian aplikasi melonjak menjadi **167 tests lulus 100% (727 assertions)**.
- **Arsitektur Tata Letak 3-Tier dengan Docked Footer dan Scroll Internal Independen (`layouts/app.blade.php` dan `resources/css/app.css`):**
  - Menerapkan arsitektur layout 3-tier: Topbar (*header*) tetap di atas, footer docked (*docked bottom dock*) permanen di bawah viewport, dan scrolling internal independen pada area konten utama (`.app-content`) dan sidebar navigasi (`.sidebar-content`).
  - Mengeliminasi isu *double scrollbar* pada window browser dan memastikan footer sistem selalu terlihat rapi tanpa terdorong ke luar layar.
- **Komponen Paginasi Responsif Global dan Toolbar Tabel (`<x-pagination>`):**
  - Komponen Blade reusable `<x-pagination>` di `resources/views/components/pagination.blade.php` dengan badge informasi jumlah data yang adaptif, tombol navigasi sebelumnya/selanjutnya yang mobile-friendly, dan link nomor halaman yang ringkas.
  - Terdaftar secara terpusat sebagai default pagination view pada `AppServiceProvider` via `Paginator::defaultView('components.pagination')`.
  - Komponen `.table-toolbar-responsive` untuk tata letak toolbar pencarian, filter dropdown, dan tombol aksi yang membungkus (*wrap*) secara proporsional di layar ponsel dan tablet.
  - Modifier `.table-responsive-scroll` dengan adaptasi ketinggian responsif terhadap tinggi layar (`calc(100vh - 360px)` / flex container) dan sticky header untuk kenyamanan navigasi tabel bervolume besar.
- **Otomatisasi Urutan Tampil Mulai 1 dan Dual Field Nama Rute Sub-Menu:**
  - Mengubah nilai default order migrasi tabel `modules`, `menus`, dan `sub_menus` menjadi 1 (menggantikan default 0).
  - Menambahkan method pembantu `nextOrder()` pada model `Module`, `Menu`, dan `SubMenu` untuk mengisi nomor urut otomatis berikutnya pada formulir tambah data.
  - Tampilan input dual-field nama rute pada formulir sub-menu (tampilan visual prefix rute menu induk yang terkunci + input nama sub-rute mandiri) guna mencegah salah ketik nama rute.
- **Perluasan Katalog Ikon Lucide Kategori Wilayah dan Data:**
  - Menambahkan koleksi ikon baru pada konfigurasi katalog `config/icons.php` dan komponen SVG `<x-icon>`: kategori Wilayah (`map`, `map-pin`, `landmark`, `building`, `building-2`, `globe`) dan kategori Data (`table`, dll.) yang dapat dipilih langsung via `<x-icon-picker>`.
- **Arsitektur Modal Interaktif Edit Data (`edit.blade.php` sebagai Komponen Modal Modular):**
  - Mentransformasikan seluruh formulir pengubahan data (*edit*) yang sebelumnya berupa halaman terpisah menjadi modal dialog interaktif langsung di atas tabel data (`index.blade.php`) tanpa reload halaman (*zero-reload workflow*).
  - Diterapkan secara seragam pada 4 entitas utama:
    - **Master Data Sekolah (`master/data-sekolah`)**: Komponen modal `#modalEditSchool` dengan integrasi form preview/upload/hapus logo Base64, select status/akreditasi, dan auto-population via payload `data-school`.
    - **Tata Kelola Modul (`system/modules`)**: Komponen modal `#modalEditModule` dengan auto-transform uppercase dan auto-population via payload `data-module`.
    - **Tata Kelola Menu (`system/menus`)**: Komponen modal `#modalEditMenu` dengan integrasi dinamis `<x-icon-picker>` (melalui helper global `window['setIconValue_' + pickerId]`), auto-transform title case, dan auto-population via payload `data-menu`.
    - **Tata Kelola Sub-Menu (`system/sub-menus`)**: Komponen modal `#modalEditSubMenu` dengan auto-transform title case dan auto-population via payload `data-sub-menu`.
  - **Arsitektur Komponen Modular (`edit.blade.php` sebagai Modal Component)**: Berkas `edit.blade.php` distandarisasi sebagai berkas view modular yang di-include (`@include('...edit')`) ke dalam `index.blade.php`, bukan lagi berupa view yang meng-`@extends('layouts.app')`.
  - **Penanganan Validasi Cerdas (*Smart Auto-Reopen*)**: Memeriksa `old('_method') === 'PUT'` untuk membuka kembali modal edit secara otomatis jika validasi backend gagal, menjaga persistensi data input pengguna (`old(...)`).
  - **Backward Compatibility Rute**: Mengubah metode `edit()` pada `SchoolController`, `ModuleController`, `MenuController`, dan `SubMenuController` untuk mengalihkan (*redirect*) ke route `index` terkait alih-alih 404/menampilkan view lama, memastikan tautan lama atau bookmark tetap mendarat aman di halaman tabel.
  - **100% Kepatuhan Arsitektur Bebas Inline Styles**: Mengandalkan sistem kelas modal universal di `resources/css/app.css` tanpa satupun atribut `style="..."` maupun tag `<style>`.
  - **Automated Feature Testing**: Memperbarui dan menyelaraskan seluruh skenario pengujian di `SchoolTest.php`, `ModuleCrudTest.php`, `MenuCrudTest.php`, dan `SubMenuCrudTest.php` untuk memvalidasi alur modal edit baru dan status redirect `edit()`. Seluruh 101 tests aplikasi lulus 100% (474 assertions).
- **Pemisahan dan Penyempurnaan Dokumentasi Fitur Unggulan (`detail/README.md` dan `README.md`):**
  - Memisahkan dan merinci seluruh 15 spesifikasi fitur unggulan secara komprehensif ke berkas `detail/README.md`.
  - Merestrukturisasi berkas `README.md` utama menjadi ringkasan yang ringkas, tajam, dan elegan, dilengkapi penunjuk tautan langsung ke `detail/README.md` untuk eksplorasi teknis mendalam.
- **Fitur Master Data Sekolah (Multi-Record Registry dan Kesiapan Bridging):**
  - Membangun fitur manajemen registri sekolah pada rute `/master/data-sekolah` (`master.data-sekolah.*`) di bawah modul Administrasi ➔ Master ➔ Data Sekolah.
  - Mendukung multi-record registri sekolah (SMA dan SMK, berstatus Negeri maupun Swasta) untuk kebutuhan pengelolaan multi-unit yayasan serta kesiapan bridging API Dapodik.
  - **Skema Basis Data (`schools`)**: Menyimpan identitas resmi (`name`, `npsn`, `nss`, `school_type`, `status`, `accreditation`), alamat lengkap (`address`, `village`, `district`, `city`, `province`, `postal_code`), kontak (`phone`, `fax`, `email`, `website`), pimpinan (`principal_name`, `principal_nip`), yayasan (`foundation_name`), `logo_path`, dan `is_active`.
  - Mengimplementasikan prinsip reduksi batasan unik: kolom `npsn` di-index tanpa `unique` constraint sesuai kebutuhan fleksibilitas operasional.
  - **Model dan Mutator**: Model `School` dilengkapi mutator otomatis `TextFormatter::titleCase()` pada kolom `name` untuk menjamin konsistensi huruf kapital (Title Case) dan preservasi akronim (`SMA`, `SMK`).
  - **Pipeline Unggah Logo Base64 (Maks. 1MB)**: Mendukung pengunggahan logo sekolah format string Base64 via JSON/form dengan live preview, validasi ukuran maks. 1MB, dan penyimpanan ke storage publik (`storage/app/public/logos`) dengan penamaan semantik collision-proof: `sekolah_u{user_id}_{timestamp}_{random8}.{ext}`.
  - **Antarmuka Pengguna Terpadu**:
    - Data Table dengan pencarian dinamis (nama sekolah, NPSN, kota), dropdown filter jenis sekolah (SMA/SMK), badge warna status/akreditasi/jenis (`badge-purple`, `badge-cyan`, `badge-success`, `badge-amber`), dan paginasi 15/halaman.
    - Modal interaktif tambah sekolah (`modalCreateSchool`) dengan auto-reopen saat validasi error.
    - Modal interaktif edit data sekolah (`modalEditSchool` via komponen modular `master.data-sekolah.edit`).
  - **15 Automated Pest Feature Tests**: Test suite `SchoolTest.php` mencakup autentikasi dan otorisasi super admin, listing dan empty state, pencarian dan filter jenis, validasi form, mutator title-case, Base64 logo upload dan storage, update dan hapus logo, penghapusan data dan berkas fisik, serta verifikasi zero inline styles (15 passed, 63 assertions). Total pengujian aplikasi mencapai 101 tests lulus 100%.

### Changed dan Refactored
- **Modularisasi Komponen Modal Tambah Data (`create.blade.php` sebagai Komponen Modal Modular):**
  - Memisahkan formulir dialog modal tambah sekolah ke dalam berkas modular terpisah `resources/views/master/data-sekolah/create.blade.php` yang di-include ke `index.blade.php`.
  - Menerapkan pola yang sama secara seragam pada seluruh modul wilayah (`provinsi/create.blade.php`, `kabupaten/create.blade.php`, `kecamatan/create.blade.php`, `kelurahan/create.blade.php`), menjadikan arsitektur modal CRUD sepenuhnya modular (`index.blade.php`, `create.blade.php`, dan `edit.blade.php`).
- **Penyempurnaan Interaktivitas Navigasi Sidebar dan Animasi Accordion:**
  - Menambahkan `flex-shrink: 0` pada item navigasi sidebar untuk mencegah pemerasan/pemotongan teks menu saat accordion dibuka.
  - Menghapus outline fokus kaku pada pemicu accordion menu dan memperhalus kurva transisi buka-tutup dengan fungsi *cubic-bezier*.
  - Menambahkan logika penyesuaian scroll otomatis saat accordion submenu di bagian bawah sidebar dibuka pengguna.
- **Standarisasi Komponen Metrik Universal dan Utilitas Dropzone:**
  - Menggantikan class CSS ad-hoc `.backup-*` dengan komponen metrik universal di `resources/css/app.css`: `.stats-grid`, `.stat-card`, `.callout-danger`, dan `.grid-split-2-1`.
  - Standarisasi class `.dropzone`, `.dropzone-icon`, `.dropzone-text`, dan area pratinjau unggahan berkas tanpa class alias.
- **Penyempurnaan Sistem Dialog Modal dan Normalisasi Margin/Padding UI (`resources/css/app.css`):**
  - **Arsitektur Flexbox Terpadu**: Menambahkan aturan `.modal-dialog form { display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin: 0; }` serta `.modal-header` dan `.modal-footer { flex-shrink: 0; }`. Memastikan footer modal (tombol Batal dan Simpan) selalu utuh, tidak pernah terpotong di bagian bawah dialog pada resolusi/tinggi layar berapa pun.
  - **Eliminasi Margin Ganda (Double Spacing)**: Menormalkan `.modal-body .form-group { margin-bottom: 0; gap: 0.375rem; }` dan `.form-grid-2col .form-group { margin-bottom: 0; }`, menghilangkan jarak vertikal berlebih antar-input yang sebelumnya mencapai 40px akibat tumpukan `gap` dan `margin-bottom`.
  - **Divider dan Label Seksi Form Terstandarisasi**: Mengatur `.form-section-divider` dengan warna border standar desain aplikasi (`border-top: 1px solid var(--infora-border); width: 100%; height: 0;`), serta mentransformasikan `.form-section-label` menjadi badge edukasi khas INFORA dengan indikator titik biru (`--infora-brand-soft` background, `--infora-brand-border`, dan `--infora-brand-primary`).
  - **Pelebaran Modal Dialog**: Meningkatkan lebar `.modal-dialog.modal-lg` menjadi `760px` agar form 2-kolom memiliki proporsi visual yang lega, serta menambahkan *slim scrollbar* 6px berujung bulat pada `.modal-body`.
- **Portabilitas Symlink Storage Relatif (*Docker dan Host Relative Symlink Preservation*)**:
  - Mengonfigurasi pembuatan symbolic link `public/storage` menggunakan path relatif (`../storage/app/public` via `storage:link --relative`) pada skrip dan `DatabaseBackupService::ensureStorageLink()`.
  - Mengeliminasi galat 403 Forbidden pada Nginx container saat mencoba membaca symlink yang sebelumnya mengarah ke path absolut mesin host (`/home/...`), menjamin berkas gambar/logo publik dapat diakses mulus dari browser baik di dalam Docker maupun di luar container.

- **Universal Smooth Real-Time Progressive Loading Screen (*Global System Indicator Engine*):**
  - Mentransformasikan indikator proses menjadi mesin *Universal Progressive Loading Screen* yang dapat dipanggil dan digunakan kembali oleh seluruh modul dan fitur aplikasi INFORA (bukan hanya Backup dan Restore).
  - Menyediakan antarmuka visual minimalis, elegan, dan tenang tanpa elemen kaku: menggantikan *stepper* bertingkat dan kotak log teknis dengan kartu melayang modern berbalut *backdrop blur*, *ambient status orb* berdenyut lembut, judul dan teks aktivitas ringkas dinamis, serta bar progres ramping (*sleek pill bar*) 9px.
  - **Animasi Sangat Halus (*Buttery Smooth Motion dan Gradient Shimmer*)**: Bar progres dilengkapi sapuan gradien bercahaya Royal Blue (`#2563EB`) ke Cyan (`#06B6D4`) yang mengalir kontinu (`@keyframes progressGradientShimmer`), transisi pergerakan kurva `cubic-bezier(0.25, 1, 0.5, 1)`, serta interpolasi numerik persentase yang mengalir halus berbasis `requestAnimationFrame`.
  - **Komponen Blade Global (`<x-progress-modal />`)**: Ditanamkan terpusat pada layout master [app.blade.php](resources/views/layouts/app.blade.php) sehingga siap digunakan di halaman mana pun tanpa duplikasi markup.
  - **Universal JavaScript API (`window.InforaProgress`)**: Modul mandiri [infora-progress.js](resources/js/infora-progress.js) dengan metode `show()`, `set()`, `finish()`, `error()`, `hide()`, dan `stream(url, formData, options)` untuk otomasi konsumsi *Server-Sent Events* (SSE).
  - **100% Kepatuhan Arsitektur Bebas Inline Styles**: Seluruh styling visual terpusat di `resources/css/app.css` tanpa satupun atribut `style="..."` maupun tag `<style>`.
  - Penyesuaian batas unggahan berkas cadangan menjadi **250 MB** pada `RestoreBackupRequest` (256.000 KB), Nginx (`client_max_body_size 250M;`), dan PHP-FPM (`upload_max_filesize = 250M`, `post_max_size = 250M`).
  - Sinkronisasi zona waktu server dan aplikasi ke **`Asia/Makassar`** (WITA, GMT+8) di `docker/php/php.ini`, `config/app.php`, `.env`, dan `.env.example`.
  - Seluruh pengujian fitur dan unit test Pest lulus 100% (86 passed, 398 assertions).
- **Sistem Cadangan dan Pemulihan Sistem Lengkap (*Full System Snapshot dan Server Migration Ready*):**
  - Meningkatkan fitur Backup dan Restore pada rute `/backup-restore` (`backup-restore`) di bawah modul PENGATURAN SISTEM dari sekadar dump database menjadi snapshot sistem terpadu berformat `.zip`.
  - Mengemas basis data (`database.sql`), seluruh berkas/gambar unggahan pengguna (`storage/app/public/`), dan berkas `manifest.json` metadata ke dalam satu arsip portabel mandiri via native PHP `ZipArchive`.
  - Pengecualian cerdas pada folder `vendor/`, `node_modules/`, `.git/`, dan `storage/framework/` agar ukuran arsip tetap ringkas dan bebas konflik binary OS antar server.
  - **Pencegahan Otomatis Gambar 404 (*Symlink Health Check dan Auto-Repair*)**: Saat pemulihan (*restore*), sistem memeriksa kesehatan symlink `public/storage`; jika sudah terhubung valid ke target lokal, sistem melanjutkan tanpa operasi berlebih; jika belum ada atau rusak (*broken link* dari server lama), sistem otomatis membuat ulang symlink (`Artisan::call('storage:link')`).
  - **Menu Dasar Bawaan Sistem (*Baseline System Seeder*)**: Menu mandiri **Backup dan Restore** (`route: backup-restore`, `icon: folder`, `order: 2`) resmi dijadikan menu sistem dasar di [SystemMenuSeeder.php](database/seeders/SystemMenuSeeder.php) di bawah modul PENGATURAN SISTEM, menjamin menu ini langsung aktif di sidebar saat *fresh installation* (`php artisan migrate:fresh --seed`) agar proses pemulihan data dapat langsung dieksekusi seketika.
  - Test suite Pest diperluas dengan [SystemBaselineSeederTest.php](tests/Feature/System/SystemBaselineSeederTest.php) (total 83 tests aplikasi lulus 100%, 377 assertions).
- **Sistem Tata Kelola Menu Akses dan Dynamic Role Templates (User-Centric Granular Permissions):**
  - Mengimplementasikan otorisasi navigasi berbasis pengguna pada rute `/sistem/menu-akses` (`sistem.menu-akses`) lengkap dengan 4 hak aksi granular: Lihat (`can_view`), Tambah (`can_create`), Ubah (`can_edit`), dan Hapus (`can_delete`).
  - Sistem template peran dinamis (`menu_access_templates`) yang dapat dikonfigurasi penuh oleh Super Admin untuk beragam peran (Guru Pengajar, Wali Kelas, Wakasek Kurikulum, Wakasek Kesiswaan, Staf TU, Siswa Reguler, Siswa PKL) melalui antarmuka khusus.
  - Fitur 1-Klik Salin Template Peran (*Apply Preset*) saat mengelola izin pengguna untuk pengisian hak akses instan dalam 1 detik.
  - Antarmuka manajemen lengkap di `resources/views/system/menu-access/` dengan kepatuhan arsitektur 100% bebas *inline style* dan bebas tag `<style>`.
  - Integrasi filtering dinamis pada `SidebarComposer` dengan proteksi *root bypass* permanen untuk Super Admin.
  - Test suite terintegrasi Pest `MenuAccessTest.php` (total 67 tests aplikasi lulus 100%, 299 assertions).
- **Halaman Placeholder "Fitur Dalam Pengembangan" dan Dynamic Fallback (Zero Dead Links):**
  - Mengeliminasi tautan mati (`href="#"`) di sidebar: Setiap menu atau sub-menu yang dibuat oleh Super Admin namun belum memiliki rute implementasi aktif di `routes/web.php` secara otomatis mengarah ke halaman pratinjau `/system/under-development?type={menu|submenu}&id={id}`.
  - Implementasi `UnderDevelopmentController` yang mengekstrak informasi hierarki entitas navigasi (Modul, Menu, Sub-Menu) dan menghasilkan saran cetak biru (*blueprint*) pengembang berupa snippet rute Laravel dan perintah CLI `php artisan make:controller ...`.
  - Tampilan antarmuka profesional `resources/views/system/under-development.blade.php` dengan tema visual Infora, breadcrumbs navigasi, badge status *Under Development*, kartu detail entitas, dan tombol aksi cepat (*Edit Konfigurasi* dan *Kembali ke Dashboard*).
  - Preservasi state aktif sidebar: Saat halaman placeholder diakses, accordion menu induk otomatis tetap terbuka (`is-open active-parent`) dan sub-menu bersangkutan disorot aktif (`active`).
  - Suite pengujian fitur otomatis (`UnderDevelopmentTest.php`) dan penyesuaian assertions pada `SidebarFrontendTest.php`, seluruh 59 test suite aplikasi lulus 100% (261 assertions).
- **Ikon Pencarian Interaktif pada Sidebar Ciut dan Shortcut Keyboard (`Ctrl+K`):**
  - Mengubah kotak pencarian (`.sidebar-search .search-box`) menjadi tombol ikon 40px x 40px interaktif saat sidebar dalam keadaan ciut (72px), mengisi ruang kosong di atas menu Dashboard dengan fungsi yang produktif dan estetik.
  - Mengklik tombol pencarian pada mode ciut akan langsung membuka sidebar secara otomatis dan memfokuskan kursor ke input pencarian (`#sidebarMenuSearch`).
  - Dukungan shortcut keyboard global `Ctrl+K` atau `/` (saat tidak sedang mengetik di input/textarea) untuk membuka sidebar dan memfokuskan pencarian menu dari mana saja di dalam aplikasi.
- **Seksi Profil Pengguna dan Dropup Popover Interaktif di Sidebar Footer:**
  - Memindahkan informasi profil pengguna dan aksi keluar dari topbar ke bagian bawah sidebar (*sidebar footer*), menghadirkan topbar yang bersih, fokus, dan lapang.
  - Kartu pengguna interaktif (`#userProfileTrigger`) yang memicu popover menu melayang ke atas (*dropup*) saat diklik, dilengkapi indikator panah *chevron* yang berputar 180° saat terbuka.
  - Menu popover lengkap: Header ringkasan akun dengan avatar inisial, opsi **Pengaturan Profile**, **Ubah Password**, **Bantuan** (Pusat Bantuan INFORA), serta tombol **Keluar / Logout** aman berbalut aksi destruktif (*danger styling*).
  - Interaksi aksesibel: Menutup otomatis via tombol `Escape`, menutup saat mengklik area di luar (*click outside*), dan notifikasi melayang (*floating toast*) non-intrusif pada fitur yang sedang dipersiapkan.
  - Responsif pada sidebar ciut: Saat sidebar diciutkan (72px), menu otomatis melayang ke arah samping kanan (`left: 76px; bottom: 8px; width: 230px`) sehingga seluruh navigasi tetap dapat dijangkau dengan mudah.

### Changed dan Refactored
- **Penyempurnaan Animasi Menu Ikon dan Penjangkaran 26px (*Buttery Smooth Anchored Transition*):**
  - Mengeliminasi efek hentakan visual (*abrupt jump*) pada ikon-ikon navigasi menu saat sidebar diciutkan atau dibuka.
  - Menjaga koordinat horizontal ikon terkunci stabil pada margin 26px dari tepi kiri (`16px padding container + 10px padding tombol = 26px`), baik saat terbuka (260px) maupun ciut (72px) tanpa mengubah padding menjadi 0 atau memaksakan `justify-content: center`.
  - Menggantikan `display: none` pada teks menu, badge jumlah, dan panah accordion dengan transisi kurva halus `opacity: 0.2s` dan `transform: translateX(-10px)`.
  - Penutupan halus accordion submenu yang sedang terbuka (`max-height: 0; opacity: 0; padding: 0`) saat sidebar diciutkan.
  - Transformasi halus pemisah modul (`.menu-category-label`) menjadi garis pembatas tipis 1px yang rapi di mode ciut.
- **Standarisasi Sistem Class Global Universal Dropdown (`resources/css/app.css`):**
  - Merefaktor seluruh penamaan class ad-hoc (seperti `.user-dropup-*`) ke sistem komponen dropdown modular universal: `.dropdown`, `.dropdown.dropdown-full`, `.dropdown-menu`, `.dropdown-menu-up`, `.dropdown-menu-end`, `.dropdown-menu-full`, `.dropdown-header`, `.dropdown-divider`, `.dropdown-list`, `.dropdown-item`, dan `.dropdown-item.is-danger`.
  - Mengimplementasikan komponen kartu profil universal `.user-card-button` dan standarisasi avatar global `.user-avatar-circle` (38px) serta varian `.user-avatar-circle.avatar-sm` (28px).
  - Menegakkan kepatuhan 100% pada aturan arsitektur *Strict No Ad-Hoc Classes* (bebas class sekali pakai, tanpa inline styles, dan tanpa tag `<style>`).
- **Perbaikan Penjangkaran Animasi Transisi Sidebar (*Anchored Symmetrical Transition*):**
  - Mengeliminasi bug transisi di mana elemen pengguna sempat memusatkan diri ke tengah lalu bergeser ke kiri (*auto ketengah baru ke kiri*) saat toggle sidebar ciut/buka.
  - Mengunci posisi avatar pada koordinat horizontal tetap 17px dari tepi kiri layar (`padding: 0.75rem 12px` pada footer + `padding: 0.375rem 5px` pada kartu = 17px), identik dengan margin emblem logo header (`padding: 1.25rem 17px`).
  - Menjaga alignment selalu `justify-content: flex-start` (tanpa pernah berganti ke `center`).
  - Menerapkan transisi halus `opacity: 0.2s ease` dan `transform: translateX(-10px)` pada nama pengguna dan ikon chevron, sehingga teks memudar mulus selaras dengan penyusutan lebar sidebar (260px ke 72px).
- **Pemisah Modul Classic Minimalist dan Eliminasi Opsi Icon Modul:**
  - Menerapkan desain pemisah kategori modul *Classic Minimalist* (`.menu-category-label` dengan garis pembatas tipis atas dan tipografi uppercase elegan) yang rapi dan profesional.
  - Menghapus atribut dan kolom `icon` dari tabel database `modules`, model, factory, seeder, Form Requests, dan views modul karena kategori modul berfungsi sebagai header pengelompokan yang tidak menampilkan icon di sidebar.

- **Modal Interaktif Formulir Tambah (Modul, Menu, dan Sub-Menu):**
  - Alih-alih berpindah/redirect ke halaman baru saat mengklik tombol "+ Tambah", sistem kini memunculkan modal dialog interaktif langsung pada halaman index (`system/modules`, `system/menus`, `system/sub-menus`).
  - Arsitektur styling modal global reusable di `resources/css/app.css` (`.modal-backdrop`, `.modal-dialog`, `.modal-header`, `.modal-body`, `.modal-footer`).
  - Layering Z-Index berlapis: Modal form berada pada `z-index: 9000` dan Modal Icon Picker berada pada `z-index: 10000`, memungkinkan katalog visual ikon dibuka di atas modal formulir secara mulus.
  - Penanganan validasi cerdas: Modal otomatis terbuka kembali (*auto-reopen*) ketika terdapat kesalahan input dari server (`$errors->any()`).
- **Visual Icon Picker Interaktif dan Katalog Ikon Terkurasi (100% Free - Lucide Icons):**
  - Komponen Blade reusable `<x-icon-picker>` di `resources/views/components/icon-picker.blade.php` dengan live preview card terpilih, pencarian instan (nama/kata kunci Indonesia), filter kategori (Navigasi, Akademik, Data, Pengguna, Sistem, Umum), dan modal katalog visual yang mudah diklik tanpa perlu mengetik manual.
  - Konfigurasi katalog ikon resmi di `config/icons.php` berisi ~38 ikon Lucide SVG 100% gratis dan open-source (berlisensi ISC tanpa biaya/lisensi berbayar).
  - Perluasan komponen `<x-icon>` dengan dukungan SVG path lengkap untuk seluruh koleksi ikon (`school`, `graduation-cap`, `book-open`, `award`, `database`, `users`, `compass`, `settings`, dll).
  - Integrasi Icon Picker pada formulir Tambah/Edit Modul dan Menu.
- **Resolusi Rute Fleksibel dan Panduan Informatif Nama Rute:**
  - Metode cerdas pada model `Menu` dan `SubMenu` (`getRouteUrlAttribute()`, `isRouteActive()`, `hasActiveSubMenu()`) yang otomatis mencocokkan format hierarki sederhana `modul.menu` atau `modul.menu.submenu` tanpa mewajibkan pengguna menambahkan akhiran teknis `.index`.
  - Kotak panduan informatif (`.route-guide-box`) pada formulir Menu dan Sub-Menu yang menjelaskan kapan rute harus dikosongkan (menu induk dropdown) dan kapan harus diisi, dilengkapi *quick suggestion pills* dan `<datalist>` rute terdaftar.
- **Penyempurnaan UI/UX Sub-Menu Sidebar:**
  - Menghapus titik (*bullet dot*) pada sub-menu sidebar demi visual yang lebih bersih dan rapi.
  - Mengganti bullet dengan indentasi bertingkat elegan dan indikator garis vertikal aksen (*left accent indicator bar*) saat di-hover dan aktif.
- **Modul Autentikasi Super Admin:**
  - Login multi-identifier fleksibel via **Username** maupun **Email** dengan proteksi session fixation.
  - Verifikasi status keaktifan akun (`is_active`).
  - Fitur logout aman (session invalidation dan token CSRF regeneration).
  - Middleware `SuperAdminMiddleware` untuk membatasi akses khusus akun bertipe `super_admin`.
- **Database dan Model:**
  - Migrasi penambahan kolom sistem pada tabel `users`: `username`, `user_type`, `is_active`, `avatar_path`.
  - Seeder default Super Admin (`superadmin` / `password` / `superadmin@infora.test`).
  - Helper methods pada model `User`: `isSuperAdmin()`, `isAdmin()`, `isGuru()`, `isSiswa()`.
- **Design System dan Antarmuka Awal:**
  - Standardisasi class CSS global reusable di `resources/css/app.css` (bebas class ad-hoc, tanpa *inline styles*, dan tanpa tag `<style>`).
  - Master layout `layouts/auth.blade.php` dan `layouts/app.blade.php`.
  - Tampilan Dashboard awal Super Admin dengan **1 menu tunggal: Dashboard**.
  - Fitur UX interaktif Lihat/Sembunyikan Kata Sandi (*Show/Hide Password Toggle*) dengan ikon SVG ramah aksesibilitas.
  - Indikator loading interaktif dan proteksi *anti-spam click* pada tombol "Masuk ke Dashboard" (`#btnLogin`) dengan SVG spinner berputar halus dan transisi teks "Memproses...".
  - Fitur pencarian menu real-time pada sidebar Dashboard dengan standardisasi komponen reusable `.search-box`, `.search-icon`, `.search-input`, `.search-clear` serta `.empty-state` untuk feedback saat menu tidak ditemukan.
  - Fitur Collapsible Sidebar interaktif pada layout dashboard: tombol toggle panel (`#sidebarToggle`) dengan class reusable `.btn-icon` di topbar, transisi lebar halus (260px ke 72px), tampilan branding kompak, animasi rotasi ikon panah, dan persistensi status via `localStorage`.
  - Integrasi identitas brand visual resmi pada header sidebar menggunakan aset emblem badge simetris presisi (`public/images/infora-emblem-badge.png`) berpadu dengan tipografi tajam Plus Jakarta Sans (Opsi 1 Hybrid).
- **Fondasi Menu Hirarki Dinamis (Modul, Menu, Sub-Menu Tanpa Unique Constraint):**
  - Migrasi skema database terstruktur: tabel `modules`, `menus`, dan `sub_menus` tanpa kolom unik yang membebani (bebas bentrok penamaan, murni mengandalkan Primary Key ID).
  - Model Eloquent `Module`, `Menu`, `SubMenu` lengkap dengan relasi berjenjang `hasMany`/`belongsTo`, casts, dan factories pendukung.
  - Seeder default `SystemMenuSeeder` yang mendaftarkan 3 menu sistem dasar (*Modul*, *Menu*, *Sub-Menu*) pada modul "Pengaturan Sistem" serta menu "Dashboard" pada modul "Navigasi Utama".
- **Integrasi Sidebar Frontend Dinamis dan Interaktif:**
  - View Composer `SidebarComposer` yang menginjeksi daftar modul, menu, dan sub-menu aktif ke `layouts.app` dengan proteksi tabel `Schema::hasTable('modules')`.
  - Dukungan render hierarki 3 tingkat (*Modules* ➔ *Menus* ➔ *Sub-Menus*) dengan komponen ikon `<x-icon>` yang mendukung ikon Lucide SVG (`layout-dashboard`, `layers`, `menu`, `list-tree`, `shield-check`, `settings`, dll).
  - Dropdown Accordion sub-menu interaktif (`.nav-group-item`, `.nav-group-trigger`, `.nav-arrow-icon`, `.nav-submenu-list`) dengan rotasi panah halus 180° dan auto-uncollapse sidebar saat menu ber-sub-menu diklik dari status *collapsed*.
  - Pencarian menu real-time multi-level cerdas yang mencakup nama modul, menu, hingga sub-menu, otomatis membuka parent accordion jika sub-menu cocok, dan menyembunyikan kategori modul yang tidak relevan.
  - Presisi koordinat anchor kiri 26px pada seluruh ikon navigasi, baik pada mode expanded (260px) maupun collapsed (72px), menghasilkan animasi transisi lipat sidebar yang mulus tanpa pergeseran horizontal (0px horizontal jump).
- **Backend CRUD Lengkap Pengaturan Sistem (Modul, Menu, Sub-Menu):**
  - Rute resource RESTful terproteksi: `/system/modules`, `/system/menus`, `/system/sub-menus` di bawah middleware `['auth', 'super_admin']`.
  - Form Request Validation Layer dengan pesan bahasa Indonesia: `StoreModuleRequest`, `UpdateModuleRequest`, `StoreMenuRequest`, `UpdateMenuRequest`, `StoreSubMenuRequest`, `UpdateSubMenuRequest` (bebas aturan *unique* untuk fleksibilitas maksimal sesuai kebutuhan user).
  - Controller CRUD lengkap di namespace `App\Http\Controllers\System`:
    - `ModuleController`: daftar modul dengan hitungan relasi menu (`menus_count`), pencarian, form tambah/edit, dan cascade delete.
    - `MenuController`: daftar menu dengan relasi modul dan hitungan sub-menu (`sub_menus_count`), filter dropdown modul, dan cascade delete.
    - `SubMenuController`: daftar sub-menu dengan relasi induk menu dan modul, filter dropdown menu, dan form tambah/edit.
  - Komponen Desain Global dan Antarmuka Manajemen (9 views di `resources/views/system/`):
    - Komponen tabel data elegan (`.table-card`, `.table-toolbar`, `.data-table`, `.table-actions`, `.table-footer`).
    - Alert notifikasi sukses/gagal (`.alert-success`, `.alert-danger`).
    - Komponen form input, select, checkbox, hint, dan tombol aksi (`.btn-edit`, `.btn-delete`, `.btn-primary`, `.btn-secondary`).
    - 100% mematuhi aturan strict arsitektur: **Zero inline styles (`style=""`) dan Zero tag `<style>`**.
- **Sistem Otomatisasi Kapitalisasi Teks Input (UPPERCASE Modul dan Title Case Menu/Sub-Menu):**
  - Helper terpusat `App\Support\TextFormatter` untuk pemformatan teks konsisten: metode `upper()` dan `titleCase()` yang cerdas mempertahankan akronim standar pendidikan dan teknologi (`SMK`, `SMA`, `SIM`, `PKL`, `KBM`, `GTK`, `BAN-SM`, `RPP`, `IT`, `TU`, dll.).
  - Eloquent Attribute Mutators pada Model `Module` (nama otomatis tersimpan dalam format **UPPERCASE**) serta `Menu` dan `SubMenu` (nama otomatis tersimpan dalam format **Capitalize Each Word / Title Case** dengan preservasi akronim).
  - Integrasi Frontend Reaktif dan CSS Utilitas:
    - Atribut `data-transform="uppercase"` pada formulir modul dengan auto-uppercase instan saat mengetik tanpa merusak posisi kursor (*caret range*).
    - Atribut `data-transform="title-case"` pada formulir menu dan sub-menu dengan pemformatan otomatis saat event `blur` dan sebelum `submit`.
    - Kelas utilitas CSS `.text-uppercase` dan `.text-capitalize` di `resources/css/app.css` untuk feedback visual seketika.
  - Pembaruan seeder baseline `SystemMenuSeeder` dengan nama modul berformat kapital (`NAVIGASI UTAMA` dan `PENGATURAN SISTEM`).
  - Unit test baru `TextFormatterTest` dan peningkatan test suite hingga **54 tests lulus 100% (242 assertions)**.
- **Automated Testing dan Jaminan Kualitas:**
  - Penambahan feature dan unit tests baru (`TextFormatterTest`, `SidebarFrontendTest`, `ModuleCrudTest`, `MenuCrudTest`, `SubMenuCrudTest`).
  - Total test suite mencapai **54 tests lulus 100% (242 assertions)**.
  - 100% lulus standardisasi formatting Laravel Pint (PSR-12).

### Changed
- **Transformasi Desain Sistem ke Nuansa Terang, Ramah dan Keterbacaan Tinggi:**
  - Pembaruan tema visual global di `resources/css/app.css` dari nuansa gelap ke tema terang (*bright, smooth, clean modern*).
  - Peningkatan kontras teks (`Slate-900 #0F172A` di atas latar kanvas sejuk `#F8FAFC` dan kartu `#FFFFFF`) untuk menjamin keterbacaan maksimal (WCAG AAA) yang nyaman bagi guru dan siswa.
  - Harmonisasi palet warna aksen pendidikan: Sky Blue (`#0284C7`) dan Royal Blue (`#2563EB`) dengan latar navigasi aktif *soft blue* (`#EFF6FF`).
  - Penyesuaian elevasi kartu putih bersih dengan batas presisi halus (`#E2E8F0`) dan bayangan lembut melayang.

### Planned (Tahap Berikutnya)
- Skema tabel profil relasional 1-to-1 (`student_profiles`, `teacher_profiles`, `staff_profiles`).
- Dedicated Layout lanjutan: Mobile App-like (`layouts/mobile.blade.php`) dan Desktop Power-Dashboard (`layouts/desktop.blade.php`).
- Registry menu terpusat (`config/menu.php`) dengan alokasi hak akses *User-Centric* dan de-duplikasi otomatis.

---

## [0.1.0] - 2026-09-05

### Added
- **Docker Multi-Platform Environment (Non-Sail):**
  - Containerization terisolasi dengan Docker Compose: PHP 8.4-FPM (`infora_app`), Nginx Alpine (`infora_web`), MySQL 8.0 (`infora_mysql`), dan Node.js 22 LTS (`infora_node`).
  - Skrip automasi serbaguna lintas platform: `./dev` (Bash untuk Linux/macOS/WSL) dan `dev.ps1` (PowerShell untuk Windows).
  - Konfigurasi environment `.env.docker.example` dan template koneksi database lokal/remote.
- **Standar Arsitektur Inti Platform:**
  - **API-First dan System Bridging:** Arsitektur modular siap bridging dengan Dapodik, e-Rapor, LMS, dan WhatsApp Gateway.
  - **Pure JSON Response Contract:** Seluruh endpoint dan interaksi data wajib mengembalikan format `application/json` murni; secara ketat menghindari format HTML pada respons data.
  - **Mobile-First dan Dedicated Layouts:** Pemisahan struktur fisik template antara Desktop (power-dashboard analitik) dan Mobile (app-like bottom navigation dan card drawer).
  - **Standar Reusable Global CSS:** Seluruh styling antarmuka wajib menggunakan class global reusable; dilarang keras membuat class khusus/ad-hoc, tanpa *inline styles* (`style="..."`), dan tanpa tag `<style>` pada view.
  - **Standar Media Upload Base64:** Seluruh pengunggahan dokumentasi foto (KBM, PKL, Akreditasi) menggunakan string Base64 maksimal 1MB per berkas dengan preservasi kualitas visual.
  - **Pola Penamaan Berkas Semantik:** Standarisasi nama file otomatis `{modul}_u{user_id}_{YYYYMMDD_His}_{random_8char}.{ekstensi}` untuk audit trail dan pencegahan tabrakan nama berkas.
  - **Arsitektur Akun Sivitas (4 Tipe Akun):** Klasifikasi tingkat pengguna `super_admin`, `admin`, `guru`, dan `siswa` dengan profil relasional terpisah dan login fleksibel (NISN/NIP/Username/Email).
  - **Sistem Menu User-Centric:** Alokasi menu berbasis pengguna untuk mengakomodasi peran majemuk guru tanpa duplikasi menu.
- **Dokumentasi Proyek Komprehensif:**
  - `README.md`: Gambaran platform, badges arsitektur, dan panduan quick start Docker.
  - `TODO.md`: Roadmap tahapan implementasi dari Fase 1 hingga Fase 6.
  - `detail/DESIGN.md`: Spesifikasi teknis, diagram arsitektur bridging, skema data, dan keamanan.
  - `detail/docs/BRANDING.md`: Filosofi penamaan brand INFORA, palet warna, dan aset logo.

### Removed
- Seluruh referensi modul presensi digital / absensi / RFID / geofencing dari template lama yang tidak relevan dengan fokus SIM SMA dan SMK.
- Berkas dokumentasi redundan `detail/README.md`.
