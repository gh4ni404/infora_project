{{-- Modal Ubah Data Tingkatan --}}
<x-modal
    id="modalEditTingkatan"
    title="Ubah Data Tingkatan"
    subtitle="Perbarui konfigurasi data tingkatan kelas sekolah"
    method="PUT"
    form-id="formEditTingkatan"
>
    <x-form-input type="hidden" name="school_id" id="edit_school_id" :value="$selectedSchoolId" />

    <div class="form-section-label">Unit Sekolah</div>
    <x-form-input
        name="school_name"
        id="edit_school_name"
        label="Unit Sekolah"
        :value="$selectedSchool?->name ?? '-'"
        hint="Unit sekolah tempat tingkatan ini terdaftar."
        disabled
    />

    <div class="form-section-label">Identitas Tingkatan</div>
    <div class="form-grid-2col">
        <x-form-input
            name="kode"
            id="edit_kode"
            label="Kode / Notasi Tingkatan"
            placeholder="Contoh: X, XI, XII"
            maxlength="20"
            hint="Kode unik pengenal tingkatan."
            required
        />

        <x-form-input
            type="number"
            name="tingkat_angka"
            id="edit_tingkat_angka"
            label="Tingkat Angka"
            placeholder="Contoh: 10, 11, 12"
            min="1"
            max="13"
            hint="Angka urutan alami tingkatan kelas (1 - 13)."
            required
        />
    </div>

    <x-form-input
        name="nama"
        id="edit_nama"
        label="Nama Tingkatan"
        placeholder="Contoh: Kelas 10 / Tingkat X"
        maxlength="100"
        hint="Nama lengkap tingkatan dalam kurikulum dan administrasi sekolah."
        required
    />

    <div class="form-section-label">Kurikulum dan Status</div>
    <div class="form-grid-2col">
        <x-form-input
            name="fase"
            id="edit_fase"
            label="Fase Kurikulum Merdeka (Opsional)"
            list="list_fase_kurikulum"
            placeholder="Pilih atau ketik fase..."
            maxlength="20"
            hint="Contoh: Fase E, Fase F (atau kosongkan untuk K13)."
        />

        <div class="pt-6">
            <x-form-checkbox
                name="is_active"
                id="edit_is_active"
                label="Status Tingkatan Aktif (Dapat digunakan pada Rombel & KBM)"
                value="1"
            />
        </div>
    </div>

    <div class="form-section-label">Catatan Tambahan</div>
    <x-form-textarea
        name="keterangan"
        id="edit_keterangan"
        label="Keterangan (Opsional)"
        placeholder="Catatan tambahan mengenai tingkatan kelas ini..."
        rows="3"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditTingkatan">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitEditTingkatan">Update</button>
    </x-slot:footer>
</x-modal>
