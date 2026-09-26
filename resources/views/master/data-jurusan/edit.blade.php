{{-- Modal Ubah Data Jurusan --}}
<x-modal
    id="modalEditJurusan"
    title="Ubah Data Jurusan"
    subtitle="Perbarui informasi konsentrasi keahlian atau peminatan sekolah"
    {{-- size="lg" --}}
    method="PUT"
    form-id="formEditJurusan"
>
    <x-form-input type="hidden" name="school_id" id="edit_school_id" :value="$selectedSchoolId" />

    <div class="form-section-label">Unit Sekolah dan Jenjang</div>
    <div class="form-grid-2col">
        <x-form-input
            name="school_name"
            id="edit_school_name"
            label="Unit Sekolah"
            :value="$selectedSchool?->name ?? '-'"
            hint="Unit sekolah tempat jurusan ini terdaftar."
            disabled
        />

        <x-form-select
            name="jenjang"
            id="edit_jenjang"
            label="Jenjang Pendidikan"
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
            id="edit_kode"
            label="Kode Jurusan"
            placeholder="Contoh: RPL, TKJ, MIPA"
            maxlength="20"
            hint="Kode unik pengenal jurusan."
            required
        />

        <x-form-input
            name="singkatan"
            id="edit_singkatan"
            label="Singkatan / Inisial"
            placeholder="Contoh: RPL"
            maxlength="20"
            hint="Dapat dikosongkan jika sama dengan kode jurusan."
        />
    </div>

    <x-form-input
        name="nama"
        id="edit_nama"
        label="Nama Jurusan / Konsentrasi Keahlian"
        placeholder="Contoh: Rekayasa Perangkat Lunak"
        maxlength="150"
        required
    />

    <div class="form-section-label">Struktur Keahlian dan Pimpinan</div>
    <div class="form-grid-2col">
        <x-form-input
            name="bidang_keahlian"
            id="edit_bidang_keahlian"
            label="Bidang Keahlian"
            list="list_bidang_keahlian"
            placeholder="Pilih atau ketik bidang keahlian..."
            maxlength="100"
            hint="Khusus SMK (contoh: Teknologi Informasi)."
        />

        <x-form-input
            name="program_keahlian"
            id="edit_program_keahlian"
            label="Program Keahlian"
            list="list_program_keahlian"
            placeholder="Pilih atau ketik program keahlian..."
            maxlength="100"
            hint="Khusus SMK (contoh: PPLG)."
        />
    </div>

    <x-form-input
        name="kepala_jurusan"
        id="edit_kepala_jurusan"
        label="Kepala Program / Ketua Jurusan (Kaprog / Kajur)"
        placeholder="Nama lengkap pimpinan jurusan (opsional)..."
        maxlength="100"
    />

    <div class="form-section-label">Status dan Keterangan</div>
    <x-form-textarea
        name="deskripsi"
        id="edit_deskripsi"
        label="Keterangan Tambahan (Opsional)"
        placeholder="Deskripsi singkat mengenai jurusan atau peminatan ini..."
        rows="3"
    />

    <x-form-checkbox
        name="is_active"
        id="edit_is_active"
        label="Status Jurusan Aktif (Dapat digunakan pada Rombel dan Kurikulum)"
        value="1"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditJurusan">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitEditJurusan">Update</button>
    </x-slot:footer>
</x-modal>