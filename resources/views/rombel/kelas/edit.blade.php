{{-- Modal Ubah Data Rombongan Belajar (Kelas) --}}
<x-modal
    id="modalEditKelas"
    title="Ubah Data Rombongan Belajar (Kelas)"
    subtitle="Perbarui konfigurasi data kelas, wali kelas, atau kapasitas siswa"
    method="PUT"
    form-id="formEditKelas">
    <x-form-input type="hidden" name="school_id" id="edit_school_id" :value="$selectedSchoolId" />

    <div class="form-section-label">Tahun Ajaran & Penjenjangan</div>
    <div class="form-grid-2col">
        <x-form-select
            name="tahun_ajaran_id"
            id="edit_tahun_ajaran_id"
            label="Tahun Ajaran"
            :options="$tahunAjarans->mapWithKeys(fn($ta) => [$ta->id => $ta->tahun . ($ta->is_active ? ' (Aktif)' : '')])"
            required
            hint="Tahun ajaran operasional rombongan belajar ini."
        />

        <x-form-select
            name="tingkatan_id"
            id="edit_tingkatan_id"
            label="Tingkatan / Jenjang"
            placeholder="-- Pilih Tingkatan Kelas --"
            :options="$tingkatans->mapWithKeys(fn($t) => [$t->id => $t->nama . ($t->fase ? ' (' . $t->fase . ')' : '')])"
            required
            hint="Jenjang kelas (misal: Kelas X, XI, XII)."
        />
    </div>

    <x-form-select
        name="jurusan_id"
        id="edit_jurusan_id"
        label="Konsentrasi Keahlian / Jurusan (Opsional)"
        placeholder="Semua Jurusan / Umum (Tanpa Pengecualian)"
        :options="$jurusans->mapWithKeys(fn($j) => [$j->id => ($j->singkatan ? '[' . $j->singkatan . '] ' : '') . $j->nama])"
        hint="Pilih program keahlian jika kelas ini khusus untuk SMK/Peminatan."
    />

    <div class="form-section-label">Identitas & Penamaan Kelas</div>
    <div class="form-grid-2col">
        <x-form-input
            name="nama"
            id="edit_nama"
            label="Nama Rombel / Kelas"
            placeholder="Contoh: X PPLG 1, XI TKJ 2"
            maxlength="100"
            hint="Nama resmi rombongan belajar (otomatis Title Case)."
            required
        />

        <x-form-input
            name="kode"
            id="edit_kode"
            label="Kode Singkatan Kelas (Opsional)"
            placeholder="Contoh: 10-PPLG-1, 11-TKJ-2"
            maxlength="50"
            hint="Kode notasi ringkas jadwal/rapor (otomatis kapital)."
        />
    </div>

    <div class="form-section-label">Wali Kelas, Fasilitas & Kapasitas</div>
    <x-form-select
        name="wali_kelas_id"
        id="edit_wali_kelas_id"
        label="Wali Kelas (GTK Pembina)"
        placeholder="-- Pilih Wali Kelas (Belum Ditugaskan) --"
        :options="$gtks->mapWithKeys(function($gtk) use ($assignedWaliMap) {
            $fullName = trim(($gtk->gelar_depan ? $gtk->gelar_depan . ' ' : '') . $gtk->nama_lengkap . ($gtk->gelar_belakang ? ', ' . $gtk->gelar_belakang : ''));
            $nipInfo = $gtk->nip ? ' [NIP: ' . $gtk->nip . ']' : '';
            $assignedInfo = isset($assignedWaliMap[$gtk->id]) ? ' (⚠️ Wali di ' . $assignedWaliMap[$gtk->id] . ')' : '';
            return [$gtk->id => $fullName . $nipInfo . $assignedInfo];
        })"
        hint="Pilih guru pembina kelas. Tanda ⚠️ mengindikasikan guru telah memiliki tugas wali kelas pada tahun ajaran ini."
    />

    <div class="form-grid-2col">
        <x-form-input
            name="ruangan"
            id="edit_ruangan"
            label="Ruangan Belajar (Opsional)"
            placeholder="Contoh: Ruang Teori 101, Lab RPL"
            maxlength="100"
            hint="Nama atau kode ruangan fisik tempat KBM berlangsung."
        />

        <x-form-input
            type="number"
            name="kapasitas"
            id="edit_kapasitas"
            label="Kapasitas Siswa (Daya Tampung)"
            placeholder="Contoh: 36"
            min="1"
            max="100"
            hint="Jumlah maksimum kursi siswa per rombongan belajar."
            required
        />
    </div>

    <div class="pt-2">
        <x-form-checkbox
            name="is_active"
            id="edit_is_active"
            label="Status Rombel Aktif (Dapat digunakan untuk penempatan siswa dan jadwal KBM)"
            value="1"
        />
    </div>

    <div class="form-section-label">Catatan Tambahan</div>
    <x-form-textarea
        name="keterangan"
        id="edit_keterangan"
        label="Keterangan (Opsional)"
        placeholder="Catatan tambahan mengenai rombongan belajar ini..."
        rows="3"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditKelas">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitEditKelas">Perbarui Kelas</button>
    </x-slot:footer>
</x-modal>
