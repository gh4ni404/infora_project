{{-- Modal Ubah Data GTK Component --}}
<x-modal
    id="modalEditGtk"
    title="Ubah Data Pendidik & Tenaga Kependidikan"
    subtitle="Perbarui biodata pribadi, data kepegawaian, atau kredensial akun pengguna"
    method="PUT"
    form-id="formEditGtk">

    <input type="hidden" name="school_id" id="edit_school_id" value="{{ $selectedSchoolId }}">

    {{-- Seksi 1: Identitas & Biodata Diri --}}
    <div class="form-section-label">Identitas & Biodata Pribadi</div>
    <div class="form-grid-2col">
        <x-form-input
            name="nama_lengkap"
            id="edit_nama_lengkap"
            label="Nama Lengkap"
            data-transform="title-case"
            placeholder="Contoh: Budi Santoso (tanpa gelar)"
            required
        />

        <div class="form-grid-2col">
            <x-form-input
                name="gelar_depan"
                id="edit_gelar_depan"
                label="Gelar Depan"
                placeholder="Contoh: Dr., Drs."
                maxlength="50"
            />
            <x-form-input
                name="gelar_belakang"
                id="edit_gelar_belakang"
                label="Gelar Belakang"
                placeholder="Contoh: S.Pd., M.Kom."
                maxlength="50"
            />
        </div>

        <x-form-select
            name="jenis_kelamin"
            id="edit_jenis_kelamin"
            label="Jenis Kelamin"
            placeholder="Pilih Jenis Kelamin"
            :options="['L' => 'Laki-laki', 'P' => 'Perempuan']"
            required
        />

        <x-form-input
            name="nik"
            id="edit_nik"
            label="NIK (Nomor Induk Kependudukan)"
            placeholder="16 digit angka sesuai KTP"
            maxlength="20"
        />

        <x-form-input
            name="tempat_lahir"
            id="edit_tempat_lahir"
            label="Tempat Lahir"
            data-transform="title-case"
            placeholder="Contoh: Watampone"
            maxlength="100"
        />

        <x-form-input
            type="date"
            name="tanggal_lahir"
            id="edit_tanggal_lahir"
            label="Tanggal Lahir"
        />

        <x-form-select
            name="agama"
            id="edit_agama"
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
            id="edit_status_kepegawaian"
            label="Status Kepegawaian"
            placeholder="Pilih Status"
            :options="collect($statusKepegawaianList)->mapWithKeys(fn($item) => [$item => $item])->all()"
            required
        />

        <x-form-input
            name="nip"
            id="edit_nip"
            label="NIP (Nomor Induk Pegawai)"
            placeholder="18 digit angka (khusus PNS/PPPK)"
            maxlength="30"
        />

        <x-form-input
            name="nuptk"
            id="edit_nuptk"
            label="NUPTK"
            placeholder="16 digit angka pendidik"
            maxlength="30"
        />

        <x-form-select
            name="pendidikan_terakhir"
            id="edit_pendidikan_terakhir"
            label="Pendidikan Terakhir"
            placeholder="Pilih Jenjang Pendidikan"
            :options="collect($pendidikanList)->mapWithKeys(fn($item) => [$item => $item])->all()"
        />

        <x-form-input
            name="jurusan_pendidikan"
            id="edit_jurusan_pendidikan"
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
                        <label class="form-check" for="edit_ptk_{{ Str::slug($ptk) }}">
                            <input
                                type="checkbox"
                                name="jenis_ptk[]"
                                value="{{ $ptk }}"
                                id="edit_ptk_{{ Str::slug($ptk) }}"
                                class="form-check-input edit-jenis-ptk-cb"
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
                        <label class="form-check" for="edit_ptk_{{ Str::slug($ptk) }}">
                            <input
                                type="checkbox"
                                name="jenis_ptk[]"
                                value="{{ $ptk }}"
                                id="edit_ptk_{{ Str::slug($ptk) }}"
                                class="form-check-input edit-jenis-ptk-cb"
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
            id="edit_no_hp"
            label="Nomor HP / WhatsApp"
            placeholder="Contoh: 081234567890"
            maxlength="30"
        />

        <x-form-input
            type="email"
            name="email"
            id="edit_email"
            label="Alamat Email Pribadi / Dinas"
            placeholder="Contoh: budi.santoso@sekolah.sch.id"
            maxlength="100"
        />
    </div>

    <div class="form-grid-2col">
        <div class="form-group">
            <label for="edit_provinsi_id" class="form-label">Provinsi</label>
            <x-searchable-select
                name="provinsi_id"
                id="edit_provinsi_id"
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
            <label for="edit_kabupaten_id" class="form-label">Kabupaten / Kota</label>
            <x-searchable-select
                name="kabupaten_id"
                id="edit_kabupaten_id"
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
            <label for="edit_kecamatan_id" class="form-label">Kecamatan</label>
            <x-searchable-select
                name="kecamatan_id"
                id="edit_kecamatan_id"
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
            <label for="edit_kelurahan_id" class="form-label">Kelurahan / Desa</label>
            <x-searchable-select
                name="kelurahan_id"
                id="edit_kelurahan_id"
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
        id="edit_alamat"
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
            id="edit_username"
            label="Username Login"
            placeholder="Username saat ini"
            maxlength="50"
        />

        <x-form-input
            type="password"
            name="password"
            id="edit_password"
            label="Ganti Kata Sandi (Opsional)"
            placeholder="Kosongkan jika tidak ingin mengubah kata sandi"
            hint="Hanya diisi jika ingin memperbarui kata sandi pengguna ini."
        />

        <x-form-select
            name="role_template"
            id="edit_role_template"
            label="Ubah Template Peran & Hak Akses"
            placeholder="Biarkan jika tidak ingin mereset peran"
            :options="$roleTemplates->mapWithKeys(fn($r) => [$r->role_key => $r->role_name . ' (' . ucfirst($r->role_category) . ')'])->all()"
            hint="Memilih template baru akan memperbarui matriks izin menu GTK ini."
        />
    </div>

    {{-- Seksi 5: Foto Profil & Status --}}
    <div class="form-section-divider"></div>
    <div class="form-section-label">Foto Profil & Status Keaktifan</div>

    <x-image-upload
        name="foto"
        id="edit_foto"
        placeholder="Klik untuk ganti foto profil GTK"
        hint="Format: PNG, JPG, WebP. Maks 1MB."
    />

    <x-form-checkbox
        name="is_active"
        id="edit_is_active"
        label="GTK aktif dan dapat mengakses sistem"
        value="1"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditGtk">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitEditGtk">Perbarui Data</button>
    </x-slot:footer>
</x-modal>
