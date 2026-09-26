# Kontrak Respons Pure JSON untuk Endpoint Asinkron & Bridging

Aturan ini mengikat seluruh perancangan endpoint API, cascading filter dropdown, dan data feed di platform INFORA sesuai `detail/DESIGN.md` Seksi 3.1 dan `README.md`.

---

## 1. Prinsip Kontrak Pure JSON (Zero HTML in Data Payloads)
1. **Wajib Format JSON Murni:**
   - Seluruh endpoint yang melayani pemanggilan asinkron (AJAX browser, filter bertingkat, kalender event feed, dan integrasi bridging eksternal) **wajib** mengembalikan response murni JSON:
     ```php
     return response()->json($data);
     ```
   - Header HTTP response wajib menyertakan `Content-Type: application/json`.
2. **Larangan Menyisipkan HTML:**
   - Dilarang keras merender potongan HTML Blade (`view('...')->render()`) atau menyisipkan tag HTML di dalam payload data JSON.
   - Perenderan data menjadi elemen antarmuka sepenuhnya menjadi tanggung jawab lapisan klien (JavaScript / Blade Components).

---

## 2. Standarisasi Format Respons & Status Code
1. **Struktur Data Bersih:**
   - Untuk endpoint query daftar (seperti cascading wilayah di `WilayahDropdownController` atau agenda di `KalenderAkademikController`), kembalikan array objek data secara langsung dengan kolom yang spesifik (`->get(['id', 'kode', 'nama'])`).
2. **Status Kode HTTP Semantik:**
   - `200 OK` untuk pembacaan data berhasil.
   - `201 Created` untuk pembuatan data baru via API.
   - `422 Unprocessable Content` untuk kegagalan validasi input data.
   - `403 Forbidden` jika pengguna tidak memiliki izin akses.
   - `404 Not Found` jika entitas tidak ditemukan.
