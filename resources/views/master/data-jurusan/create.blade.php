{{-- Modal Tambah Data Jurusan --}}
<x-modal
    id="modalCreateJurusan"
    title="Tambah Data Jurusan Baru"
    subtitle="Tambahkan data konsentrasi keahlian (SMK) atau peminatan (SMA) untuk unit sekolah terpilih"
    {{-- size="lg" --}}
    action="{{ route('master.data-jurusan.store') }}"
    form-id="formCreateJurusan">
    <x-form-input type="hidden" name="school_id" :value="$selectedSchoolId" />

    <div class="form-section-label">Unit Sekolah dan Jenjang</div>
    <div class="form-grid-2col">
        <x-form-input
            name="school_name"
            id="create_school_name"
            label="Unit Sekolah"
            :value="$selectedSchool?->name ?? '-'"
            hint="Jurusan akan otomatis terhubung ke unit sekolah ini."
            disabled
        />

        <x-form-select
            name="jenjang"
            id="create_jenjang"
            label="Jenjang Pendidikan"
            {{-- placeholder="Pilih Jenjang" --}}
            :selected="$selectedSchool?->school_type"
            :options="[
                'SMK' => 'SMK (Konsentrasi Keahlian)',
                'SMA' => 'SMA (Peminatan / Fase)',
                'MA'  => 'MA (Peminatan / Fase)',
            ]"
            required
        />
    </div>

    <div class="form-section-label">Identitas Jurusan</div>
    <div class="form-grid-2col">
        <x-form-input
            name="kode"
            id="create_kode"
            label="Kode Jurusan"
            placeholder="Contoh: RPL, TKJ, MIPA"
            maxlength="20"
            hint="Kode unik pengenal jurusan (otomatis kapital)."
            required
        />

        <x-form-input
            name="singkatan"
            id="create_singkatan"
            label="Singkatan / Inisial"
            placeholder="Contoh: RPL"
            maxlength="20"
            hint="Dapat dikosongkan jika sama dengan kode jurusan."
        />
    </div>

    <x-form-input
        name="nama"
        id="create_nama"
        label="Nama Jurusan / Konsentrasi Keahlian"
        placeholder="Contoh: Rekayasa Perangkat Lunak"
        maxlength="150"
        required
    />

    <div class="form-section-label">Struktur Keahlian dan Pimpinan</div>
    <div class="form-grid-2col">
        <x-form-input
            name="bidang_keahlian"
            id="create_bidang_keahlian"
            label="Bidang Keahlian"
            list="list_bidang_keahlian"
            placeholder="Pilih atau ketik bidang keahlian..."
            maxlength="100"
            hint="Khusus SMK (contoh: Teknologi Informasi)."
        />

        <x-form-input
            name="program_keahlian"
            id="create_program_keahlian"
            label="Program Keahlian"
            list="list_program_keahlian"
            placeholder="Pilih atau ketik program keahlian..."
            maxlength="100"
            hint="Khusus SMK (contoh: PPLG)."
        />
    </div>

    <x-form-input
        name="kepala_jurusan"
        id="create_kepala_jurusan"
        label="Kepala Program / Ketua Jurusan (Kaprog / Kajur)"
        placeholder="Nama lengkap pimpinan jurusan (opsional)..."
        maxlength="100"
    />

    <div class="form-section-label">Status dan Keterangan</div>
    <x-form-textarea
        name="deskripsi"
        id="create_deskripsi"
        label="Keterangan Tambahan (Opsional)"
        placeholder="Deskripsi singkat mengenai jurusan atau peminatan ini..."
        rows="3"
    />

    <x-form-checkbox
        name="is_active"
        id="create_is_active"
        label="Status Jurusan Aktif (Dapat digunakan pada Rombel dan Kurikulum)"
        value="1"
        :checked="old('is_active', '1') === '1'"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateJurusan">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitCreateJurusan">Simpan</button>
    </x-slot:footer>
</x-modal>

{{-- Datalist Referensi Bidang dan Program Keahlian --}}
<datalist id="list_bidang_keahlian">
    @foreach ($recommendedBidang as $bidang)
        <option value="{{ $bidang }}"></option>
    @endforeach
</datalist>

<datalist id="list_program_keahlian">
    @foreach ($recommendedProgram as $prog)
        <option value="{{ $prog }}"></option>
    @endforeach
</datalist>