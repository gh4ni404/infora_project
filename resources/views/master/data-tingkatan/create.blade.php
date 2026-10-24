{{-- Modal Tambah Data Tingkatan --}}
<x-modal
    id="modalCreateTingkatan"
    title="Tambah Data Tingkatan Baru"
    :subtitle="'Tambahkan tingkatan kelas untuk ' . ($selectedSchool?->name ?? 'unit sekolah terpilih')"
    action="{{ route('master.data-tingkatan.store') }}"
    form-id="formCreateTingkatan">
    <x-form-input type="hidden" name="school_id" :value="$selectedSchoolId" />

    <div class="form-section-label">Identitas Tingkatan</div>
    <div class="form-grid-2col">
        <x-form-input
            name="kode"
            id="create_kode"
            label="Kode / Notasi Tingkatan"
            placeholder="Contoh: X, XI, XII"
            maxlength="20"
            hint="Kode pengenal tingkatan (otomatis kapital)."
            required
        />

        <x-form-input
            type="number"
            name="tingkat_angka"
            id="create_tingkat_angka"
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
        id="create_nama"
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
            id="create_fase"
            label="Fase Kurikulum Merdeka (Opsional)"
            list="list_fase_kurikulum"
            placeholder="Pilih atau ketik fase..."
            maxlength="20"
            hint="Contoh: Fase E, Fase F (atau kosongkan untuk K13)."
        />

        <div class="pt-6">
            <x-form-checkbox
                name="is_active"
                id="create_is_active"
                label="Status Tingkatan Aktif (Dapat digunakan pada Rombel & KBM)"
                value="1"
                :checked="old('is_active', '1') === '1'"
            />
        </div>
    </div>

    <div class="form-section-label">Catatan Tambahan</div>
    <x-form-textarea
        name="keterangan"
        id="create_keterangan"
        label="Keterangan (Opsional)"
        placeholder="Catatan tambahan mengenai tingkatan kelas ini..."
        rows="3"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateTingkatan">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitCreateTingkatan">Simpan</button>
    </x-slot:footer>
</x-modal>

{{-- Datalist Referensi Fase Kurikulum Merdeka --}}
<datalist id="list_fase_kurikulum">
    <option value="Fase A">Fase A (SD Kelas 1-2)</option>
    <option value="Fase B">Fase B (SD Kelas 3-4)</option>
    <option value="Fase C">Fase C (SD Kelas 5-6)</option>
    <option value="Fase D">Fase D (SMP Kelas 7-9)</option>
    <option value="Fase E">Fase E (SMA/SMK Kelas 10)</option>
    <option value="Fase F">Fase F (SMA/SMK Kelas 11-12)</option>
</datalist>
