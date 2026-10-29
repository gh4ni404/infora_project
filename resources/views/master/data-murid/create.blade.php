{{-- Modal Tambah Data Murid Component --}}
<x-modal
    id="modalCreateMurid"
    title="Tambah Data Murid Baru"
    subtitle="Pendaftaran peserta didik baru ke satuan pendidikan lengkap dengan identitas, rombel, dan akun"
    action="{{ route('master.data-murid.store') }}"
    size="xl"
    form-id="formCreateMurid">

    <x-form-input type="hidden" name="school_id" id="create_school_id" :value="$selectedSchoolId" />

    {{-- Seksi 1: Identitas Pokok & Rombel --}}
    <div class="form-section-label">Identitas Pokok & Rombongan Belajar (Rombel)</div>
    <div class="form-grid-2col">
        <div class="col-span-full">
            <x-form-input
                name="nama_lengkap"
                id="create_nama_lengkap"
                label="Nama Lengkap Murid"
                data-transform="title-case"
                placeholder="Contoh: Muhammad Raditya"
                hint="Nama resmi sesuai akta kelahiran / ijazah sebelumnya."
                required
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

        <x-form-select
            name="kelas_id"
            id="create_kelas_id"
            label="Rombongan Belajar / Kelas"
            placeholder="-- Belum Masuk Kelas (Dapat Diatur Nanti) --"
            :options="$kelasList->mapWithKeys(fn($k) => [$k->id => $k->nama . ($k->tingkatan ? ' (' . $k->tingkatan->nama . ')' : '')])->all()"
            hint="Pilih kelas aktif jika murid sudah dialokasikan ke rombel."
        />

        <x-form-input
            name="nisn"
            id="create_nisn"
            label="NISN (Nomor Induk Siswa Nasional)"
            placeholder="10 digit nomor NISN nasional"
            maxlength="20"
        />

        <x-form-input
            name="nis"
            id="create_nis"
            label="NIS (Nomor Induk Siswa)"
            placeholder="Nomor induk siswa lokal sekolah"
            maxlength="30"
        />

        <x-form-input
            name="nik"
            id="create_nik"
            label="NIK (Nomor Induk Kependudukan)"
            placeholder="16 digit angka sesuai KK / KTP"
            maxlength="20"
        />

        <x-form-input
            name="angkatan"
            id="create_angkatan"
            label="Tahun Angkatan / Masuk"
            placeholder="Contoh: 2026 atau 2026/2027"
            :value="date('Y')"
            maxlength="20"
        />

        <x-form-input
            name="tempat_lahir"
            id="create_tempat_lahir"
            label="Tempat Lahir"
            data-transform="title-case"
            placeholder="Kota / Kabupaten Lahir"
            maxlength="100"
        />

        <x-form-input
            type="date"
            name="tanggal_lahir"
            id="create_tanggal_lahir"
            label="Tanggal Lahir"
        />

        <div class="col-span-full">
            <x-form-select
                name="agama"
                id="create_agama"
                label="Agama"
                placeholder="Pilih Agama"
                :options="[
                    'Islam' => 'Islam',
                    'Kristen' => 'Kristen Protestan',
                    'Katolik' => 'Katolik',
                    'Hindu' => 'Hindu',
                    'Buddha' => 'Buddha',
                    'Konghucu' => 'Konghucu',
                ]"
            />
        </div>
    </div>

    {{-- Seksi 2: Kontak & Domisili Administratif Kemendagri --}}
    <div class="form-section-divider"></div>
    <div class="form-section-label">Kontak & Domisili Administratif (Kemendagri)</div>
    <div class="form-grid-2col">
        <x-form-input
            type="tel"
            name="no_hp"
            id="create_no_hp"
            label="No. HP / WhatsApp Murid"
            placeholder="Contoh: 081234567890"
            maxlength="30"
        />

        <x-form-input
            type="email"
            name="email"
            id="create_email"
            label="Alamat Email Murid"
            placeholder="nama@email.com"
            maxlength="100"
        />

        <div class="form-group">
            <label for="create_provinsi_id" class="form-label">Provinsi Domisili</label>
            <x-searchable-select
                name="provinsi_id"
                id="create_provinsi_id"
                placeholder="Pilih Provinsi..."
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
                placeholder="Pilih Kabupaten/Kota..."
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
                placeholder="Pilih Kecamatan..."
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
                placeholder="Pilih Kelurahan/Desa..."
                search-placeholder="Cari kelurahan/desa..."
                :disabled="!old('kecamatan_id')"
                :value="old('kelurahan_id')"
            />
            @error('kelurahan_id')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-span-full">
            <x-form-textarea
                name="alamat"
                id="create_alamat"
                label="Alamat Jalan / Dusun / RT & RW"
                placeholder="Nama jalan, nomor rumah, RT/RW, atau nama dusun"
                rows="2"
                maxlength="1000"
            />
        </div>
    </div>

    {{-- Seksi 3: Data Orang Tua / Wali --}}
    <div class="form-section-divider"></div>
    <div class="form-section-label">Data Orang Tua / Wali Siswa</div>
    <div class="form-grid-2col">
        <x-form-input
            name="nama_ayah"
            id="create_nama_ayah"
            label="Nama Ayah Kandung"
            data-transform="title-case"
            placeholder="Nama lengkap ayah"
            maxlength="150"
        />

        <x-form-input
            name="nama_ibu"
            id="create_nama_ibu"
            label="Nama Ibu Kandung"
            data-transform="title-case"
            placeholder="Nama lengkap ibu"
            maxlength="150"
        />

        <x-form-input
            name="nama_wali"
            id="create_nama_wali"
            label="Nama Wali (Opsional)"
            data-transform="title-case"
            placeholder="Nama wali jika tinggal bersama wali"
            maxlength="150"
        />

        <x-form-input
            type="tel"
            name="no_hp_ortu"
            id="create_no_hp_ortu"
            label="No. HP / WhatsApp Orang Tua / Wali"
            placeholder="Contoh: 081298765432"
            hint="Nomor penting untuk pengumuman sekolah dan kontak darurat."
            maxlength="30"
        />
    </div>

    {{-- Seksi 4: Akun Pengguna & Status Keaktifan --}}
    <div class="form-section-divider"></div>
    <div class="form-section-label">Akun Pengguna Login & Status Keaktifan</div>
    <div class="space-y-4">
        <x-form-checkbox
            name="create_user_account"
            id="create_create_user_account"
            label="Otomatis buatkan akun pengguna login (User) untuk murid ini"
            value="1"
            :checked="true"
        />

        <div class="form-grid-2col" id="createUserCredentialsBox">
            <x-form-input
                name="username"
                id="create_username"
                label="Username Login Kustom (Opsional)"
                placeholder="Biarkan kosong untuk otomatis (NISN / NIS)"
                maxlength="50"
                hint="Jika dikosongkan, sistem akan otomatis menggunakan NISN atau NIS siswa."
            />

            <x-form-input
                type="password"
                name="password"
                id="create_password"
                label="Kata Sandi Kustom (Opsional)"
                placeholder="Biarkan kosong untuk bawaan default"
                minlength="6"
                hint="Sandi bawaan sistem: NISN murid, atau 'infora123'."
            />
        </div>

        <div class="form-grid-2col pt-2">
            <x-form-select
                name="status"
                id="create_status"
                label="Status Siswa"
                :options="[
                    'aktif' => 'Aktif',
                    'lulus' => 'Lulus',
                    'mutasi' => 'Mutasi / Pindah',
                    'keluar' => 'Keluar',
                    'dropout' => 'Drop Out (DO)',
                ]"
                :selected="'aktif'"
            />

            <div class="flex items-center pt-6">
                <x-form-checkbox
                    name="is_active"
                    id="create_is_active"
                    label="Status murid aktif di sistem"
                    value="1"
                    :checked="true"
                />
            </div>
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateMurid">Batal</button>
        <button type="submit" class="btn-primary">Simpan Data Murid</button>
    </x-slot:footer>
</x-modal>
