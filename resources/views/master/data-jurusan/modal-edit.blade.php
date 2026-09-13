<!-- Modal Ubah Data Jurusan Component -->
<div class="modal-backdrop hidden" id="modalEditJurusan" role="dialog" aria-modal="true" aria-labelledby="modalEditJurusanTitle">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalEditJurusanTitle">Ubah Data Jurusan</h3>
                <p class="modal-subtitle">Perbarui informasi konsentrasi keahlian atau peminatan sekolah</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseEditJurusan" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form method="POST" action="" id="formEditJurusan">
            @csrf
            @method('PUT')
            <input type="hidden" name="school_id" id="edit_school_id" value="{{ $selectedSchoolId }}">

            <div class="modal-body">
                <div class="form-section-label">Unit Sekolah &amp; Jenjang</div>
                <div class="form-grid-2col">
                    <div class="form-group">
                        <label class="form-label">Unit Sekolah</label>
                        <input type="text" class="form-input" value="{{ $selectedSchool?->name ?? '-' }}" disabled>
                        <div class="form-hint">Unit sekolah tempat jurusan ini terdaftar.</div>
                    </div>

                    <div class="form-group">
                        <label for="edit_jenjang" class="form-label">Jenjang Pendidikan <span class="text-danger">*</span></label>
                        <select id="edit_jenjang" name="jenjang" class="form-select @error('jenjang') border-danger @enderror" required>
                            <option value="SMK">SMK (Konsentrasi Keahlian)</option>
                            <option value="SMA">SMA (Peminatan / Fase)</option>
                        </select>
                        @error('jenjang')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-section-label">Identitas Jurusan</div>
                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="edit_kode" class="form-label">Kode Jurusan <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="edit_kode"
                            name="kode"
                            class="form-input @error('kode') border-danger @enderror"
                            placeholder="Contoh: RPL, TKJ, MIPA"
                            maxlength="20"
                            required
                        >
                        <div class="form-hint">Kode unik pengenal jurusan.</div>
                        @error('kode')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_singkatan" class="form-label">Singkatan / Inisial</label>
                        <input
                            type="text"
                            id="edit_singkatan"
                            name="singkatan"
                            class="form-input @error('singkatan') border-danger @enderror"
                            placeholder="Contoh: RPL"
                            maxlength="20"
                        >
                        <div class="form-hint">Dapat dikosongkan jika sama dengan kode jurusan.</div>
                        @error('singkatan')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_nama" class="form-label">Nama Jurusan / Konsentrasi Keahlian <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="edit_nama"
                        name="nama"
                        class="form-input @error('nama') border-danger @enderror"
                        placeholder="Contoh: Rekayasa Perangkat Lunak"
                        maxlength="150"
                        required
                    >
                    @error('nama')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-section-label">Struktur Keahlian &amp; Pimpinan</div>
                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="edit_bidang_keahlian" class="form-label">Bidang Keahlian</label>
                        <input
                            type="text"
                            id="edit_bidang_keahlian"
                            name="bidang_keahlian"
                            list="list_bidang_keahlian"
                            class="form-input @error('bidang_keahlian') border-danger @enderror"
                            placeholder="Pilih atau ketik bidang keahlian..."
                            maxlength="100"
                        >
                        <div class="form-hint">Khusus SMK (contoh: Teknologi Informasi).</div>
                        @error('bidang_keahlian')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_program_keahlian" class="form-label">Program Keahlian</label>
                        <input
                            type="text"
                            id="edit_program_keahlian"
                            name="program_keahlian"
                            list="list_program_keahlian"
                            class="form-input @error('program_keahlian') border-danger @enderror"
                            placeholder="Pilih atau ketik program keahlian..."
                            maxlength="100"
                        >
                        <div class="form-hint">Khusus SMK (contoh: PPLG).</div>
                        @error('program_keahlian')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_kepala_jurusan" class="form-label">Kepala Program / Ketua Jurusan (Kaprog / Kajur)</label>
                    <input
                        type="text"
                        id="edit_kepala_jurusan"
                        name="kepala_jurusan"
                        class="form-input @error('kepala_jurusan') border-danger @enderror"
                        placeholder="Nama lengkap pimpinan jurusan (opsional)..."
                        maxlength="100"
                    >
                    @error('kepala_jurusan')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-section-label">Status &amp; Keterangan</div>
                <div class="form-group">
                    <label for="edit_deskripsi" class="form-label">Keterangan Tambahan (Opsional)</label>
                    <textarea
                        id="edit_deskripsi"
                        name="deskripsi"
                        class="form-input form-textarea @error('deskripsi') border-danger @enderror"
                        placeholder="Deskripsi singkat mengenai jurusan atau peminatan ini..."
                        rows="3"
                    ></textarea>
                    @error('deskripsi')
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
                            id="edit_is_active"
                        >
                        <span class="form-check-label">Status Jurusan Aktif (Dapat digunakan pada Rombel dan Kurikulum)</span>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelEditJurusan">Batal</button>
                <button type="submit" class="btn-primary" id="btnSubmitEditJurusan">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
