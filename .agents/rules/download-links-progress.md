# File Download Links & Navigation Progress Loader Invariant

## 1. Karakteristik Unduhan Berkas (Content-Disposition)
- Tautan yang mengunduh berkas (`Content-Disposition: attachment`) menghasilkan respons biner tanpa memicu pergantian halaman (*page unload*).
- Jika sistem memiliki global progress modal (seperti `InforaProgress` di `resources/js/app.js`), pendengar klik biasa akan mengira browser sedang berpindah halaman dan menampilkan modal loading yang kemudian macet permanen (misal di 70%).

## 2. Standar Implementasi Tombol/Tautan Unduh
Setiap tag `<a>` yang berfungsi mengunduh berkas wajib:
1. Menyertakan atribut `data-no-progress` dan `download`:
   ```blade
   <a href="{{ route('...') }}" class="btn-download" data-no-progress download title="...">
   ```
2. Memiliki class `.btn-download` atau rute berpola `/download/`.
3. Global click listener di `resources/js/app.js` wajib mengecek dan mengabaikan link yang memiliki:
   - Atribut `data-no-progress`
   - Atribut `download`
   - Class `btn-download`
   - URL yang memuat `/download/`
