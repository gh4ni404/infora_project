{{-- Modal Tambah Data Mata Pelajaran --}}
<x-modal
    id="modalCreateMapel"
    title="Tambah Data Mata Pelajaran Baru"
    :subtitle="'Tambahkan mata pelajaran untuk ' . ($selectedSchool?->name ?? 'unit sekolah terpilih')"
    action="{{ route('master.data-mapel.store') }}"
    form-id="formCreateMapel">
    <x-form-input type="hidden" name="school_id" :value="$selectedSchoolId" />

    <div class="form-section-label">Identitas Mata Pelajaran</div>
    <div class="form-grid-2col">
        <x-form-input
            name="kode"
            id="create_kode"
            label="Kode Mata Pelajaran"
            placeholder="Contoh: MAT, BIND, PAI"
            maxlength="20"
            hint="Kode notasi mata pelajaran (otomatis kapital)."
            required
        />

        <x-form-input
            name="singkatan"
            id="create_singkatan"
            label="Singkatan / Alias (Opsional)"
            placeholder="Contoh: MTK, B.IND, PAI"
            maxlength="20"
            hint="Kode ringkas untuk jadwal atau rapor."
        />
    </div>

    <x-form-input
        name="nama"
        id="create_nama"
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
            id="create_kelompok"
            label="Kelompok Mata Pelajaran"
            :options="[
                'Umum' => 'Umum (Muatan Nasional)',
                'Kejuruan / Peminatan' => 'Kejuruan / Peminatan',
                'Muatan Lokal' => 'Muatan Lokal',
                'Pilihan' => 'Pilihan',
            ]"
            :selected="old('kelompok', 'Umum')"
            required
            hint="Kategori penempatan mapel dalam struktur kurikulum."
        />

        <x-form-select
            name="jurusan_id"
            id="create_jurusan_id"
            label="Konsentrasi Keahlian / Jurusan (Opsional)"
            placeholder="Semua Jurusan / Umum (Tanpa Pengecualian)"
            :options="$jurusans->mapWithKeys(fn($j) => [$j->id => ($j->singkatan ? '[' . $j->singkatan . '] ' : '') . $j->nama])"
            :selected="old('jurusan_id')"
            hint="Pilih jurusan jika mapel ini khusus diajarkan di jurusan tertentu (SMK)."
        />
    </div>

    <div class="form-section-label">Beban Jam & Status</div>
    <div class="form-grid-2col">
        <x-form-input
            type="number"
            name="beban_jam"
            id="create_beban_jam"
            label="Beban Jam (JP per Minggu)"
            placeholder="Contoh: 2, 3, 4"
            min="1"
            max="20"
            :value="old('beban_jam', '2')"
            hint="Alokasi jam pelajaran tatap muka per minggu (1 - 20 JP)."
            required
        />

        <div class="pt-6">
            <x-form-checkbox
                name="is_active"
                id="create_is_active"
                label="Status Mata Pelajaran Aktif (Dapat dijadwalkan pada KBM)"
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
        placeholder="Catatan tambahan mengenai mata pelajaran ini..."
        rows="3"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateMapel">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitCreateMapel">Simpan</button>
    </x-slot:footer>
</x-modal>
