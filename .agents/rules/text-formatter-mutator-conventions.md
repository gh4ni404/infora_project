# Model Mutator Text Formatting & Casing Conventions

Aturan ini mengikat standarisasi pemformatan teks otomatis pada Model Eloquent melalui service terpusat `App\Support\TextFormatter` sesuai `detail/DESIGN.md` Seksi 6 poin 4.

---

## 1. Aturan Casing Berdasarkan Entitas
1. **Nama Modul (UPPERCASE):**
   - Nama modul sistem (`Module::$name`) wajib disimpan dalam huruf kapital penuh (UPPERCASE) guna menonjolkan fungsi pemisah kategori navigasi yang tegas.
   - Menggunakan: `TextFormatter::upper($value)`.
2. **Nama Menu, Sub-Menu, Jurusan, dan Wilayah (Title Case dengan Preservasi Akronim):**
   - Wajib diformat menjadi Title Case (*Capitalize Each Word*) dengan preservasi akronim penting dunia pendidikan dan pemerintahan:
     - Akronim yang wajib diproteksi tetap kapital: `SMK`, `SMA`, `MA`, `SIM`, `PKL`, `KBM`, `GTK`, `BAN-SM`, `RPP`, `IT`, `TU`, `IPA`, `IPS`, `BK`, `MIPA`, `TKJ`, `RPL`.
   - Menggunakan: `TextFormatter::titleCase($value)`.
3. **Singkatan Provinsi / Kode Singkat:**
   - Wajib disimpan dalam format UPPERCASE (`singkatan`).

---

## 2. Implementasi Dua Lapis (Frontend & Backend Mutator)
1. **Lapisan Frontend:**
   - Elemen input pada form Blade menyertakan atribut `data-transform="uppercase"` atau `data-transform="titlecase"`.
2. **Lapisan Backend Mutator:**
   - Model Eloquent mendefinisikan mutator modern menggunakan `Attribute::make()`:
     ```php
     use App\Support\TextFormatter;
     use Illuminate\Database\Eloquent\Casts\Attribute;

     protected function name(): Attribute
     {
         return Attribute::make(
             set: fn (?string $value) => TextFormatter::titleCase($value),
         );
     }
     ```
   - Mutator ini menjamin data tetap terformat rapi meskipun disimpan melalui Seeder, Factory, API, maupun form web.
