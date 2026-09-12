<!-- Modal Edit Modul Component -->
<div class="modal-backdrop hidden" id="modalEditModule" role="dialog" aria-modal="true" aria-labelledby="modalEditModuleTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalEditModuleTitle">Formulir Perubahan Modul</h3>
                <p class="modal-subtitle">Perbarui data konfigurasi modul navigasi sistem INFORA</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseEditModule" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="" id="formEditModule">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_module_name" class="form-label">Nama Modul <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="edit_module_name"
                        name="name"
                        class="form-input @error('name') border-danger @enderror"
                        data-transform="uppercase"
                        placeholder="Contoh: PENGATURAN SISTEM, AKADEMIK, KESISWAAN"
                        value="{{ old('name') }}"
                        required
                    >
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Nama modul otomatis diformat kapital (UPPERCASE) dan ditampilkan sebagai pemisah kategori pada sidebar.</div>
                </div>

                <div class="form-group">
                    <label for="edit_module_order" class="form-label">Urutan Tampil (Order)</label>
                    <input
                        type="number"
                        id="edit_module_order"
                        name="order"
                        class="form-input @error('order') border-danger @enderror"
                        value="{{ old('order', 0) }}"
                        min="0"
                    >
                    @error('order')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Urutan numerik dari yang terkecil (0, 1, 2, ...) untuk penataan posisi di sidebar.</div>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input
                            type="checkbox"
                            id="edit_module_is_active"
                            name="is_active"
                            value="1"
                            class="form-check-input"
                            {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                        >
                        <span class="form-check-label">Aktifkan modul ini pada sidebar sistem</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelEditModule">Batal</button>
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
