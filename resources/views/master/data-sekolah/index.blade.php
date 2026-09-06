@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Data Sekolah</h2>
        <div class="page-subtitle">Kelola registri sekolah SMA & SMK dalam platform INFORA</div>
    </div>
    <div class="page-actions">
        <button type="button" class="btn-primary" id="btnOpenCreateSchool">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Tambah Sekolah</span>
        </button>
    </div>
</div>

@if (session('success'))
    <div class="alert-success">
        <div class="alert-content">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

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

<div class="table-card">
    <div class="table-toolbar">
        <form method="GET" action="{{ route('master.data-sekolah.index') }}" class="page-actions">
            <div class="search-box toolbar-search-box">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="Cari nama sekolah, NPSN, atau kota..."
                    value="{{ request('search') }}"
                >
            </div>

            <select name="school_type" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Jenis</option>
                <option value="SMA" {{ request('school_type') === 'SMA' ? 'selected' : '' }}>SMA</option>
                <option value="SMK" {{ request('school_type') === 'SMK' ? 'selected' : '' }}>SMK</option>
            </select>
        </form>

        <div class="table-cell-muted">
            Total: <strong>{{ $schools->total() }}</strong> Sekolah
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Sekolah</th>
                    <th>NPSN</th>
                    <th>Jenis</th>
                    <th>Status</th>
                    <th>Akreditasi</th>
                    <th>Kota</th>
                    <th>Kondisi</th>
                    <th class="col-w-actions">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($schools as $school)
                    <tr>
                        <td>
                            <div class="alert-content">
                                @if ($school->logo_path)
                                    <img src="{{ asset('storage/' . $school->logo_path) }}" alt="Logo" class="school-logo-thumb">
                                @else
                                    <span class="school-logo-placeholder">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                        </svg>
                                    </span>
                                @endif
                                <div>
                                    <div class="table-cell-bold">{{ $school->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <code class="text-brand">{{ $school->npsn }}</code>
                        </td>
                        <td>
                            @if ($school->school_type === 'SMA')
                                <span class="badge badge-purple">SMA</span>
                            @else
                                <span class="badge badge-cyan">SMK</span>
                            @endif
                        </td>
                        <td>
                            @if ($school->status === 'Negeri')
                                <span class="badge badge-success">Negeri</span>
                            @else
                                <span class="badge badge-amber">Swasta</span>
                            @endif
                        </td>
                        <td>
                            @switch($school->accreditation)
                                @case('A')
                                    <span class="badge badge-success">A</span>
                                    @break
                                @case('B')
                                    <span class="badge badge-cyan">B</span>
                                    @break
                                @case('C')
                                    <span class="badge badge-amber">C</span>
                                    @break
                                @case('Belum')
                                    <span class="badge badge-neutral">Belum</span>
                                    @break
                                @default
                                    <span class="table-cell-muted">-</span>
                            @endswitch
                        </td>
                        <td>
                            <span class="table-cell-muted">{{ $school->city ?? '-' }}</span>
                        </td>
                        <td>
                            @if ($school->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-neutral">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-actions table-actions-right">
                                <a href="{{ route('master.data-sekolah.edit', $school) }}" class="btn-edit" title="Edit Data Sekolah">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                    </svg>
                                    <span>Edit</span>
                                </a>
                                <form method="POST" action="{{ route('master.data-sekolah.destroy', $school) }}" onsubmit="return confirm('Hapus data sekolah {{ $school->name }} dari registri?');" class="form-inline-action">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete" title="Hapus Sekolah">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <svg class="empty-state-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                                <span class="empty-state-text">Belum ada data sekolah yang terdaftar. Klik "Tambah Sekolah" untuk mendaftarkan sekolah pertama.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($schools->hasPages())
        <div class="table-footer">
            {{ $schools->links() }}
        </div>
    @endif
</div>

<!-- Modal Tambah Sekolah -->
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
                    <div class="school-logo-upload" id="createLogoUploadArea">
                        <input type="hidden" name="logo" id="create_logo_base64">
                        <input type="file" id="create_logo_file" accept="image/png,image/jpeg,image/webp,image/gif" class="hidden">
                        <div class="school-logo-upload-content" id="createLogoPlaceholder">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                                <circle cx="9" cy="9" r="2"></circle>
                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                            </svg>
                            <span>Klik untuk unggah logo sekolah</span>
                            <span class="form-hint">Format: PNG, JPG, WebP, GIF. Maks 1MB.</span>
                        </div>
                        <div class="school-logo-upload-preview hidden" id="createLogoPreview">
                            <img id="createLogoPreviewImg" alt="Preview Logo">
                            <button type="button" class="school-logo-remove-btn" id="createLogoRemoveBtn" title="Hapus Logo">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalCreateSchool');
    const btnOpen = document.getElementById('btnOpenCreateSchool');
    const btnClose = document.getElementById('btnCloseCreateSchool');
    const btnCancel = document.getElementById('btnCancelCreateSchool');

    function openModal() {
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.classList.add('modal-open');
        const firstInput = modal.querySelector('input[name="name"]');
        if (firstInput) setTimeout(() => firstInput.focus(), 50);
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    btnOpen && btnOpen.addEventListener('click', openModal);
    btnClose && btnClose.addEventListener('click', closeModal);
    btnCancel && btnCancel.addEventListener('click', closeModal);

    modal && modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    // Base64 Logo Upload Handler (Create Form)
    setupLogoUpload('create_logo_file', 'create_logo_base64', 'createLogoPlaceholder', 'createLogoPreview', 'createLogoPreviewImg', 'createLogoRemoveBtn', 'createLogoUploadArea');

    @if ($errors->any())
        openModal();
    @endif
});

function setupLogoUpload(fileInputId, base64InputId, placeholderId, previewId, previewImgId, removeBtnId, uploadAreaId) {
    const fileInput = document.getElementById(fileInputId);
    const base64Input = document.getElementById(base64InputId);
    const placeholder = document.getElementById(placeholderId);
    const preview = document.getElementById(previewId);
    const previewImg = document.getElementById(previewImgId);
    const removeBtn = document.getElementById(removeBtnId);
    const uploadArea = document.getElementById(uploadAreaId);

    if (!fileInput || !uploadArea) return;

    uploadArea.addEventListener('click', function(e) {
        if (e.target.closest('.school-logo-remove-btn')) return;
        fileInput.click();
    });

    fileInput.addEventListener('change', function() {
        const file = fileInput.files[0];
        if (!file) return;

        if (file.size > 1048576) {
            alert('Ukuran berkas logo melebihi 1MB. Silakan pilih berkas yang lebih kecil.');
            fileInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            base64Input.value = e.target.result;
            previewImg.src = e.target.result;
            placeholder.classList.add('hidden');
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    });

    removeBtn && removeBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        base64Input.value = '';
        fileInput.value = '';
        previewImg.src = '';
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
    });
}
</script>
@endsection
