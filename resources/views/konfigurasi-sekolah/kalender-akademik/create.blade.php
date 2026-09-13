<!-- Modal Tambah Kegiatan Kalender Akademik Component -->
<div class="modal-backdrop hidden" id="modalCreateKalender" role="dialog" aria-modal="true" aria-labelledby="modalCreateKalenderTitle">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalCreateKalenderTitle">Tambah Agenda Kalender Akademik</h3>
                <p class="modal-subtitle">Tambahkan agenda, ujian, libur, atau kegiatan sekolah ke dalam kalender akademik</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseCreateKalender" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('konfigurasi-sekolah.kalender-akademik.store') }}" id="formCreateKalender">
            @csrf
            <input type="hidden" name="view" value="{{ $activeView }}">

            <div class="modal-body">
                <div class="form-section-label">Informasi Sekolah & Periode</div>
                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="create_school_id" class="form-label">Unit Sekolah <span class="text-danger">*</span></label>
                        <select id="create_school_id" name="school_id" class="form-select @error('school_id') border-danger @enderror" required>
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}" {{ (string) old('school_id', $selectedSchoolId) === (string) $school->id ? 'selected' : '' }}>
                                    {{ $school->name }} ({{ $school->school_type }})
                                </option>
                            @endforeach
                        </select>
                        @error('school_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-grid-2col">
                        <div class="form-group">
                            <label for="create_tahun_ajaran" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="create_tahun_ajaran"
                                name="tahun_ajaran"
                                class="form-input @error('tahun_ajaran') border-danger @enderror"
                                placeholder="Contoh: 2026/2027"
                                value="{{ old('tahun_ajaran', $defaultTahunAjaran) }}"
                                required
                            >
                            @error('tahun_ajaran')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="create_semester" class="form-label">Semester <span class="text-danger">*</span></label>
                            <select id="create_semester" name="semester" class="form-select @error('semester') border-danger @enderror" required>
                                <option value="ganjil" {{ old('semester', 'ganjil') === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="genap" {{ old('semester') === 'genap' ? 'selected' : '' }}>Genap</option>
                            </select>
                            @error('semester')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-section-divider"></div>
                <div class="form-section-label">Detail Agenda & Tanggal Kegiatan</div>

                <div class="form-group">
                    <label for="create_judul_kegiatan" class="form-label">Nama / Judul Kegiatan <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="create_judul_kegiatan"
                        name="judul_kegiatan"
                        class="form-input @error('judul_kegiatan') border-danger @enderror"
                        data-transform="title-case"
                        placeholder="Contoh: Penilaian Tengah Semester (PTS) Ganjil"
                        value="{{ old('judul_kegiatan') }}"
                        required
                    >
                    @error('judul_kegiatan')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="create_tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            id="create_tanggal_mulai"
                            name="tanggal_mulai"
                            class="form-input @error('tanggal_mulai') border-danger @enderror"
                            value="{{ old('tanggal_mulai') }}"
                            required
                        >
                        @error('tanggal_mulai')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="create_tanggal_selesai" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            id="create_tanggal_selesai"
                            name="tanggal_selesai"
                            class="form-input @error('tanggal_selesai') border-danger @enderror"
                            value="{{ old('tanggal_selesai') }}"
                            required
                        >
                        @error('tanggal_selesai')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Sama dengan tanggal mulai jika kegiatan berlangsung 1 hari.</div>
                    </div>
                </div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="create_kategori" class="form-label">Kategori Kegiatan <span class="text-danger">*</span></label>
                        <select id="create_kategori" name="kategori" class="form-select @error('kategori') border-danger @enderror" required>
                            @foreach ($kategoriOptions as $key => $opt)
                                <option value="{{ $key }}" data-default-color="{{ $opt['color'] }}" {{ old('kategori', 'Kegiatan Sekolah') === $key ? 'selected' : '' }}>
                                    {{ $opt['label'] }} ({{ $opt['description'] }})
                                </option>
                            @endforeach
                        </select>
                        @error('kategori')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="create_warna" class="form-label">Warna Penanda <span class="text-danger">*</span></label>
                        <select id="create_warna" name="warna" class="form-select @error('warna') border-danger @enderror" required>
                            @foreach ($warnaOptions as $colorKey => $colorLabel)
                                <option value="{{ $colorKey }}" {{ old('warna', 'blue') === $colorKey ? 'selected' : '' }}>
                                    {{ $colorLabel }}
                                </option>
                            @endforeach
                        </select>
                        @error('warna')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Warna badge dan highlight pada grid kalender.</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="custom-control-label" for="create_libur_kbm">
                        <input
                            type="checkbox"
                            id="create_libur_kbm"
                            name="libur_kbm"
                            value="1"
                            class="form-checkbox"
                            {{ old('libur_kbm') ? 'checked' : '' }}
                        >
                        <span class="custom-control-text">
                            <strong>Libur KBM</strong> &ndash; Tandai sebagai hari libur (tidak ada proses pembelajaran tatap muka)
                        </span>
                    </label>
                </div>

                <div class="form-group">
                    <label for="create_keterangan" class="form-label">Keterangan / Catatan</label>
                    <textarea
                        id="create_keterangan"
                        name="keterangan"
                        class="form-input @error('keterangan') border-danger @enderror"
                        rows="3"
                        placeholder="Deskripsi singkat, instruksi, atau catatan kegiatan (opsional)..."
                    >{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelCreateKalender">Batal</button>
                <button type="submit" class="btn-primary" id="btnSubmitCreateKalender">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Simpan Kegiatan</span>
                </button>
            </div>
        </form>
    </div>
</div>

