# Pipeline Unggah Gambar Base64 Terpusat (ImageUploader)

Aturan ini mengikat seluruh penanganan unggah berkas gambar (logo sekolah, foto profil sivitas, dokumentasi kegiatan, bukti fisik akreditasi) di aplikasi INFORA sesuai `detail/DESIGN.md` Seksi 2.4 poin 17 dan Seksi 7.

---

## 1. Standar Layanan Unggah: `App\Support\ImageUploader`
1. **Layanan Murni PHP Native:**
   - Seluruh pemrosesan gambar wajib menggunakan service terpusat `App\Support\ImageUploader::uploadBase64()`.
   - Dilarang membuat logika decode base64 atau manipulasi file gambar ad-hoc di dalam controller.
2. **Format Data URI Base64:**
   - Gambar dikirimkan melalui payload form sebagai string Data URI Base64 valid (`data:image/{type};base64,...`).
   - Ekstensi yang didukung secara ketat: `png`, `jpg`, `jpeg`, `webp`, `gif`, `svg`.
3. **Batasan Ukuran Berkas Ketat Maksimal 1MB:**
   - Batas maksimum ukuran biner gambar yang diunggah adalah **1 Megabyte** (`DEFAULT_MAX_BYTES = 1048576`).
   - Gambar yang melebihi batas 1MB akan ditolak secara otomatis oleh validator internal `ImageUploader`.
4. **Pola Penamaan Semantik Collision-Proof:**
   - Seluruh berkas yang tersimpan wajib mengikuti format penamaan baku:
     ```text
     {modul}_u{user_id}_{YYYYMMDD_His}_{random_8char}.{ekstensi}
     ```
   - Contoh: `sekolah_u1_20260926_094000_a1b2c3d4.webp`, `kbm_u7_20260905_143022_9f8e7d6c.jpg`.
5. **Pembersihan Berkas Lama:**
   - Gunakan `ImageUploader::delete($oldPath, 'public')` saat pengguna memperbarui atau menghapus gambar guna mencegah penumpukan file yatim (*orphaned files*).

---

## 2. Integrasi Komponen Blade UI: `<x-image-upload>`
1. **Gunakan Komponen Universal:**
   - Seluruh form yang memerlukan input gambar wajib menggunakan komponen Blade `<x-image-upload>`:
     ```blade
     <x-image-upload
         name="logo"
         label="Logo Sekolah"
         :current-image="$dataSekolah->logo_path"
         directory="logos"
         module="sekolah"
         shape="circle"
         hint="Format: PNG, JPG, WEBP. Maksimal 1 MB."
     />
     ```
2. **Modul JavaScript Pendamping:**
   - Modul `window.ImageUpload` di `resources/js/image-upload.js` menangani pembacaan file lokal pengguna via FileReader API, live preview instan di browser, konversi otomatis ke base64, dan penanda flag penghapusan foto (`{name}_remove`).
