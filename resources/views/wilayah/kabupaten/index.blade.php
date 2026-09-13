@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Wilayah Kabupaten & Kota</h2>
        <div class="page-subtitle">Kelola registri wilayah administratif kabupaten dan kota se-Indonesia dalam platform INFORA</div>
    </div>
    <div class="page-actions">
        <button type="button" class="btn-primary" id="btnOpenCreateKabupaten">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Tambah Kabupaten / Kota</span>
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
        <form method="GET" action="{{ route('wilayah.kabupaten') }}" class="table-toolbar-form">
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

                <select id="filter_provinsi" name="provinsi_id" class="form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Provinsi</option>
                    @foreach ($provinsiList as $prov)
                        <option value="{{ $prov->id }}" {{ request('provinsi_id') == $prov->id ? 'selected' : '' }}>
                            {{ $prov->kode }} - {{ $prov->nama }}
                        </option>
                    @endforeach
                </select>

                <select id="filter_tipe" name="tipe" class="form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Tipe</option>
                    <option value="Kabupaten" {{ request('tipe') === 'Kabupaten' ? 'selected' : '' }}>Kabupaten</option>
                    <option value="Kota" {{ request('tipe') === 'Kota' ? 'selected' : '' }}>Kota</option>
                </select>

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
                    placeholder="Cari kode, kabupaten, kota, atau provinsi..."
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
                    <th>Tipe</th>
                    <th>Nama Wilayah</th>
                    <th>Status</th>
                    <th>Diperbarui</th>
                    <th class="col-w-actions">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kabupatenList as $kabupaten)
                    <tr>
                        <td>
                            <span class="table-cell-muted">{{ $kabupatenList->firstItem() + $loop->index }}</span>
                        </td>
                        <td>
                            <code class="text-brand font-semibold">{{ $kabupaten->kode }}</code>
                        </td>
                        <td>
                            <span class="table-cell-muted">{{ $kabupaten->provinsi->nama ?? '-' }}</span>
                        </td>
                        <td>
                            @if ($kabupaten->tipe === 'Kota')
                                <span class="badge badge-primary">Kota</span>
                            @else
                                <span class="badge badge-cyan">Kabupaten</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-cell-bold">{{ $kabupaten->nama_lengkap }}</div>
                        </td>
                        <td>
                            @if ($kabupaten->status)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-neutral">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <span class="table-cell-muted">{{ $kabupaten->updated_at ? $kabupaten->updated_at->diffForHumans() : '-' }}</span>
                        </td>
                        <td>
                            <div class="table-actions table-actions-right">
                                <button
                                    type="button"
                                    class="btn-edit btn-open-edit-kabupaten"
                                    title="Edit Data Kabupaten/Kota"
                                    data-kabupaten="{{ json_encode($kabupaten) }}"
                                    data-action="{{ route('wilayah.kabupaten.update', $kabupaten) }}"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    class="btn-delete btn-open-delete-kabupaten"
                                    title="Hapus Data Kabupaten/Kota"
                                    data-name="{{ $kabupaten->nama_lengkap }}"
                                    data-kode="{{ $kabupaten->kode }}"
                                    data-action="{{ route('wilayah.kabupaten.destroy', $kabupaten) }}"
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
                                <span class="empty-state-text">Belum ada data wilayah kabupaten atau kota yang ditemukan.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div class="pagination-summary">
            @if ($kabupatenList->total() > 0)
                Menampilkan <strong>{{ $kabupatenList->firstItem() ?? 1 }}</strong> &ndash; <strong>{{ $kabupatenList->lastItem() ?? $kabupatenList->total() }}</strong> dari <strong>{{ $kabupatenList->total() }}</strong> Kabupaten/Kota
            @else
                Menampilkan <strong>0</strong> Kabupaten/Kota
            @endif
        </div>

        @if ($kabupatenList->hasPages())
            <nav class="pagination-nav" role="navigation" aria-label="Navigasi Halaman">
                {{-- Tombol Sebelumnya --}}
                @if ($kabupatenList->onFirstPage())
                    <span class="pagination-btn disabled" aria-disabled="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        <span>Sebelumnya</span>
                    </span>
                @else
                    <a href="{{ $kabupatenList->previousPageUrl() }}" class="pagination-btn" rel="prev">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        <span>Sebelumnya</span>
                    </a>
                @endif

                {{-- Nomor Halaman --}}
                <div class="pagination-pages">
                    @foreach ($kabupatenList->getUrlRange(1, $kabupatenList->lastPage()) as $page => $url)
                        @if ($page == $kabupatenList->currentPage())
                            <span class="pagination-page active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-page">{{ $page }}</a>
                        @endif
                    @endforeach
                </div>

                {{-- Tombol Selanjutnya --}}
                @if ($kabupatenList->hasMorePages())
                    <a href="{{ $kabupatenList->nextPageUrl() }}" class="pagination-btn" rel="next">
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

<!-- Modal Tambah Kabupaten Component -->
@include('wilayah.kabupaten.create')

<!-- Modal Edit Kabupaten Component -->
@include('wilayah.kabupaten.edit')

<!-- Modal Konfirmasi Hapus Bahaya (Danger Confirmation Modal) -->
<div class="modal-backdrop hidden" id="modalDeleteKabupaten" role="dialog" aria-modal="true" aria-labelledby="modalDeleteKabupatenTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title text-danger" id="modalDeleteKabupatenTitle">Konfirmasi Hapus Kabupaten / Kota</h3>
                <p class="modal-subtitle">Tindakan ini akan menghapus data wilayah kabupaten/kota secara permanen dari sistem</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseDeleteKabupaten" aria-label="Tutup Dialog">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="" id="formDeleteKabupaten">
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
                                Anda akan menghapus data <strong id="deleteKabupatenName">-</strong> (<code id="deleteKabupatenKode" class="text-brand">-</code>). Pastikan tidak ada data sekolah atau wilayah turunan yang masih menautkan entitas ini.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelDeleteKabupaten">Batal</button>
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
    // Create Modal Elements
    const createModal = document.getElementById('modalCreateKabupaten');
    const btnOpenCreate = document.getElementById('btnOpenCreateKabupaten');
    const btnCloseCreate = document.getElementById('btnCloseCreateKabupaten');
    const btnCancelCreate = document.getElementById('btnCancelCreateKabupaten');
    const createProvSelect = document.getElementById('create_provinsi_id');
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
        if (e.target === createModal) {
            closeCreateModal();
        }
    });

    // Auto-prefixing kode wilayah pada Create Modal
    if (createProvSelect && createKodeInput) {
        createProvSelect.addEventListener('searchable-select:change', function(e) {
            const provKode = e.detail?.extra || '';
            if (provKode) {
                const currentVal = createKodeInput.value.trim();
                if (!currentVal || currentVal.length <= 2) {
                    createKodeInput.value = provKode;
                } else if (!currentVal.startsWith(provKode)) {
                    createKodeInput.value = provKode + currentVal.slice(2, 4);
                }
            } else if (!e.detail?.value) {
                if (createKodeInput.value.length <= 2) {
                    createKodeInput.value = '';
                }
            }
        });
    }

    // Edit Modal Elements
    const editModal = document.getElementById('modalEditKabupaten');
    const formEdit = document.getElementById('formEditKabupaten');
    const btnCloseEdit = document.getElementById('btnCloseEditKabupaten');
    const btnCancelEdit = document.getElementById('btnCancelEditKabupaten');
    const editProvSelect = document.getElementById('edit_provinsi_id');
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
        if (e.target === editModal) {
            closeEditModal();
        }
    });

    // Auto-prefixing kode wilayah pada Edit Modal
    if (editProvSelect && editKodeInput) {
        editProvSelect.addEventListener('searchable-select:change', function(e) {
            const provKode = e.detail?.extra || '';
            if (provKode) {
                const currentVal = editKodeInput.value.trim();
                if (!currentVal || currentVal.length <= 2) {
                    editKodeInput.value = provKode;
                } else if (!currentVal.startsWith(provKode)) {
                    editKodeInput.value = provKode + currentVal.slice(2, 4);
                }
            }
        });
    }

    // Delete Modal Elements
    const deleteModal = document.getElementById('modalDeleteKabupaten');
    const formDelete = document.getElementById('formDeleteKabupaten');
    const deleteNameSpan = document.getElementById('deleteKabupatenName');
    const deleteKodeSpan = document.getElementById('deleteKabupatenKode');
    const btnCloseDelete = document.getElementById('btnCloseDeleteKabupaten');
    const btnCancelDelete = document.getElementById('btnCancelDeleteKabupaten');

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
        if (e.target === deleteModal) {
            closeDeleteModal();
        }
    });

    // Edit Button Triggers on Table Rows
    document.querySelectorAll('.btn-open-edit-kabupaten').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const data = JSON.parse(this.dataset.kabupaten || '{}');
            const action = this.dataset.action;

            formEdit.action = action;
            const editProvInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_provinsi_id') : null;
            if (editProvInst) {
                editProvInst.setValue(data.provinsi_id || '', '', '', false);
            } else {
                document.getElementById('edit_provinsi_id').value = data.provinsi_id || '';
            }
            document.getElementById('edit_tipe').value = data.tipe || 'Kabupaten';
            document.getElementById('edit_kode').value = data.kode || '';
            document.getElementById('edit_nama').value = data.nama || '';
            document.getElementById('edit_status').checked = Boolean(data.status);

            openEditModal();
        });
    });

    // Delete Button Triggers on Table Rows
    document.querySelectorAll('.btn-open-delete-kabupaten').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const name = this.dataset.name || '';
            const kode = this.dataset.kode || '';
            const action = this.dataset.action;

            openDeleteModal(name, kode, action);
        });
    });

    // Keyboard ESC to close any open modal
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

    // Auto reopen modal on validation errors
    @if ($errors->any())
        @if (old('_method') === 'PUT')
            openEditModal();
        @else
            openCreateModal();
        @endif
    @endif
});
</script>
@endsection
