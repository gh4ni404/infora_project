@php
    $createSemesterSubtitle = new \Illuminate\Support\HtmlString(
        'Tahun Ajaran: <strong class="text-primary">' . e($selectedTahunAjaran?->tahun ?? '-') . '</strong> &bull; ' . e($selectedSchool?->name ?? '-')
    );
@endphp

{{-- ========================================================================
     MODAL TAMBAH SEMESTER
     ======================================================================== --}}
<x-modal
    id="modalCreateSemester"
    title="Tambah Semester Baru"
    :subtitle="$createSemesterSubtitle"
    :action="$selectedTahunAjaran ? route('konfigurasi-sekolah.tahun-ajaran.semester.store', $selectedTahunAjaran) : ''"
    form-id="formCreateSemester"
>
    <x-form-select
        name="semester"
        id="create_semester_type"
        label="Pilihan Semester"
        hint="Pilih jenis semester standar (Ganjil atau Genap)."
        :selected="old('semester', 'ganjil')"
        :options="[
            'ganjil' => 'Semester Ganjil',
            'genap' => 'Semester Genap',
        ]"
        required
    />

    <div class="form-grid-2col">
        <x-form-input
            type="date"
            name="tanggal_mulai"
            id="create_tanggal_mulai_sem"
            label="Tanggal Mulai"
            required
        />

        <x-form-input
            type="date"
            name="tanggal_selesai"
            id="create_tanggal_selesai_sem"
            label="Tanggal Selesai"
            required
        />
    </div>

    <x-form-textarea
        name="keterangan"
        id="create_keterangan_sem"
        label="Keterangan (Opsional)"
        placeholder="Catatan mengenai pelaksanaan semester ini..."
        rows="2"
    />

    <x-form-checkbox
        name="is_active"
        id="create_is_active_sem"
        label="Jadikan sebagai Semester Aktif Sekarang"
        hint="Akan menonaktifkan semester aktif sebelumnya pada sekolah ini."
        value="1"
        :checked="(bool) old('is_active')"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateSemester">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitCreateSemester">Simpan</button>
    </x-slot:footer>
</x-modal>

{{-- ========================================================================
     MODAL UBAH SEMESTER
     ======================================================================== --}}
<x-modal
    id="modalEditSemester"
    title="Ubah Data Semester"
    subtitle="Perbarui tanggal pelaksanaan atau status semester"
    method="PUT"
    form-id="formEditSemester"
>
    <x-form-select
        name="semester"
        id="edit_sem_type"
        label="Pilihan Semester"
        :options="[
            'ganjil' => 'Semester Ganjil',
            'genap' => 'Semester Genap',
        ]"
        required
    />

    <div class="form-grid-2col">
        <x-form-input
            type="date"
            name="tanggal_mulai"
            id="edit_sem_tanggal_mulai"
            label="Tanggal Mulai"
            required
        />

        <x-form-input
            type="date"
            name="tanggal_selesai"
            id="edit_sem_tanggal_selesai"
            label="Tanggal Selesai"
            required
        />
    </div>

    <x-form-textarea
        name="keterangan"
        id="edit_sem_keterangan"
        label="Keterangan (Opsional)"
        placeholder="Catatan mengenai pelaksanaan semester ini..."
        rows="2"
    />

    <x-form-checkbox
        name="is_active"
        id="edit_sem_is_active"
        label="Jadikan sebagai Semester Aktif"
        value="1"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditSemester">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitEditSemester">Update</button>
    </x-slot:footer>
</x-modal>
