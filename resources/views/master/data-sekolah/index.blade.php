@extends('layouts.app')

@section('content')
<x-page-header title="Data Sekolah">
    <x-slot:subtitle>
        Kelola registri sekolah SMA dan SMK dalam platform INFORA
    </x-slot:subtitle>
    <x-slot:actions>
        <button type="button" class="btn-primary" id="btnOpenCreateSchool">
            <x-icon name="add" size="18" />
        </button>
    </x-slot:actions>
</x-page-header>

{{-- Flash Alerts --}}
<x-alert type="success" />
<x-alert type="error" />
<x-alert type="validation" />

<x-data-table route="{{ route('master.data-sekolah.index') }}" search-placeholder="Cari nama sekolah, NPSN, atau kota..." :per-page="$perPageInput">
    <x-slot:filters>
        <x-form-select 
        id="school_type"
        name="school_type"
        class="form-select-sm"
        onchange="this.form.submit()"
        placeholder="Semua Jenis"
        :selected="request('school_type')"
        :options="['SMA' => 'SMA', 'SMK' => 'SMK', 'MA' => 'MA']" />
    </x-slot:filters>
    <thead>
        <tr>
            <th>Sekolah</th>
            <th>NPSN</th>
            <th>Jenis</th>
            <th>Status</th>
            <th>Akreditasi</th>
            <th>Kota</th>
            <th>Kondisi</th>
            <th>Aksi</th>
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
                            <x-icon name="home" size="18" />
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
                    <span class="badge badge-warning">Swasta</span>
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
                        <span class="badge badge-warning">C</span>
                        @break
                    @case('Belum')
                        <span class="badge badge-neutral">Belum</span>
                        @break
                    @default
                        <span class="table-cell-muted">-</span>
                @endswitch
            </td>
            <td>
                <span class="table-cell-muted">{{ $school->kabupaten?->nama ?? $school->city ?? '-' }}</span>
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
                        <x-icon name="edit" size="14" />
                        <!-- <span>Edit</span> -->
                    </button>
                    <form method="POST" action="{{ route('master.data-sekolah.destroy', $school) }}" onsubmit="return confirm('Hapus data sekolah {{ $school->name }} dari registri?');" class="form-inline-action">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete" title="Hapus Sekolah">
                            <x-icon name="delete" size="14" />
                            <!-- <span>Hapus</span> -->
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <x-empty-state icon="home" iconSize="48" colspan="8" :text="request()->filled('search') || request()->filled('school_type') 
            ? 'Hasil pencarian tidak ditemukan.' 
            : 'Belum ada data sekolah yang terdaftar. Klik tombol Tambah untuk mendaftarkan sekolah pertama.'">
        </x-empty-state>
    @endforelse
    </tbody>
    <x-slot:footer>
        <x-pagination-summary :paginator="$schools" />
    </x-slot>
</x-data-table>

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

            // Populasi nilai dropdown wilayah di modal edit
            const editProvSelect = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_provinsi_id') : null;
            const editKabSelect = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kabupaten_id') : null;
            const editKecSelect = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kecamatan_id') : null;
            const editKelSelect = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kelurahan_id') : null;

            if (editProvSelect) {
                editProvSelect.setValue(
                    school.provinsi_id || '',
                    school.provinsi?.nama || school.province || '',
                    school.provinsi?.kode || '',
                    false
                );
            }

            if (school.provinsi_id && editKabSelect) {
                editKabSelect.setDisabled(false);
                editKabSelect.setLoading(true);

                fetch(`{{ route('wilayah.dropdown.kabupaten') }}?provinsi_id=${encodeURIComponent(school.provinsi_id)}`)
                    .then(res => res.json())
                    .then(kabData => {
                        editKabSelect.setOptions(kabData, school.kabupaten_id);
                        editKabSelect.setLoading(false);

                        if (school.kabupaten_id && editKecSelect) {
                            editKecSelect.setDisabled(false);
                            editKecSelect.setLoading(true);
                            return fetch(`{{ route('wilayah.dropdown.kecamatan') }}?kabupaten_id=${encodeURIComponent(school.kabupaten_id)}`);
                        }
                    })
                    .then(res => res ? res.json() : null)
                    .then(kecData => {
                        if (!kecData || !editKecSelect) return;
                        editKecSelect.setOptions(kecData, school.kecamatan_id);
                        editKecSelect.setLoading(false);

                        if (school.kecamatan_id && editKelSelect) {
                            editKelSelect.setDisabled(false);
                            editKelSelect.setLoading(true);
                            return fetch(`{{ route('wilayah.dropdown.kelurahan') }}?kecamatan_id=${encodeURIComponent(school.kecamatan_id)}`);
                        }
                    })
                    .then(res => res ? res.json() : null)
                    .then(kelData => {
                        if (!kelData || !editKelSelect) return;
                        editKelSelect.setOptions(kelData, school.kelurahan_id);
                        editKelSelect.setLoading(false);
                    })
                    .catch(err => {
                        console.error('Gagal memuat rantai wilayah edit:', err);
                        editKabSelect?.setLoading(false);
                        editKecSelect?.setLoading(false);
                        editKelSelect?.setLoading(false);
                    });
            } else {
                if (editKabSelect) { editKabSelect.setOptions([], ''); editKabSelect.setDisabled(true); }
                if (editKecSelect) { editKecSelect.setOptions([], ''); editKecSelect.setDisabled(true); }
                if (editKelSelect) { editKelSelect.setOptions([], ''); editKelSelect.setDisabled(true); }
            }

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

        // Pulihkan opsi bertingkat jika terjadi error validasi
        @if (old('provinsi_id'))
            (function() {
                const targetPrefix = '{{ old('_method') === 'PUT' ? 'edit' : 'create' }}';
                const oldProvId = '{{ old('provinsi_id') }}';
                const oldKabId = '{{ old('kabupaten_id') }}';
                const oldKecId = '{{ old('kecamatan_id') }}';
                const oldKelId = '{{ old('kelurahan_id') }}';

                const targetKab = window.SearchableSelect ? window.SearchableSelect.getInstance(targetPrefix + '_kabupaten_id') : null;
                const targetKec = window.SearchableSelect ? window.SearchableSelect.getInstance(targetPrefix + '_kecamatan_id') : null;
                const targetKel = window.SearchableSelect ? window.SearchableSelect.getInstance(targetPrefix + '_kelurahan_id') : null;

                if (targetKab) {
                    targetKab.setDisabled(false);
                    targetKab.setLoading(true);
                    fetch(`{{ route('wilayah.dropdown.kabupaten') }}?provinsi_id=${encodeURIComponent(oldProvId)}`)
                        .then(res => res.json())
                        .then(kabData => {
                            targetKab.setOptions(kabData, oldKabId);
                            targetKab.setLoading(false);

                            if (oldKabId && targetKec) {
                                targetKec.setDisabled(false);
                                targetKec.setLoading(true);
                                return fetch(`{{ route('wilayah.dropdown.kecamatan') }}?kabupaten_id=${encodeURIComponent(oldKabId)}`);
                            }
                        })
                        .then(res => res ? res.json() : null)
                        .then(kecData => {
                            if (!kecData || !targetKec) return;
                            targetKec.setOptions(kecData, oldKecId);
                            targetKec.setLoading(false);

                            if (oldKecId && targetKel) {
                                targetKel.setDisabled(false);
                                targetKel.setLoading(true);
                                return fetch(`{{ route('wilayah.dropdown.kelurahan') }}?kecamatan_id=${encodeURIComponent(oldKecId)}`);
                            }
                        })
                        .then(res => res ? res.json() : null)
                        .then(kelData => {
                            if (!kelData || !targetKel) return;
                            targetKel.setOptions(kelData, oldKelId);
                            targetKel.setLoading(false);
                        })
                        .catch(err => console.error('Gagal memulihkan opsi old wilayah:', err));
                }
            })();
        @endif
    @endif
});

    // Delegasi Event Global untuk Cascading Wilayah (Modal Tambah & Modal Edit)
    document.addEventListener('searchable-select:change', function(e) {
        const inputId = e.target?.id || e.detail?.inputId || '';
        if (!inputId) return;

        let prefix = null;
        if (inputId.startsWith('create_')) prefix = 'create';
        else if (inputId.startsWith('edit_')) prefix = 'edit';
        if (!prefix) return;

        const val = e.detail?.value;
        const text = e.detail?.text;
        const item = e.detail?.item;

        if (inputId === prefix + '_provinsi_id') {
            const provInput = document.getElementById(prefix + '_province');
            const kabInput = document.getElementById(prefix + '_city');
            const kecInput = document.getElementById(prefix + '_district');
            const kelInput = document.getElementById(prefix + '_village');

            if (provInput) provInput.value = text || '';
            if (kabInput) kabInput.value = '';
            if (kecInput) kecInput.value = '';
            if (kelInput) kelInput.value = '';

            const kabSelect = window.SearchableSelect?.getInstance(prefix + '_kabupaten_id');
            const kecSelect = window.SearchableSelect?.getInstance(prefix + '_kecamatan_id');
            const kelSelect = window.SearchableSelect?.getInstance(prefix + '_kelurahan_id');

            if (kabSelect) {
                kabSelect.setOptions([]);
                kabSelect.clear(false);
            }
            if (kecSelect) {
                kecSelect.setOptions([]);
                kecSelect.clear(false);
                kecSelect.setDisabled(true);
            }
            if (kelSelect) {
                kelSelect.setOptions([]);
                kelSelect.clear(false);
                kelSelect.setDisabled(true);
            }

            if (!val) {
                if (kabSelect) kabSelect.setDisabled(true);
                return;
            }

            if (kabSelect) {
                kabSelect.setDisabled(false);
                kabSelect.setLoading(true);
            }

            fetch(`{{ route('wilayah.dropdown.kabupaten') }}?provinsi_id=${encodeURIComponent(val)}`)
                .then(res => res.json())
                .then(data => {
                    const kab = window.SearchableSelect?.getInstance(prefix + '_kabupaten_id');
                    if (kab) {
                        kab.setOptions(data);
                        kab.setLoading(false);
                        kab.setDisabled(false);
                    }
                })
                .catch(err => {
                    console.error('Gagal mengambil data kabupaten:', err);
                    const kab = window.SearchableSelect?.getInstance(prefix + '_kabupaten_id');
                    if (kab) kab.setLoading(false);
                });
        } else if (inputId === prefix + '_kabupaten_id') {
            const kabInput = document.getElementById(prefix + '_city');
            const kecInput = document.getElementById(prefix + '_district');
            const kelInput = document.getElementById(prefix + '_village');

            if (kabInput) kabInput.value = text || '';
            if (kecInput) kecInput.value = '';
            if (kelInput) kelInput.value = '';

            const kecSelect = window.SearchableSelect?.getInstance(prefix + '_kecamatan_id');
            const kelSelect = window.SearchableSelect?.getInstance(prefix + '_kelurahan_id');

            if (kecSelect) {
                kecSelect.setOptions([]);
                kecSelect.clear(false);
            }
            if (kelSelect) {
                kelSelect.setOptions([]);
                kelSelect.clear(false);
                kelSelect.setDisabled(true);
            }

            if (!val) {
                if (kecSelect) kecSelect.setDisabled(true);
                return;
            }

            if (kecSelect) {
                kecSelect.setDisabled(false);
                kecSelect.setLoading(true);
            }

            fetch(`{{ route('wilayah.dropdown.kecamatan') }}?kabupaten_id=${encodeURIComponent(val)}`)
                .then(res => res.json())
                .then(data => {
                    const kec = window.SearchableSelect?.getInstance(prefix + '_kecamatan_id');
                    if (kec) {
                        kec.setOptions(data);
                        kec.setLoading(false);
                        kec.setDisabled(false);
                    }
                })
                .catch(err => {
                    console.error('Gagal mengambil data kecamatan:', err);
                    const kec = window.SearchableSelect?.getInstance(prefix + '_kecamatan_id');
                    if (kec) kec.setLoading(false);
                });
        } else if (inputId === prefix + '_kecamatan_id') {
            const kecInput = document.getElementById(prefix + '_district');
            const kelInput = document.getElementById(prefix + '_village');

            if (kecInput) kecInput.value = text || '';
            if (kelInput) kelInput.value = '';

            const kelSelect = window.SearchableSelect?.getInstance(prefix + '_kelurahan_id');

            if (kelSelect) {
                kelSelect.setOptions([]);
                kelSelect.clear(false);
            }

            if (!val) {
                if (kelSelect) kelSelect.setDisabled(true);
                return;
            }

            if (kelSelect) {
                kelSelect.setDisabled(false);
                kelSelect.setLoading(true);
            }

            fetch(`{{ route('wilayah.dropdown.kelurahan') }}?kecamatan_id=${encodeURIComponent(val)}`)
                .then(res => res.json())
                .then(data => {
                    const kel = window.SearchableSelect?.getInstance(prefix + '_kelurahan_id');
                    if (kel) {
                        kel.setOptions(data);
                        kel.setLoading(false);
                        kel.setDisabled(false);
                    }
                })
                .catch(err => {
                    console.error('Gagal mengambil data kelurahan:', err);
                    const kel = window.SearchableSelect?.getInstance(prefix + '_kelurahan_id');
                    if (kel) kel.setLoading(false);
                });
        } else if (inputId === prefix + '_kelurahan_id') {
            const kelInput = document.getElementById(prefix + '_village');
            const postalInput = document.getElementById(prefix + '_postal_code');

            if (kelInput) kelInput.value = text || '';

            if (val && item && item.kode_pos) {
                if (postalInput) postalInput.value = item.kode_pos;
            }
        }
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
