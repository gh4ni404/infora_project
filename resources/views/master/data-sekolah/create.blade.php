{{-- Modal Tambah Sekolah Component --}}
<x-modal
    id="modalCreateSchool"
    title="Tambah Sekolah Baru"
    subtitle="Daftarkan data sekolah SMA atau SMK ke dalam registri platform INFORA"
    {{-- size="lg" --}}
    action="{{ route('master.data-sekolah.store') }}"
    form-id="formCreateSchool">

    <div class="form-section-label">Identitas Resmi</div>
    <div class="form-grid-2col">
        <x-form-input
            name="name"
            id="create_name"
            label="Nama Sekolah"
            data-transform="title-case"
            placeholder="Contoh: SMA Negeri 1 Makassar"
            required
        />

        <x-form-input
            name="npsn"
            id="create_npsn"
            label="NPSN"
            placeholder="8 digit angka, contoh: 40312345"
            maxlength="8"
            hint="Nomor Pokok Sekolah Nasional (8 digit)."
            required
        />

        <x-form-select
            name="school_type"
            id="create_school_type"
            label="Jenis Sekolah"
            placeholder="Pilih Jenis"
            :options="['SMA' => 'SMA', 'SMK' => 'SMK', 'MA' => 'MA']"
            required
        />

        <x-form-select
            name="status"
            id="create_status"
            label="Status"
            placeholder="Pilih Status"
            :options="['Negeri' => 'Negeri', 'Swasta' => 'Swasta']"
            required
        />

        <x-form-select
            name="accreditation"
            id="create_accreditation"
            label="Akreditasi"
            placeholder="Belum Ditentukan"
            :options="[
                'A' => 'A (Unggul)',
                'B' => 'B (Baik)',
                'C' => 'C (Cukup)',
                'Belum' => 'Belum Terakreditasi',
            ]"
        />

        <x-form-input
            name="nss"
            id="create_nss"
            label="NSS"
            placeholder="Nomor Statistik Sekolah"
            maxlength="20"
        />
    </div>

    <div class="form-section-divider"></div>
    <div class="form-section-label">Wilayah dan Alamat</div>

    <!-- Hidden inputs untuk sinkronisasi nama teks wilayah demi kompatibilitas cadangan -->
    <input type="hidden" name="province" id="create_province" value="{{ old('province') }}">
    <input type="hidden" name="city" id="create_city" value="{{ old('city') }}">
    <input type="hidden" name="district" id="create_district" value="{{ old('district') }}">
    <input type="hidden" name="village" id="create_village" value="{{ old('village') }}">

    <div class="form-grid-2col">
        <div class="form-group">
            <label for="create_provinsi_id" class="form-label">Provinsi</label>
            <x-searchable-select
                name="provinsi_id"
                id="create_provinsi_id"
                placeholder="Pilih Provinsi"
                search-placeholder="Cari provinsi..."
                :options="$provinsiList ?? []"
                :value="old('provinsi_id')"
            />
            @error('provinsi_id')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="create_kabupaten_id" class="form-label">Kabupaten / Kota</label>
            <x-searchable-select
                name="kabupaten_id"
                id="create_kabupaten_id"
                placeholder="Pilih Kabupaten/Kota"
                search-placeholder="Cari kabupaten/kota..."
                :disabled="!old('provinsi_id')"
                :value="old('kabupaten_id')"
            />
            @error('kabupaten_id')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="create_kecamatan_id" class="form-label">Kecamatan</label>
            <x-searchable-select
                name="kecamatan_id"
                id="create_kecamatan_id"
                placeholder="Pilih Kecamatan"
                search-placeholder="Cari kecamatan..."
                :disabled="!old('kabupaten_id')"
                :value="old('kecamatan_id')"
            />
            @error('kecamatan_id')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="create_kelurahan_id" class="form-label">Kelurahan / Desa</label>
            <x-searchable-select
                name="kelurahan_id"
                id="create_kelurahan_id"
                placeholder="Pilih Kelurahan/Desa"
                search-placeholder="Cari kelurahan/desa..."
                :disabled="!old('kecamatan_id')"
                :value="old('kelurahan_id')"
            />
            @error('kelurahan_id')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-grid-2col mt-4">
        <x-form-input
            name="postal_code"
            id="create_postal_code"
            label="Kode Pos"
            placeholder="Kode Pos (otomatis terisi saat memilih kelurahan)"
            maxlength="10"
        />
    </div>

    <div class="mt-4">
        <x-form-textarea
            name="address"
            id="create_address"
            label="Alamat Jalan / Lengkap"
            placeholder="Jalan, RT/RW, dan detail alamat lengkap sekolah"
            rows="2"
        />
    </div>

    <div class="form-section-divider"></div>
    <div class="form-section-label">Kontak</div>

    <div class="form-grid-2col">
        <x-form-input
            name="phone"
            id="create_phone"
            label="Telepon"
            placeholder="Nomor Telepon"
            maxlength="20"
        />
        <x-form-input
            name="fax"
            id="create_fax"
            label="Fax"
            placeholder="Nomor Fax"
            maxlength="20"
        />
        <x-form-input
            type="email"
            name="email"
            id="create_email"
            label="Email"
            placeholder="email@sekolah.sch.id"
        />
        <x-form-input
            type="url"
            name="website"
            id="create_website"
            label="Website"
            placeholder="https://sekolah.sch.id"
        />
    </div>

    <div class="form-section-divider"></div>
    <div class="form-section-label">Pimpinan dan Yayasan</div>

    <div class="form-grid-2col">
        <x-form-input
            name="principal_name"
            id="create_principal_name"
            label="Nama Kepala Sekolah"
            placeholder="Nama lengkap beserta gelar"
        />
        <x-form-input
            name="principal_nip"
            id="create_principal_nip"
            label="NIP Kepala Sekolah"
            placeholder="Nomor Induk Pegawai"
            maxlength="30"
        />
        <x-form-input
            name="foundation_name"
            id="create_foundation_name"
            label="Nama Yayasan"
            placeholder="Relevan untuk sekolah Swasta"
            hint="Isi jika sekolah berstatus Swasta dan bernaung di bawah yayasan."
        />
    </div>

    <div class="form-section-divider"></div>
    <div class="form-section-label">Logo Sekolah</div>

    <x-image-upload
        name="logo"
        id="create_logo"
        placeholder="Klik untuk unggah logo sekolah"
        hint="Format: PNG, JPG, WebP, GIF. Maks 1MB."
    />

    <x-form-checkbox
        name="is_active"
        id="create_is_active"
        label="Sekolah aktif dan tampil dalam sistem"
        value="1"
        :checked="old('is_active', '1') == '1'"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateSchool">Batal</button>
        <button type="submit" class="btn-primary">Simpan</button>
    </x-slot:footer>
</x-modal>
