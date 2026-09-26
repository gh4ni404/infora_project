{{-- ========================================================================
     MODAL TAMBAH TAHUN AJARAN
     ======================================================================== --}}
<x-modal
    id="modalCreateTahunAjaran"
    title="Tambah Tahun Ajaran Baru"
    subtitle="Tambahkan periode tahun ajaran baru untuk unit sekolah terpilih"
    :action="route('konfigurasi-sekolah.tahun-ajaran.store')"
    form-id="formCreateTahunAjaran"
>
    <x-form-input type="hidden" name="school_id" :value="$selectedSchoolId" />

    <x-form-input
        name="school_name"
        label="Unit Sekolah Terpilih"
        :value="$selectedSchool?->name ?? '-'"
        hint="Tahun ajaran akan dikaitkan pada unit sekolah ini."
        disabled
    />

    <x-form-input
        name="tahun"
        id="create_tahun"
        label="Tahun Ajaran"
        placeholder="Contoh: 2026/2027"
        hint="Gunakan format 4 digit tahun / 4 digit tahun (contoh: 2026/2027)."
        maxlength="9"
        required
    />

    <x-form-textarea
        name="keterangan"
        id="create_keterangan_ta"
        label="Keterangan (Opsional)"
        placeholder="Catatan tambahan mengenai tahun ajaran ini..."
        rows="3"
    />

    <x-form-checkbox
        name="is_active"
        id="create_is_active_ta"
        label="Jadikan sebagai Tahun Ajaran Aktif"
        hint="Mengaktifkan tahun ajaran ini akan menonaktifkan status aktif tahun ajaran lain pada sekolah ini."
        value="1"
        :checked="(bool) old('is_active')"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateTahunAjaran">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitCreateTahunAjaran">Simpan</button>
    </x-slot:footer>
</x-modal>

{{-- ========================================================================
     MODAL UBAH TAHUN AJARAN
     ======================================================================== --}}
<x-modal
    id="modalEditTahunAjaran"
    title="Ubah Data Tahun Ajaran"
    subtitle="Perbarui informasi data tahun ajaran sekolah"
    method="PUT"
    form-id="formEditTahunAjaran"
>
    <x-form-input type="hidden" name="school_id" id="edit_ta_school_id" :value="$selectedSchoolId" />

    <x-form-input
        name="school_name_edit"
        label="Unit Sekolah Terpilih"
        :value="$selectedSchool?->name ?? '-'"
        disabled
    />

    <x-form-input
        name="tahun"
        id="edit_ta_tahun"
        label="Tahun Ajaran"
        placeholder="Contoh: 2026/2027"
        hint="Format: YYYY/YYYY (contoh: 2026/2027)."
        maxlength="9"
        required
    />

    <x-form-textarea
        name="keterangan"
        id="edit_ta_keterangan"
        label="Keterangan (Opsional)"
        placeholder="Catatan tambahan mengenai tahun ajaran ini..."
        rows="3"
    />

    <x-form-checkbox
        name="is_active"
        id="edit_ta_is_active"
        label="Jadikan sebagai Tahun Ajaran Aktif"
        value="1"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditTahunAjaran">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitEditTahunAjaran">Update</button>
    </x-slot:footer>
</x-modal>
