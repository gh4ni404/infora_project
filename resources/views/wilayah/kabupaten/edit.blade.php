{{-- Modal Edit Kabupaten Component --}}
<x-modal
    id="modalEditKabupaten"
    title="Edit Wilayah Kabupaten / Kota"
    subtitle="Perbarui data wilayah administratif kabupaten atau kota dalam platform INFORA"
    size="lg"
    method="PUT"
    form-id="formEditKabupaten"
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

    <div class="form-grid-2col">
        <x-form-select
            name="tipe"
            id="edit_tipe"
            label="Tipe Wilayah"
            :options="[
                'Kabupaten' => 'Kabupaten',
                'Kota' => 'Kota',
            ]"
            required
        />

        <x-form-input
            name="kode"
            id="edit_kode"
            label="Kode Wilayah (4 Digit)"
            placeholder="Contoh: 7371"
            maxlength="4"
            hint="Kode standar 4 digit Kemendagri (2 digit awal harus sesuai provinsi)."
            required
        />
    </div>

    <x-form-input
        name="nama"
        id="edit_nama"
        label="Nama Kabupaten / Kota"
        data-transform="title-case"
        placeholder="Contoh: Makassar atau Maros"
        hint="Format Title Case otomatis tanpa perlu menyertakan kata 'Kabupaten' atau 'Kota'."
        required
    />

    <x-form-checkbox
        name="status"
        id="edit_status"
        label="Kabupaten/Kota aktif dan dapat digunakan pada data wilayah anak dan sekolah"
        value="1"
        :checked="old('status', '1') == '1'"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditKabupaten">Batal</button>
        <button type="submit" class="btn-primary">Update</button>
    </x-slot:footer>
</x-modal>
