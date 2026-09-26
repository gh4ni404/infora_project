{{-- Modal Edit Kelurahan / Desa Component --}}
<x-modal
    id="modalEditKelurahan"
    title="Edit Data Kelurahan / Desa"
    subtitle="Perbarui data wilayah administratif kelurahan atau desa pada platform INFORA"
    size="lg"
    method="PUT"
    form-id="formEditKelurahan"
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

    <div class="form-group">
        <label for="edit_kecamatan_id" class="form-label">Kecamatan Induk <span class="text-danger">*</span></label>
        <x-searchable-select
            name="kecamatan_id"
            id="edit_kecamatan_id"
            placeholder="Pilih Kecamatan Induk"
            search-placeholder="Cari kecamatan..."
            :value="old('kecamatan_id')"
            required
        />
        @error('kecamatan_id')
            <div class="form-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-grid-2col">
        <x-form-select
            name="tipe"
            id="edit_tipe"
            label="Tipe Wilayah"
            :options="[
                'Kelurahan' => 'Kelurahan',
                'Desa' => 'Desa',
            ]"
            required
        />

        <x-form-input
            name="kode"
            id="edit_kode"
            label="Kode Wilayah (10 Digit)"
            placeholder="Contoh: 7308211001"
            maxlength="10"
            hint="Kode standar 10 digit Kemendagri (6 digit awal harus sesuai kecamatan terpilih)."
            required
        />
    </div>

    <div class="form-grid-2col">
        <x-form-input
            name="nama"
            id="edit_nama"
            label="Nama Kelurahan / Desa"
            data-transform="title-case"
            placeholder="Nama kelurahan atau desa"
            hint="Format Title Case otomatis tanpa kata 'Kelurahan' atau 'Desa'."
            required
        />

        <x-form-input
            name="kode_pos"
            id="edit_kode_pos"
            label="Kode Pos (Opsional)"
            placeholder="Contoh: 92711"
            maxlength="5"
        />
    </div>

    <x-form-checkbox
        name="status"
        id="edit_status"
        label="Aktifkan data wilayah kelurahan/desa ini dalam sistem"
        value="1"
        :checked="old('status', '1') == '1'"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditKelurahan">Batal</button>
        <button type="submit" class="btn-primary">Update</button>
    </x-slot:footer>
</x-modal>
