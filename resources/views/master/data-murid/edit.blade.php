{{-- Modal Ubah Data Murid Component --}}
<x-modal
    id="modalEditMurid"
    size="xl"
    title="Ubah Data Murid"
    subtitle="Perbarui identitas peserta didik, penempatan rombel, kontak ortu, atau akun login"
    method="PUT"
    form-id="formEditMurid">

    <x-form-input type="hidden" name="school_id" id="edit_school_id" :value="$selectedSchoolId" />

    {{-- Seksi 1: Identitas Pokok & Rombel --}}
    <div class="form-section-label">Identitas Pokok & Rombongan Belajar (Rombel)</div>
    <div class="form-grid-2col">
        <div class="col-span-full">
            <x-form-input
                name="nama_lengkap"
                id="edit_nama_lengkap"
                label="Nama Lengkap Murid"
                data-transform="title-case"
                placeholder="Contoh: Muhammad Raditya"
                required
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

        <x-form-select
            name="kelas_id"
            id="edit_kelas_id"
            label="Rombongan Belajar / Kelas"
            placeholder="-- Belum Masuk Kelas (Tanpa Rombel) --"
            :options="$kelasList->mapWithKeys(fn($k) => [$k->id => $k->nama . ($k->tingkatan ? ' (' . $k->tingkatan->nama . ')' : '')])->all()"
        />

        <x-form-input
            name="nisn"
            id="edit_nisn"
            label="NISN (Nomor Induk Siswa Nasional)"
            placeholder="10 digit nomor NISN nasional"
            maxlength="20"
        />

        <x-form-input
            name="nis"
            id="edit_nis"
            label="NIS (Nomor Induk Siswa)"
            placeholder="Nomor induk siswa lokal sekolah"
            maxlength="30"
        />

        <x-form-input
            name="nik"
            id="edit_nik"
            label="NIK (Nomor Induk Kependudukan)"
            placeholder="16 digit angka sesuai KK / KTP"
            maxlength="20"
        />

        <x-form-input
            name="angkatan"
            id="edit_angkatan"
            label="Tahun Angkatan / Masuk"
            placeholder="Contoh: 2026 atau 2026/2027"
            maxlength="20"
        />

        <x-form-input
            name="tempat_lahir"
            id="edit_tempat_lahir"
            label="Tempat Lahir"
            data-transform="title-case"
            placeholder="Kota / Kabupaten Lahir"
            maxlength="100"
        />

        <x-form-input
            type="date"
            name="tanggal_lahir"
            id="edit_tanggal_lahir"
            label="Tanggal Lahir"
        />

        <div class="col-span-full">
            <x-form-select
                name="agama"
                id="edit_agama"
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
            id="edit_no_hp"
            label="No. HP / WhatsApp Murid"
            placeholder="Contoh: 081234567890"
            maxlength="30"
        />

        <x-form-input
            type="email"
            name="email"
            id="edit_email"
            label="Alamat Email Murid"
            placeholder="nama@email.com"
            maxlength="100"
        />

        <x-form-select
            name="provinsi_id"
            id="edit_provinsi_id"
            label="Provinsi Domisili"
            placeholder="Pilih Provinsi..."
        />

        <x-form-select
            name="kabupaten_id"
            id="edit_kabupaten_id"
            label="Kabupaten / Kota"
            placeholder="Pilih Kabupaten/Kota..."
        />

        <x-form-select
            name="kecamatan_id"
            id="edit_kecamatan_id"
            label="Kecamatan"
            placeholder="Pilih Kecamatan..."
        />

        <x-form-select
            name="kelurahan_id"
            id="edit_kelurahan_id"
            label="Kelurahan / Desa"
            placeholder="Pilih Kelurahan/Desa..."
        />

        <div class="col-span-full">
            <x-form-input
                name="alamat"
                id="edit_alamat"
                label="Alamat Jalan / Dusun / RT & RW"
                placeholder="Nama jalan, nomor rumah, RT/RW, atau nama dusun"
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
            id="edit_nama_ayah"
            label="Nama Ayah Kandung"
            data-transform="title-case"
            placeholder="Nama lengkap ayah"
            maxlength="150"
        />

        <x-form-input
            name="nama_ibu"
            id="edit_nama_ibu"
            label="Nama Ibu Kandung"
            data-transform="title-case"
            placeholder="Nama lengkap ibu"
            maxlength="150"
        />

        <x-form-input
            name="nama_wali"
            id="edit_nama_wali"
            label="Nama Wali (Opsional)"
            data-transform="title-case"
            placeholder="Nama wali jika ada"
            maxlength="150"
        />

        <x-form-input
            type="tel"
            name="no_hp_ortu"
            id="edit_no_hp_ortu"
            label="No. HP / WhatsApp Orang Tua / Wali"
            placeholder="Contoh: 081298765432"
            maxlength="30"
        />
    </div>

    {{-- Seksi 4: Akun Pengguna & Kredensial --}}
    <div class="form-section-divider"></div>
    <div class="form-section-label">Akun Pengguna Login & Status Keaktifan</div>

    {{-- Kartu Ringkasan Status Akun --}}
    <div class="card-surface border rounded-xl p-3.5 mb-4 bg-slate-50/70">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-brand/10 text-brand flex items-center justify-center font-bold text-sm">
                    <x-icon name="user" class="w-4 h-4" />
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-medium">Akun Pengguna Terhubung</div>
                    <div class="text-sm font-semibold text-slate-800" id="edit_account_username_text">-</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span id="edit_account_status_badge" class="badge badge-neutral">-</span>
                <a href="#" id="edit_account_menu_access_btn" class="btn-secondary btn-sm" target="_blank" rel="noopener">
                    <x-icon name="shield" class="w-3.5 h-3.5" />
                    Hak Akses
                </a>
            </div>
        </div>
    </div>

    <div class="form-grid-2col">
        <x-form-input
            name="username"
            id="edit_username"
            label="Username Login"
            placeholder="Username untuk login"
            maxlength="50"
        />

        <x-form-input
            type="password"
            name="password"
            id="edit_password"
            label="Kata Sandi Baru"
            placeholder="Kosongkan jika tidak ingin mengubah"
            minlength="6"
            hint="Isi kolom ini hanya jika Anda ingin menetapkan kata sandi baru untuk murid."
        />

        <x-form-select
            name="status"
            id="edit_status"
            label="Status Siswa"
            :options="[
                'aktif' => 'Aktif',
                'lulus' => 'Lulus',
                'mutasi' => 'Mutasi / Pindah',
                'keluar' => 'Keluar',
                'dropout' => 'Drop Out (DO)',
            ]"
        />

        <div class="flex items-center pt-6">
            <x-form-checkbox
                name="is_active"
                id="edit_is_active"
                label="Status murid dan akun login aktif di sistem"
                value="1"
            />
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditMurid">Batal</button>
        <button type="submit" class="btn-primary">Perbarui Data Murid</button>
    </x-slot:footer>
</x-modal>
