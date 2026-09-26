# Modular Modal CRUD Architecture (Zero Standalone Edit Pages)

Aturan ini mengikat seluruh pengembangan modul administratif di INFORA sesuai dengan `detail/DESIGN.md` Seksi 2.4 poin 13.

---

## 1. Arsitektur Modal CRUD Terintegrasi (Zero-Reload Workflow)
1. **Dilarang Membuat Halaman Terpisah untuk Create & Edit:**
   - Dilarang membuat file view mandiri yang meng-`@extends('layouts.app')` untuk aksi `create.blade.php` atau `edit.blade.php` pada entitas administratif (Data Sekolah, Modul, Menu, Sub-Menu, Wilayah Administratif, Jurusan, dll).
   - Seluruh formulir Tambah (`create`) dan Ubah (`edit`) wajib diimplementasikan sebagai dialog modal interaktif di atas halaman `index.blade.php`.
2. **Pemisahan Berkas Blade Modular:**
   - Buat berkas `create.blade.php` dan `edit.blade.php` sebagai komponen Blade modular yang di-include (`@include('...create')`, `@include('...edit')`) ke dalam `index.blade.php`.
3. **Kompatibilitas Rute `edit()`:**
   - Metode `Controller::edit()` wajib mengalihkan (*redirect*) pengguna kembali ke route `index` guna menjaga kompatibilitas bookmark dan rute RESTful resource standard:
     ```php
     public function edit(Entity $entity): RedirectResponse
     {
         return redirect()->route('module.entity.index');
     }
     ```

---

## 2. Pengisian Data Dinamis & Auto-Reopen Validasi
1. **Pengisian Form Edit via Data Attributes & JavaScript:**
   - Tombol pemicu edit pada tabel data menyertakan atribut `data-*` (misal `data-school`, `data-jurusan`, `data-module`) berisi representasi JSON atau atribut model.
   - Event listener JavaScript membaca atribut tersebut dan mengisi input form modal secara instan sebelum modal dibuka via `InforaModals.open('modalEditXxx')`.
2. **Penanganan Validasi Server Cerdas (*Auto-Reopen Invariant*):**
   - Ketika terjadi kegagalan validasi backend (`$errors->any()`), modal dialog terkait wajib terbuka kembali secara otomatis saat halaman dirender ulang dengan data input (`old(...)`) tetap terjaga:
     ```blade
     @if ($errors->any() && old('_method') === 'PUT')
         <script>
             document.addEventListener('DOMContentLoaded', () => {
                 InforaModals.open('modalEdit');
             });
         </script>
     @elseif ($errors->any())
         <script>
             document.addEventListener('DOMContentLoaded', () => {
                 InforaModals.open('modalCreate');
             });
         </script>
     @endif
     ```

---

## 3. Struktur Flexbox Dialog Modal & Zero Clipping
1. **Sticky Header & Sticky Footer:**
   - Seluruh modal wajib menggunakan struktur `.modal-dialog form { display: flex; flex-direction: column; flex: 1; min-height: 0; }` dengan `.modal-header` dan `.modal-footer` terproteksi `flex-shrink: 0`.
   - Tombol aksi simpan/batal tidak boleh terpotong (*zero clipping*) pada resolusi layar apa pun.
2. **Dynamic Scrollbar:**
   - Area `.modal-body` wajib memanfaatkan overflow vertikal dengan custom slim scrollbar (6px) dan normalisasi margin input (`.modal-body .form-group { margin-bottom: 0; }`).
