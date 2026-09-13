<!-- Modal Edit Kelurahan / Desa Component -->
<div class="modal-backdrop hidden" id="modalEditKelurahan" role="dialog" aria-modal="true" aria-labelledby="modalEditKelurahanTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalEditKelurahanTitle">Edit Data Kelurahan / Desa</h3>
                <p class="modal-subtitle">Perbarui data wilayah administratif kelurahan atau desa pada platform INFORA</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseEditKelurahan" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="" id="formEditKelurahan">
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
                    <div class="form-hint">Pilih provinsi induk untuk membatasi opsi kabupaten/kota.</div>
                </div>

                <div class="form-group">
                    <label for="edit_kabupaten_id" class="form-label">Kabupaten / Kota Induk <span class="text-danger">*</span></label>
                    <select
                        id="edit_kabupaten_id"
                        name="kabupaten_id"
                        class="form-input @error('kabupaten_id') border-danger @enderror"
                        required
                    >
                        <option value="">-- Pilih Kabupaten / Kota --</option>
                        @foreach ($kabupatenList as $kab)
                            <option
                                value="{{ $kab->id }}"
                                data-provinsi="{{ $kab->provinsi_id }}"
                                data-kode="{{ $kab->kode }}"
                                {{ old('kabupaten_id') == $kab->id ? 'selected' : '' }}
                            >
                                {{ $kab->kode }} - {{ $kab->tipe }} {{ $kab->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('kabupaten_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Pilih kabupaten/kota induk untuk membatasi opsi kecamatan.</div>
                </div>

                <div class="form-group">
                    <label for="edit_kecamatan_id" class="form-label">Kecamatan Induk <span class="text-danger">*</span></label>
                    <select
                        id="edit_kecamatan_id"
                        name="kecamatan_id"
                        class="form-input @error('kecamatan_id') border-danger @enderror"
                        required
                    >
                        <option value="">-- Pilih Kecamatan Induk --</option>
                        @foreach ($kecamatanList as $kec)
                            <option
                                value="{{ $kec->id }}"
                                data-kabupaten="{{ $kec->kabupaten_id }}"
                                data-kode="{{ $kec->kode }}"
                                {{ old('kecamatan_id') == $kec->id ? 'selected' : '' }}
                            >
                                {{ $kec->kode }} - {{ $kec->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('kecamatan_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Pilih kecamatan induk yang menaungi kelurahan/desa ini.</div>
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
                            <option value="Kelurahan" {{ old('tipe') === 'Kelurahan' ? 'selected' : '' }}>Kelurahan</option>
                            <option value="Desa" {{ old('tipe') === 'Desa' ? 'selected' : '' }}>Desa</option>
                        </select>
                        @error('tipe')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Tipe wilayah administratif (Kelurahan atau Desa).</div>
                    </div>

                    <div class="form-group">
                        <label for="edit_kode" class="form-label">Kode Wilayah (10 Digit) <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="edit_kode"
                            name="kode"
                            class="form-input @error('kode') border-danger @enderror"
                            placeholder="Contoh: 7308211001"
                            value="{{ old('kode') }}"
                            maxlength="10"
                            required
                        >
                        @error('kode')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Kode standar 10 digit Kemendagri.</div>
                    </div>
                </div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="edit_nama" class="form-label">Nama Kelurahan / Desa <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="edit_nama"
                            name="nama"
                            class="form-input @error('nama') border-danger @enderror"
                            data-transform="title-case"
                            placeholder="Nama kelurahan atau desa"
                            value="{{ old('nama') }}"
                            required
                        >
                        @error('nama')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Nama tanpa awalan 'Kelurahan' atau 'Desa'.</div>
                    </div>

                    <div class="form-group">
                        <label for="edit_kode_pos" class="form-label">Kode Pos (Opsional)</label>
                        <input
                            type="text"
                            id="edit_kode_pos"
                            name="kode_pos"
                            class="form-input @error('kode_pos') border-danger @enderror"
                            placeholder="Contoh: 92711"
                            value="{{ old('kode_pos') }}"
                            maxlength="5"
                        >
                        @error('kode_pos')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Kode pos 5 digit angka.</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-check-label-wrapper">
                        <input
                            type="checkbox"
                            id="edit_status"
                            name="status"
                            value="1"
                            class="form-check-input"
                            {{ old('status', '1') === '1' ? 'checked' : '' }}
                        >
                        <span class="form-check-label">Status data wilayah kelurahan/desa aktif dalam sistem</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelEditKelurahan">Batal</button>
                <button type="submit" class="btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Perbarui Data</span>
                </button>
            </div>
        </form>
    </div>
</div>
