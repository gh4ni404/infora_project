{{-- Modal Ubah Kegiatan Kalender Akademik Component --}}
<x-modal
    id="modalEditKalender"
    title="Ubah Agenda Kalender Akademik"
    subtitle="Perbarui rincian kegiatan, jadwal tanggal, semester, atau kategori"
    size="lg"
    method="PUT"
    action=""
    form-id="formEditKalender"
>
    <x-form-input type="hidden" name="view" :value="$activeView" />
    <x-form-input type="hidden" name="kalender_id" id="edit_kalender_id" />

    <div class="form-section-label">Informasi Sekolah dan Periode</div>
    <div class="form-grid-2col">
        <x-form-select
            name="school_id"
            id="edit_school_id"
            label="Unit Sekolah"
            :options="$schools->mapWithKeys(fn($school) => [$school->id => $school->name . ' (' . $school->school_type . ')'])"
            required
        />

        <div class="form-grid-2col">
            <x-form-input
                name="tahun_ajaran"
                id="edit_tahun_ajaran"
                label="Tahun Ajaran"
                placeholder="Contoh: 2026/2027"
                required
            />

            <x-form-select
                name="semester"
                id="edit_semester"
                label="Semester"
                :options="[
                    'ganjil' => 'Ganjil',
                    'genap' => 'Genap',
                ]"
                required
            />
        </div>
    </div>

    <div class="form-section-divider"></div>
    <div class="form-section-label">Detail Agenda dan Tanggal Kegiatan</div>

    <x-form-input
        name="judul_kegiatan"
        id="edit_judul_kegiatan"
        label="Nama / Judul Kegiatan"
        placeholder="Contoh: Penilaian Tengah Semester (PTS) Ganjil"
        data-transform="title-case"
        required
    />

    <div class="form-grid-2col">
        <x-form-input
            type="date"
            name="tanggal_mulai"
            id="edit_tanggal_mulai"
            label="Tanggal Mulai"
            required
        />

        <x-form-input
            type="date"
            name="tanggal_selesai"
            id="edit_tanggal_selesai"
            label="Tanggal Selesai"
            hint="Sama dengan tanggal mulai jika kegiatan berlangsung 1 hari."
            required
        />
    </div>

    <div class="form-grid-2col">
        <x-form-select
            name="kategori"
            id="edit_kategori"
            label="Kategori Kegiatan"
            required
        >
            @foreach ($kategoriOptions as $key => $opt)
                <option value="{{ $key }}" data-default-color="{{ $opt['color'] }}">
                    {{ $opt['label'] }} ({{ $opt['description'] }})
                </option>
            @endforeach
        </x-form-select>

        <x-form-select
            name="warna"
            id="edit_warna"
            label="Warna Penanda"
            hint="Warna badge dan highlight pada grid kalender."
            :options="$warnaOptions"
            required
        />
    </div>

    <x-form-checkbox
        name="libur_kbm"
        id="edit_libur_kbm"
        label="Libur KBM"
        hint="Tandai sebagai hari libur (tidak ada proses pembelajaran tatap muka)."
    />

    <x-form-textarea
        name="keterangan"
        id="edit_keterangan"
        label="Keterangan / Catatan"
        rows="3"
        placeholder="Deskripsi singkat, instruksi, atau catatan kegiatan (opsional)..."
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditKalender">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitEditKalender">
            <x-icon name="save" size="18" />
            <span>Perbarui Kegiatan</span>
        </button>
    </x-slot:footer>
</x-modal>
