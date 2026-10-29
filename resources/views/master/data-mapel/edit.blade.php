{{-- Modal Ubah Data Mata Pelajaran --}}
<x-modal
    id="modalEditMapel"
    title="Ubah Data Mata Pelajaran"
    subtitle="Perbarui konfigurasi data mata pelajaran sekolah"
    method="PUT"
    form-id="formEditMapel">
    <x-form-input type="hidden" name="school_id" id="edit_school_id" :value="$selectedSchoolId" />

    <div class="form-section-label">Unit Sekolah</div>
    <x-form-input
        name="school_name"
        id="edit_school_name"
        label="Unit Sekolah"
        :value="$selectedSchool?->name ?? '-'"
        hint="Unit sekolah tempat mata pelajaran ini terdaftar."
        disabled
    />

    <div class="form-section-label">Identitas Mata Pelajaran</div>
    <div class="form-grid-2col">
        <x-form-input
            name="kode"
            id="edit_kode"
            label="Kode Mata Pelajaran"
            placeholder="Contoh: MAT, BIND, PAI"
            maxlength="20"
            hint="Kode notasi mata pelajaran."
            required
        />

        <x-form-input
            name="singkatan"
            id="edit_singkatan"
            label="Singkatan / Alias (Opsional)"
            placeholder="Contoh: MTK, B.IND, PAI"
            maxlength="20"
            hint="Kode ringkas untuk jadwal atau rapor."
        />
    </div>

    <x-form-input
        name="nama"
        id="edit_nama"
        label="Nama Mata Pelajaran"
        placeholder="Contoh: Matematika Wajib, Bahasa Indonesia"
        maxlength="150"
        hint="Nama lengkap resmi mata pelajaran dalam kurikulum."
        required
    />

    <div class="form-section-label">Kelompok Kurikulum & Kejuruan</div>
    <div class="form-grid-2col">
        <x-form-select
            name="kelompok"
            id="edit_kelompok"
            label="Kelompok Mata Pelajaran"
            :options="[
                'Umum' => 'Umum (Muatan Nasional)',
                'Kejuruan / Peminatan' => 'Kejuruan / Peminatan',
                'Muatan Lokal' => 'Muatan Lokal',
                'Pilihan' => 'Pilihan',
            ]"
            required
            hint="Kategori penempatan mapel dalam struktur kurikulum."
        />

        <x-form-select
            name="jurusan_id"
            id="edit_jurusan_id"
            label="Konsentrasi Keahlian / Jurusan (Opsional)"
            placeholder="Semua Jurusan / Umum (Tanpa Pengecualian)"
            :options="$jurusans->mapWithKeys(fn($j) => [$j->id => ($j->singkatan ? '[' . $j->singkatan . '] ' : '') . $j->nama])"
            hint="Pilih jurusan jika mapel ini khusus diajarkan di jurusan tertentu (SMK)."
        />
    </div>

    <div class="form-section-label">Beban Jam & Status</div>
    <div class="form-grid-2col">
        <x-form-input
            type="number"
            name="beban_jam"
            id="edit_beban_jam"
            label="Beban Jam (JP per Minggu)"
            placeholder="Contoh: 2, 3, 4"
            min="1"
            max="20"
            hint="Alokasi jam pelajaran tatap muka per minggu (1 - 20 JP)."
            required
        />

        <div class="pt-6">
            <x-form-checkbox
                name="is_active"
                id="edit_is_active"
                label="Status Mata Pelajaran Aktif (Dapat dijadwalkan pada KBM)"
                value="1"
            />
        </div>
    </div>

    <div class="form-section-label">Catatan Tambahan</div>
    <x-form-textarea
        name="keterangan"
        id="edit_keterangan"
        label="Keterangan (Opsional)"
        placeholder="Catatan tambahan mengenai mata pelajaran ini..."
        rows="3"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditMapel">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitEditMapel">Update</button>
    </x-slot:footer>
</x-modal>
