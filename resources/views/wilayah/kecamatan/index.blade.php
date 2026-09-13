@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Wilayah Kecamatan</h2>
        <div class="page-subtitle">Kelola registri wilayah administratif kecamatan se-Indonesia dalam platform INFORA</div>
    </div>
    <div class="page-actions">
        <button type="button" class="btn-primary" id="btnOpenCreateKecamatan">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Tambah Kecamatan</span>
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

@if (session('error'))
    <div class="alert-danger">
        <div class="alert-content">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>{{ session('error') }}</span>
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
        <form method="GET" action="{{ route('wilayah.kecamatan') }}" class="table-toolbar-form" id="formFilterKecamatan">
            <div class="page-actions">
                <div class="per-page-selector">
                    <label for="table_per_page" class="table-cell-muted">Tampilkan:</label>
                    <select id="table_per_page" name="per_page" class="form-select-sm" onchange="this.form.submit()">
                        <option value="15" {{ request('per_page', '15') === '15' ? 'selected' : '' }}>15</option>
                        <option value="30" {{ request('per_page', '30') === '30' ? 'selected' : '' }}>30</option>
                        <option value="90" {{ request('per_page', '90') === '90' ? 'selected' : '' }}>90</option>
                        <option value="semua" {{ in_array(request('per_page'), ['semua', 'all'], true) ? 'selected' : '' }}>Semua</option>
                    </select>
                </div>

                {{-- Filter Provinsi (Tingkat 1) --}}
                <select id="filter_provinsi" name="provinsi_id" class="form-select-sm">
                    <option value="">Semua Provinsi</option>
                    @foreach ($provinsiList as $prov)
                        <option value="{{ $prov->id }}" {{ request('provinsi_id') == $prov->id ? 'selected' : '' }}>
                            {{ $prov->kode }} - {{ $prov->nama }}
                        </option>
                    @endforeach
                </select>

                {{-- Filter Kabupaten (Tingkat 2 - Cascading) --}}
                <select id="filter_kabupaten" name="kabupaten_id" class="form-select-sm">
                    <option value="">Semua Kabupaten / Kota</option>
                    @foreach ($kabupatenList as $kab)
                        <option
                            value="{{ $kab->id }}"
                            data-provinsi="{{ $kab->provinsi_id }}"
                            {{ request('kabupaten_id') == $kab->id ? 'selected' : '' }}
                        >
                            {{ $kab->kode }} - {{ $kab->tipe }} {{ $kab->nama }}
                        </option>
                    @endforeach
                </select>

                {{-- Filter Status --}}
                <select id="filter_status" name="status" class="form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status') === '1' || request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' || request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
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
                    placeholder="Cari kode, kecamatan, kabupaten, atau provinsi..."
                    value="{{ request('search') }}"
                >
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-w-sm">No</th>
                    <th>Kode</th>
                    <th>Provinsi</th>
                    <th>Kabupaten / Kota</th>
                    <th>Nama Kecamatan</th>
                    <th>Status</th>
                    <th>Diperbarui</th>
                    <th class="col-w-actions">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kecamatanList as $kecamatan)
                    <tr>
                        <td>
                            <span class="table-cell-muted">{{ $kecamatanList->firstItem() + $loop->index }}</span>
                        </td>
                        <td>
                            <code class="text-brand font-semibold">{{ $kecamatan->kode }}</code>
                        </td>
                        <td>
                            <span class="table-cell-muted">{{ $kecamatan->kabupaten->provinsi->nama ?? '-' }}</span>
                        </td>
                        <td>
                            @if ($kecamatan->kabupaten)
                                @if ($kecamatan->kabupaten->tipe === 'Kota')
                                    <span class="badge badge-primary">{{ $kecamatan->kabupaten->nama_lengkap }}</span>
                                @else
                                    <span class="badge badge-cyan">{{ $kecamatan->kabupaten->nama_lengkap }}</span>
                                @endif
                            @else
                                <span class="table-cell-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-cell-bold">{{ $kecamatan->nama_lengkap }}</div>
                        </td>
                        <td>
                            @if ($kecamatan->status)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-neutral">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <span class="table-cell-muted">{{ $kecamatan->updated_at ? $kecamatan->updated_at->diffForHumans() : '-' }}</span>
                        </td>
                        <td>
                            <div class="table-actions table-actions-right">
                                <button
                                    type="button"
                                    class="btn-edit btn-open-edit-kecamatan"
                                    title="Edit Data Kecamatan"
                                    data-kecamatan="{{ json_encode([
                                        'id' => $kecamatan->id,
                                        'provinsi_id' => $kecamatan->kabupaten?->provinsi_id,
                                        'kabupaten_id' => $kecamatan->kabupaten_id,
                                        'kode' => $kecamatan->kode,
                                        'nama' => $kecamatan->nama,
                                        'status' => (int) $kecamatan->status,
                                    ]) }}"
                                    data-action="{{ route('wilayah.kecamatan.update', $kecamatan) }}"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    class="btn-delete btn-open-delete-kecamatan"
                                    title="Hapus Data Kecamatan"
                                    data-name="{{ $kecamatan->nama_lengkap }}"
                                    data-kode="{{ $kecamatan->kode }}"
                                    data-action="{{ route('wilayah.kecamatan.destroy', $kecamatan) }}"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <svg class="empty-state-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon>
                                    <line x1="8" y1="2" x2="8" y2="18"></line>
                                    <line x1="16" y1="6" x2="16" y2="22"></line>
                                </svg>
                                <span class="empty-state-text">Belum ada data wilayah kecamatan yang ditemukan.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div class="pagination-summary">
            @if ($kecamatanList->total() > 0)
                Menampilkan <strong>{{ $kecamatanList->firstItem() ?? 1 }}</strong> &ndash; <strong>{{ $kecamatanList->lastItem() ?? $kecamatanList->total() }}</strong> dari <strong>{{ $kecamatanList->total() }}</strong> Kecamatan
            @else
                Menampilkan <strong>0</strong> Kecamatan
            @endif
        </div>

        @if ($kecamatanList->hasPages())
            <nav class="pagination-nav" role="navigation" aria-label="Navigasi Halaman">
                {{-- Tombol Sebelumnya --}}
                @if ($kecamatanList->onFirstPage())
                    <span class="pagination-btn disabled" aria-disabled="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        <span>Sebelumnya</span>
                    </span>
                @else
                    <a href="{{ $kecamatanList->previousPageUrl() }}" class="pagination-btn" rel="prev">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        <span>Sebelumnya</span>
                    </a>
                @endif

                {{-- Nomor Halaman --}}
                <div class="pagination-pages">
                    @foreach ($kecamatanList->getUrlRange(1, $kecamatanList->lastPage()) as $page => $url)
                        @if ($page == $kecamatanList->currentPage())
                            <span class="pagination-page active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-page">{{ $page }}</a>
                        @endif
                    @endforeach
                </div>

                {{-- Tombol Selanjutnya --}}
                @if ($kecamatanList->hasMorePages())
                    <a href="{{ $kecamatanList->nextPageUrl() }}" class="pagination-btn" rel="next">
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

<!-- Modal Tambah Kecamatan Component -->
@include('wilayah.kecamatan.create')

<!-- Modal Edit Kecamatan Component -->
@include('wilayah.kecamatan.edit')

<!-- Modal Konfirmasi Hapus Bahaya (Danger Confirmation Modal) -->
<div class="modal-backdrop hidden" id="modalDeleteKecamatan" role="dialog" aria-modal="true" aria-labelledby="modalDeleteKecamatanTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title text-danger" id="modalDeleteKecamatanTitle">Konfirmasi Hapus Kecamatan</h3>
                <p class="modal-subtitle">Tindakan ini akan menghapus data wilayah kecamatan secara permanen dari sistem</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseDeleteKecamatan" aria-label="Tutup Dialog">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="" id="formDeleteKecamatan">
            @csrf
            @method('DELETE')
            <div class="modal-body">
                <div class="alert-danger mb-4">
                    <div class="alert-content">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <div>
                            <strong>Peringatan Integritas Data!</strong>
                            <div class="text-sm mt-1">
                                Anda akan menghapus data <strong id="deleteKecamatanName">-</strong> (<code id="deleteKecamatanKode" class="text-brand">-</code>). Pastikan tidak ada data sekolah atau registri kelurahan yang masih menautkan entitas ini.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelDeleteKecamatan">Batal</button>
                <button type="submit" class="btn-danger">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    <span>Ya, Hapus Data</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Helper fungsi penyaring opsi dropdown cascading
    function filterKabupatenOptions(provSelect, kabSelect, selectedKabId = null) {
        if (!provSelect || !kabSelect) return;
        const provId = provSelect.value;
        let hasValidSelection = false;

        Array.from(kabSelect.options).forEach(opt => {
            if (opt.value === "") {
                opt.hidden = false;
                return;
            }
            const optProv = opt.dataset.provinsi;
            const matches = !provId || optProv === provId;
            opt.hidden = !matches;

            if (matches && selectedKabId && opt.value === String(selectedKabId)) {
                opt.selected = true;
                hasValidSelection = true;
            }
        });

        if (!hasValidSelection && kabSelect.selectedOptions.length > 0 && kabSelect.selectedOptions[0].hidden) {
            kabSelect.value = "";
        }
    }

    // 1. Cascading pada Toolbar Filter
    const filterProv = document.getElementById('filter_provinsi');
    const filterKab = document.getElementById('filter_kabupaten');
    const formFilter = document.getElementById('formFilterKecamatan');

    if (filterProv && filterKab) {
        filterKabupatenOptions(filterProv, filterKab, '{{ request('kabupaten_id') }}');

        filterProv.addEventListener('change', function() {
            filterKabupatenOptions(filterProv, filterKab);
            if (formFilter) formFilter.submit();
        });

        filterKab.addEventListener('change', function() {
            if (formFilter) formFilter.submit();
        });
    }

    // Helper pemuatan data kabupaten via AJAX untuk SearchableSelect
    function loadKabupatenOptions(kabInstance, provId, selectedKabId = null) {
        if (!kabInstance) return Promise.resolve();
        if (!provId) {
            kabInstance.setOptions([], '');
            kabInstance.setDisabled(true);
            return Promise.resolve();
        }

        kabInstance.setDisabled(false);
        kabInstance.setLoading(true);

        return fetch(`{{ route('wilayah.dropdown.kabupaten') }}?provinsi_id=${encodeURIComponent(provId)}`)
            .then(res => res.json())
            .then(data => {
                kabInstance.setOptions(data, selectedKabId);
                kabInstance.setLoading(false);
            })
            .catch(err => {
                console.error('Gagal memuat daftar kabupaten:', err);
                kabInstance.setLoading(false);
            });
    }

    // 2. Create Modal Elements & Handlers
    const createModal = document.getElementById('modalCreateKecamatan');
    const btnOpenCreate = document.getElementById('btnOpenCreateKecamatan');
    const btnCloseCreate = document.getElementById('btnCloseCreateKecamatan');
    const btnCancelCreate = document.getElementById('btnCancelCreateKecamatan');
    const createProvSelect = document.getElementById('create_provinsi_id');
    const createKabSelect = document.getElementById('create_kabupaten_id');
    const createKodeInput = document.getElementById('create_kode');

    function openCreateModal() {
        if (!createModal) return;
        createModal.classList.remove('hidden');
        document.body.classList.add('modal-open');
        const trigger = document.getElementById('trigger_create_provinsi_id');
        if (trigger) setTimeout(() => trigger.focus(), 50);
    }

    function closeCreateModal() {
        if (!createModal) return;
        createModal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    btnOpenCreate && btnOpenCreate.addEventListener('click', function() {
        openCreateModal();
    });
    btnCloseCreate && btnCloseCreate.addEventListener('click', closeCreateModal);
    btnCancelCreate && btnCancelCreate.addEventListener('click', closeCreateModal);

    createModal && createModal.addEventListener('click', function(e) {
        if (e.target === createModal) closeCreateModal();
    });

    if (createProvSelect) {
        createProvSelect.addEventListener('searchable-select:change', function(e) {
            const provId = e.detail?.value || '';
            const createKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('create_kabupaten_id') : null;

            if (provId) {
                loadKabupatenOptions(createKabInst, provId, null);
            } else {
                if (createKabInst) {
                    createKabInst.clear(true);
                    createKabInst.setDisabled(true);
                }
                if (createKodeInput && createKodeInput.value.length <= 4) {
                    createKodeInput.value = '';
                }
            }
        });
    }

    if (createKabSelect) {
        createKabSelect.addEventListener('searchable-select:change', function(e) {
            const kabKode = e.detail?.extra || e.detail?.item?.kode || '';
            if (kabKode && createKodeInput) {
                const currentVal = createKodeInput.value.trim();
                if (!currentVal || currentVal.length <= 4) {
                    createKodeInput.value = kabKode;
                } else if (!currentVal.startsWith(kabKode)) {
                    createKodeInput.value = kabKode + currentVal.slice(4, 6);
                }
            } else if (!e.detail?.value && createKodeInput) {
                if (createKodeInput.value.length <= 4) {
                    createKodeInput.value = '';
                }
            }
        });
    }

    // 3. Edit Modal Elements & Handlers
    const editModal = document.getElementById('modalEditKecamatan');
    const formEdit = document.getElementById('formEditKecamatan');
    const btnCloseEdit = document.getElementById('btnCloseEditKecamatan');
    const btnCancelEdit = document.getElementById('btnCancelEditKecamatan');
    const editProvSelect = document.getElementById('edit_provinsi_id');
    const editKabSelect = document.getElementById('edit_kabupaten_id');
    const editKodeInput = document.getElementById('edit_kode');

    function openEditModal() {
        if (!editModal) return;
        editModal.classList.remove('hidden');
        document.body.classList.add('modal-open');
        const trigger = document.getElementById('trigger_edit_provinsi_id');
        if (trigger) setTimeout(() => trigger.focus(), 50);
    }

    function closeEditModal() {
        if (!editModal) return;
        editModal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    btnCloseEdit && btnCloseEdit.addEventListener('click', closeEditModal);
    btnCancelEdit && btnCancelEdit.addEventListener('click', closeEditModal);

    editModal && editModal.addEventListener('click', function(e) {
        if (e.target === editModal) closeEditModal();
    });

    if (editProvSelect) {
        editProvSelect.addEventListener('searchable-select:change', function(e) {
            const provId = e.detail?.value || '';
            const editKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kabupaten_id') : null;

            if (provId) {
                loadKabupatenOptions(editKabInst, provId, null);
            } else {
                if (editKabInst) {
                    editKabInst.clear(true);
                    editKabInst.setDisabled(true);
                }
                if (editKodeInput && editKodeInput.value.length <= 4) {
                    editKodeInput.value = '';
                }
            }
        });
    }

    if (editKabSelect) {
        editKabSelect.addEventListener('searchable-select:change', function(e) {
            const kabKode = e.detail?.extra || e.detail?.item?.kode || '';
            if (kabKode && editKodeInput) {
                const currentVal = editKodeInput.value.trim();
                if (!currentVal || currentVal.length <= 4) {
                    editKodeInput.value = kabKode;
                } else if (!currentVal.startsWith(kabKode)) {
                    editKodeInput.value = kabKode + currentVal.slice(4, 6);
                }
            }
        });
    }

    // 4. Delete Modal Elements & Handlers
    const deleteModal = document.getElementById('modalDeleteKecamatan');
    const formDelete = document.getElementById('formDeleteKecamatan');
    const deleteNameSpan = document.getElementById('deleteKecamatanName');
    const deleteKodeSpan = document.getElementById('deleteKecamatanKode');
    const btnCloseDelete = document.getElementById('btnCloseDeleteKecamatan');
    const btnCancelDelete = document.getElementById('btnCancelDeleteKecamatan');

    function openDeleteModal(name, kode, action) {
        if (!deleteModal) return;
        deleteNameSpan.textContent = name;
        deleteKodeSpan.textContent = kode;
        formDelete.action = action;
        deleteModal.classList.remove('hidden');
        document.body.classList.add('modal-open');
    }

    function closeDeleteModal() {
        if (!deleteModal) return;
        deleteModal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    btnCloseDelete && btnCloseDelete.addEventListener('click', closeDeleteModal);
    btnCancelDelete && btnCancelDelete.addEventListener('click', closeDeleteModal);

    deleteModal && deleteModal.addEventListener('click', function(e) {
        if (e.target === deleteModal) closeDeleteModal();
    });

    // 5. Triggers pada Tombol Edit di Baris Tabel
    document.querySelectorAll('.btn-open-edit-kecamatan').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const data = JSON.parse(this.dataset.kecamatan || '{}');
            const action = this.dataset.action;

            formEdit.action = action;

            const editProvInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_provinsi_id') : null;
            const editKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kabupaten_id') : null;

            if (editProvInst) {
                editProvInst.setValue(data.provinsi_id || '', '', '', false);
            }

            if (data.provinsi_id && editKabInst) {
                loadKabupatenOptions(editKabInst, data.provinsi_id, data.kabupaten_id);
            } else if (editKabInst) {
                editKabInst.setOptions([], '');
                editKabInst.setDisabled(true);
            }

            if (editKodeInput) editKodeInput.value = data.kode || '';
            document.getElementById('edit_nama').value = data.nama || '';
            document.getElementById('edit_status').checked = Boolean(data.status);

            openEditModal();
        });
    });

    // 6. Triggers pada Tombol Hapus di Baris Tabel
    document.querySelectorAll('.btn-open-delete-kecamatan').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const name = this.dataset.name || '';
            const kode = this.dataset.kode || '';
            const action = this.dataset.action;

            openDeleteModal(name, kode, action);
        });
    });

    // 7. Penanganan Tombol ESC untuk Menutup Modal Aktif
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (createModal && !createModal.classList.contains('hidden')) {
                closeCreateModal();
            }
            if (editModal && !editModal.classList.contains('hidden')) {
                closeEditModal();
            }
            if (deleteModal && !deleteModal.classList.contains('hidden')) {
                closeDeleteModal();
            }
        }
    });

    // 8. Auto Reopen Modal jika Terjadi Error Validasi
    @if ($errors->any())
        @if (old('_method') === 'PUT')
            openEditModal();
            @if (old('provinsi_id'))
                (function() {
                    const editKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kabupaten_id') : null;
                    loadKabupatenOptions(editKabInst, '{{ old('provinsi_id') }}', '{{ old('kabupaten_id') }}');
                })();
            @endif
        @else
            openCreateModal();
            @if (old('provinsi_id'))
                (function() {
                    const createKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('create_kabupaten_id') : null;
                    loadKabupatenOptions(createKabInst, '{{ old('provinsi_id') }}', '{{ old('kabupaten_id') }}');
                })();
            @endif
        @endif
    @endif
});
</script>
@endsection
