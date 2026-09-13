<!-- Modal Tambah Kecamatan Component -->
<div class="modal-backdrop hidden" id="modalCreateKecamatan" role="dialog" aria-modal="true" aria-labelledby="modalCreateKecamatanTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalCreateKecamatanTitle">Tambah Kecamatan Baru</h3>
                <p class="modal-subtitle">Daftarkan data wilayah administratif kecamatan baru ke dalam platform INFORA</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseCreateKecamatan" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('wilayah.kecamatan.store') }}" id="formCreateKecamatan">
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
                    <div class="form-hint">Pilih provinsi induk untuk menyaring daftar pilihan kabupaten/kota secara otomatis.</div>
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
                    <div class="form-hint">Pilih kabupaten/kota induk untuk menentukan awalan 4 digit kode kecamatan.</div>
                </div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="create_kode" class="form-label">Kode Kecamatan (6 Digit) <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="create_kode"
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
                        <div class="form-hint">Kode standar 6 digit Kemendagri (4 digit awal harus sesuai kabupaten terpilih).</div>
                    </div>

                    <div class="form-group">
                        <label for="create_nama" class="form-label">Nama Kecamatan <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="create_nama"
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
                        <div class="form-hint">Masukkan nama wilayah kecamatan (sistem otomatis Title Case).</div>
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
                        <span class="form-check-label">Aktifkan data wilayah kecamatan ini dalam sistem</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelCreateKecamatan">Batal</button>
                <button type="submit" class="btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Kecamatan</span>
                </button>
            </div>
        </form>
    </div>
</div>
