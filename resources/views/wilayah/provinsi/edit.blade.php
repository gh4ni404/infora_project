{{-- Modal Edit Provinsi Component --}}
<x-modal
    id="modalEditProvinsi"
    title="Formulir Perubahan Data Provinsi"
    subtitle="Perbarui data kode dan nama wilayah provinsi dalam platform INFORA"
    size="lg"
    method="PUT"
    form-id="formEditProvinsi"
>
    <x-form-input
        name="kode"
        id="edit_kode"
        label="Kode Wilayah (2 Digit)"
        placeholder="Contoh: 73"
        maxlength="2"
        hint="Kode standar 2 digit Kemendagri / BPS (contoh: 31 untuk DKI Jakarta, 73 untuk Sulawesi Selatan)."
        required
    />

    <x-form-input
        name="nama"
        id="edit_nama"
        label="Nama Provinsi"
        placeholder="Contoh: Sulawesi Selatan"
        data-transform="title-case"
        hint="Format otomatis Title Case dengan preservasi akronim resmi (DKI, DI)."
        required
    />

    <x-form-checkbox
        name="status"
        id="edit_status"
        label="Provinsi aktif dan dapat digunakan pada data wilayah anak"
        value="1"
        :checked="old('status', '1') == '1'"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditProvinsi">Batal</button>
        <button type="submit" class="btn-primary">Update</button>
    </x-slot:footer>
</x-modal>
