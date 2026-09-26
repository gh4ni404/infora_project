{{-- Modal Tambah Kabupaten Component --}}
<x-modal
    id="modalCreateKabupaten"
    title="Tambah Kabupaten / Kota Baru"
    subtitle="Daftarkan data wilayah administratif kabupaten atau kota baru ke dalam platform INFORA"
    size="lg"
    action="{{ route('wilayah.kabupaten.store') }}"
    form-id="formCreateKabupaten"
>
    <div class="form-group">
        <label for="create_provinsi_id" class="form-label">Provinsi Induk <span class="text-danger">*</span></label>
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
        <div class="form-hint">Pilih provinsi induk untuk menentukan awalan 2 digit kode wilayah secara otomatis.</div>
    </div>

    <div class="form-grid-2col">
        <x-form-select
            name="tipe"
            id="create_tipe"
            label="Tipe Wilayah"
            :selected="old('tipe', 'Kabupaten')"
            :options="[
                'Kabupaten' => 'Kabupaten',
                'Kota' => 'Kota',
            ]"
            required
        />

        <x-form-input
            name="kode"
            id="create_kode"
            label="Kode Wilayah (4 Digit)"
            placeholder="Contoh: 7371"
            maxlength="4"
            hint="Kode standar 4 digit Kemendagri (2 digit awal harus sesuai provinsi)."
            required
        />
    </div>

    <x-form-input
        name="nama"
        id="create_nama"
        label="Nama Kabupaten / Kota"
        data-transform="title-case"
        placeholder="Contoh: Makassar atau Maros"
        hint="Masukkan nama wilayah tanpa kata 'Kabupaten' atau 'Kota' (sistem otomatis memformat Title Case)."
        required
    />

    <x-form-checkbox
        name="status"
        id="create_status"
        label="Kabupaten/Kota aktif dan dapat digunakan pada data wilayah anak dan sekolah"
        value="1"
        :checked="old('status', '1') == '1'"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateKabupaten">Batal</button>
        <button type="submit" class="btn-primary">Simpan</button>
    </x-slot:footer>
</x-modal>
