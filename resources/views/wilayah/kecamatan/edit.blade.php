<!-- Modal Edit Kecamatan Component -->
<div class="modal-backdrop hidden" id="modalEditKecamatan" role="dialog" aria-modal="true" aria-labelledby="modalEditKecamatanTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalEditKecamatanTitle">Edit Data Kecamatan</h3>
                <p class="modal-subtitle">Perbarui data wilayah administratif kecamatan pada platform INFORA</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseEditKecamatan" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="" id="formEditKecamatan">
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
                    <div class="form-hint">Pilih kabupaten/kota induk yang menaungi kecamatan ini.</div>
                </div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="edit_kode" class="form-label">Kode Kecamatan (6 Digit) <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="edit_kode"
                            name="kode"
                            class="form-input @error('kode') border-danger @enderror"
                            placeholder="Contoh: 730801"
                            value="{{ old('kode') }}"
                            maxlength="6"
                            required
                        >
                        @error('kode')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Kode standar 6 digit Kemendagri.</div>
                    </div>

                    <div class="form-group">
                        <label for="edit_nama" class="form-label">Nama Kecamatan <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="edit_nama"
                            name="nama"
                            class="form-input @error('nama') border-danger @enderror"
                            data-transform="title-case"
                            placeholder="Contoh: Tanete Riattang"
                            value="{{ old('nama') }}"
                            required
                        >
                        @error('nama')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Nama kecamatan tanpa awalan kata 'Kecamatan'.</div>
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
                        <span class="form-check-label">Status data wilayah kecamatan aktif dalam sistem</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelEditKecamatan">Batal</button>
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
