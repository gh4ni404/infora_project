{{-- Modal Tambah Data GTK Component --}}
<x-modal
    id="modalCreateGtk"
    title="Tambah Data Pendidik & Tenaga Kependidikan"
    subtitle="Daftarkan guru atau staf tata usaha baru serta buatkan akun pengguna sistem"
    action="{{ route('master.data-gtk.store') }}"
    size="lg"
    form-id="formCreateGtk">

    <input type="hidden" name="school_id" id="create_school_id" value="{{ $selectedSchoolId }}">

    {{-- Seksi 1: Identitas & Biodata Diri --}}
    <div class="form-section-label">Identitas & Biodata Pribadi</div>
    <div class="form-grid-2col">
        <x-form-input
            name="nama_lengkap"
            id="create_nama_lengkap"
            label="Nama Lengkap"
            data-transform="title-case"
            placeholder="Contoh: Budi Santoso (tanpa gelar)"
            required
        />

        <div class="form-grid-2col">
            <x-form-input
                name="gelar_depan"
                id="create_gelar_depan"
                label="Gelar Depan"
                placeholder="Contoh: Dr., Drs."
                maxlength="50"
            />
            <x-form-input
                name="gelar_belakang"
                id="create_gelar_belakang"
                label="Gelar Belakang"
                placeholder="Contoh: S.Pd., M.Kom."
                maxlength="50"
            />
        </div>

        <x-form-select
            name="jenis_kelamin"
            id="create_jenis_kelamin"
            label="Jenis Kelamin"
            placeholder="Pilih Jenis Kelamin"
            :options="['L' => 'Laki-laki', 'P' => 'Perempuan']"
            required
        />

        <x-form-input
            name="nik"
            id="create_nik"
            label="NIK (Nomor Induk Kependudukan)"
            placeholder="16 digit angka sesuai KTP"
            maxlength="20"
        />

        <x-form-input
            name="tempat_lahir"
            id="create_tempat_lahir"
            label="Tempat Lahir"
            data-transform="title-case"
            placeholder="Contoh: Watampone"
            maxlength="100"
        />

        <x-form-input
            type="date"
            name="tanggal_lahir"
            id="create_tanggal_lahir"
            label="Tanggal Lahir"
        />

        <x-form-select
            name="agama"
            id="create_agama"
            label="Agama"
            placeholder="Pilih Agama"
            :options="[
                'Islam' => 'Islam',
                'Kristen Protestan' => 'Kristen Protestan',
                'Katolik' => 'Katolik',
                'Hindu' => 'Hindu',
                'Buddha' => 'Buddha',
                'Khonghucu' => 'Khonghucu',
            ]"
        />
    </div>

    {{-- Seksi 2: Kepegawaian & Kualifikasi Pendidikan --}}
    <div class="form-section-divider"></div>
    <div class="form-section-label">Klasifikasi Kepegawaian & Kualifikasi Pendidikan</div>
    <div class="form-grid-2col">
        <x-form-select
            name="status_kepegawaian"
            id="create_status_kepegawaian"
            label="Status Kepegawaian"
            placeholder="Pilih Status"
            :options="collect($statusKepegawaianList)->mapWithKeys(fn($item) => [$item => $item])->all()"
            required
        />

        <x-form-input
            name="nip"
            id="create_nip"
            label="NIP (Nomor Induk Pegawai)"
            placeholder="18 digit angka (khusus PNS/PPPK)"
            maxlength="30"
        />

        <x-form-input
            name="nuptk"
            id="create_nuptk"
            label="NUPTK"
            placeholder="16 digit angka pendidik"
            maxlength="30"
        />

        <x-form-select
            name="pendidikan_terakhir"
            id="create_pendidikan_terakhir"
            label="Pendidikan Terakhir"
            placeholder="Pilih Jenjang Pendidikan"
            :options="collect($pendidikanList)->mapWithKeys(fn($item) => [$item => $item])->all()"
        />

        <x-form-input
            name="jurusan_pendidikan"
            id="create_jurusan_pendidikan"
            label="Jurusan / Program Studi"
            data-transform="title-case"
            placeholder="Contoh: Pendidikan Matematika"
            maxlength="100"
        />
    </div>

    {{-- Penugasan PTK Majemuk --}}
    <div class="form-section-divider"></div>
    <div class="form-section-label">Jenis PTK & Penugasan (Dapat Memilih Lebih dari Satu)</div>
    <div class="form-hint mb-3">Centang seluruh tugas atau peranan yang diemban oleh GTK ini di satuan pendidikan.</div>

    @error('jenis_ptk')
        <div class="alert alert-danger mb-3">{{ $message }}</div>
    @enderror

    <div class="form-grid-2col">
        {{-- Kelompok Penugasan Pendidik (Guru) --}}
        <div class="card-surface p-3 border">
            <div class="card-header pb-2 mb-2 border-bottom">
                <span class="badge badge-primary">Pendidik</span>
                <span class="font-weight-semibold">Tugas Guru & Pengajar</span>
            </div>
            <div class="card-body p-0">
                @foreach ($jenisPtkPendidik as $ptk)
                    <div class="form-group mb-2">
                        <label class="form-check" for="create_ptk_{{ Str::slug($ptk) }}">
                            <input
                                type="checkbox"
                                name="jenis_ptk[]"
                                value="{{ $ptk }}"
                                id="create_ptk_{{ Str::slug($ptk) }}"
                                class="form-check-input"
                                @checked(in_array($ptk, (array) old('jenis_ptk', [])))
                            >
                            <span class="form-check-label">{{ $ptk }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Kelompok Penugasan Tenaga Kependidikan (Tendik) --}}
        <div class="card-surface p-3 border">
            <div class="card-header pb-2 mb-2 border-bottom">
                <span class="badge badge-purple">Tendik</span>
                <span class="font-weight-semibold">Tugas Tata Usaha & Staf</span>
            </div>
            <div class="card-body p-0">
                @foreach ($jenisPtkTendik as $ptk)
                    <div class="form-group mb-2">
                        <label class="form-check" for="create_ptk_{{ Str::slug($ptk) }}">
                            <input
                                type="checkbox"
                                name="jenis_ptk[]"
                                value="{{ $ptk }}"
                                id="create_ptk_{{ Str::slug($ptk) }}"
                                class="form-check-input"
                                @checked(in_array($ptk, (array) old('jenis_ptk', [])))
                            >
                            <span class="form-check-label">{{ $ptk }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Seksi 3: Kontak & Alamat --}}
    <div class="form-section-divider"></div>
    <div class="form-section-label">Kontak & Alamat Domisili</div>
    <div class="form-grid-2col">
        <x-form-input
            name="no_hp"
            id="create_no_hp"
            label="Nomor HP / WhatsApp"
            placeholder="Contoh: 081234567890"
            maxlength="30"
        />

        <x-form-input
            type="email"
            name="email"
            id="create_email"
            label="Alamat Email Pribadi / Dinas"
            placeholder="Contoh: budi.santoso@sekolah.sch.id"
            maxlength="100"
        />
    </div>

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

    <x-form-textarea
        name="alamat"
        id="create_alamat"
        label="Alamat Jalan / Dusun / RT / RW"
        placeholder="Alamat jalan, nomor rumah, RT/RW, atau dusun tempat tinggal..."
        rows="2"
    />

    {{-- Seksi 4: Kredensial Akun Pengguna Sistem --}}
    <div class="form-section-divider"></div>
    <div class="form-section-label">Kredensial Akun Pengguna Sistem</div>
    <div class="form-grid-2col">
        <x-form-input
            name="username"
            id="create_username"
            label="Username Login"
            placeholder="Otomatis dari NIP/NUPTK/NIK jika kosong"
            hint="Kosongkan jika ingin sistem mengisinya secara otomatis."
            maxlength="50"
        />

        <x-form-input
            type="password"
            name="password"
            id="create_password"
            label="Kata Sandi (Password)"
            placeholder="Otomatis NIP/NUPTK atau infora123 jika kosong"
            hint="Kosongkan untuk menggunakan kata sandi bawaan sistem."
        />

        <x-form-select
            name="role_template"
            id="create_role_template"
            label="Template Peran & Hak Akses"
            placeholder="Pilih Template Hak Akses"
            :options="$roleTemplates->mapWithKeys(fn($r) => [$r->role_key => $r->role_name . ' (' . ucfirst($r->role_category) . ')'])->all()"
            hint="Menentukan menu apa saja yang dapat diakses oleh GTK ini."
        />
    </div>

    {{-- Seksi 5: Foto Profil & Status --}}
    <div class="form-section-divider"></div>
    <div class="form-section-label">Foto Profil & Status Keaktifan</div>

    <x-image-upload
        name="foto"
        id="create_foto"
        placeholder="Klik untuk unggah foto profil GTK"
        hint="Format: PNG, JPG, WebP. Maks 1MB."
    />

    <x-form-checkbox
        name="is_active"
        id="create_is_active"
        label="GTK aktif dan dapat mengakses sistem"
        value="1"
        :checked="old('is_active', '1') == '1'"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateGtk">Batal</button>
        <button type="submit" class="btn-primary">Simpan GTK</button>
    </x-slot:footer>
</x-modal>
