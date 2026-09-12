<!-- Modal Edit Provinsi Component -->
<div class="modal-backdrop hidden" id="modalEditProvinsi" role="dialog" aria-modal="true" aria-labelledby="modalEditProvinsiTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalEditProvinsiTitle">Formulir Perubahan Data Provinsi</h3>
                <p class="modal-subtitle">Perbarui data kode dan nama wilayah provinsi dalam platform INFORA</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseEditProvinsi" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="" id="formEditProvinsi">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_kode" class="form-label">Kode Wilayah (2 Digit) <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="edit_kode"
                        name="kode"
                        class="form-input @error('kode') border-danger @enderror"
                        placeholder="Contoh: 73"
                        value="{{ old('kode') }}"
                        maxlength="2"
                        required
                    >
                    @error('kode')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Kode standar 2 digit Kemendagri / BPS (contoh: 31 untuk DKI Jakarta, 73 untuk Sulawesi Selatan).</div>
                </div>

                <div class="form-group">
                    <label for="edit_nama" class="form-label">Nama Provinsi <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="edit_nama"
                        name="nama"
                        class="form-input @error('nama') border-danger @enderror"
                        data-transform="title-case"
                        placeholder="Contoh: Sulawesi Selatan"
                        value="{{ old('nama') }}"
                        required
                    >
                    @error('nama')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Format otomatis Title Case dengan preservasi akronim resmi (DKI, DI).</div>
                </div>

                <div class="form-group">
                    <label class="form-check-label-wrapper">
                        <input
                            type="checkbox"
                            id="edit_status"
                            name="status"
                            value="1"
                            class="form-check-input"
                            {{ old('status', '1') == '1' ? 'checked' : '' }}
                        >
                        <span class="form-check-label">Provinsi aktif dan dapat digunakan pada data wilayah anak</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelEditProvinsi">Batal</button>
                <button type="submit" class="btn-primary">
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
