<!-- Modal Edit Kabupaten Component -->
<div class="modal-backdrop hidden" id="modalEditKabupaten" role="dialog" aria-modal="true" aria-labelledby="modalEditKabupatenTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalEditKabupatenTitle">Edit Wilayah Kabupaten / Kota</h3>
                <p class="modal-subtitle">Perbarui data wilayah administratif kabupaten atau kota dalam platform INFORA</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseEditKabupaten" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="" id="formEditKabupaten">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_provinsi_id" class="form-label">Provinsi Induk <span class="text-danger">*</span></label>
                    <select
                        id="edit_provinsi_id"
                        name="provinsi_id"
                        class="form-input @error('provinsi_id') border-danger @enderror"
                        required
                    >
                        <option value="">-- Pilih Provinsi Induk --</option>
                        @foreach ($provinsiList as $prov)
                            <option
                                value="{{ $prov->id }}"
                                data-kode="{{ $prov->kode }}"
                                {{ old('provinsi_id') == $prov->id ? 'selected' : '' }}
                            >
                                {{ $prov->kode }} - {{ $prov->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('provinsi_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="edit_tipe" class="form-label">Tipe Wilayah <span class="text-danger">*</span></label>
                        <select
                            id="edit_tipe"
                            name="tipe"
                            class="form-input @error('tipe') border-danger @enderror"
                            required
                        >
                            <option value="Kabupaten">Kabupaten</option>
                            <option value="Kota">Kota</option>
                        </select>
                        @error('tipe')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_kode" class="form-label">Kode Wilayah (4 Digit) <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="edit_kode"
                            name="kode"
                            class="form-input @error('kode') border-danger @enderror"
                            placeholder="Contoh: 7371"
                            value="{{ old('kode') }}"
                            maxlength="4"
                            required
                        >
                        @error('kode')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Kode standar 4 digit Kemendagri (2 digit awal harus sesuai provinsi).</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_nama" class="form-label">Nama Kabupaten / Kota <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="edit_nama"
                        name="nama"
                        class="form-input @error('nama') border-danger @enderror"
                        data-transform="title-case"
                        placeholder="Contoh: Makassar atau Maros"
                        value="{{ old('nama') }}"
                        required
                    >
                    @error('nama')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Format Title Case otomatis tanpa perlu menyertakan kata 'Kabupaten' atau 'Kota'.</div>
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
                        <span class="form-check-label">Kabupaten/Kota aktif dan dapat digunakan pada data wilayah anak dan sekolah</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelEditKabupaten">Batal</button>
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
