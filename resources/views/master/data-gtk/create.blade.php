{{-- Modal Tambah Data GTK Component (Quick Onboarding: Nama, JK, Status & Penugasan) --}}
<x-modal
    id="modalCreateGtk"
    title="Tambah Data Pendidik & Tenaga Kependidikan"
    subtitle="Pendaftaran cepat GTK baru. Akun login pengguna akan digenerate otomatis oleh sistem."
    action="{{ route('master.data-gtk.store') }}"
    size="lg"
    form-id="formCreateGtk">

    <input type="hidden" name="school_id" id="create_school_id" value="{{ $selectedSchoolId }}">


    {{-- Seksi 1: Identitas Pokok & Kepegawaian --}}
    <div class="form-section-label">Identitas Pokok & Kepegawaian</div>
    <div class="form-grid-2col">
        <div class="col-span-full">
            <x-form-input
                name="nama_lengkap"
                id="create_nama_lengkap"
                label="Nama Lengkap"
                data-transform="title-case"
                placeholder="Contoh: Budi Santoso"
                hint="Nama resmi guru atau staf (tanpa gelar). Nama ini juga akan digunakan sebagai username login & kata sandi awal akun."
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
            name="status_kepegawaian"
            id="create_status_kepegawaian"
            label="Status Kepegawaian"
            placeholder="Pilih Status Kepegawaian"
            :options="collect($statusKepegawaianList)->mapWithKeys(fn($item) => [$item => $item])->all()"
            required
        />
        <div class="col-span-full">
            <x-form-select
                name="jenis_ptk"
                id="create_jenis_ptk"
                label="Peran / Kategori GTK"
                placeholder="Pilih Peran GTK"
                :options="[
                    'Guru' => 'Guru (Tenaga Pendidik)',
                    'Staf' => 'Staf (Tenaga Kependidikan / TU)',
                ]"
                hint="Tentukan status pokok. Pemetaan tugas spesifik (Wali Kelas, PKL, dll.) dapat diatur saat edit data."
                required
            />
        </div>
    </div>

    {{-- Seksi 2: Status Keaktifan --}}
    <div class="form-section-divider"></div>
    <x-form-checkbox
        name="is_active"
        id="create_is_active"
        label="GTK aktif dan akun pengguna dapat langsung mengakses sistem"
        value="1"
        :checked="old('is_active', '1') == '1'"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateGtk">Batal</button>
        <button type="submit" class="btn-primary">Simpan GTK</button>
    </x-slot:footer>
</x-modal>
