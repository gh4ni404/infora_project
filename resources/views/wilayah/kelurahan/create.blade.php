{{-- Modal Tambah Kelurahan / Desa Component --}}
<x-modal
    id="modalCreateKelurahan"
    title="Tambah Kelurahan / Desa Baru"
    subtitle="Daftarkan data wilayah administratif kelurahan atau desa baru ke dalam platform INFORA"
    size="lg"
    action="{{ route('wilayah.kelurahan.store') }}"
    form-id="formCreateKelurahan"
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
        <div class="form-hint">Pilih provinsi induk untuk menyaring pilihan kabupaten/kota secara otomatis.</div>
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
        <div class="form-hint">Pilih kabupaten/kota untuk menyaring pilihan kecamatan induk.</div>
    </div>

    <div class="form-group">
        <label for="create_kecamatan_id" class="form-label">Kecamatan Induk <span class="text-danger">*</span></label>
        <x-searchable-select
            name="kecamatan_id"
            id="create_kecamatan_id"
            placeholder="Pilih Kecamatan Induk"
            search-placeholder="Cari kecamatan..."
            :disabled="!old('kabupaten_id')"
            :value="old('kecamatan_id')"
            required
        />
        @error('kecamatan_id')
            <div class="form-error">{{ $message }}</div>
        @enderror
        <div class="form-hint">Pilih kecamatan induk untuk menentukan awalan 6 digit kode kelurahan/desa.</div>
    </div>

    <div class="form-grid-2col">
        <x-form-select
            name="tipe"
            id="create_tipe"
            label="Tipe Wilayah"
            :selected="old('tipe', 'Kelurahan')"
            :options="[
                'Kelurahan' => 'Kelurahan',
                'Desa' => 'Desa',
            ]"
            hint="Pilih klasifikasi wilayah administratif (Kelurahan atau Desa)."
            required
        />

        <x-form-input
            name="kode"
            id="create_kode"
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
            id="create_nama"
            label="Nama Kelurahan / Desa"
            data-transform="title-case"
            placeholder="Nama kelurahan atau desa"
            hint="Nama entitas tanpa kata 'Kelurahan' atau 'Desa' (otomatis Title Case)."
            required
        />

        <x-form-input
            name="kode_pos"
            id="create_kode_pos"
            label="Kode Pos (Opsional)"
            placeholder="Contoh: 92711"
            maxlength="5"
            hint="Kode pos 5 digit wilayah untuk sinkronisasi alamat profil sekolah."
        />
    </div>

    <x-form-checkbox
        name="status"
        id="create_status"
        label="Aktifkan data wilayah kelurahan/desa ini dalam sistem"
        value="1"
        :checked="old('status', '1') === '1'"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateKelurahan">Batal</button>
        <button type="submit" class="btn-primary">Simpan</button>
    </x-slot:footer>
</x-modal>
