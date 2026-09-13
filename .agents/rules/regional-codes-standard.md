# Standar Kode Wilayah Administratif Pemerintahan (KEMENDAGRI)

## 1. Kewajiban Penggunaan Standar Kemendagri (Bukan BPS)
- Seluruh kode wilayah administratif dalam platform INFORA (Provinsi, Kabupaten/Kota, Kecamatan, dan Kelurahan/Desa) **WAJIB** menggunakan standar resmi **Kementerian Dalam Negeri Republik Indonesia (Kemendagri)**.
- Dilarang keras menggunakan kode wilayah statistik (Wilkerstat) dari Badan Pusat Statistik (BPS) sebagai kode entitas wilayah utama.

## 2. Peringatan Ketidakcocokan Kode (BPS vs Kemendagri)
Kode wilayah BPS dan Kemendagri sering kali berbeda secara signifikan pada level kabupaten/kota. Jangan pernah berasumsi keduanya identik:
- **Sulawesi Selatan (Kode Provinsi 73):**
  - Kabupaten Bone: Kemendagri = `7308` (BPS = `7311`)
  - Kabupaten Maros: Kemendagri = `7309` (BPS = `7308`)
  - Kabupaten Pangkajene dan Kepulauan: Kemendagri = `7310` (BPS = `7309`)
  - Kabupaten Barru: Kemendagri = `7311` (BPS = `7310`)
  - Kabupaten Luwu Timur: Kemendagri = `7324` (BPS = `7325`)
- **DKI Jakarta (Kode Provinsi 31):**
  - Kota Administrasi Jakarta Pusat: Kemendagri = `3171` (BPS = `3173`)
  - Kota Administrasi Jakarta Utara: Kemendagri = `3172` (BPS = `3175`)
  - Kota Administrasi Jakarta Barat: Kemendagri = `3173` (BPS = `3174`)
  - Kota Administrasi Jakarta Selatan: Kemendagri = `3174` (BPS = `3171`)
  - Kota Administrasi Jakarta Timur: Kemendagri = `3175` (BPS = `3172`)

## 3. Rujukan Validasi Resmi
Jika memerlukan pemetaan atau penambahan data wilayah baru:
1. Gunakan data bridging resmi BPS-Kemendagri pada portal SIG BPS:
   - URL: `https://sig.bps.go.id/bridging-kode/index`
   - Endpoint: `/rest-bridging-dagri/getwilayah?level={tingkat}&parent={kode_induk}`
   - Ambil atribut **`kode_dagri`**, jangan mengambil `kode_bps`.
2. Atau rujuk Kepmendagri No. 100.1.1-6117 / Permendagri No. 72 Tahun 2019 / Permendagri No. 137 Tahun 2017.

## 4. Format Penyimpanan & Konsistensi Awalan
- Simpan kode wilayah sebagai string numerik murni tanpa tanda titik (misal: Kemendagri `73.08` disimpan sebagai `7308`).
- Pastikan 2 digit pertama kode kabupaten/kota selalu identik dengan kode provinsi induknya.

## 5. Standar Kode & Verifikasi Kelurahan vs Desa (Tingkat 4)
- Kode wilayah Kemendagri tingkat 4 terdiri dari 10 digit (misal `7308182003`):
  - 2 digit: Provinsi (`73`)
  - 2 digit: Kabupaten/Kota (`08`)
  - 2 digit: Kecamatan (`18`)
  - 4 digit: Entitas Desa/Kelurahan:
    - **Kelurahan** diawali angka `1` (misal `1001` -> `7308181001` Pompanua).
    - **Desa** diawali angka `2` (misal `2003` -> `7308182003` Welado).
- Saat melakukan seeding atau validasi wilayah kecamatan, **WAJIB** mengecek kelengkapan seluruh desa/kelurahan dari endpoint bridging resmi:
  `https://sig.bps.go.id/rest-bridging-dagri/getwilayah?level=desa&parent={kode_kecamatan_bertitik}`
- Jangan pernah memotong atau melewatkan desa/kelurahan (seperti Welado, Pinceng Pute, Labissa) karena akan menggeser kode urutan wilayah lainnya.

