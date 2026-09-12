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
        <form method="GET" action="{{ route('master.data-sekolah.index') }}" class="table-toolbar-form">
            <div class="page-actions">
                <div class="per-page-selector">
                    <label for="table_per_page" class="table-cell-muted">Tampilkan:</label>
                    <select id="table_per_page" name="per_page" class="form-select-sm" onchange="this.form.submit()">
                        <option value="15" {{ request('per_page', '15') === '15' ? 'selected' : '' }}>15</option>
                        <option value="30" {{ request('per_page') === '30' ? 'selected' : '' }}>30</option>
                        <option value="90" {{ request('per_page') === '90' ? 'selected' : '' }}>90</option>
                        <option value="semua" {{ in_array(request('per_page'), ['semua', 'all'], true) ? 'selected' : '' }}>Semua</option>
                    </select>
                    <span class="table-cell-muted">data</span>
                </div>

                <select name="school_type" class="form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Jenis</option>
                    <option value="SMA" {{ request('school_type') === 'SMA' ? 'selected' : '' }}>SMA</option>
                    <option value="SMK" {{ request('school_type') === 'SMK' ? 'selected' : '' }}>SMK</option>
                </select>
            </div>

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
        </form>
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
                                    <img src="{{ asset('storage/' . $school->logo_path) }}" alt="Logo" class="entity-logo-thumb">
                                @else
                                    <span class="entity-logo-placeholder">
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
                                <span class="badge badge-primary">SMA</span>
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
                                <button
                                    type="button"
                                    class="btn-edit btn-open-edit-school"
                                    title="Edit Data Sekolah"
                                    data-school="{{ json_encode($school) }}"
                                    data-action="{{ route('master.data-sekolah.update', $school) }}"
                                    @if ($school->logo_path)
                                        data-logo-url="{{ asset('storage/' . $school->logo_path) }}"
                                    @endif
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                    </svg>
                                    <!-- <span>Edit</span> -->
                                </button>
                                <form method="POST" action="{{ route('master.data-sekolah.destroy', $school) }}" onsubmit="return confirm('Hapus data sekolah {{ $school->name }} dari registri?');" class="form-inline-action">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete" title="Hapus Sekolah">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                        <!-- <span>Hapus</span> -->
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

    <div class="table-footer">
        <div class="pagination-summary">
            @if ($schools->total() > 0)
                Menampilkan <strong>{{ $schools->firstItem() ?? 1 }}</strong> &ndash; <strong>{{ $schools->lastItem() ?? $schools->total() }}</strong> dari <strong>{{ $schools->total() }}</strong> Sekolah
            @else
                Menampilkan <strong>0</strong> Sekolah
            @endif
        </div>

        @if ($schools->hasPages())
            <nav class="pagination-nav" role="navigation" aria-label="Navigasi Halaman">
                {{-- Tombol Sebelumnya --}}
                @if ($schools->onFirstPage())
                    <span class="pagination-btn disabled" aria-disabled="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        <span>Sebelumnya</span>
                    </span>
                @else
                    <a href="{{ $schools->previousPageUrl() }}" class="pagination-btn" rel="prev">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        <span>Sebelumnya</span>
                    </a>
                @endif

                {{-- Nomor Halaman --}}
                <div class="pagination-pages">
                    @foreach ($schools->getUrlRange(1, $schools->lastPage()) as $page => $url)
                        @if ($page == $schools->currentPage())
                            <span class="pagination-page active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-page">{{ $page }}</a>
                        @endif
                    @endforeach
                </div>

                {{-- Tombol Selanjutnya --}}
                @if ($schools->hasMorePages())
                    <a href="{{ $schools->nextPageUrl() }}" class="pagination-btn" rel="next">
                        <span>Selanjutnya</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                @else
                    <span class="pagination-btn disabled" aria-disabled="true">
                        <span>Selanjutnya</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </span>
                @endif
            </nav>
        @endif
    </div>
</div>

<!-- Modal Tambah Sekolah Component -->
@include('master.data-sekolah.create')

<!-- Modal Edit Sekolah Component -->
@include('master.data-sekolah.edit')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Create Modal Elements
    const createModal = document.getElementById('modalCreateSchool');
    const btnOpenCreate = document.getElementById('btnOpenCreateSchool');
    const btnCloseCreate = document.getElementById('btnCloseCreateSchool');
    const btnCancelCreate = document.getElementById('btnCancelCreateSchool');

    function openCreateModal() {
        if (!createModal) return;
        createModal.classList.remove('hidden');
        document.body.classList.add('modal-open');
        const firstInput = createModal.querySelector('input[name="name"]');
        if (firstInput) setTimeout(() => firstInput.focus(), 50);
    }

    function closeCreateModal() {
        if (!createModal) return;
        createModal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    btnOpenCreate && btnOpenCreate.addEventListener('click', openCreateModal);
    btnCloseCreate && btnCloseCreate.addEventListener('click', closeCreateModal);
    btnCancelCreate && btnCancelCreate.addEventListener('click', closeCreateModal);

    createModal && createModal.addEventListener('click', function(e) {
        if (e.target === createModal) {
            closeCreateModal();
        }
    });

    // Edit Modal Elements
    const editModal = document.getElementById('modalEditSchool');
    const formEdit = document.getElementById('formEditSchool');
    const btnCloseEdit = document.getElementById('btnCloseEditSchool');
    const btnCancelEdit = document.getElementById('btnCancelEditSchool');

    function openEditModal() {
        if (!editModal) return;
        editModal.classList.remove('hidden');
        document.body.classList.add('modal-open');
        const firstInput = editModal.querySelector('input[name="name"]');
        if (firstInput) setTimeout(() => firstInput.focus(), 50);
    }

    function closeEditModal() {
        if (!editModal) return;
        editModal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    btnCloseEdit && btnCloseEdit.addEventListener('click', closeEditModal);
    btnCancelEdit && btnCancelEdit.addEventListener('click', closeEditModal);

    editModal && editModal.addEventListener('click', function(e) {
        if (e.target === editModal) {
            closeEditModal();
        }
    });

    // Edit Button Triggers on Table Rows
    document.querySelectorAll('.btn-open-edit-school').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const school = JSON.parse(this.dataset.school || '{}');
            const action = this.dataset.action;
            const logoUrl = this.dataset.logoUrl;

            formEdit.action = action;
            document.getElementById('edit_name').value = school.name || '';
            document.getElementById('edit_npsn').value = school.npsn || '';
            document.getElementById('edit_school_type').value = school.school_type || '';
            document.getElementById('edit_status').value = school.status || '';
            document.getElementById('edit_accreditation').value = school.accreditation || '';
            document.getElementById('edit_nss').value = school.nss || '';
            document.getElementById('edit_address').value = school.address || '';
            document.getElementById('edit_village').value = school.village || '';
            document.getElementById('edit_district').value = school.district || '';
            document.getElementById('edit_city').value = school.city || '';
            document.getElementById('edit_province').value = school.province || '';
            document.getElementById('edit_postal_code').value = school.postal_code || '';
            document.getElementById('edit_phone').value = school.phone || '';
            document.getElementById('edit_fax').value = school.fax || '';
            document.getElementById('edit_email').value = school.email || '';
            document.getElementById('edit_website').value = school.website || '';
            document.getElementById('edit_principal_name').value = school.principal_name || '';
            document.getElementById('edit_principal_nip').value = school.principal_nip || '';
            document.getElementById('edit_foundation_name').value = school.foundation_name || '';
            document.getElementById('edit_is_active').checked = Boolean(school.is_active);

            // Reset logo states in edit modal
            document.getElementById('edit_logo_base64').value = '';
            document.getElementById('edit_remove_logo').value = '0';
            document.getElementById('edit_logo_file').value = '';
            const previewImg = document.getElementById('editLogoPreviewImg');
            const preview = document.getElementById('editLogoPreview');
            const placeholder = document.getElementById('editLogoPlaceholder');

            if (logoUrl) {
                previewImg.src = logoUrl;
                placeholder.classList.add('hidden');
                preview.classList.remove('hidden');
            } else {
                previewImg.src = '';
                preview.classList.add('hidden');
                placeholder.classList.remove('hidden');
            }

            openEditModal();
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (createModal && !createModal.classList.contains('hidden')) {
                closeCreateModal();
            }
            if (editModal && !editModal.classList.contains('hidden')) {
                closeEditModal();
            }
        }
    });

    // Base64 Logo Upload Handlers
    setupLogoUpload('create_logo_file', 'create_logo_base64', 'createLogoPlaceholder', 'createLogoPreview', 'createLogoPreviewImg', 'createLogoRemoveBtn', 'createLogoUploadArea', null);
    setupLogoUpload('edit_logo_file', 'edit_logo_base64', 'editLogoPlaceholder', 'editLogoPreview', 'editLogoPreviewImg', 'editLogoRemoveBtn', 'editLogoUploadArea', 'edit_remove_logo');

    @if ($errors->any())
        @if (old('_method') === 'PUT')
            openEditModal();
        @else
            openCreateModal();
        @endif
    @endif
});

function setupLogoUpload(fileInputId, base64InputId, placeholderId, previewId, previewImgId, removeBtnId, uploadAreaId, removeLogoInputId) {
    const fileInput = document.getElementById(fileInputId);
    const base64Input = document.getElementById(base64InputId);
    const placeholder = document.getElementById(placeholderId);
    const preview = document.getElementById(previewId);
    const previewImg = document.getElementById(previewImgId);
    const removeBtn = document.getElementById(removeBtnId);
    const uploadArea = document.getElementById(uploadAreaId);
    const removeLogoInput = removeLogoInputId ? document.getElementById(removeLogoInputId) : null;

    if (!fileInput || !uploadArea) return;

    uploadArea.addEventListener('click', function(e) {
        if (e.target.closest('.dropzone-remove-btn')) return;
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
            if (removeLogoInput) removeLogoInput.value = '0';
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
        if (removeLogoInput) removeLogoInput.value = '1';
        previewImg.src = '';
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
    });
}
</script>
@endsection
