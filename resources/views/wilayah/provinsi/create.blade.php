{{-- Modal Tambah Provinsi Component --}}
<x-modal
    id="modalCreateProvinsi"
    title="Tambah Provinsi Baru"
    subtitle="Daftarkan data wilayah administratif provinsi baru ke dalam platform INFORA"
    size="lg"
    action="{{ route('wilayah.provinsi.store') }}"
    form-id="formCreateProvinsi"
>
    <x-form-input
        name="kode"
        id="create_kode"
        label="Kode Wilayah (2 Digit)"
        placeholder="Contoh: 73"
        maxlength="2"
        hint="Kode standar 2 digit Kemendagri / BPS (contoh: 31 untuk DKI Jakarta, 73 untuk Sulawesi Selatan)."
        required
    />

    <x-form-input
        name="nama"
        id="create_nama"
        label="Nama Provinsi"
        placeholder="Contoh: Sulawesi Selatan"
        data-transform="title-case"
        hint="Format otomatis Title Case dengan preservasi akronim resmi (DKI, DI)."
        required
    />

    <x-form-checkbox
        name="status"
        id="create_status"
        label="Provinsi aktif dan dapat digunakan pada data wilayah anak"
        value="1"
        :checked="old('status', '1') == '1'"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateProvinsi">Batal</button>
        <button type="submit" class="btn-primary">Simpan</button>
    </x-slot:footer>
</x-modal>
