<!-- Modal Tambah Sekolah Component -->
<div class="modal-backdrop hidden" id="modalCreateSchool" role="dialog" aria-modal="true" aria-labelledby="modalCreateSchoolTitle">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalCreateSchoolTitle">Tambah Sekolah Baru</h3>
                <p class="modal-subtitle">Daftarkan data sekolah SMA atau SMK ke dalam registri platform INFORA</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseCreateSchool" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('master.data-sekolah.store') }}" id="formCreateSchool">
            @csrf
            <div class="modal-body">
                <div class="form-section-label">Identitas Resmi</div>
                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="create_name" class="form-label">Nama Sekolah <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="create_name"
                            name="name"
                            class="form-input @error('name') border-danger @enderror"
                            data-transform="title-case"
                            placeholder="Contoh: SMA Negeri 1 Makassar"
                            value="{{ old('name') }}"
                            required
                        >
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="create_npsn" class="form-label">NPSN <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="create_npsn"
                            name="npsn"
                            class="form-input @error('npsn') border-danger @enderror"
                            placeholder="8 digit angka, contoh: 40312345"
                            value="{{ old('npsn') }}"
                            maxlength="8"
                            required
                        >
                        @error('npsn')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Nomor Pokok Sekolah Nasional (8 digit).</div>
                    </div>

                    <div class="form-group">
                        <label for="create_school_type" class="form-label">Jenis Sekolah <span class="text-danger">*</span></label>
                        <select id="create_school_type" name="school_type" class="form-select @error('school_type') border-danger @enderror" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="SMA" {{ old('school_type') === 'SMA' ? 'selected' : '' }}>SMA</option>
                            <option value="SMK" {{ old('school_type') === 'SMK' ? 'selected' : '' }}>SMK</option>
                        </select>
                        @error('school_type')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="create_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="create_status" name="status" class="form-select @error('status') border-danger @enderror" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="Negeri" {{ old('status') === 'Negeri' ? 'selected' : '' }}>Negeri</option>
                            <option value="Swasta" {{ old('status') === 'Swasta' ? 'selected' : '' }}>Swasta</option>
                        </select>
                        @error('status')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="create_accreditation" class="form-label">Akreditasi</label>
                        <select id="create_accreditation" name="accreditation" class="form-select @error('accreditation') border-danger @enderror">
                            <option value="">-- Belum Ditentukan --</option>
                            <option value="A" {{ old('accreditation') === 'A' ? 'selected' : '' }}>A (Unggul)</option>
                            <option value="B" {{ old('accreditation') === 'B' ? 'selected' : '' }}>B (Baik)</option>
                            <option value="C" {{ old('accreditation') === 'C' ? 'selected' : '' }}>C (Cukup)</option>
                            <option value="Belum" {{ old('accreditation') === 'Belum' ? 'selected' : '' }}>Belum Terakreditasi</option>
                        </select>
                        @error('accreditation')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="create_nss" class="form-label">NSS</label>
                        <input
                            type="text"
                            id="create_nss"
                            name="nss"
                            class="form-input @error('nss') border-danger @enderror"
                            placeholder="Nomor Statistik Sekolah (opsional)"
                            value="{{ old('nss') }}"
                            maxlength="20"
                        >
                        @error('nss')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-section-divider"></div>
                <div class="form-section-label">Alamat</div>

                <div class="form-group">
                    <label for="create_address" class="form-label">Alamat Jalan</label>
                    <textarea
                        id="create_address"
                        name="address"
                        class="form-input form-textarea @error('address') border-danger @enderror"
                        placeholder="Jalan, RT/RW, dan detail alamat lengkap"
                        rows="2"
                    >{{ old('address') }}</textarea>
                    @error('address')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="create_village" class="form-label">Kelurahan / Desa</label>
                        <input type="text" id="create_village" name="village" class="form-input" value="{{ old('village') }}" placeholder="Kelurahan/Desa">
                    </div>
                    <div class="form-group">
                        <label for="create_district" class="form-label">Kecamatan</label>
                        <input type="text" id="create_district" name="district" class="form-input" value="{{ old('district') }}" placeholder="Kecamatan">
                    </div>
                    <div class="form-group">
                        <label for="create_city" class="form-label">Kabupaten / Kota</label>
                        <input type="text" id="create_city" name="city" class="form-input" value="{{ old('city') }}" placeholder="Kabupaten/Kota">
                    </div>
                    <div class="form-group">
                        <label for="create_province" class="form-label">Provinsi</label>
                        <input type="text" id="create_province" name="province" class="form-input" value="{{ old('province') }}" placeholder="Provinsi">
                    </div>
                    <div class="form-group">
                        <label for="create_postal_code" class="form-label">Kode Pos</label>
                        <input type="text" id="create_postal_code" name="postal_code" class="form-input" value="{{ old('postal_code') }}" maxlength="10" placeholder="Kode Pos">
                    </div>
                </div>

                <div class="form-section-divider"></div>
                <div class="form-section-label">Kontak</div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="create_phone" class="form-label">Telepon</label>
                        <input type="text" id="create_phone" name="phone" class="form-input" value="{{ old('phone') }}" maxlength="20" placeholder="Nomor Telepon">
                    </div>
                    <div class="form-group">
                        <label for="create_fax" class="form-label">Fax</label>
                        <input type="text" id="create_fax" name="fax" class="form-input" value="{{ old('fax') }}" maxlength="20" placeholder="Nomor Fax">
                    </div>
                    <div class="form-group">
                        <label for="create_email" class="form-label">Email</label>
                        <input type="email" id="create_email" name="email" class="form-input @error('email') border-danger @enderror" value="{{ old('email') }}" placeholder="email@sekolah.sch.id">
                        @error('email')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="create_website" class="form-label">Website</label>
                        <input type="url" id="create_website" name="website" class="form-input @error('website') border-danger @enderror" value="{{ old('website') }}" placeholder="https://sekolah.sch.id">
                        @error('website')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-section-divider"></div>
                <div class="form-section-label">Pimpinan & Yayasan</div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="create_principal_name" class="form-label">Nama Kepala Sekolah</label>
                        <input type="text" id="create_principal_name" name="principal_name" class="form-input" value="{{ old('principal_name') }}" placeholder="Nama lengkap beserta gelar">
                    </div>
                    <div class="form-group">
                        <label for="create_principal_nip" class="form-label">NIP Kepala Sekolah</label>
                        <input type="text" id="create_principal_nip" name="principal_nip" class="form-input" value="{{ old('principal_nip') }}" maxlength="30" placeholder="Nomor Induk Pegawai">
                    </div>
                    <div class="form-group">
                        <label for="create_foundation_name" class="form-label">Nama Yayasan</label>
                        <input type="text" id="create_foundation_name" name="foundation_name" class="form-input" value="{{ old('foundation_name') }}" placeholder="Relevan untuk sekolah Swasta">
                        <div class="form-hint">Isi jika sekolah berstatus Swasta dan bernaung di bawah yayasan.</div>
                    </div>
                </div>

                <div class="form-section-divider"></div>
                <div class="form-section-label">Logo Sekolah</div>

                <div class="form-group">
                    <div class="dropzone-upload" id="createLogoUploadArea">
                        <input type="hidden" name="logo" id="create_logo_base64">
                        <input type="file" id="create_logo_file" accept="image/png,image/jpeg,image/webp,image/gif" class="hidden">
                        <div class="dropzone-upload-content" id="createLogoPlaceholder">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                                <circle cx="9" cy="9" r="2"></circle>
                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                            </svg>
                            <span>Klik untuk unggah logo sekolah</span>
                            <span class="form-hint">Format: PNG, JPG, WebP, GIF. Maks 1MB.</span>
                        </div>
                        <div class="dropzone-upload-preview hidden" id="createLogoPreview">
                            <img id="createLogoPreviewImg" alt="Preview Logo">
                            <button type="button" class="dropzone-remove-btn" id="createLogoRemoveBtn" title="Hapus Logo">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="form-check-input"
                            {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                        >
                        <span class="form-check-label">Sekolah aktif dan tampil dalam sistem</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelCreateSchool">Batal</button>
                <button type="submit" class="btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Sekolah</span>
                </button>
            </div>
        </form>
    </div>
</div>
