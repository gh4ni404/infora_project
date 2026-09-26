{{-- Modal Edit Kecamatan Component --}}
<x-modal
    id="modalEditKecamatan"
    title="Edit Data Kecamatan"
    subtitle="Perbarui data wilayah administratif kecamatan pada platform INFORA"
    size="lg"
    method="PUT"
    form-id="formEditKecamatan"
>
    <div class="form-group">
        <label for="edit_provinsi_id" class="form-label">Provinsi Induk <span class="text-danger">*</span></label>
        <x-searchable-select
            name="provinsi_id"
            id="edit_provinsi_id"
            placeholder="Pilih Provinsi Induk"
            search-placeholder="Cari provinsi..."
            :options="$provinsiList"
            :value="old('provinsi_id')"
            required
        />
        @error('provinsi_id')
            <div class="form-error">{{ $message }}</div>
        @enderror
        <div class="form-hint">Ubah provinsi induk untuk memuat ulang daftar kabupaten/kota.</div>
    </div>

    <div class="form-group">
        <label for="edit_kabupaten_id" class="form-label">Kabupaten / Kota Induk <span class="text-danger">*</span></label>
        <x-searchable-select
            name="kabupaten_id"
            id="edit_kabupaten_id"
            placeholder="Pilih Kabupaten / Kota"
            search-placeholder="Cari kabupaten/kota..."
            :value="old('kabupaten_id')"
            required
        />
        @error('kabupaten_id')
            <div class="form-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-grid-2col">
        <x-form-input
            name="kode"
            id="edit_kode"
            label="Kode Kecamatan (6 Digit)"
            placeholder="Contoh: 730821"
            maxlength="6"
            hint="Kode standar 6 digit Kemendagri (4 digit awal harus sesuai kabupaten terpilih)."
            required
        />

        <x-form-input
            name="nama"
            id="edit_nama"
            label="Nama Kecamatan"
            data-transform="title-case"
            placeholder="Contoh: Tanete Riattang"
            hint="Sistem otomatis memformat Title Case."
            required
        />
    </div>

    <x-form-checkbox
        name="status"
        id="edit_status"
        label="Aktifkan data wilayah kecamatan ini dalam sistem"
        value="1"
        :checked="old('status', '1') == '1'"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditKecamatan">Batal</button>
        <button type="submit" class="btn-primary">Update</button>
    </x-slot:footer>
</x-modal>
