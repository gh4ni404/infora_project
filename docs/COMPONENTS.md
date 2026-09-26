# 🧩 Panduan Lengkap Blade Components — INFORA

Dokumentasi resmi ini merangkum seluruh **23 Anonymous Blade Components** yang tersedia di aplikasi **INFORA** ([`resources/views/components/`](file:///home/gania/infora_project/resources/views/components/)). Komponen-komponen ini dirancang untuk memastikan konsistensi antarmuka pengguna (UI/UX), mereduksi pengulangan kode (*boilerplate*), dan menyatu secara native dengan sistem desain di [`resources/css/app.css`](file:///home/gania/infora_project/resources/css/app.css).

---

## 📚 Daftar Isi

1. [Komponen Form dan Input](#-1-komponen-form--input)
   - [`<x-form-input>`](#x-form-input)
   - [`<x-form-select>`](#x-form-select)
   - [`<x-form-textarea>`](#x-form-textarea)
   - [`<x-form-checkbox>`](#x-form-checkbox)
   - [`<x-searchable-select>`](#x-searchable-select)
   - [`<x-icon-picker>`](#x-icon-picker)
   - [`<x-image-upload>`](#x-image-upload)
2. [Komponen Halaman, Navigasi dan Metrik](#-2-komponen-halaman-navigasi--metrik)
   - [`<x-page-header>`](#x-page-header)
   - [`<x-tab-pills>` dan `<x-tab-pill>`](#x-tab-pills--x-tab-pill)
   - [`<x-stats-grid>` dan `<x-stat-card>`](#x-stats-grid--x-stat-card)
   - [`<x-alert>`](#x-alert)
   - [`<x-status-banner>`](#x-status-banner)
3. [Komponen Tabel dan Penyajian Data](#-3-komponen-tabel--penyajian-data)
   - [`<x-data-table>`](#x-data-table)
   - [`<x-search-toolbar>`](#x-search-toolbar)
   - [`<x-table-actions>`](#x-table-actions)
   - [`<x-empty-state>`](#x-empty-state)
   - [`<x-pagination-summary>`](#x-pagination-summary)
   - [`<x-pagination>`](#x-pagination)
4. [Komponen Dialog dan Modal](#-4-komponen-dialog--modal)
   - [`<x-modal>`](#x-modal)
   - [`<x-progress-modal>`](#x-progress-modal)
5. [Komponen Grafis dan Ikon](#-5-komponen-grafis--ikon)
   - [`<x-icon>`](#x-icon)
6. [Integrasi Modul JavaScript](#-6-integrasi-modul-javascript)
   - [`InforaModals`](#inforamodals)
   - [`SearchableSelect`](#searchableselect)
   - [`ImageUpload`](#imageupload)
   - [`InforaProgress`](#inforaprogress)
   - [`PermissionMatrix`](#permissionmatrix)
7. [Panduan Kontribusi Bagi Pengembang Baru](#-7-panduan-kontribusi-bagi-pengembang-baru)

---

## 📝 1. Komponen Form dan Input

Komponen form standar secara otomatis menyediakan:
- Pembungkus `<div class="form-group">` (kecuali tipe `hidden`).
- Label teks dengan tanda bintang `<span class="text-danger">*</span>` bila `required` aktif.
- Retensi input otomatis dari session via `old($name, $value)`.
- Border merah `.border-danger` saat field gagal validasi.
- Pesan kesalahan validasi otomatis via `<div class="form-error">{{ $message }}</div>`.
- Penerusan atribut HTML native (`placeholder`, `maxlength`, `min`, `max`, `step`, `disabled`, dll) via `$attributes`.

---

### `<x-form-input>`
Komponen universal untuk input teks, angka, password, email, tanggal, maupun input tersembunyi (*hidden input*).

#### Props
| Prop | Tipe | Default | Wajib? | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `name` | `string` | - | **Ya** | Nama field input HTML dan nama kunci validasi request. |
| `label` | `string` | `null` | Opsional | Label teks di atas input. |
| `id` | `string` | `$name` | Opsional | ID elemen input. Otomatis disamakan dengan `name` jika tidak diisi. |
| `type` | `string` | `'text'` | Opsional | Tipe input (`text`, `number`, `password`, `email`, `date`, `hidden`, dll). |
| `value` | `mixed` | `null` | Opsional | Nilai bawaan input (akan ditimpa oleh `old($name)` jika tersedia). |
| `hint` | `string` | `null` | Opsional | Teks petunjuk kecil di bawah input (`.form-hint`). |
| `required` | `bool` | `false` | Opsional | Menambahkan atribut `required` dan tanda bintang merah pada label. |
| `groupClass` | `string` | `''` | Opsional | Class CSS tambahan pada elemen pembungkus `.form-group`. |

#### Slots
- **`{{ $slot }}`**: Konten tambahan di dalam pembungkus `.form-group` (diletakkan di bagian paling bawah setelah pesan error).

#### Contoh Penggunaan
```blade
{{-- Input Teks Standar dengan Hint dan Validasi --}}
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
Komponen dropdown select standar dengan dukungan array key-value, Eloquent Collection, array bertingkat, opsi placeholder, serta closure atribut dinamis.

#### Props
| Prop | Tipe | Default | Wajib? | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `name` | `string` | - | **Ya** | Nama field select dan validasi. |
| `label` | `string` | `null` | Opsional | Label teks dropdown. |
| `id` | `string` | `$name` | Opsional | ID elemen select. |
| `selected` | `mixed` | `null` | Opsional | Nilai yang terpilih secara bawaan (prioritas kedua setelah `old($name)`). |
| `placeholder` | `string` | `null` | Opsional | Opsi pertama kosong/pilihan awal (misal: `"Pilih Jenjang"`). |
| `hint` | `string` | `null` | Opsional | Teks petunjuk di bawah select. |
| `required` | `bool` | `false` | Opsional | Menandai field wajib dipilih. |
| `groupClass` | `string` | `''` | Opsional | Class CSS tambahan pada `.form-group`. |
| `options` | `iterable` | `[]` | Opsional | Kumpulan data opsi (Eloquent Collection, array model, array asosiatif key-value, atau array of arrays). |
| `optionAttributes` | `callable\|array` | `null` | Opsional | Closure `fn($item, $val)` atau array mapping atribut kustom (seperti `data-*` atau `disabled`) untuk setiap `<option>`. |

#### Slots
- **`{{ $slot }}`**: Berisi tag-tag `<option>` kustom bila tidak menggunakan prop `options` (atau untuk opsi tambahan di bagian bawah).

#### Contoh Penggunaan
```blade
{{-- 1. Menggunakan Array Key-Value Sederhana --}}
<x-form-select
    name="jenjang"
    label="Jenjang Pendidikan"
    placeholder="Pilih Jenjang"
    :selected="old('jenjang', $selectedSchool?->school_type)"
    :options="[
        'SMK' => 'SMK (Konsentrasi Keahlian)',
        'SMA' => 'SMA (Peminatan / Fase)',
    ]"
    required
/>

{{-- 2. Menggunakan Eloquent Collection dengan Custom Data Attribute --}}
<x-form-select
    name="module_id"
    id="modal_module_id"
    label="Induk Modul"
    placeholder="Pilih Induk Modul"
    :selected="request('module_id', $selectedModuleId ?? null)"
    :options="$modules"
    :option-attributes="fn($module) => ['data-next-order' => \App\Models\Menu::nextOrder($module->id)]"
    hint="Menu akan dikelompokkan di bawah modul yang Anda tentukan."
    required
/>
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
| `groupClass` | `string` | `''` | Opsional | Class CSS tambahan pada `.form-group`. |

#### Slots
- **`{{ $slot }}`**: Fallback nilai teks jika prop `:value` bernilai `null` (`old($name, $value ?? $slot)`).

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
| `checked` | `bool` | `false` | Opsional | Status centang awal (otomatis mempertahankan nilai `old($name)`). |
| `hint` | `string` | `null` | Opsional | Petunjuk keterangan tambahan. |
| `groupClass` | `string` | `''` | Opsional | Class CSS tambahan pada `.form-group`. |

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
Komponen dropdown relasi dinamis dengan antarmuka pencarian instan, badge ekstra, pembersihan nilai (*clearable*), dan integrasi penuh dengan modul JavaScript `window.SearchableSelect`.

> [!NOTE]
> Berbeda dari select standar, `<x-searchable-select>` adalah kontrol kustom yang merender hidden input bersama tombol trigger dan panel menu. Komponen ini dapat berdiri sendiri atau dibungkus di dalam `<div class="form-group">` bersama `<label>` dan `@error`.

#### Props
| Prop | Tipe | Default | Wajib? | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `name` | `string` | - | **Ya** | Nama field hidden input yang dikirim pada payload HTTP form. |
| `id` | `string` | `$name` | Opsional | ID unik elemen hidden input dan pemicu JavaScript. |
| `value` | `mixed` | `null` | Opsional | Nilai ID yang terpilih (otomatis membaca `old($name)`). |
| `placeholder` | `string` | `'Pilih Salah Satu'` | Opsional | Teks tombol trigger ketika belum ada pilihan. |
| `searchPlaceholder` | `string` | `'Ketik untuk mencari...'` | Opsional | Placeholder di dalam kotak input filter dropdown. |
| `options` | `iterable` | `[]` | Opsional | Kumpulan data awal (`id`/`value`, `nama`/`label`/`name`, dan opsional `kode`/`extra`/`kode_pos`). |
| `required` | `bool` | `false` | Opsional | Menambahkan atribut `required` pada hidden input. |
| `disabled` | `bool` | `false` | Opsional | Menonaktifkan dropdown saat render awal. |
| `clearable` | `bool` | `true` | Opsional | Menampilkan tombol silang (x) untuk mengosongkan nilai yang dipilih. |
| `emptyText` | `string` | `'Data tidak ditemukan'` | Opsional | Pesan saat kata kunci pencarian tidak cocok dengan opsi manapun. |

#### Contoh Penggunaan di Blade
```blade
<div class="form-group">
    <label for="create_provinsi_id" class="form-label">
        Provinsi Induk <span class="text-danger">*</span>
    </label>
    <x-searchable-select
        name="provinsi_id"
        id="create_provinsi_id"
        placeholder="Pilih Provinsi Induk"
        search-placeholder="Cari provinsi..."
        :options="$provinsiList"
        :value="old('provinsi_id')"
        required
    />
    @error('provinsi_id')
        <div class="form-error">{{ $message }}</div>
    @enderror
    <div class="form-hint">Pilih provinsi induk untuk menentukan wilayah anak secara otomatis.</div>
</div>
```

#### Penggunaan Dinamis via JavaScript
Lihat bagian [Integrasi SearchableSelect](#searchableselect) untuk manipulasi opsi bertingkat (*cascading*) seperti Provinsi ➔ Kabupaten ➔ Kecamatan ➔ Kelurahan.

---

### `<x-icon-picker>`
Komponen interaktif untuk memilih ikon visual dengan katalog resmi Lucide Icons yang dikonfigurasikan pada [`config/icons.php`](file:///home/gania/infora_project/config/icons.php). Dilengkapi pratinjau kartu aktif, tombol pemicu modal katalog, pencarian kata kunci, serta filter chip kategori.

#### Props
| Prop | Tipe | Default | Wajib? | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `name` | `string` | `'icon'` | Opsional | Nama hidden input pengirim nama ikon ke controller. |
| `value` | `string` | `'layers'` | Opsional | Nilai nama ikon terpilih secara bawaan (mendukung `old($name)`). |
| `id` | `string` | `$name` | Opsional | ID unik input dan namespace fungsi JS. |

#### Contoh Penggunaan
```blade
<div class="form-group">
    <label for="modal_menu_icon" class="form-label">Ikon Menu</label>
    <x-icon-picker
        name="icon"
        id="modal_menu_icon"
        :value="old('icon', 'menu')"
    />
    <div class="form-hint">Klik tombol Pilih Ikon untuk membuka katalog ikon sistem.</div>
</div>
```

#### Integrasi JavaScript
Setiap komponen `<x-icon-picker>` secara otomatis mengekspos fungsi global untuk mengubah ikon terpilih secara dinamis (sangat berguna ketika modal Edit dibuka):
```javascript
// Mengubah icon picker dengan id="edit_menu_icon"
if (typeof window['setIconValue_edit_menu_icon'] === 'function') {
    window['setIconValue_edit_menu_icon'](item.icon || 'menu');
}
```

---

### `<x-image-upload>`
Komponen dropzone unggah foto dan logo sekolah terintegrasi dengan pratinjau instan (*instant preview*), konversi base64 otomatis, validasi ukuran berkas, tombol hapus foto, serta dukungan input hidden penghapusan berkas lama (`remove_logo`/`remove_photo`).

#### Props
| Prop | Tipe | Default | Wajib? | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `name` | `string` | `'image'` | Opsional | Nama field hidden input pengirim base64 ke server. |
| `id` | `string` | `$name` | Opsional | ID unik elemen dropzone dan namespace JavaScript. |
| `label` | `string` | `null` | Opsional | Label teks di atas dropzone. |
| `value` | `string` | `null` | Opsional | URL atau base64 gambar awal untuk ditampilkan pada pratinjau (misal saat edit). |
| `placeholder` | `string` | `'Klik untuk unggah foto'` | Opsional | Teks instruksi utama pada area dropzone. |
| `hint` | `string` | `'Format: PNG, JPG, WebP, GIF. Maks 1MB.'` | Opsional | Teks petunjuk kecil di bawah dropzone. |
| `removeName` | `string` | `null` | Opsional | Nama input hidden flag penghapusan foto (misal: `'remove_logo'`). Bernilai `'1'` saat tombol hapus ditekan. |
| `maxSize` | `int` | `1048576` | Opsional | Batas maksimal ukuran berkas dalam bytes (default 1MB). |
| `maxSizeText` | `string` | `'1MB'` | Opsional | Teks pesan peringatan saat ukuran berkas terlampaui. |
| `accept` | `string` | `'image/png,image/jpeg,image/webp,image/gif'` | Opsional | Tipe berkas yang diizinkan. |
| `icon` | `string` | `'upload-image'` | Opsional | Nama icon `<x-icon>` di dalam dropzone. |
| `previewShape` | `string` | `'circle'` | Opsional | Bentuk pratinjau gambar (`'circle'` atau `'rounded'`). |
| `required` | `bool` | `false` | Opsional | Menandai field wajib diisi saat form disubmit. |
| `asFile` | `bool` | `false` | Opsional | Jika `true`, atribut `name` diletakkan pada input file langsung (untuk form `multipart/form-data`). |

#### Contoh Penggunaan: Form Tambah (Create)
```blade
<x-image-upload
    name="logo"
    id="create_logo"
    placeholder="Klik untuk unggah logo sekolah"
    hint="Format: PNG, JPG, WebP, GIF. Maks 1MB."
/>
```

#### Contoh Penggunaan: Form Ubah (Edit dengan Flag Hapus)
```blade
<x-image-upload
    name="logo"
    id="edit_logo"
    remove-name="remove_logo"
    placeholder="Klik untuk unggah logo baru"
    hint="Format: PNG, JPG, WebP, GIF. Maks 1MB."
/>
```

#### Integrasi JavaScript
Lihat bagian [Integrasi ImageUpload](#imageupload) untuk memanipulasi pratinjau secara terprogram.

---

## 🏛️ 2. Komponen Halaman, Navigasi dan Metrik

### `<x-page-header>`
Menampilkan header halaman standar lengkap dengan judul utama, subjudul/deskripsi, dan wadah tombol aksi.

#### Props
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `title` | `string` | - | Judul utama halaman (elemen `h2.page-title`). |
| `subtitle` | `string` | `null` | Deskripsi subjudul atau navigasi pelengkap. |

#### Slots
- **`<x-slot:actions>`**: Tombol-tombol aksi di sebelah kanan header (`.page-actions`).

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

### `<x-tab-pills>` dan `<x-tab-pill>`
Komponen navigasi tab modern berformat pil (*pills*) dengan aksesibilitas ARIA bawaan (`role="tablist"` dan `role="tab"`). Mendukung tautan halaman (`<a>`) maupun tombol aksi berbasis JavaScript (`<button>`).

#### Props `<x-tab-pills>`
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `label` | `string` | `null` | Label aksesibilitas (`aria-label`). |
| `role` | `string` | `'tablist'` | Atribut ARIA role container. |

#### Props `<x-tab-pill>`
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `active` | `bool` | `false` | Menandai tab aktif (`.active`, `aria-selected="true"`, dan `aria-current="page"`). |
| `href` | `string` | `null` | URL tautan. Jika diisi merender tag `<a>`, jika `null` merender `<button type="button">`. |
| `icon` | `string` | `null` | Nama ikon `<x-icon>` di samping label tab. |
| `iconSize` | `int` | `15` | Ukuran pixel ikon. |
| `badge` | `string\|int` | `null` | Angka atau teks badge counter di sebelah kanan label tab. |
| `badgeClass` | `string` | `'badge-cyan'` | Class warna badge (`badge-cyan`, `badge-blue`, `badge-green`, dsb). |

#### Contoh Penggunaan 1: Tautan Navigasi Antar Halaman
```blade
<x-tab-pills label="Navigasi Pengaturan Sistem">
    <x-tab-pill href="{{ route('system.modules.index') }}" :active="request()->routeIs('system.modules.*')" icon="layers">
        Modul Sistem
    </x-tab-pill>
    <x-tab-pill href="{{ route('system.menus.index') }}" :active="request()->routeIs('system.menus.*')" icon="menu" :badge="$totalMenus">
        Menu Navigasi
    </x-tab-pill>
</x-tab-pills>
```

#### Contoh Penggunaan 2: Tombol Tab Interaktif Berbasis JavaScript
```blade
<x-tab-pills label="Mode Tampilan Kalender">
    <x-tab-pill id="btnViewCalendar" :active="true" icon="calendar">
        Kalender
    </x-tab-pill>
    <x-tab-pill id="btnViewList" :active="false" icon="list-bullet">
        Daftar Agenda
    </x-tab-pill>
</x-tab-pills>
```

---

### `<x-stats-grid>` dan `<x-stat-card>`
Menampilkan kartu metrik statistik ringkasan (KPI) secara responsif di bagian atas halaman index atau dashboard.

#### Props `<x-stat-card>`
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `label` | `string` | `null` | Label metrik (misal: "Total Jurusan", "Siswa Aktif"). |
| `value` | `string\|int` | `null` | Angka/nilai metrik. Jika kosong, membaca dari default `$slot`. |
| `icon` | `string` | `null` | Nama ikon yang dirender di samping kiri. |
| `color` | `string` | `'primary'` | Variasi tema warna ikon (`primary`, `success`, `warning`, `danger`, `info`). Menghasilkan class `.stat-icon.is-{$color}`. |

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
Komponen notifikasi pesan kilas (*flash messages*) atau peringatan validasi dengan tombol penutup (*dismissible*).

#### Props
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `type` | `string` | `'success'` | Jenis alert (`success`, `error`, `warning`, `info`, `validation`). |
| `title` | `string` | `null` | Judul tebal notifikasi. |
| `message` | `string` | `null` | Isi pesan teks. Jika null, otomatis membaca `session($type)`. Khusus tipe `'validation'`, otomatis membaca `$errors->first()`. |
| `dismissible` | `bool` | `true` | Menampilkan tombol tutup (silang) di pojok kanan alert. |

#### Slots
- **`{{ $slot }}`**: Konten HTML kustom untuk menggantikan teks pesan default.

#### Contoh Penggunaan
```blade
{{-- Otomatis membaca session('success') dari controller --}}
<x-alert type="success" />

{{-- Menampilkan pesan error validasi pertama jika ada --}}
<x-alert type="validation" />

{{-- Notifikasi kustom dengan judul dan slot --}}
<x-alert type="warning" title="Peringatan Tahun Ajaran" :dismissible="false">
    Tahun ajaran ini memiliki semester yang sedang berjalan. Perubahan data dapat mempengaruhi jadwal KBM.
</x-alert>
```

---

### `<x-status-banner>`
Komponen spanduk status kontekstual (*persistent context banner*) untuk menampilkan status sistem, kondisi prasyarat konfigurasi, atau status aktif halaman dengan indikator badge dan tombol aksi opsional.

#### Perbedaan Utama dengan `<x-alert>`
- **`<x-alert>`**: Digunakan untuk *transient flash messages* (notifikasi session sukses/gagal dari controller) yang dilengkapi tombol penutup (*dismissible*).
- **`<x-status-banner>`**: Digunakan untuk *persistent context banner* (status kontekstual permanen yang melekat pada halaman, misalnya status keterhubungan unit sekolah, status semester aktif, atau prasyarat data) dengan badge status di sebelah kanan dan tanpa tombol penutup.

#### Props
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `type` | `string` | `'info'` | Jenis tema warna alert (`info`, `success`, `warning`, `danger`, `error`). Menghasilkan class `.alert-{$type}` serta otomatis menentukan ikon dan warna badge bawaan. |
| `title` | `string` | `null` | Judul tebal status banner (dirender di dalam tag `<strong>`). |
| `message` | `string` | `null` | Isi pesan teks status banner jika tidak menggunakan slot default. |
| `badge` | `string` | `null` | Teks badge status. |
| `badgeColor` | `string` | `null` | Override warna badge (misal: `'success'`, `'warning'`, `'danger'`). Jika kosong, otomatis mengikuti tema `type`. |
| `actionTitle` | `string` | `null` | Teks label pada tombol atau tautan aksi. |
| `actionHref` | `string` | `null` | URL target tautan aksi (menghasilkan tag `<a>`). |
| `actionType` | `string` | `null` | Tipe elemen aksi (`'link'` / `'a'` atau `'button'`). Otomatis `'button'` jika `actionHref` kosong. |
| `actionIcon` | `string` | `null` | Nama ikon `<x-icon>` di dalam tombol aksi (otomatis berukuran 14px). |
| `actionId` | `string` | `null` | Atribut ID pada elemen aksi. |
| `actionOnClick` | `string` | `null` | Atribut handler JavaScript `onclick` pada tombol aksi. |

#### Slots
- **`{{ $slot }}`**: Konten pesan utama (teks/HTML dinamis) untuk menggantikan prop `message`.
- **`<x-slot:actions>`**: Slot aksi tingkat lanjut untuk tombol majemuk/kustom di dalam banner (`.alert-actions`).
- **`<x-slot:badge>`**: Konten badge kustom di dalam wadah teks.
- **`<x-slot:right>`**: Konten kustom pengganti badge.

#### Contoh Penggunaan 1: Banner Satu Baris (Pesan Statis)
```blade
<x-status-banner 
    title="Unit Sekolah Belum Dikonfigurasi:" 
    message="Akun Anda belum terhubung dengan unit sekolah manapun. Silakan hubungi Super Administrator untuk konfigurasi unit sekolah." 
    badge="Perlu Konfigurasi" 
/>
```

#### Contoh Penggunaan 2: Banner dengan Slot Teks Dinamis & Badge Override
```blade
<x-status-banner title="Saat Ini:" badge="Aktif" badge-color="success">
    {{ $activeSemester->nama_lengkap }}
</x-status-banner>
```

#### Contoh Penggunaan 3: Banner dengan Aksi Tautan (Link / `<a>`)
```blade
<x-status-banner
    title="Tahun Ajaran Belum Dikonfigurasi:"
    badge="Tahun Ajaran Diperlukan"
    action-icon="settings"
    action-title="Konfigurasi Sekarang"
    :action-href="route('konfigurasi-sekolah.tahun-ajaran')">
    <br>Unit sekolah <strong>{{ $selectedSchool->name }}</strong> belum memiliki Tahun Ajaran.
    Kalender akademik membutuhkan setidaknya satu Tahun Ajaran untuk dapat menambahkan agenda kegiatan sekolah.
</x-status-banner>
```

#### Contoh Penggunaan 4: Banner dengan Aksi Tombol (`<button>`)
```blade
<x-status-banner
    title="Diagnostik Sistem"
    badge="Siap"
    badge-color="cyan"
    action-type="button"
    action-id="btnRunDiagnostic"
    action-icon="play"
    action-title="Jalankan Diagnostik"
    message="Klik tombol di bawah untuk memeriksa integritas tabel database."
/>
```

---

## 📊 3. Komponen Tabel dan Penyajian Data

### `<x-data-table>`
Komponen terpadu pembungkus tabel data dengan dua mode tampilan: **Mode Form Pencarian** (default) dan **Mode Preset Judul** (title preset).

#### Props
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `route` | `string` | `null` | URL tujuan form pencarian (misal: `route('master.data-jurusan.index')`). |
| `title` | `string` | `null` | Jika diisi, mengaktifkan **Mode Preset Judul** pada toolbar. |
| `subtitle` | `mixed` | `null` | Subjudul teks/HTML pada Mode Preset Judul. |
| `searchPlaceholder` | `string` | `'Cari data...'` | Placeholder pada input pencarian. |
| `perPage` | `int\|string` | `null` | Nilai `per_page` aktif saat ini untuk memunculkan pemilih jumlah baris. |
| `perPageText` | `string` | `'data'` | Teks di samping pemilih per_page (misal: `'jurusan'`). |
| `total` | `int` | `null` | Jumlah total data untuk ditampilkan di toolbar. |
| `totalLabel` | `string` | `'Data'` | Label satuan data total (misal: `'Siswa'`, `'Jurusan'`). |
| `totalPrefix` | `string` | `'Total:'` | Awalan teks total. |

#### Slots
- **`{{ $slot }}`**: Elemen `<thead>` dan `<tbody>` dari tabel data (`.data-table`).
- **`<x-slot:toolbar>`**: Menggantikan seluruh isi toolbar dengan komponen kustom mandiri.
- **`<x-slot:filters>`**: Input filter tambahan (dropdown status, kategori, dll) pada Mode Form Pencarian.
- **`<x-slot:actions>`**: Tombol aksi di sebelah kanan pada Mode Preset Judul.
- **`<x-slot:subtitle>`**: Slot subjudul kustom pada Mode Preset Judul.
- **`<x-slot:footer>`**: Bagian bawah tabel (biasanya diisi `<x-pagination-summary>`).

#### Contoh Penggunaan 1: Mode Form Pencarian dan Filter
```blade
<x-data-table
    :route="route('master.data-jurusan.index')"
    search-placeholder="Cari nama, kode jurusan..."
    :per-page="request('per_page', 15)"
    per-page-text="jurusan"
    :total="$jurusans->total()"
    total-label="jurusan">

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

#### Contoh Penggunaan 2: Mode Preset Judul (Title Toolbar)
```blade
<x-data-table
    title="Daftar Semester Aktif"
    :total="$semesters->count()"
    total-label="Semester">
    <x-slot:actions>
        <button type="button" class="btn-primary" id="btnOpenCreateSemester">
            <x-icon name="plus" size="16" />
            <span>Tambah Semester</span>
        </button>
    </x-slot:actions>

    <thead>
        <tr>
            <th>Tahun Ajaran</th>
            <th>Semester</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <!-- Data baris -->
    </tbody>
</x-data-table>
```

---

### `<x-search-toolbar>`
Komponen toolbar pencarian mandiri dengan form GET, input pencarian, pemilih `per_page` otomatis, dan label counter data.

#### Props
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `action` | `string` | `null` | URL aksi pengiriman form pencarian. |
| `method` | `string` | `'GET'` | Method form HTTP. |
| `searchPlaceholder` | `string` | `'Cari data...'` | Placeholder kotak input cari. |
| `perPage` | `int\|string` | `null` | Nilai `per_page` aktif saat ini (jika `null`, selector tidak ditampilkan). |
| `perPageOptions` | `array` | `[15, 30, 90]` | Pilihan jumlah per_page. |
| `perPageAllLabel` | `string` | `'Semua'` | Label untuk opsi menampilkan semua data (`'semua'`). |
| `perPageText` | `string` | `'data'` | Label di samping dropdown pemilih per_page. |
| `total` | `int` | `null` | Total data untuk indikator ringkasan di kanan. |
| `totalLabel` | `string` | `'data'` | Satuan label total data. |
| `totalPrefix` | `string` | `'Total:'` | Teks awalan sebelum angka total. |
| `showTotal` | `bool` | `true` | Apakah indikator total dirender jika `total !== null`. |

#### Slots
- **`<x-slot:filters>`** atau **`{{ $slot }}`**: Dropdown filter tambahan di sebelah pemilih per_page.

---

### `<x-table-actions>`
Membungkus tombol-tombol aksi pada baris tabel (Detail, Edit, Hapus) dengan perataan yang konsisten.

#### Props
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `align` | `string` | `'right'` | Perataan tombol (`'right'`, `'center'`, `'left'`). Menghasilkan class `.table-actions.table-actions-{$align}`. |

---

### `<x-empty-state>`
Menampilkan ikon dan pesan ramah di dalam baris tabel saat data tidak ditemukan atau pencarian nihil.

#### Props
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `colspan` | `int` | `5` | Jumlah kolom tabel yang direntangkan (`<td colspan="...">`). |
| `title` | `string` | `null` | Judul kondisi kosong. |
| `text` | `string` | `'Belum ada data yang ditemukan.'` | Pesan deskripsi penjelasan. |
| `icon` | `string` | `'search'` | Nama ikon SVG di bagian tengah. |
| `iconSize` | `int` | `36` | Ukuran pixel ikon. |
| `action` | `slot` | `null` | Tombol CTA (opsional). |

---

### `<x-pagination-summary>` dan `<x-pagination>`
- **`<x-pagination-summary>`**: Pembungkus `table-footer` yang merender `$paginator->links()` jika data memiliki lebih dari 1 halaman atau `total > 0`.
- **`<x-pagination>`**: Template tampilan paginasi universal yang didaftarkan secara global pada [`AppServiceProvider`](file:///home/gania/infora_project/app/Providers/AppServiceProvider.php) (`Paginator::defaultView('components.pagination')`). Menyediakan ringkasan range data (contoh: *"Menampilkan 1 sampai 15 dari 120 total data"*) dan tautan halaman interaktif.

#### Contoh Penggunaan
```blade
<x-pagination-summary :paginator="$jurusans" />
```

---

## 🪟 4. Komponen Dialog dan Modal

### `<x-modal>`
Komponen dialog interaktif terpadu untuk formulir data (Tambah/Ubah), dialog informasi, maupun **Preset Konfirmasi Hapus** (`name="delete"`). Dilengkapi pembungkus form otomatis, proteksi `@csrf`, method spoofing (`@method`), serta integrasi penuh dengan modul JavaScript `InforaModals`.

#### Variasi / Mode Tampilan
1. **Mode Standar (`@default`)**: Digunakan untuk formulir Tambah (*Create*), Ubah (*Edit*), maupun dialog informasi/rincian data.
2. **Mode Preset Delete (`name="delete"`)**: Tampilan khusus konfirmasi hapus (*danger theme*) dengan ambient halo icon merah, judul peringatan, kartu detail item dinamis, dan tombol aksi simetris (Batal dan Hapus).

#### Props
| Prop | Tipe | Default | Mode | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `string` | `null` | Semua | **Wajib**. ID unik elemen modal backdrop (misal: `modalCreateJurusan`). |
| `name` | `string` | `null` | Semua | Nama varian. Gunakan `name="delete"` untuk mengaktifkan Preset Modal Hapus. |
| `title` | `string` | `null` | Semua | Judul header modal. |
| `subtitle` | `string` | `null` | Standar | Subjudul deskripsi di bawah judul header. |
| `size` | `string` | `null` | Standar | Ukuran lebar dialog (`'sm'`, `'lg'`, atau default). Pada preset delete otomatis `'sm'`. |
| `action` | `string` | `null` | Semua | URL tujuan form action. Jika diisi atau memiliki `method`, modal otomatis membungkus konten dengan tag `<form>`. |
| `method` | `string` | `null` | Semua | Method HTTP form (`'POST'`, `'PUT'`, `'PATCH'`, `'DELETE'`). Pada preset delete otomatis `'DELETE'`. |
| `formId` | `string` | `null` | Semua | Atribut `id` untuk tag `<form>`. |
| `messageId` | `string` | `null` | Delete | ID elemen paragraf pesan konfirmasi untuk dimanipulasi via script. |
| `badgeId` | `string` | `null` | Delete | ID elemen badge kode/kategori untuk dimanipulasi via script. |
| `itemTitleId` | `string` | `null` | Delete | ID elemen judul item yang akan dihapus untuk dimanipulasi via script. |
| `cancelBtnId` | `string` | `null` | Delete | ID tombol "Batal". |
| `confirmBtnId` | `string` | `null` | Delete | ID tombol "Hapus" (submit). |

#### Slots
- **`{{ $slot }}`**: Konten utama modal (field formulir pada modal standar, atau custom body pada modal delete).
- **`<x-slot:footer>`**: Tombol aksi bagian bawah pada modal standar (Batal, Simpan, dll).
- **`<x-slot:hiddenInputs>`**: Input hidden tambahan khusus pada modal delete.

#### Contoh 1: Modal Tambah (Create)
```blade
<x-modal
    id="modalCreateJurusan"
    title="Tambah Data Jurusan Baru"
    subtitle="Tambahkan data konsentrasi keahlian baru"
    size="lg"
    action="{{ route('master.data-jurusan.store') }}"
    form-id="formCreateJurusan">

    <x-form-input name="kode" label="Kode Jurusan" required />
    <x-form-input name="nama" label="Nama Jurusan" required />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateJurusan">Batal</button>
        <button type="submit" class="btn-primary">Simpan</button>
    </x-slot:footer>
</x-modal>
```

#### Contoh 2: Modal Ubah (Edit)
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

#### Contoh 3: Preset Modal Konfirmasi Hapus (Delete)
```blade
<x-modal
    name="delete"
    id="modalDeleteJurusan"
    form-id="formDeleteJurusan"
    cancel-btn-id="btnCancelDeleteJurusan"
    confirm-btn-id="btnConfirmDeleteJurusan"
    message-id="deleteJurusanMessage"
    badge-id="deleteJurusanBadge"
    item-title-id="deleteJurusanTitle"
/>
```

---

### `<x-progress-modal>`
Modal indikator proses global (*singleton*) dengan ambient status orb, bilah persentase beranimasi halus, dan pelaporan status langsung.

> [!IMPORTANT]
> Komponen ini tidak membutuhkan props dan telah terpasang secara global pada [`resources/views/layouts/app.blade.php`](file:///home/gania/infora_project/resources/views/layouts/app.blade.php) dan [`resources/views/layouts/auth.blade.php`](file:///home/gania/infora_project/resources/views/layouts/auth.blade.php). Anda cukup memanggil API JavaScript `window.InforaProgress` dari script halaman manapun.

#### Contoh Pemanggilan di Layout
```blade
<x-progress-modal />
```

---

## 🎨 5. Komponen Grafis dan Ikon

### `<x-icon>`
Merender SVG icon native dari keluarga Lucide Icons secara instan tanpa ketergantungan web font eksternal.

#### Props
| Prop | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `name` | `string` | `null` | **Wajib**. Nama ikon (misal: `plus`, `edit`, `trash`, `school`, `calendar`, `database`, `user`, `search`, `check`, `arrow-right`, `layers`, `compass`, `grid`, `menu`, `list-tree`, dsb). |
| `size` | `int\|string` | `null` | Ukuran pixel ikon (jika `null`, otomatis berukuran 20px). |
| `class` | `string` | `''` | Class CSS tambahan (misal: `text-danger`, `animate-spin`, `mr-2`). |
| `id` | `string` | `''` | Atribut ID elemen `<svg>`. |

#### Contoh Penggunaan
```blade
<x-icon name="plus" size="18" />
<x-icon name="trash" size="16" class="text-danger" />
<x-icon name="loader-ring" size="16" class="animate-spin" />
```

---

## ⚙️ 6. Integrasi Modul JavaScript

Aplikasi INFORA dilengkapi modul JavaScript terpusat untuk mempermudah manipulasi interaktif:

### `InforaModals`
Mengelola pembukaan, penutupan, klik backdrop, penekanan tombol Escape, serta pengisian data otomatis pada form Edit dan Delete.

```javascript
document.addEventListener('DOMContentLoaded', function () {
    // 1. Registrasi penutupan modal (ESC, Backdrop, dan Tombol Batal)
    InforaModals.setup('modalCreateJurusan', { cancelBtnId: 'btnCancelCreateJurusan' });
    InforaModals.setup('modalEditJurusan', { cancelBtnId: 'btnCancelEditJurusan' });
    InforaModals.setup('modalDeleteJurusan', { cancelBtnId: 'btnCancelDeleteJurusan' });

    // 2. Hubungkan tombol buka modal tambah
    InforaModals.bindOpenButton('btnOpenCreateJurusan', 'modalCreateJurusan');

    // 3. Hubungkan tombol edit pada tabel (otomatis mengisi action form dan input)
    InforaModals.bindEditButtons('.btn-open-edit-jurusan', 'modalEditJurusan', function (modal, data, formEdit) {
        document.getElementById('edit_kode').value = data.kode || '';
        document.getElementById('edit_nama').value = data.nama || '';
    });

    // 4. Hubungkan tombol hapus pada tabel (otomatis mengisi action destroy dan teks konfirmasi)
    InforaModals.bindDeleteButtons('.btn-open-delete-jurusan', 'modalDeleteJurusan', function (modal, btn) {
        document.getElementById('modalDeleteJurusanTitle').textContent = 'Hapus Jurusan ' + btn.dataset.kode + '?';
        document.getElementById('deleteJurusanMessage').textContent = 'Apakah Anda yakin ingin menghapus jurusan berikut?';
        document.getElementById('deleteJurusanBadge').textContent = btn.dataset.kode || '-';
        document.getElementById('deleteJurusanTitle').textContent = btn.dataset.nama || '-';
    });
});
```

---

### `SearchableSelect`
Mengontrol komponen `<x-searchable-select>` secara terprogram, termasuk memuat data bertingkat (*cascading dynamic dropdown*):

```javascript
// Mengambil instance berdasarkan ID input hidden
const kabSelect = window.SearchableSelect.getInstance('create_kabupaten_id');

// Memuat data opsi baru secara asinkron
kabSelect.setLoading(true);
fetch(`/api/wilayah/kabupaten?provinsi_id=${provId}`)
    .then(res => res.json())
    .then(data => {
        kabSelect.setOptions(data, null); // Array of { id, nama, kode }
        kabSelect.setDisabled(false);
    })
    .finally(() => {
        kabSelect.setLoading(false);
    });

// Mendengarkan perubahan nilai pilihan
document.getElementById('create_provinsi_id').addEventListener('change', function(e) {
    console.log('Provinsi terpilih:', e.target.value);
});
```

#### Method yang Tersedia pada Instance `SearchableSelect`:
- `setOptions(options, selectedValue = null)`: Memperbarui daftar pilihan opsi.
- `setValue(value, text = '', extra = '', triggerChangeEvent = true)`: Memilih nilai tertentu.
- `clear(triggerChangeEvent = true)`: Mengosongkan pilihan aktif.
- `setDisabled(bool)`: Mengaktifkan atau menonaktifkan dropdown.
- `setLoading(bool)`: Menampilkan/menyembunyikan ikon loading spinner.
- `open()`, `close()`, `toggle()`: Membuka atau menutup panel menu.

---

### `ImageUpload`
Modul JavaScript terpusat untuk mengontrol komponen `<x-image-upload>`:

```javascript
// 1. Mengambil instance ImageUpload berdasarkan ID
const logoUpload = window.ImageUpload.getInstance('edit_logo');

// 2. Menampilkan pratinjau gambar awal (misal saat membuka modal Edit)
logoUpload.setPreview('https://example.com/storage/logos/sekolah.png');

// 3. Mengosongkan foto dan mengaktifkan flag remove_logo
logoUpload.clear();

// 4. Mengambil nilai base64 aktif saat ini
const base64Data = logoUpload.getValue();

// 5. Mendengarkan event perubahan foto
document.getElementById('imageUpload_create_logo').addEventListener('image-upload:change', function(e) {
    console.log('File dipilih:', e.detail.file);
    console.log('Base64:', e.detail.base64);
});
```

---

### `InforaProgress`
Mengontrol `<x-progress-modal>` untuk navigasi antar halaman, submit form berat, dan Server-Sent Events (SSE) streaming (seperti Backup dan Restore):

```javascript
// Menampilkan progress modal dengan judul dan detail
window.InforaProgress.show({
    title: 'Memproses Sinkronisasi Data',
    statusText: 'Menghubungkan ke server Dapodik...',
    detail: 'Memvalidasi sertifikat SSL...',
    percent: 15
});

// Memperbarui persentase dan teks status
window.InforaProgress.set(65, 'Mengunduh data rombel...', '120 dari 180 data telah diterima');

// Menandai sukses atau menyembunyikan
window.InforaProgress.success('Sinkronisasi Selesai', 'Seluruh data berhasil diperbarui.');
window.InforaProgress.hide();

// Streaming proses backup/restore berbasis SSE
window.InforaProgress.stream('/system/backup-restore/run', formData, {
    onProgress: (payload) => { /* update teks/bar */ },
    onSuccess: (payload) => { /* selesai */ },
    onError: (err) => { /* penanganan gagal */ }
});
```

---

### `PermissionMatrix`
Modul JavaScript mandiri (`resources/js/permission-matrix.js`) untuk mengelola tabel matriks hak akses (*Role-Based Access Control* / RBAC):

```javascript
// 1. Inisialisasi otomatis via selektor container atau dokumen
const matrix = window.PermissionMatrix.init('#userPermissionForm');

// 2. Aksi Cepat / Toolbar Masal
matrix.selectAllView(); // Centang seluruh izin 'Lihat'
matrix.selectAllCRUD(); // Centang seluruh izin 'Lihat' dan aksi CRUD (Tambah, Ubah, Hapus)
matrix.uncheckAll();    // Kosongkan seluruh centang izin

// 3. Aksi Cepat per Modul
matrix.toggleModule(moduleId); // Toggle seluruh izin lihat pada modul terkait
```

#### Fitur dan Logika Otomatis `PermissionMatrix`:
- **Dependensi Baris (View vs Action):** Ketika `can_view` dinonaktifkan, seluruh checkbox aksi CRUD (`create`, `edit`, `delete`) pada baris yang sama otomatis dinonaktifkan (*disabled*) dan dicentang ulang (*uncheck*). Sebaliknya jika aksi CRUD dipilih, `can_view` otomatis ikut aktif.
- **Sinkronisasi Hirarki (Parent Menu vs Sub-Menu):** Jika `can_view` pada sub-menu diaktifkan, `can_view` pada menu induk otomatis ikut tercentang.
- **Auto-Initialization:** Menginisialisasi secara otomatis saat halaman memiliki elemen bertanda `.matrix-table`, `.matrix-checkbox`, atau `.perm-view`.

---

## 🚀 7. Panduan Kontribusi Bagi Pengembang Baru

Jika Anda ingin menambahkan atau memodifikasi komponen di repositori INFORA:

### 1. Standar Pembuatan Komponen
- Gunakan format **Anonymous Component** (hanya file Blade) kecuali jika komponen tersebut membutuhkan query database mandiri.
- Simpan berkas baru di `resources/views/components/{nama-komponen}.blade.php`.
- Gunakan perintah Artisan:
  ```bash
  php artisan make:component nama-komponen --view --no-interaction
  ```

### 2. Standar Props dan Error Handling
- Selalu gunakan `@props([...])` di baris pertama file untuk mendokumentasikan input yang diterima.
- Untuk komponen form, selalu sediakan fallback error bag yang aman dari unit-testing:
  ```blade
  @php
      $errors = $errors ?? view()->shared('errors', new \Illuminate\Support\ViewErrorBag);
  @endphp
  ```
- Selalu gabungkan atribut native melalui `$attributes->merge(['class' => '...'])`.

### 3. Standar Pengujian (Testing)
Setiap komponen Blade wajib memiliki pengujian regresi di [`tests/Feature/BladeComponentsTest.php`](file:///home/gania/infora_project/tests/Feature/BladeComponentsTest.php) menggunakan Pest:
```php
test('tab-pills renders wrapper with tablist role and accessible label', function () {
    $view = $this->blade(
        '<x-tab-pills label="Pilihan Tampilan">
            <x-tab-pill id="btnTab1" :active="true" icon="calendar">Kalender</x-tab-pill>
        </x-tab-pills>'
    );

    $view->assertSee('class="tab-pills"', false);
    $view->assertSee('role="tablist"', false);
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
