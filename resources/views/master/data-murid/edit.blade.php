{{-- Modal Ubah Data Murid Component --}}
<x-modal
    id="modalEditMurid"
    size="lg"
    title="Ubah Data Murid"
    subtitle="Perbarui identitas dasar, rombel, dan status keaktifan peserta didik"
    method="PUT"
    form-id="formEditMurid">

    <x-form-input type="hidden" name="school_id" id="edit_school_id" :value="$selectedSchoolId" />

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
            name="angkatan"
            id="edit_angkatan"
            label="Tahun Angkatan / Masuk"
            placeholder="Contoh: 2026 atau 2026/2027"
            maxlength="20"
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
            required
        />

        <div class="flex items-center pt-6">
            <x-form-checkbox
                name="is_active"
                id="edit_is_active"
                label="Status murid aktif di sistem"
                value="1"
            />
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditMurid">Batal</button>
        <button type="submit" class="btn-primary">Perbarui Data Murid</button>
    </x-slot:footer>
</x-modal>
