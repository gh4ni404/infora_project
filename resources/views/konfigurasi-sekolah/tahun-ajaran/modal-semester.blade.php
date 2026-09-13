<!-- Modal Tambah Semester Component -->
<div class="modal-backdrop hidden" id="modalCreateSemester" role="dialog" aria-modal="true" aria-labelledby="modalCreateSemesterTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalCreateSemesterTitle">Tambah Semester Baru</h3>
                <p class="modal-subtitle">
                    Tahun Ajaran: <strong class="text-primary">{{ $selectedTahunAjaran?->tahun ?? '-' }}</strong>
                    &bull; {{ $selectedSchool?->name ?? '-' }}
                </p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseCreateSemester" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        @if ($selectedTahunAjaran)
            <form method="POST" action="{{ route('konfigurasi-sekolah.tahun-ajaran.semester.store', $selectedTahunAjaran) }}" id="formCreateSemester">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="create_semester_type" class="form-label">Pilihan Semester <span class="text-danger">*</span></label>
                        <select id="create_semester_type" name="semester" class="form-select @error('semester') border-danger @enderror" required>
                            <option value="ganjil" {{ old('semester') === 'ganjil' ? 'selected' : '' }}>Semester Ganjil</option>
                            <option value="genap" {{ old('semester') === 'genap' ? 'selected' : '' }}>Semester Genap</option>
                        </select>
                        <div class="form-hint">Pilih jenis semester standar (Ganjil atau Genap).</div>
                        @error('semester')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-grid-2col">
                        <div class="form-group">
                            <label for="create_tanggal_mulai_sem" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input
                                type="date"
                                id="create_tanggal_mulai_sem"
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
                            <label for="create_tanggal_selesai_sem" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input
                                type="date"
                                id="create_tanggal_selesai_sem"
                                name="tanggal_selesai"
                                class="form-input @error('tanggal_selesai') border-danger @enderror"
                                value="{{ old('tanggal_selesai') }}"
                                required
                            >
                            @error('tanggal_selesai')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="create_keterangan_sem" class="form-label">Keterangan (Opsional)</label>
                        <textarea
                            id="create_keterangan_sem"
                            name="keterangan"
                            class="form-input form-textarea @error('keterangan') border-danger @enderror"
                            placeholder="Catatan mengenai pelaksanaan semester ini..."
                            rows="2"
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
                                id="create_is_active_sem"
                                {{ old('is_active') ? 'checked' : '' }}
                            >
                            <span class="form-check-label">Jadikan sebagai Semester Aktif Sekarang</span>
                        </label>
                        <div class="form-hint">Akan menonaktifkan semester aktif sebelumnya pada sekolah ini.</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btnCancelCreateSemester">Batal</button>
                    <button type="submit" class="btn-primary" id="btnSubmitCreateSemester">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <span>Simpan Semester</span>
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>

<!-- Modal Ubah Semester Component -->
<div class="modal-backdrop hidden" id="modalEditSemester" role="dialog" aria-modal="true" aria-labelledby="modalEditSemesterTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalEditSemesterTitle">Ubah Data Semester</h3>
                <p class="modal-subtitle" id="modalEditSemesterSubtitle">Perbarui tanggal pelaksanaan atau status semester</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseEditSemester" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form method="POST" action="" id="formEditSemester">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_sem_type" class="form-label">Pilihan Semester <span class="text-danger">*</span></label>
                    <select id="edit_sem_type" name="semester" class="form-select" required>
                        <option value="ganjil">Semester Ganjil</option>
                        <option value="genap">Semester Genap</option>
                    </select>
                </div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="edit_sem_tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            id="edit_sem_tanggal_mulai"
                            name="tanggal_mulai"
                            class="form-input"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="edit_sem_tanggal_selesai" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            id="edit_sem_tanggal_selesai"
                            name="tanggal_selesai"
                            class="form-input"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_sem_keterangan" class="form-label">Keterangan (Opsional)</label>
                    <textarea
                        id="edit_sem_keterangan"
                        name="keterangan"
                        class="form-input form-textarea"
                        placeholder="Catatan mengenai pelaksanaan semester ini..."
                        rows="2"
                    ></textarea>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="form-check-input"
                            id="edit_sem_is_active"
                        >
                        <span class="form-check-label">Jadikan sebagai Semester Aktif</span>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelEditSemester">Batal</button>
                <button type="submit" class="btn-primary" id="btnSubmitEditSemester">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Perbarui Semester</span>
                </button>
            </div>
        </form>
    </div>
</div>
