# 🧩 Panduan Lengkap Blade Components — INFORA

Dokumentasi resmi ini merangkum seluruh **Anonymous Blade Components** yang tersedia di aplikasi **INFORA** (`resources/views/components/`). Komponen-komponen ini dirancang untuk memastikan konsistensi antarmuka pengguna (UI/UX), mereduksi pengulangan kode (*boilerplate*), dan menyatu secara native dengan sistem CSS proyek di [`resources/css/app.css`](file:///home/gania/infora_project/resources/css/app.css).

---

## 📚 Daftar Isi

1. [Komponen Form & Input](#-1-komponen-form--input)
   - [`<x-form-input>`](#x-form-input)
   - [`<x-form-select>`](#x-form-select)
   - [`<x-form-textarea>`](#x-form-textarea)
   - [`<x-form-checkbox>`](#x-form-checkbox)
   - [`<x-searchable-select>`](#x-searchable-select)
   - [`<x-icon-picker>`](#x-icon-picker)
2. [Komponen Halaman & Layout](#-2-komponen-halaman--layout)
   - [`<x-page-header>`](#x-page-header)
   - [`<x-stats-grid>`](#x-stats-grid)
   - [`<x-stat-card>`](#x-stat-card)
   - [`<x-alert>`](#x-alert)
3. [Komponen Tabel & Daftar Data](#-3-komponen-tabel--daftar-data)
   - [`<x-data-table>`](#x-data-table)
   - [`<x-search-toolbar>`](#x-search-toolbar)
   - [`<x-table-actions>`](#x-table-actions)
   - [`<x-empty-state>`](#x-empty-state)
   - [`<x-pagination-summary>`](#x-pagination-summary)
   - [`<x-pagination>`](#x-pagination)
4. [Komponen Dialog & Modal](#-4-komponen-dialog--modal)
   - [`<x-modal>`](#x-modal)
   - [`<x-progress-modal>`](#x-progress-modal)
5. [Komponen Grafis & Ikon](#-5-komponen-grafis--ikon)
   - [`<x-icon>`](#x-icon)
6. [Panduan Bagi Pengembang Baru](#-panduan-kontribusi-bagi-pengembang-baru)

---

## 📝 1. Komponen Form & Input

Setiap komponen form standar secara otomatis mengotomatiskan:
- Pembungkus `<div class="form-group">`
- Label dengan tanda bintang `<span class="text-danger">*</span>` bila `required` aktif
- Retensi input lama `old($name, $value)`
- Border merah `.border-danger` saat validasi gagal
- Pesan kesalahan validasi otomatis via `<div class="form-error">{{ $message }}</div>`
- Meneruskan atribut HTML native (`placeholder`, `maxlength`, `min`, `max`, `step`, dll) via `$attributes`.

---

### `<x-form-input>`
Komponen universal untuk input teks, angka, password, email, tanggal, maupun input tersembunyi (*hidden input*).

#### Props
| Prop | Tipe | Default | Wajib? | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `name` | `string` | - | **Ya** | Nama field input HTML & nama kunci validasi request. |
| `label` | `string` | `null` | Opsional | Label teks di atas input. |
| `id` | `string` | `$name` | Opsional | ID elemen input. Otomatis disamakan dengan `name` jika tidak diisi. |
| `type` | `string` | `'text'` | Opsional | Tipe input (`text`, `number`, `password`, `email`, `hidden`, dll). |
| `value` | `mixed` | `null` | Opsional | Nilai bawaan input (akan ditimpa oleh `old($name)` jika tersedia). |
| `hint` | `string` | `null` | Opsional | Teks petunjuk kecil di bawah input (`.form-hint`). |
| `required` | `bool` | `false` | Opsional | Menambahkan atribut `required` dan tanda bintang merah pada label. |
| `groupClass`| `string` | `''` | Opsional | Class CSS tambahan pada elemen `.form-group`. |

#### Contoh Penggunaan
```blade
{{-- Input Teks Standar dengan Hint & Validasi --}}
<x-form-input
    name="kode"
    label="Kode Jurusan"
    placeholder="Contoh: RPL, TKJ, MIPA"
    maxlength="20"
    hint="Kode unik pengenal jurusan (otomatis kapital)."
    required
/>

{{-- Input Non-aktif (Disabled) dengan Nilai Dinamis --}}
<x-form-input
    name="school_name"
    label="Unit Sekolah"
    :value="$selectedSchool?->name ?? '-'"
    hint="Jurusan akan otomatis terhubung ke unit sekolah ini."
    disabled
/>

{{-- Input Hidden (Otomatis tanpa wrapper .form-group) --}}
<x-form-input type="hidden" name="school_id" :value="$selectedSchoolId" />
```

---

### `<x-form-select>`
Komponen dropdown select standar dengan dukungan placeholder dan opsi kustom via default slot.

#### Props
| Prop | Tipe | Default | Wajib? | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `name` | `string` | - | **Ya** | Nama field select & validasi. |
| `label` | `string` | `null` | Opsional | Label teks dropdown. |
| `id` | `string` | `$name` | Opsional | ID elemen select. |
| `selected` | `mixed` | `null` | Opsional | Nilai yang terpilih secara bawaan. |
| `placeholder`| `string` | `null` | Opsional | Opsi pertama kosong/pilihan awal (misal: `"-- Pilih Jenjang --"`). |
| `hint` | `string` | `null` | Opsional | Teks petunjuk di bawah select. |
| `required` | `bool` | `false` | Opsional | Menandai field wajib dipilih. |
| `groupClass`| `string` | `''` | Opsional | Class CSS tambahan pada `.form-group`. |

#### Slots
- **`{{ $slot }}`**: Berisi tag-tag `<option>`.

#### Contoh Penggunaan
```blade
<x-form-select
    name="jenjang"
    id="create_jenjang"
    label="Jenjang Pendidikan"
    placeholder="-- Pilih Jenjang --"
    required>
    <option value="SMK" {{ old('jenjang', $selectedSchool?->school_type) === 'SMK' ? 'selected' : '' }}>SMK (Konsentrasi Keahlian)</option>
    <option value="SMA" {{ old('jenjang', $selectedSchool?->school_type) === 'SMA' ? 'selected' : '' }}>SMA (Peminatan / Fase)</option>
</x-form-select>
```

---

### `<x-form-textarea>`
Komponen input teks panjang multiline terintegrasi dengan class `.form-textarea`.

#### Props
| Prop | Tipe | Default | Wajib? | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `name` | `string` | - | **Ya** | Nama field textarea. |
| `label` | `string` | `null` | Opsional | Label teks di atas textarea. |
| `id` | `string` | `$name` | Opsional | ID elemen textarea. |
| `value` | `string` | `null` | Opsional | Isi teks bawaan textarea. |
| `rows` | `int` | `3` | Opsional | Jumlah baris tinggi textarea. |
| `hint` | `string` | `null` | Opsional | Teks petunjuk di bawah textarea. |
| `required` | `bool` | `false` | Opsional | Menandai field wajib diisi. |
| `groupClass`| `string` | `''` | Opsional | Class CSS tambahan pada `.form-group`. |

#### Contoh Penggunaan
```blade
<x-form-textarea
    name="deskripsi"
    id="create_deskripsi"
    label="Keterangan Tambahan (Opsional)"
    placeholder="Deskripsi singkat mengenai jurusan atau profil keahlian..."
    rows="4"
    hint="Maksimal 1000 karakter."
/>
```

---

### `<x-form-checkbox>`
Komponen kotak centang status atau toggle boolean dengan struktur `.form-check`.

#### Props
| Prop | Tipe | Default | Wajib? | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `name` | `string` | - | **Ya** | Nama field checkbox. |
| `label` | `string` | - | **Ya** | Label teks di samping kotak centang. |
| `id` | `string` | `$name` | Opsional | ID elemen input checkbox. |
| `value` | `string` | `'1'` | Opsional | Nilai yang dikirim saat tercentang. |
| `checked` | `bool` | `false` | Opsional | Status centang awal. |
| `hint` | `string` | `null` | Opsional | Petunjuk keterangan tambahan. |
| `groupClass`| `string` | `''` | Opsional | Class CSS tambahan pada `.form-group`. |

#### Contoh Penggunaan
```blade
<x-form-checkbox
    name="is_active"
    id="create_is_active"
    label="Status Jurusan Aktif (Dapat digunakan pada Rombel dan Kurikulum)"
    value="1"
    :checked="old('is_active', '1') === '1'"
/>
```

---

### `<x-searchable-select>`
Komponen dropdown relasi dinamis dengan fitur pencarian real-time dan cascading filter (misal: Provinsi ➔ Kabupaten ➔ Kecamatan ➔ Kelurahan).

#### Props Utama
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `name` | `string` | - | Nama field input tersembunyi yang menyimpan ID terpilih. |
| `label` | `string` | `null` | Label field. |
| `endpoint` | `string` | `null` | URL JSON endpoint untuk mengambil data pencarian. |
| `placeholder` | `string` | `'Pilih...'` | Teks placeholder input pencarian. |
| `value` | `mixed` | `null` | ID nilai yang sedang terpilih. |
| `displayValue` | `string` | `null` | Teks label yang ditampilkan untuk nilai terpilih. |
| `parentSelect` | `string` | `null` | ID elemen parent jika menggunakan relasi berjenjang (*cascading*). |

#### Contoh Penggunaan
```blade
<x-searchable-select
    name="kabupaten_id"
    label="Kabupaten / Kota"
    endpoint="{{ route('wilayah.dropdown.kabupaten') }}"
    placeholder="Cari kabupaten..."
    :value="old('kabupaten_id', $school->kabupaten_id)"
    :display-value="$school->kabupaten?->nama"
    parent-select="provinsi_id"
    required
/>
```

---

### `<x-icon-picker>`
Komponen input interaktif untuk memilih ikon visual dengan katalog ikon bawaan INFORA.

#### Props Utama
- `name` (*wajib*): Nama input pengirim nilai ikon (misal: `icon`).
- `label` (*opsional*, default: `'Pilih Ikon'`): Label teks.
- `selected` (*opsional*): Nama ikon yang terpilih saat ini.

#### Contoh Penggunaan
```blade
<x-icon-picker
    name="icon"
    label="Ikon Menu"
    :selected="old('icon', $menu->icon ?? 'circle')"
    hint="Klik untuk membuka katalog ikon sistem."
/>
```

---

## 🏛️ 2. Komponen Halaman & Layout

### `<x-page-header>`
Menampilkan header halaman standar lengkap dengan judul, subjudul, dan slot aksi (tombol tambah, filter, dsb).

#### Props
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `title` | `string` | - | Judul utama halaman (elemen `h2.page-title`). |
| `subtitle` | `string` | `null` | Deskripsi atau navigasi rekam jejak (*breadcrumb*). |

#### Slots
- **`<x-slot:actions>`**: Tombol-tombol aksi di sebelah kanan header.

#### Contoh Penggunaan
```blade
<x-page-header
    title="Master Data Jurusan"
    subtitle="Kelola data konsentrasi keahlian SMK dan peminatan SMA">
    <x-slot:actions>
        <button type="button" class="btn-primary" id="btnOpenCreateModal">
            <x-icon name="plus" size="18" />
            <span>Tambah Jurusan</span>
        </button>
    </x-slot:actions>
</x-page-header>
```

---

### `<x-stats-grid>` & `<x-stat-card>`
Menampilkan kartu metrik statistik ringkasan (KPI) secara responsif di bagian atas halaman index atau dashboard.

#### `<x-stat-card>` Props
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `label` | `string` | - | Label metrik (misal: "Total Jurusan", "Siswa Aktif"). |
| `value` | `string\|int` | `null` | Angka/nilai metrik. Jika kosong, membaca dari default slot. |
| `icon` | `string` | `null` | Nama ikon yang dirender di samping kiri. |
| `color` | `string` | `'primary'` | Variasi tema warna (`primary`, `success`, `warning`, `danger`, `info`). |

#### Contoh Penggunaan
```blade
<x-stats-grid>
    <x-stat-card
        icon="book-open"
        label="Total Jurusan"
        :value="$totalCount"
        color="primary"
    />
    <x-stat-card
        icon="check-circle"
        label="Jurusan Aktif"
        :value="$activeCount"
        color="success"
    />
    <x-stat-card
        icon="layers"
        label="Bidang Keahlian"
        :value="$bidangCount"
        color="info"
    />
</x-stats-grid>
```

---

### `<x-alert>`
Komponen notifikasi pesan kilas (*flash messages*) atau peringatan validasi.

#### Props
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `type` | `string` | `'success'` | Jenis alert (`success`, `error`, `warning`, `info`, `validation`). |
| `title` | `string` | `null` | Judul tebal notifikasi. |
| `message` | `string` | `null` | Isi pesan teks. Jika null, otomatis membaca `session($type)`. |

#### Contoh Penggunaan
```blade
{{-- Otomatis membaca session('success') dari controller --}}
<x-alert type="success" />

{{-- Notifikasi kustom statis --}}
<x-alert
    type="warning"
    title="Peringatan Tahun Ajaran"
    message="Tahun ajaran ini memiliki semester yang sedang berjalan."
/>
```

---

## 📊 3. Komponen Tabel & Daftar Data

### `<x-data-table>` & `<x-search-toolbar>`
Komponen terpadu untuk membungkus tabel data dengan form pencarian, pemilih jumlah baris (*per-page*), dan container responsif.

#### Props `<x-data-table>`
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `route` | `string` | - | URL aksi pencarian (misal: `route('master.data-jurusan')`). |
| `searchPlaceholder` | `string` | `'Cari data...'` | Placeholder kotak input cari. |
| `perPage` | `int` | `15` | Nilai per_page aktif saat ini. |
| `perPageText` | `string` | `'data'` | Teks di samping per_page (misal: `'jurusan'`). |

#### Slots
- **`{{ $slot }}`**: Elemen `<thead>` dan `<tbody>` dari tabel data.
- **`<x-slot:filters>`**: Input filter tambahan (select filter status, kategori, dsb) yang diletakkan di toolbar.
- **`<x-slot:footer>`**: Bagian bawah tabel (biasanya diisi `<x-pagination-summary>`).

#### Contoh Penggunaan
```blade
<x-data-table
    :route="route('master.data-jurusan')"
    search-placeholder="Cari nama, kode jurusan..."
    :per-page="$perPageInput"
    per-page-text="jurusan">

    {{-- Filter Tambahan di Toolbar --}}
    <x-slot:filters>
        <select name="status" class="form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Non-Aktif</option>
        </select>
    </x-slot:filters>

    <thead>
        <tr>
            <th>Kode</th>
            <th>Nama Jurusan</th>
            <th>Jenjang</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($jurusans as $jurusan)
            <tr>
                <td>{{ $jurusan->kode }}</td>
                <td>{{ $jurusan->nama }}</td>
                <td>{{ $jurusan->jenjang }}</td>
                <td>...</td>
                <td>
                    <x-table-actions align="right">
                        <button class="btn-table-action" ...>Edit</button>
                    </x-table-actions>
                </td>
            </tr>
        @empty
            <x-empty-state
                colspan="5"
                title="Data Jurusan Tidak Ditemukan"
                text="Belum ada data jurusan yang terdaftar pada sekolah ini."
            />
        @endforelse
    </tbody>

    <x-slot:footer>
        <x-pagination-summary :paginator="$jurusans" />
    </x-slot:footer>
</x-data-table>
```

---

### `<x-table-actions>`
Membungkus tombol-tombol aksi pada baris tabel (Edit, Hapus, Detail) dengan perataan yang rapi.

#### Props
- `align` (*opsional*, default: `'right'`): Perataan tombol (`'right'`, `'center'`, `'left'`).

---

### `<x-empty-state>`
Menampilkan ilustrasi ikon dan pesan ramah saat tabel tidak memiliki data atau pencarian nihil.

#### Props
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `colspan` | `int` | `5` | Jumlah kolom tabel yang direntangkan (`colspan`). |
| `title` | `string` | `null` | Judul kondisi kosong. |
| `text` | `string` | `'Belum ada data...'` | Pesan deskripsi atau saran tindakan. |
| `icon` | `string` | `'search'` | Nama ikon SVG di tengah. |
| `action` | `slot` | `null` | Tombol CTA (opsional) seperti "Tambah Data Baru". |

---

### `<x-pagination-summary>` & `<x-pagination>`
- **`<x-pagination>`**: Komponen tampilan paginasi universal yang didaftarkan secara global pada [`AppServiceProvider`](file:///home/gania/infora_project/app/Providers/AppServiceProvider.php) (`Paginator::defaultView('components.pagination')`).
- **`<x-pagination-summary>`**: Pembungkus `<tfoot>` tabel yang merender links paginasi secara otomatis bila data melebihi 1 halaman.

#### Contoh Penggunaan
```blade
<x-pagination-summary :paginator="$jurusans" />
```

---

## 🪟 4. Komponen Dialog & Modal

### `<x-modal>`
Komponen dialog interaktif yang fleksibel untuk modal informasi, formulir data (Create, Edit), maupun preset konfirmasi hapus (*delete confirmation*). Dilengkapi pembungkus form otomatis, `@csrf`, method spoofing (`@method`), serta integrasi penuh dengan modul JavaScript `InforaModals`.

#### Variasi / Mode Tampilan
1. **Mode Standar (`@default`)**: Digunakan untuk formulir Tambah (*Create*), Ubah (*Edit*), maupun dialog rincian/informasi.
2. **Mode Preset Delete (`name="delete"`)**: Tampilan elegan khusus konfirmasi hapus (*danger theme*) dengan ambient halo icon merah, judul peringatan, kartu detail item dinamis, serta tombol aksi simetris (Batal & Hapus).

#### Props
| Prop | Tipe | Default | Mode | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `name` | `string` | `null` | Semua | Nama varian modal. Gunakan `name="delete"` untuk mengaktifkan preset modal konfirmasi hapus. |
| `id` | `string` | `null` | Semua | **Wajib**. ID unik elemen modal backdrop (misal: `modalCreateJurusan`, `modalDeleteJurusan`). |
| `title` | `string` | `null` | Semua | Judul header modal (pada modal delete dapat diisi langsung atau dinamis via JavaScript). |
| `subtitle` | `string` | `null` | Standar | Subjudul deskripsi kecil di bawah judul header. |
| `size` | `string` | `'default'` | Standar | Ukuran lebar dialog modal (`'default'`, `'sm'`, atau `'lg'`). Pada modal delete otomatis `'sm'`. |
| `action` | `string` | `null` | Semua | URL tujuan form action. Jika diisi/terdapat `method`, modal otomatis membungkus body dengan tag `<form>`. |
| `method` | `string` | `null` | Semua | Method HTTP form (`'POST'`, `'PUT'`, `'PATCH'`, `'DELETE'`). Pada modal delete otomatis `'DELETE'`. |
| `formId` | `string` | `null` | Semua | Atribut `id` untuk elemen tag `<form>`. |
| `messageId` | `string` | `null` | Delete | ID elemen paragraf pesan konfirmasi untuk dimanipulasi via script. |
| `badgeId` | `string` | `null` | Delete | ID elemen badge kategori/kode untuk dimanipulasi via script. |
| `itemTitleId` | `string` | `null` | Delete | ID elemen judul/nama item yang akan dihapus untuk dimanipulasi via script. |
| `cancelBtnId` | `string` | `null` | Delete | ID tombol "Batal" untuk dihubungkan dengan `InforaModals.setup`. |
| `confirmBtnId` | `string` | `null` | Delete | ID tombol "Hapus" (submit). |

#### Slots
- **`{{ $slot }}`**: Konten utama modal (field formulir pada modal standar, atau custom body pada modal delete).
- **`<x-slot:footer>`**: Tombol aksi bagian bawah pada modal standar (Batal, Simpan, dll).
- **`<x-slot:hiddenInputs>`**: Input hidden tambahan khusus pada modal delete (misal: filter `view`).

#### Contoh Penggunaan 1: Modal Form Tambah (Create)
```blade
<x-modal
    id="modalCreateJurusan"
    title="Tambah Data Jurusan Baru"
    subtitle="Tambahkan data konsentrasi keahlian baru"
    size="lg"
    action="{{ route('master.data-jurusan.store') }}"
    form-id="formCreateJurusan">

    {{-- Body Formulir --}}
    <x-form-input name="kode" label="Kode Jurusan" required />
    <x-form-input name="nama" label="Nama Jurusan" required />

    {{-- Footer Modal --}}
    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateJurusan">Batal</button>
        <button type="submit" class="btn-primary">Simpan</button>
    </x-slot:footer>
</x-modal>
```

#### Contoh Penggunaan 2: Modal Form Ubah (Edit)
```blade
<x-modal
    id="modalEditJurusan"
    title="Ubah Data Jurusan"
    subtitle="Perbarui rincian informasi jurusan"
    size="lg"
    method="PUT"
    form-id="formEditJurusan">

    <x-form-input name="kode" id="edit_kode" label="Kode Jurusan" required />
    <x-form-input name="nama" id="edit_nama" label="Nama Jurusan" required />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditJurusan">Batal</button>
        <button type="submit" class="btn-primary">Perbarui Data</button>
    </x-slot:footer>
</x-modal>
```

#### Contoh Penggunaan 3: Preset Modal Konfirmasi Hapus (Delete)
```blade
<x-modal
    name="delete"
    id="modalDeleteJurusan"
    form-id="formDeleteJurusan"
    cancel-btn-id="btnCancelDeleteJurusan"
    confirm-btn-id="btnConfirmDeleteJurusan"
    message-id="deleteJurusanMessage"
    badge-id="deleteJurusanBadge"
    item-title-id="deleteJurusanTitle" />
```

#### Integrasi JavaScript (`InforaModals`)
Seluruh interaksi pembukaan, penutupan, maupun penyuntikan data modal ditangani melalui modul JavaScript `InforaModals`:

```javascript
document.addEventListener('DOMContentLoaded', function () {
    // 1. Setup penutupan modal (Tombol Batal, Klik Backdrop, dan Tombol Escape)
    InforaModals.setup('modalCreateJurusan', { cancelBtnId: 'btnCancelCreateJurusan' });
    InforaModals.setup('modalEditJurusan', { cancelBtnId: 'btnCancelEditJurusan' });
    InforaModals.setup('modalDeleteJurusan', { cancelBtnId: 'btnCancelDeleteJurusan' });

    // 2. Hubungkan tombol buka modal tambah
    InforaModals.bindOpenButton('btnOpenCreateJurusan', 'modalCreateJurusan');

    // 3. Hubungkan tombol edit di tabel (otomatis mengisi form.action & nilai input)
    InforaModals.bindEditButtons('.btn-open-edit-jurusan', 'modalEditJurusan', function (modal, data, formEdit) {
        document.getElementById('edit_kode').value = data.kode || '';
        document.getElementById('edit_nama').value = data.nama || '';
    });

    // 4. Hubungkan tombol hapus di tabel (otomatis mengisi form.action ke URL destroy item)
    InforaModals.bindDeleteButtons('.btn-open-delete-jurusan', 'modalDeleteJurusan', function (modal, btn) {
        document.getElementById('modalDeleteJurusanTitle').textContent = 'Hapus Jurusan ' + btn.dataset.kode + '?';
        document.getElementById('deleteJurusanMessage').textContent = 'Apakah Anda yakin ingin menghapus jurusan berikut?';
        document.getElementById('deleteJurusanBadge').textContent = btn.dataset.kode || '-';
        document.getElementById('deleteJurusanTitle').textContent = btn.dataset.nama || '-';
    });
});
```

---

### `<x-progress-modal>`
Modal indikator proses beranimasi halus (*smooth gradient*) yang terintegrasi dengan mesin `window.InforaProgress` untuk operasi berat seperti backup database atau restore arsip.

#### Contoh Penggunaan
```blade
<x-progress-modal
    id="inforaProgressModal"
    title="Memulihkan Basis Data"
    initial-status="Mempersiapkan berkas snapshot..."
/>
```

---

## 🎨 5. Komponen Grafis & Ikon

### `<x-icon>`
Merender SVG icon native Lucide/Feather secara instan tanpa perlu memuat file font eksternal.

#### Props
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `name` | `string` | - | **Wajib**. Nama ikon (misal: `plus`, `edit`, `trash`, `school`, `calendar`, `database`, `user`, `search`, `check`, `arrow-right`, dsb). |
| `size` | `string\|int` | `null` | Ukuran pixel ikon (misal: `18`, `24`). |
| `class` | `string` | `''` | Class CSS tambahan (misal: `text-danger`, `mr-2`). |

#### Contoh Penggunaan
```blade
<x-icon name="plus" size="18" />
<x-icon name="trash-2" size="16" class="text-danger" />
<x-icon name="school" size="24" />
```

---

## 🚀 Panduan Kontribusi Bagi Pengembang Baru

Jika Anda ingin menambahkan atau memperluas komponen di repositori INFORA:

### 1. Standar Pembuatan Komponen
- Gunakan format **Anonymous Component** (hanya file Blade) kecuali jika komponen tersebut membutuhkan query database mandiri.
- Simpan berkas baru di `resources/views/components/{nama-komponen}.blade.php`.
- Gunakan perintah Artisan:
  ```bash
  php artisan make:component nama-komponen --view
  ```

### 2. Standar Props & Error Handling
- Selalu gunakan `@props([...])` di baris pertama file untuk mendokumentasikan input yang diterima.
- Untuk komponen form, selalu gunakan pola fallback error bag yang aman dari unit-testing:
  ```blade
  @php
      $errors = $errors ?? view()->shared('errors', new \Illuminate\Support\ViewErrorBag);
  @endphp
  ```
- Selalu gabungkan atribut native melalui `$attributes->merge(['class' => '...'])`.

### 3. Standar Pengujian (Testing)
Setiap komponen Blade wajib memiliki tes regresi di [`tests/Feature/BladeComponentsTest.php`](file:///home/gania/infora_project/tests/Feature/BladeComponentsTest.php) menggunakan Pest:
```php
test('nama-komponen merender atribut dengan benar', function () {
    $view = $this->blade('<x-nama-komponen prop="nilai" />');
    $view->assertSee('nilai');
});
```

Jalankan pengujian menggunakan:
```bash
php artisan test --filter=BladeComponentsTest
```

### 4. Standar Pemformatan Kode
Pastikan kode selalu lolos linting Laravel Pint sebelum membuat commit / pull request:
```bash
vendor/bin/pint --dirty --format agent
```
