# Standar Arsitektur Ramping & Efisiensi Konteks Agent (Lean Architecture & Agent Efficiency)

Aturan ini mengikat seluruh pengembang dan agen AI (Antigravity, Claude, Cursor, Copilot) yang bekerja pada proyek INFORA Generasi Baru.

---

## 1. Masalah Utama yang Dilarang Keras (Never Repeat These Mistakes)
Dari evaluasi proyek INFORA v1, ditemukan pola overengineering yang merusak produktivitas:
1. **Ledakan Berkas (*File Explosion* / *Pseudo-Separation of Concerns*):** Satu fitur CRUD sederhana menghasilkan 11–15 file (~1.800–2.300 baris kode).
2. **Duplikasi Form Request:** Membuat dua file `StoreXRequest` dan `UpdateXRequest` yang 100% identik.
3. **Fragmentasi View Berlebihan:** Memecah form menjadi `index.blade.php`, `modal-create.blade.php`, `modal-edit.blade.php`, dan `modal-delete.blade.php`.
4. **CSS Monolitik Tanpa Manfaat Tailwind:** Menulis 4.000 baris custom CSS padahal Tailwind CSS sudah terpasang.
5. **Over-Analysis Berbelit-belit pada Fitur Sederhana:** Menghabiskan puluhan ribu token dan turn percakapan hanya untuk merencanakan CRUD standar.

---

## 2. Prinsip Lean MVC: Maksimal 3–5 Berkas per Fitur CRUD
Setiap modul CRUD standar hanya boleh terdiri dari berkas-berkas berikut:
1. **Migration:** `database/migrations/xxxx_create_[table]_table.php` (1 file)
2. **Model:** `app/Models/[Model].php` (1 file)
3. **Controller:** `app/Http/Controllers/.../[Model]Controller.php` (1 file)
4. **Form Request Tunggal:** `app/Http/Requests/.../[Model]Request.php` (0–1 file, satukan Store & Update)
5. **View:** `resources/views/.../index.blade.php` (1 file dengan modal/form terpadu)
6. **Feature Test:** `tests/Feature/[Model]Test.php` (1 file)

**Total: Maksimal 4–5 file.** Dilarang memecah menjadi lebih dari 5 file kecuali ada kebutuhan arsitektural yang disetujui secara eksplisit oleh pengguna.

---

## 3. Aturan Form Request & Validasi
- **Satukan Store & Update ke 1 Berkas:**
  ```php
  class JurusanRequest extends FormRequest
  {
      public function authorize(): bool
      {
          return true;
      }

      public function rules(): array
      {
          $id = $this->route('jurusan')?->id;

          return [
              'school_id' => ['required', 'integer', 'exists:schools,id'],
              'kode'      => ['required', 'string', 'max:20', Rule::unique('jurusan', 'kode')->ignore($id)],
              'nama'      => ['required', 'string', 'max:150'],
          ];
      }
  }
  ```
- Jika form hanya terdiri dari 2–4 field sederhana, utamakan validasi inline di Controller:
  ```php
  $validated = $request->validate([
      'nama' => ['required', 'string', 'max:100'],
  ]);
  ```

---

## 4. Aturan Blade Views & Modal Form
- **Satukan Form Tambah & Edit:** Jangan buat `modal-create` dan `modal-edit` terpisah jika inputnya sama. Gunakan satu modal form reusable. Saat tombol edit diklik, ubah atribut `action` form, tambahkan `@method('PUT')`, dan isi nilai input.
- **Satu Modal Hapus Universal Global:** Tempatkan modal dialog konfirmasi hapus di layout utama (`layouts/app.blade.php`). Tombol hapus di tabel mana pun cukup memanggil modal global tersebut dengan parameter nama item dan URL action.

---

## 5. Aturan Styling: Tailwind Utility First
- Gunakan class utilitas Tailwind langsung pada markup Blade.
- Dilarang membuat class CSS kustom ad-hoc atau class tiruan utilitas di file CSS jika Tailwind sudah bisa menanganinya (misal: gunakan `p-4 bg-white rounded-xl shadow-sm border border-slate-200` alih-alih membuat class `.card-surface-v2`).

---

## 6. Aturan AI Agent: Zero Convoluted Over-Analysis & Efisiensi Konteks
1. **Langsung Eksekusi Ramping (*Direct & Lean Execution*):**
   Untuk permintaan CRUD atau fitur standar, agent dilarang menyusun proposal arsitektur bertele-tele yang memakan banyak turn dan context. Segera buat migrasi, model, controller, view, dan test yang ramping.
2. **Jaga Context Window Tetap Bersih:**
   Hanya buka dan sunting berkas yang relevan langsung. Jangan membaca seluruh folder tanpa filter.
3. **Zero Dead Code:**
   Dilarang meninggalkan file view atau controller cadangan yang tidak pernah di-render atau dipanggil.
