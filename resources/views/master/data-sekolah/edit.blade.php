@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Edit Sekolah: {{ $school->name }}</h2>
        <div class="page-subtitle">Perbarui informasi profil dan identitas sekolah</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('master.data-sekolah.index') }}" class="btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <span>Kembali ke Daftar</span>
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert-danger">
        <div class="alert-content">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    </div>
@endif

<div class="card-surface">
    <div class="card-header">
        <span class="user-name-text">Formulir Perubahan Data Sekolah</span>
        <div class="page-actions-group">
            <span class="badge badge-cyan">ID #{{ $school->id }}</span>
            @if ($school->school_type === 'SMA')
                <span class="badge badge-purple">SMA</span>
            @else
                <span class="badge badge-cyan">SMK</span>
            @endif
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('master.data-sekolah.update', $school) }}" id="formEditSchool">
            @csrf
            @method('PUT')

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
                        value="{{ old('name', $school->name) }}"
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
                        value="{{ old('npsn', $school->npsn) }}"
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
                        <option value="SMA" {{ old('school_type', $school->school_type) === 'SMA' ? 'selected' : '' }}>SMA</option>
                        <option value="SMK" {{ old('school_type', $school->school_type) === 'SMK' ? 'selected' : '' }}>SMK</option>
                    </select>
                    @error('school_type')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select id="edit_status" name="status" class="form-select @error('status') border-danger @enderror" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="Negeri" {{ old('status', $school->status) === 'Negeri' ? 'selected' : '' }}>Negeri</option>
                        <option value="Swasta" {{ old('status', $school->status) === 'Swasta' ? 'selected' : '' }}>Swasta</option>
                    </select>
                    @error('status')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="edit_accreditation" class="form-label">Akreditasi</label>
                    <select id="edit_accreditation" name="accreditation" class="form-select @error('accreditation') border-danger @enderror">
                        <option value="">-- Belum Ditentukan --</option>
                        <option value="A" {{ old('accreditation', $school->accreditation) === 'A' ? 'selected' : '' }}>A (Unggul)</option>
                        <option value="B" {{ old('accreditation', $school->accreditation) === 'B' ? 'selected' : '' }}>B (Baik)</option>
                        <option value="C" {{ old('accreditation', $school->accreditation) === 'C' ? 'selected' : '' }}>C (Cukup)</option>
                        <option value="Belum" {{ old('accreditation', $school->accreditation) === 'Belum' ? 'selected' : '' }}>Belum Terakreditasi</option>
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
                        value="{{ old('nss', $school->nss) }}"
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
                <label for="edit_address" class="form-label">Alamat Jalan</label>
                <textarea
                    id="edit_address"
                    name="address"
                    class="form-input form-textarea @error('address') border-danger @enderror"
                    placeholder="Jalan, RT/RW, dan detail alamat lengkap"
                    rows="2"
                >{{ old('address', $school->address) }}</textarea>
                @error('address')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-grid-2col">
                <div class="form-group">
                    <label for="edit_village" class="form-label">Kelurahan / Desa</label>
                    <input type="text" id="edit_village" name="village" class="form-input" value="{{ old('village', $school->village) }}" placeholder="Kelurahan/Desa">
                </div>
                <div class="form-group">
                    <label for="edit_district" class="form-label">Kecamatan</label>
                    <input type="text" id="edit_district" name="district" class="form-input" value="{{ old('district', $school->district) }}" placeholder="Kecamatan">
                </div>
                <div class="form-group">
                    <label for="edit_city" class="form-label">Kabupaten / Kota</label>
                    <input type="text" id="edit_city" name="city" class="form-input" value="{{ old('city', $school->city) }}" placeholder="Kabupaten/Kota">
                </div>
                <div class="form-group">
                    <label for="edit_province" class="form-label">Provinsi</label>
                    <input type="text" id="edit_province" name="province" class="form-input" value="{{ old('province', $school->province) }}" placeholder="Provinsi">
                </div>
                <div class="form-group">
                    <label for="edit_postal_code" class="form-label">Kode Pos</label>
                    <input type="text" id="edit_postal_code" name="postal_code" class="form-input" value="{{ old('postal_code', $school->postal_code) }}" maxlength="10" placeholder="Kode Pos">
                </div>
            </div>

            <div class="form-section-divider"></div>
            <div class="form-section-label">Kontak</div>

            <div class="form-grid-2col">
                <div class="form-group">
                    <label for="edit_phone" class="form-label">Telepon</label>
                    <input type="text" id="edit_phone" name="phone" class="form-input" value="{{ old('phone', $school->phone) }}" maxlength="20" placeholder="Nomor Telepon">
                </div>
                <div class="form-group">
                    <label for="edit_fax" class="form-label">Fax</label>
                    <input type="text" id="edit_fax" name="fax" class="form-input" value="{{ old('fax', $school->fax) }}" maxlength="20" placeholder="Nomor Fax">
                </div>
                <div class="form-group">
                    <label for="edit_email" class="form-label">Email</label>
                    <input type="email" id="edit_email" name="email" class="form-input @error('email') border-danger @enderror" value="{{ old('email', $school->email) }}" placeholder="email@sekolah.sch.id">
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="edit_website" class="form-label">Website</label>
                    <input type="url" id="edit_website" name="website" class="form-input @error('website') border-danger @enderror" value="{{ old('website', $school->website) }}" placeholder="https://sekolah.sch.id">
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
                    <input type="text" id="edit_principal_name" name="principal_name" class="form-input" value="{{ old('principal_name', $school->principal_name) }}" placeholder="Nama lengkap beserta gelar">
                </div>
                <div class="form-group">
                    <label for="edit_principal_nip" class="form-label">NIP Kepala Sekolah</label>
                    <input type="text" id="edit_principal_nip" name="principal_nip" class="form-input" value="{{ old('principal_nip', $school->principal_nip) }}" maxlength="30" placeholder="Nomor Induk Pegawai">
                </div>
                <div class="form-group">
                    <label for="edit_foundation_name" class="form-label">Nama Yayasan</label>
                    <input type="text" id="edit_foundation_name" name="foundation_name" class="form-input" value="{{ old('foundation_name', $school->foundation_name) }}" placeholder="Relevan untuk sekolah Swasta">
                    <div class="form-hint">Isi jika sekolah berstatus Swasta dan bernaung di bawah yayasan.</div>
                </div>
            </div>

            <div class="form-section-divider"></div>
            <div class="form-section-label">Logo Sekolah</div>

            <div class="form-group">
                <div class="school-logo-upload" id="editLogoUploadArea">
                    <input type="hidden" name="logo" id="edit_logo_base64">
                    <input type="hidden" name="remove_logo" id="edit_remove_logo" value="0">
                    <input type="file" id="edit_logo_file" accept="image/png,image/jpeg,image/webp,image/gif" class="hidden">

                    @if ($school->logo_path)
                        <div class="school-logo-upload-content hidden" id="editLogoPlaceholder">
                    @else
                        <div class="school-logo-upload-content" id="editLogoPlaceholder">
                    @endif
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                            <circle cx="9" cy="9" r="2"></circle>
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                        </svg>
                        <span>Klik untuk unggah logo sekolah</span>
                        <span class="form-hint">Format: PNG, JPG, WebP, GIF. Maks 1MB.</span>
                    </div>

                    @if ($school->logo_path)
                        <div class="school-logo-upload-preview" id="editLogoPreview">
                    @else
                        <div class="school-logo-upload-preview hidden" id="editLogoPreview">
                    @endif
                        <img id="editLogoPreviewImg" src="{{ $school->logo_path ? asset('storage/' . $school->logo_path) : '' }}" alt="Preview Logo">
                        <button type="button" class="school-logo-remove-btn" id="editLogoRemoveBtn" title="Hapus Logo">
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
                        {{ old('is_active', $school->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                    >
                    <span class="form-check-label">Sekolah aktif dan tampil dalam sistem</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Perubahan</span>
                </button>
                <a href="{{ route('master.data-sekolah.index') }}" class="btn-secondary">
                    <span>Batal</span>
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reuse the global setupLogoUpload function pattern for edit form
    var fileInput = document.getElementById('edit_logo_file');
    var base64Input = document.getElementById('edit_logo_base64');
    var removeLogoInput = document.getElementById('edit_remove_logo');
    var placeholder = document.getElementById('editLogoPlaceholder');
    var preview = document.getElementById('editLogoPreview');
    var previewImg = document.getElementById('editLogoPreviewImg');
    var removeBtn = document.getElementById('editLogoRemoveBtn');
    var uploadArea = document.getElementById('editLogoUploadArea');

    if (uploadArea) {
        uploadArea.addEventListener('click', function(e) {
            if (e.target.closest('.school-logo-remove-btn')) return;
            fileInput.click();
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            var file = fileInput.files[0];
            if (!file) return;

            if (file.size > 1048576) {
                alert('Ukuran berkas logo melebihi 1MB. Silakan pilih berkas yang lebih kecil.');
                fileInput.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                base64Input.value = e.target.result;
                removeLogoInput.value = '0';
                previewImg.src = e.target.result;
                placeholder.classList.add('hidden');
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            base64Input.value = '';
            removeLogoInput.value = '1';
            fileInput.value = '';
            previewImg.src = '';
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
        });
    }
});
</script>
@endsection
