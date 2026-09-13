<!-- Modal Tambah Kelurahan / Desa Component -->
<div class="modal-backdrop hidden" id="modalCreateKelurahan" role="dialog" aria-modal="true" aria-labelledby="modalCreateKelurahanTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalCreateKelurahanTitle">Tambah Kelurahan / Desa Baru</h3>
                <p class="modal-subtitle">Daftarkan data wilayah administratif kelurahan atau desa baru ke dalam platform INFORA</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseCreateKelurahan" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('wilayah.kelurahan.store') }}" id="formCreateKelurahan">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_provinsi_id" class="form-label">Provinsi Induk <span class="text-danger">*</span></label>
                    <x-searchable-select
                        name="provinsi_id"
                        id="create_provinsi_id"
                        placeholder="-- Pilih Provinsi Induk --"
                        search-placeholder="Cari provinsi..."
                        :options="$provinsiList"
                        :value="old('provinsi_id')"
                        required
                    />
                    @error('provinsi_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Pilih provinsi induk untuk menyaring pilihan kabupaten/kota secara otomatis.</div>
                </div>

                <div class="form-group">
                    <label for="create_kabupaten_id" class="form-label">Kabupaten / Kota Induk <span class="text-danger">*</span></label>
                    <x-searchable-select
                        name="kabupaten_id"
                        id="create_kabupaten_id"
                        placeholder="-- Pilih Kabupaten / Kota --"
                        search-placeholder="Cari kabupaten/kota..."
                        :disabled="!old('provinsi_id')"
                        :value="old('kabupaten_id')"
                        required
                    />
                    @error('kabupaten_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Pilih kabupaten/kota untuk menyaring pilihan kecamatan induk.</div>
                </div>

                <div class="form-group">
                    <label for="create_kecamatan_id" class="form-label">Kecamatan Induk <span class="text-danger">*</span></label>
                    <x-searchable-select
                        name="kecamatan_id"
                        id="create_kecamatan_id"
                        placeholder="-- Pilih Kecamatan Induk --"
                        search-placeholder="Cari kecamatan..."
                        :disabled="!old('kabupaten_id')"
                        :value="old('kecamatan_id')"
                        required
                    />
                    @error('kecamatan_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Pilih kecamatan induk untuk menentukan awalan 6 digit kode kelurahan/desa.</div>
                </div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="create_tipe" class="form-label">Tipe Wilayah <span class="text-danger">*</span></label>
                        <select
                            id="create_tipe"
                            name="tipe"
                            class="form-input @error('tipe') border-danger @enderror"
                            required
                        >
                            <option value="Kelurahan" {{ old('tipe', 'Kelurahan') === 'Kelurahan' ? 'selected' : '' }}>Kelurahan</option>
                            <option value="Desa" {{ old('tipe') === 'Desa' ? 'selected' : '' }}>Desa</option>
                        </select>
                        @error('tipe')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Pilih klasifikasi wilayah administratif (Kelurahan atau Desa).</div>
                    </div>

                    <div class="form-group">
                        <label for="create_kode" class="form-label">Kode Wilayah (10 Digit) <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="create_kode"
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
                        <div class="form-hint">Kode standar 10 digit Kemendagri (6 digit awal harus sesuai kecamatan terpilih).</div>
                    </div>
                </div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="create_nama" class="form-label">Nama Kelurahan / Desa <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="create_nama"
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
                        <div class="form-hint">Nama entitas tanpa kata 'Kelurahan' atau 'Desa' (otomatis Title Case).</div>
                    </div>

                    <div class="form-group">
                        <label for="create_kode_pos" class="form-label">Kode Pos (Opsional)</label>
                        <input
                            type="text"
                            id="create_kode_pos"
                            name="kode_pos"
                            class="form-input @error('kode_pos') border-danger @enderror"
                            placeholder="Contoh: 92711"
                            value="{{ old('kode_pos') }}"
                            maxlength="5"
                        >
                        @error('kode_pos')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Kode pos 5 digit wilayah untuk sinkronisasi alamat profil sekolah.</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-check-label-wrapper">
                        <input
                            type="checkbox"
                            id="create_status"
                            name="status"
                            value="1"
                            class="form-check-input"
                            {{ old('status', '1') === '1' ? 'checked' : '' }}
                        >
                        <span class="form-check-label">Aktifkan data wilayah kelurahan/desa ini dalam sistem</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelCreateKelurahan">Batal</button>
                <button type="submit" class="btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Kelurahan / Desa</span>
                </button>
            </div>
        </form>
    </div>
</div>
