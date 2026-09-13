<!-- Modal Ubah Kegiatan Kalender Akademik Component -->
<div class="modal-backdrop hidden" id="modalEditKalender" role="dialog" aria-modal="true" aria-labelledby="modalEditKalenderTitle">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalEditKalenderTitle">Ubah Agenda Kalender Akademik</h3>
                <p class="modal-subtitle">Perbarui rincian kegiatan, jadwal tanggal, semester, atau kategori</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseEditKalender" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="" id="formEditKalender">
            @csrf
            @method('PUT')
            <input type="hidden" name="view" value="{{ $activeView }}">
            <input type="hidden" name="kalender_id" id="edit_kalender_id">

            <div class="modal-body">
                <div class="form-section-label">Informasi Sekolah & Periode</div>
                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="edit_school_id" class="form-label">Unit Sekolah <span class="text-danger">*</span></label>
                        <select id="edit_school_id" name="school_id" class="form-select" required>
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}">
                                    {{ $school->name }} ({{ $school->school_type }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-grid-2col">
                        <div class="form-group">
                            <label for="edit_tahun_ajaran" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="edit_tahun_ajaran"
                                name="tahun_ajaran"
                                class="form-input"
                                placeholder="Contoh: 2026/2027"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="edit_semester" class="form-label">Semester <span class="text-danger">*</span></label>
                            <select id="edit_semester" name="semester" class="form-select" required>
                                <option value="ganjil">Ganjil</option>
                                <option value="genap">Genap</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section-divider"></div>
                <div class="form-section-label">Detail Agenda & Tanggal Kegiatan</div>

                <div class="form-group">
                    <label for="edit_judul_kegiatan" class="form-label">Nama / Judul Kegiatan <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="edit_judul_kegiatan"
                        name="judul_kegiatan"
                        class="form-input"
                        data-transform="title-case"
                        placeholder="Contoh: Penilaian Tengah Semester (PTS) Ganjil"
                        required
                    >
                </div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="edit_tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            id="edit_tanggal_mulai"
                            name="tanggal_mulai"
                            class="form-input"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="edit_tanggal_selesai" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            id="edit_tanggal_selesai"
                            name="tanggal_selesai"
                            class="form-input"
                            required
                        >
                        <div class="form-hint">Sama dengan tanggal mulai jika kegiatan berlangsung 1 hari.</div>
                    </div>
                </div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="edit_kategori" class="form-label">Kategori Kegiatan <span class="text-danger">*</span></label>
                        <select id="edit_kategori" name="kategori" class="form-select" required>
                            @foreach ($kategoriOptions as $key => $opt)
                                <option value="{{ $key }}" data-default-color="{{ $opt['color'] }}">
                                    {{ $opt['label'] }} ({{ $opt['description'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_warna" class="form-label">Warna Penanda <span class="text-danger">*</span></label>
                        <select id="edit_warna" name="warna" class="form-select" required>
                            @foreach ($warnaOptions as $colorKey => $colorLabel)
                                <option value="{{ $colorKey }}">
                                    {{ $colorLabel }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-hint">Warna badge dan highlight pada grid kalender.</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="custom-control-label" for="edit_libur_kbm">
                        <input
                            type="checkbox"
                            id="edit_libur_kbm"
                            name="libur_kbm"
                            value="1"
                            class="form-checkbox"
                        >
                        <span class="custom-control-text">
                            <strong>Libur KBM</strong> &ndash; Tandai sebagai hari libur (tidak ada proses pembelajaran tatap muka)
                        </span>
                    </label>
                </div>

                <div class="form-group">
                    <label for="edit_keterangan" class="form-label">Keterangan / Catatan</label>
                    <textarea
                        id="edit_keterangan"
                        name="keterangan"
                        class="form-input"
                        rows="3"
                        placeholder="Deskripsi singkat, instruksi, atau catatan kegiatan (opsional)..."
                    ></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelEditKalender">Batal</button>
                <button type="submit" class="btn-primary" id="btnSubmitEditKalender">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Perbarui Kegiatan</span>
                </button>
            </div>
        </form>
    </div>
</div>
