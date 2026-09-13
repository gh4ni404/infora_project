<!-- Modal Edit Sekolah Component -->
<div class="modal-backdrop hidden" id="modalEditSchool" role="dialog" aria-modal="true" aria-labelledby="modalEditSchoolTitle">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalEditSchoolTitle">Formulir Perubahan Data Sekolah</h3>
                <p class="modal-subtitle">Perbarui informasi profil dan identitas sekolah dalam platform INFORA</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseEditSchool" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="" id="formEditSchool">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-section-label">Identitas Resmi</div>
                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="edit_name" class="form-label">Nama Sekolah <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="edit_name"
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
                        <label for="edit_npsn" class="form-label">NPSN <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="edit_npsn"
                            name="npsn"
                            class="form-input @error('npsn') border-danger @enderror"
                            placeholder="8 digit angka"
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
                        <label for="edit_school_type" class="form-label">Jenis Sekolah <span class="text-danger">*</span></label>
                        <select id="edit_school_type" name="school_type" class="form-select @error('school_type') border-danger @enderror" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="SMA" {{ old('school_type') === 'SMA' ? 'selected' : '' }}>SMA</option>
                            <option value="SMK" {{ old('school_type') === 'SMK' ? 'selected' : '' }}>SMK</option>
                        </select>
                        @error('school_type')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="edit_status" name="status" class="form-select @error('status') border-danger @enderror" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="Negeri" {{ old('status') === 'Negeri' ? 'selected' : '' }}>Negeri</option>
                            <option value="Swasta" {{ old('status') === 'Swasta' ? 'selected' : '' }}>Swasta</option>
                        </select>
                        @error('status')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_accreditation" class="form-label">Akreditasi</label>
                        <select id="edit_accreditation" name="accreditation" class="form-select @error('accreditation') border-danger @enderror">
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
                        <label for="edit_nss" class="form-label">NSS</label>
                        <input
                            type="text"
                            id="edit_nss"
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
                <div class="form-section-label">Wilayah & Alamat</div>

                <!-- Hidden inputs untuk sinkronisasi nama teks wilayah demi kompatibilitas cadangan -->
                <input type="hidden" name="province" id="edit_province" value="{{ old('province') }}">
                <input type="hidden" name="city" id="edit_city" value="{{ old('city') }}">
                <input type="hidden" name="district" id="edit_district" value="{{ old('district') }}">
                <input type="hidden" name="village" id="edit_village" value="{{ old('village') }}">

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="edit_provinsi_id" class="form-label">Provinsi</label>
                        <x-searchable-select
                            name="provinsi_id"
                            id="edit_provinsi_id"
                            placeholder="-- Pilih Provinsi --"
                            search-placeholder="Cari provinsi..."
                            :options="$provinsiList ?? []"
                            :value="old('provinsi_id')"
                        />
                        @error('provinsi_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_kabupaten_id" class="form-label">Kabupaten / Kota</label>
                        <x-searchable-select
                            name="kabupaten_id"
                            id="edit_kabupaten_id"
                            placeholder="-- Pilih Kabupaten/Kota --"
                            search-placeholder="Cari kabupaten/kota..."
                            :disabled="!old('provinsi_id')"
                            :value="old('kabupaten_id')"
                        />
                        @error('kabupaten_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_kecamatan_id" class="form-label">Kecamatan</label>
                        <x-searchable-select
                            name="kecamatan_id"
                            id="edit_kecamatan_id"
                            placeholder="-- Pilih Kecamatan --"
                            search-placeholder="Cari kecamatan..."
                            :disabled="!old('kabupaten_id')"
                            :value="old('kecamatan_id')"
                        />
                        @error('kecamatan_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit_kelurahan_id" class="form-label">Kelurahan / Desa</label>
                        <x-searchable-select
                            name="kelurahan_id"
                            id="edit_kelurahan_id"
                            placeholder="-- Pilih Kelurahan/Desa --"
                            search-placeholder="Cari kelurahan/desa..."
                            :disabled="!old('kecamatan_id')"
                            :value="old('kelurahan_id')"
                        />
                        @error('kelurahan_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-grid-2col mt-4">
                    <div class="form-group">
                        <label for="edit_postal_code" class="form-label">Kode Pos</label>
                        <input
                            type="text"
                            id="edit_postal_code"
                            name="postal_code"
                            class="form-input @error('postal_code') border-danger @enderror"
                            value="{{ old('postal_code') }}"
                            maxlength="10"
                            placeholder="Kode Pos (otomatis terisi saat memilih kelurahan)"
                        >
                        @error('postal_code')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group mt-4">
                    <label for="edit_address" class="form-label">Alamat Jalan / Lengkap</label>
                    <textarea
                        id="edit_address"
                        name="address"
                        class="form-input form-textarea @error('address') border-danger @enderror"
                        placeholder="Jalan, RT/RW, dan detail alamat lengkap sekolah"
                        rows="2"
                    >{{ old('address') }}</textarea>
                    @error('address')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-section-divider"></div>
                <div class="form-section-label">Kontak</div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="edit_phone" class="form-label">Telepon</label>
                        <input type="text" id="edit_phone" name="phone" class="form-input" value="{{ old('phone') }}" maxlength="20" placeholder="Nomor Telepon">
                    </div>
                    <div class="form-group">
                        <label for="edit_fax" class="form-label">Fax</label>
                        <input type="text" id="edit_fax" name="fax" class="form-input" value="{{ old('fax') }}" maxlength="20" placeholder="Nomor Fax">
                    </div>
                    <div class="form-group">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" id="edit_email" name="email" class="form-input @error('email') border-danger @enderror" value="{{ old('email') }}" placeholder="email@sekolah.sch.id">
                        @error('email')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="edit_website" class="form-label">Website</label>
                        <input type="url" id="edit_website" name="website" class="form-input @error('website') border-danger @enderror" value="{{ old('website') }}" placeholder="https://sekolah.sch.id">
                        @error('website')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-section-divider"></div>
                <div class="form-section-label">Pimpinan & Yayasan</div>

                <div class="form-grid-2col">
                    <div class="form-group">
                        <label for="edit_principal_name" class="form-label">Nama Kepala Sekolah</label>
                        <input type="text" id="edit_principal_name" name="principal_name" class="form-input" value="{{ old('principal_name') }}" placeholder="Nama lengkap beserta gelar">
                    </div>
                    <div class="form-group">
                        <label for="edit_principal_nip" class="form-label">NIP Kepala Sekolah</label>
                        <input type="text" id="edit_principal_nip" name="principal_nip" class="form-input" value="{{ old('principal_nip') }}" maxlength="30" placeholder="Nomor Induk Pegawai">
                    </div>
                    <div class="form-group">
                        <label for="edit_foundation_name" class="form-label">Nama Yayasan</label>
                        <input type="text" id="edit_foundation_name" name="foundation_name" class="form-input" value="{{ old('foundation_name') }}" placeholder="Relevan untuk sekolah Swasta">
                        <div class="form-hint">Isi jika sekolah berstatus Swasta dan bernaung di bawah yayasan.</div>
                    </div>
                </div>

                <div class="form-section-divider"></div>
                <div class="form-section-label">Logo Sekolah</div>

                <div class="form-group">
                    <div class="dropzone-upload" id="editLogoUploadArea">
                        <input type="hidden" name="logo" id="edit_logo_base64">
                        <input type="hidden" name="remove_logo" id="edit_remove_logo" value="0">
                        <input type="file" id="edit_logo_file" accept="image/png,image/jpeg,image/webp,image/gif" class="hidden">

                        <div class="dropzone-upload-content" id="editLogoPlaceholder">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                                <circle cx="9" cy="9" r="2"></circle>
                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                            </svg>
                            <span>Klik untuk unggah logo baru</span>
                            <span class="form-hint">Format: PNG, JPG, WebP, GIF. Maks 1MB.</span>
                        </div>

                        <div class="dropzone-upload-preview hidden" id="editLogoPreview">
                            <img id="editLogoPreviewImg" src="" alt="Preview Logo">
                            <button type="button" class="dropzone-remove-btn" id="editLogoRemoveBtn" title="Hapus Logo">
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
                            id="edit_is_active"
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
                <button type="button" class="btn-secondary" id="btnCancelEditSchool">Batal</button>
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
