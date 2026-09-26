{{-- Modal Tambah Kecamatan Component --}}
<x-modal
    id="modalCreateKecamatan"
    title="Tambah Kecamatan Baru"
    subtitle="Daftarkan data wilayah administratif kecamatan baru ke dalam platform INFORA"
    size="lg"
    action="{{ route('wilayah.kecamatan.store') }}"
    form-id="formCreateKecamatan"
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
        <div class="form-hint">Pilih provinsi induk untuk menyaring daftar pilihan kabupaten/kota secara otomatis.</div>
    </div>

    <div class="form-group">
        <label for="create_kabupaten_id" class="form-label">Kabupaten / Kota Induk <span class="text-danger">*</span></label>
        <x-searchable-select
            name="kabupaten_id"
            id="create_kabupaten_id"
            placeholder="Pilih Kabupaten / Kota"
            search-placeholder="Cari kabupaten/kota..."
            :disabled="!old('provinsi_id')"
            :value="old('kabupaten_id')"
            required
        />
        @error('kabupaten_id')
            <div class="form-error">{{ $message }}</div>
        @enderror
        <div class="form-hint">Pilih kabupaten/kota induk untuk menentukan awalan 4 digit kode kecamatan.</div>
    </div>

    <div class="form-grid-2col">
        <x-form-input
            name="kode"
            id="create_kode"
            label="Kode Kecamatan (6 Digit)"
            placeholder="Contoh: 730821"
            maxlength="6"
            hint="Kode standar 6 digit Kemendagri (4 digit awal harus sesuai kabupaten terpilih)."
            required
        />

        <x-form-input
            name="nama"
            id="create_nama"
            label="Nama Kecamatan"
            data-transform="title-case"
            placeholder="Contoh: Tanete Riattang"
            hint="Masukkan nama wilayah kecamatan (sistem otomatis Title Case)."
            required
        />
    </div>

    <x-form-checkbox
        name="status"
        id="create_status"
        label="Aktifkan data wilayah kecamatan ini dalam sistem"
        value="1"
        :checked="old('status', '1') === '1'"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateKecamatan">Batal</button>
        <button type="submit" class="btn-primary">Simpan</button>
    </x-slot:footer>
</x-modal>
