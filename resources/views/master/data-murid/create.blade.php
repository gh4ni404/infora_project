{{-- Modal Tambah Data Murid Component --}}
<x-modal
    id="modalCreateMurid"
    title="Tambah Data Murid Baru"
    subtitle="Pendaftaran peserta didik baru ke satuan pendidikan dengan pembuatan akun otomatis"
    action="{{ route('master.data-murid.store') }}"
    size="lg"
    form-id="formCreateMurid">

    <x-form-input type="hidden" name="school_id" id="create_school_id" :value="$selectedSchoolId" />
    <x-form-input type="hidden" name="create_user_account" value="1" />
    <x-form-input type="hidden" name="is_active" value="1" />

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
            hint="Pilih rombel jika murid sudah ditentukan kelasnya."
        />

        <x-form-input
            name="nisn"
            id="create_nisn"
            label="NISN (Nomor Induk Siswa Nasional)"
            placeholder="10 digit nomor NISN nasional"
            maxlength="20"
            hint="Digunakan otomatis sebagai username & kata sandi awal akun murid."
        />

        <x-form-input
            name="nis"
            id="create_nis"
            label="NIS (Nomor Induk Siswa)"
            placeholder="Nomor induk siswa lokal sekolah"
            maxlength="30"
            hint="Digunakan sebagai alternatif login jika murid belum memiliki NISN."
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
            type="email"
            name="email"
            id="create_email"
            label="Alamat Email Murid"
            placeholder="nama@email.com"
            maxlength="100"
            hint="Opsional: untuk notifikasi dan pemulihan akun murid."
        />
    </div>

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateMurid">Batal</button>
        <button type="submit" class="btn-primary">Simpan Data Murid</button>
    </x-slot:footer>
</x-modal>
