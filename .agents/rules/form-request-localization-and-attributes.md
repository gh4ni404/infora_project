# Form Request Validation, Indonesian Messages & Attributes Standard

Aturan ini mengikat seluruh logika validasi HTTP request dalam aplikasi INFORA.

---

## 1. Kewajiban Penggunaan Dedicated Form Request
1. **Dilarang Validasi Inline pada Controller:**
   - Dilarang menuliskan `$request->validate([...])` atau `Validator::make(...)` di dalam method controller CRUD (store dan update).
   - Seluruh validasi wajib didelegasikan ke class Form Request terpisah di bawah namespace `App\Http\Requests\{SubNamespace}\`.
2. **Pengelompokan Namespace Modul:**
   - Struktur Form Request wajib mencerminkan namespace controller:
     - `App\Http\Requests\Master\`
     - `App\Http\Requests\Wilayah\`
     - `App\Http\Requests\KonfigurasiSekolah\`
     - `App\Http\Requests\System\`
3. **Ekstraksi Data Teruji Saja:**
   - Controller hanya boleh memproses data yang telah teruji validasi melalui `$request->validated()`. Hindari memanggil `$request->all()` untuk mass assignment.

---

## 2. Standar Bahasa Indonesia untuk Messages & Attributes
1. **Wajib Mengimplementasikan `attributes(): array`:**
   - Setiap Form Request wajib mendefinisikan nama alias atribut dalam Bahasa Indonesia yang ramah pengguna, sehingga pesan kesalahan tidak menampilkan nama kolom teknis:
     ```php
     public function attributes(): array
     {
         return [
             'school_id' => 'Unit Sekolah',
             'nama' => 'Nama Jurusan',
             'kode' => 'Kode Jurusan',
             'is_active' => 'Status Aktif',
         ];
     }
     ```
2. **Wajib Mengimplementasikan `messages(): array`:**
   - Setiap Form Request wajib mendefinisikan pesan kesalahan yang deskriptif dan ramah sivitas sekolah dalam Bahasa Indonesia:
     ```php
     public function messages(): array
     {
         return [
             'nama.required' => 'Nama jurusan wajib diisi.',
             'kode.required' => 'Kode jurusan wajib diisi.',
             'school_id.required' => 'Pilihan unit sekolah wajib ditentukan.',
             'school_id.exists' => 'Unit sekolah yang dipilih tidak valid atau tidak terdaftar.',
         ];
     }
     ```
3. **Otorisasi Default:**
   - Method `authorize(): bool` pada Form Request mengembalikan `auth()->check()` untuk request di dalam area terproteksi autentikasi.
