{{-- Modal Tambah Kegiatan Kalender Akademik Component --}}
<x-modal
    id="modalCreateKalender"
    title="Tambah Agenda Kalender Akademik"
    subtitle="Tambahkan agenda, ujian, libur, atau kegiatan sekolah ke dalam kalender akademik"
    size="lg"
    action="{{ route('konfigurasi-sekolah.kalender-akademik.store') }}"
    form-id="formCreateKalender"
>
    <x-form-input type="hidden" name="view" :value="$activeView" />

    <div class="form-section-label">Informasi Sekolah &amp; Periode</div>
    <div class="form-grid-2col">
        <x-form-select
            name="school_id"
            id="create_school_id"
            label="Unit Sekolah"
            required
        >
            @foreach ($schools as $school)
                <option value="{{ $school->id }}" {{ (string) old('school_id', $selectedSchoolId) === (string) $school->id ? 'selected' : '' }}>
                    {{ $school->name }} ({{ $school->school_type }})
                </option>
            @endforeach
        </x-form-select>

        <div class="form-grid-2col">
            <x-form-input
                name="tahun_ajaran"
                id="create_tahun_ajaran"
                label="Tahun Ajaran"
                placeholder="Contoh: 2026/2027"
                :value="$defaultTahunAjaran"
                required
            />

            <x-form-select
                name="semester"
                id="create_semester"
                label="Semester"
                required
            >
                <option value="ganjil" {{ old('semester', 'ganjil') === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                <option value="genap" {{ old('semester', 'ganjil') === 'genap' ? 'selected' : '' }}>Genap</option>
            </x-form-select>
        </div>
    </div>

    <div class="form-section-divider"></div>
    <div class="form-section-label">Detail Agenda &amp; Tanggal Kegiatan</div>

    <x-form-input
        name="judul_kegiatan"
        id="create_judul_kegiatan"
        label="Nama / Judul Kegiatan"
        placeholder="Contoh: Penilaian Tengah Semester (PTS) Ganjil"
        data-transform="title-case"
        required
    />

    <div class="form-grid-2col">
        <x-form-input
            type="date"
            name="tanggal_mulai"
            id="create_tanggal_mulai"
            label="Tanggal Mulai"
            required
        />

        <x-form-input
            type="date"
            name="tanggal_selesai"
            id="create_tanggal_selesai"
            label="Tanggal Selesai"
            hint="Sama dengan tanggal mulai jika kegiatan berlangsung 1 hari."
            required
        />
    </div>

    <div class="form-grid-2col">
        <x-form-select
            name="kategori"
            id="create_kategori"
            label="Kategori Kegiatan"
            required
        >
            @foreach ($kategoriOptions as $key => $opt)
                <option value="{{ $key }}" data-default-color="{{ $opt['color'] }}" {{ old('kategori', 'Kegiatan Sekolah') === $key ? 'selected' : '' }}>
                    {{ $opt['label'] }} ({{ $opt['description'] }})
                </option>
            @endforeach
        </x-form-select>

        <x-form-select
            name="warna"
            id="create_warna"
            label="Warna Penanda"
            hint="Warna badge dan highlight pada grid kalender."
            required
        >
            @foreach ($warnaOptions as $colorKey => $colorLabel)
                <option value="{{ $colorKey }}" {{ old('warna', 'blue') === $colorKey ? 'selected' : '' }}>
                    {{ $colorLabel }}
                </option>
            @endforeach
        </x-form-select>
    </div>

    <x-form-checkbox
        name="libur_kbm"
        id="create_libur_kbm"
        label="Libur KBM"
        hint="Tandai sebagai hari libur (tidak ada proses pembelajaran tatap muka)."
        :checked="(bool) old('libur_kbm')"
    />

    <x-form-textarea
        name="keterangan"
        id="create_keterangan"
        label="Keterangan / Catatan"
        rows="3"
        placeholder="Deskripsi singkat, instruksi, atau catatan kegiatan (opsional)..."
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateKalender">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitCreateKalender">
            <x-icon name="save" size="18" />
            <span>Simpan Kegiatan</span>
        </button>
    </x-slot:footer>
</x-modal>
