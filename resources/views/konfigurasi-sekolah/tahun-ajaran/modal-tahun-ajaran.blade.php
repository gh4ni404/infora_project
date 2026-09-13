<!-- Modal Tambah Tahun Ajaran Component -->
<div class="modal-backdrop hidden" id="modalCreateTahunAjaran" role="dialog" aria-modal="true" aria-labelledby="modalCreateTahunAjaranTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalCreateTahunAjaranTitle">Tambah Tahun Ajaran Baru</h3>
                <p class="modal-subtitle">Tambahkan periode tahun ajaran baru untuk unit sekolah terpilih</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseCreateTahunAjaran" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('konfigurasi-sekolah.tahun-ajaran.store') }}" id="formCreateTahunAjaran">
            @csrf
            <input type="hidden" name="school_id" value="{{ $selectedSchoolId }}">

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Unit Sekolah Terpilih</label>
                    <input type="text" class="form-input" value="{{ $selectedSchool?->name ?? '-' }}" disabled>
                    <div class="form-hint">Tahun ajaran akan dikaitkan pada unit sekolah ini.</div>
                </div>

                <div class="form-group">
                    <label for="create_tahun" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="create_tahun"
                        name="tahun"
                        class="form-input @error('tahun') border-danger @enderror"
                        placeholder="Contoh: 2026/2027"
                        value="{{ old('tahun') }}"
                        maxlength="9"
                        required
                    >
                    <div class="form-hint">Gunakan format 4 digit tahun / 4 digit tahun (contoh: 2026/2027).</div>
                    @error('tahun')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="create_keterangan_ta" class="form-label">Keterangan (Opsional)</label>
                    <textarea
                        id="create_keterangan_ta"
                        name="keterangan"
                        class="form-input form-textarea @error('keterangan') border-danger @enderror"
                        placeholder="Catatan tambahan mengenai tahun ajaran ini..."
                        rows="3"
                    >{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="form-check-input"
                            id="create_is_active_ta"
                            {{ old('is_active') ? 'checked' : '' }}
                        >
                        <span class="form-check-label">Jadikan sebagai Tahun Ajaran Aktif</span>
                    </label>
                    <div class="form-hint">Mengaktifkan tahun ajaran ini akan menonaktifkan status aktif tahun ajaran lain pada sekolah ini.</div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelCreateTahunAjaran">Batal</button>
                <button type="submit" class="btn-primary" id="btnSubmitCreateTahunAjaran">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Tahun Ajaran</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ubah Tahun Ajaran Component -->
<div class="modal-backdrop hidden" id="modalEditTahunAjaran" role="dialog" aria-modal="true" aria-labelledby="modalEditTahunAjaranTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalEditTahunAjaranTitle">Ubah Data Tahun Ajaran</h3>
                <p class="modal-subtitle">Perbarui informasi data tahun ajaran sekolah</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseEditTahunAjaran" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form method="POST" action="" id="formEditTahunAjaran">
            @csrf
            @method('PUT')
            <input type="hidden" name="school_id" id="edit_ta_school_id" value="{{ $selectedSchoolId }}">

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Unit Sekolah Terpilih</label>
                    <input type="text" class="form-input" value="{{ $selectedSchool?->name ?? '-' }}" disabled>
                </div>

                <div class="form-group">
                    <label for="edit_ta_tahun" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="edit_ta_tahun"
                        name="tahun"
                        class="form-input"
                        placeholder="Contoh: 2026/2027"
                        maxlength="9"
                        required
                    >
                    <div class="form-hint">Format: YYYY/YYYY (contoh: 2026/2027).</div>
                </div>

                <div class="form-group">
                    <label for="edit_ta_keterangan" class="form-label">Keterangan (Opsional)</label>
                    <textarea
                        id="edit_ta_keterangan"
                        name="keterangan"
                        class="form-input form-textarea"
                        placeholder="Catatan tambahan mengenai tahun ajaran ini..."
                        rows="3"
                    ></textarea>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="form-check-input"
                            id="edit_ta_is_active"
                        >
                        <span class="form-check-label">Jadikan sebagai Tahun Ajaran Aktif</span>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelEditTahunAjaran">Batal</button>
                <button type="submit" class="btn-primary" id="btnSubmitEditTahunAjaran">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Perbarui Tahun Ajaran</span>
                </button>
            </div>
        </form>
    </div>
</div>
