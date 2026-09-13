<!-- Modal Tambah Data Jurusan Component -->
<div class="modal-backdrop hidden" id="modalCreateJurusan" role="dialog" aria-modal="true" aria-labelledby="modalCreateJurusanTitle">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalCreateJurusanTitle">Tambah Data Jurusan Baru</h3>
                <p class="modal-subtitle">Tambahkan data konsentrasi keahlian (SMK) atau peminatan (SMA) untuk unit sekolah terpilih</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseCreateJurusan" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('master.data-jurusan.store') }}" id="formCreateJurusan">
            @csrf
            <input type="hidden" name="school_id" value="{{ $selectedSchoolId }}">

            <div class="modal-body">
                <div class="form-section-label">Unit Sekolah &amp; Jenjang</div>
                <div class="form-grid-2col">
                    <div class="form-group">
                        <label class="form-label">Unit Sekolah</label>
                        <input type="text" class="form-input" value="{{ $selectedSchool?->name ?? '-' }}" disabled>
                        <div class="form-hint">Jurusan akan otomatis terhubung ke unit sekolah ini.</div>
                    </div>

                    <div class="form-group">
                        <label for="create_jenjang" class="form-label">Jenjang Pendidikan <span class="text-danger">*</span></label>
                        <select id="create_jenjang" name="jenjang" class="form-select @error('jenjang') border-danger @enderror" required>
                            <option value="SMK" {{ old('jenjang', $selectedSchool?->school_type) === 'SMK' ? 'selected' : '' }}>SMK (Konsentrasi Keahlian)</option>
                            <option value="SMA" {{ old('jenjang', $selectedSchool?->school_type) === 'SMA' ? 'selected' : '' }}>SMA (Peminatan / Fase)</option>
                        </select>
                        @error('jenjang')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-section-label">Identitas Jurusan</div>
                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="create_kode" class="form-label">Kode Jurusan <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="create_kode"
                            name="kode"
                            class="form-input @error('kode') border-danger @enderror"
                            placeholder="Contoh: RPL, TKJ, MIPA"
                            value="{{ old('kode') }}"
                            maxlength="20"
                            required
                        >
                        <div class="form-hint">Kode unik pengenal jurusan (otomatis kapital).</div>
                        @error('kode')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="create_singkatan" class="form-label">Singkatan / Inisial</label>
                        <input
                            type="text"
                            id="create_singkatan"
                            name="singkatan"
                            class="form-input @error('singkatan') border-danger @enderror"
                            placeholder="Contoh: RPL"
                            value="{{ old('singkatan') }}"
                            maxlength="20"
                        >
                        <div class="form-hint">Dapat dikosongkan jika sama dengan kode jurusan.</div>
                        @error('singkatan')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="create_nama" class="form-label">Nama Jurusan / Konsentrasi Keahlian <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="create_nama"
                        name="nama"
                        class="form-input @error('nama') border-danger @enderror"
                        placeholder="Contoh: Rekayasa Perangkat Lunak"
                        value="{{ old('nama') }}"
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
                        <label for="create_bidang_keahlian" class="form-label">Bidang Keahlian</label>
                        <input
                            type="text"
                            id="create_bidang_keahlian"
                            name="bidang_keahlian"
                            list="list_bidang_keahlian"
                            class="form-input @error('bidang_keahlian') border-danger @enderror"
                            placeholder="Pilih atau ketik bidang keahlian..."
                            value="{{ old('bidang_keahlian') }}"
                            maxlength="100"
                        >
                        <div class="form-hint">Khusus SMK (contoh: Teknologi Informasi).</div>
                        @error('bidang_keahlian')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="create_program_keahlian" class="form-label">Program Keahlian</label>
                        <input
                            type="text"
                            id="create_program_keahlian"
                            name="program_keahlian"
                            list="list_program_keahlian"
                            class="form-input @error('program_keahlian') border-danger @enderror"
                            placeholder="Pilih atau ketik program keahlian..."
                            value="{{ old('program_keahlian') }}"
                            maxlength="100"
                        >
                        <div class="form-hint">Khusus SMK (contoh: PPLG).</div>
                        @error('program_keahlian')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="create_kepala_jurusan" class="form-label">Kepala Program / Ketua Jurusan (Kaprog / Kajur)</label>
                    <input
                        type="text"
                        id="create_kepala_jurusan"
                        name="kepala_jurusan"
                        class="form-input @error('kepala_jurusan') border-danger @enderror"
                        placeholder="Nama lengkap pimpinan jurusan (opsional)..."
                        value="{{ old('kepala_jurusan') }}"
                        maxlength="100"
                    >
                    @error('kepala_jurusan')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-section-label">Status &amp; Keterangan</div>
                <div class="form-group">
                    <label for="create_deskripsi" class="form-label">Keterangan Tambahan (Opsional)</label>
                    <textarea
                        id="create_deskripsi"
                        name="deskripsi"
                        class="form-input form-textarea @error('deskripsi') border-danger @enderror"
                        placeholder="Deskripsi singkat mengenai jurusan atau peminatan ini..."
                        rows="3"
                    >{{ old('deskripsi') }}</textarea>
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
                            id="create_is_active"
                            {{ old('is_active', '1') === '1' ? 'checked' : '' }}
                        >
                        <span class="form-check-label">Status Jurusan Aktif (Dapat digunakan pada Rombel dan Kurikulum)</span>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelCreateJurusan">Batal</button>
                <button type="submit" class="btn-primary" id="btnSubmitCreateJurusan">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Jurusan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Datalist Referensi Bidang & Program Keahlian --}}
<datalist id="list_bidang_keahlian">
    @foreach ($recommendedBidang as $bidang)
        <option value="{{ $bidang }}"></option>
    @endforeach
</datalist>

<datalist id="list_program_keahlian">
    @foreach ($recommendedProgram as $prog)
        <option value="{{ $prog }}"></option>
    @endforeach
</datalist>
