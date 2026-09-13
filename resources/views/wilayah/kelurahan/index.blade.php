@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Wilayah Kelurahan & Desa</h2>
        <div class="page-subtitle">Kelola registri wilayah administratif kelurahan dan desa se-Indonesia dalam platform INFORA</div>
    </div>
    <div class="page-actions">
        <button type="button" class="btn-primary" id="btnOpenCreateKelurahan">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Tambah Kelurahan / Desa</span>
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
        <form method="GET" action="{{ route('wilayah.kelurahan') }}" class="table-toolbar-form" id="formFilterKelurahan">
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

                {{-- Filter Kecamatan (Tingkat 3 - Cascading) --}}
                <select id="filter_kecamatan" name="kecamatan_id" class="form-select-sm">
                    <option value="">Semua Kecamatan</option>
                    @foreach ($kecamatanList as $kec)
                        <option
                            value="{{ $kec->id }}"
                            data-kabupaten="{{ $kec->kabupaten_id }}"
                            {{ request('kecamatan_id') == $kec->id ? 'selected' : '' }}
                        >
                            {{ $kec->kode }} - {{ $kec->nama }}
                        </option>
                    @endforeach
                </select>

                {{-- Filter Tipe --}}
                <select id="filter_tipe" name="tipe" class="form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Tipe</option>
                    <option value="Kelurahan" {{ request('tipe') === 'Kelurahan' ? 'selected' : '' }}>Kelurahan</option>
                    <option value="Desa" {{ request('tipe') === 'Desa' ? 'selected' : '' }}>Desa</option>
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
                    placeholder="Cari kode, nama, kode pos, kecamatan..."
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
                    <th>Kecamatan</th>
                    <th>Tipe</th>
                    <th>Nama Kelurahan / Desa</th>
                    <th>Kode Pos</th>
                    <th>Status</th>
                    <th>Diperbarui</th>
                    <th class="col-w-actions">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kelurahanList as $kelurahan)
                    <tr>
                        <td>
                            <span class="table-cell-muted">{{ $kelurahanList->firstItem() + $loop->index }}</span>
                        </td>
                        <td>
                            <code class="text-brand font-semibold">{{ $kelurahan->kode }}</code>
                        </td>
                        <td>
                            <span class="table-cell-muted">{{ $kelurahan->kecamatan?->kabupaten?->provinsi?->nama ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="table-cell-muted">{{ $kelurahan->kecamatan?->kabupaten?->nama_lengkap ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="table-cell-muted">{{ $kelurahan->kecamatan?->nama_lengkap ?? '-' }}</span>
                        </td>
                        <td>
                            @if ($kelurahan->tipe === 'Kelurahan')
                                <span class="badge badge-cyan">Kelurahan</span>
                            @else
                                <span class="badge badge-primary">Desa</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-cell-bold">{{ $kelurahan->nama }}</div>
                        </td>
                        <td>
                            @if ($kelurahan->kode_pos)
                                <span class="badge badge-neutral font-mono">{{ $kelurahan->kode_pos }}</span>
                            @else
                                <span class="table-cell-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if ($kelurahan->status)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-neutral">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <span class="table-cell-muted">{{ $kelurahan->updated_at ? $kelurahan->updated_at->diffForHumans() : '-' }}</span>
                        </td>
                        <td>
                            <div class="table-actions table-actions-right">
                                <button
                                    type="button"
                                    class="btn-edit btn-open-edit-kelurahan"
                                    title="Edit Data Kelurahan / Desa"
                                    data-kelurahan="{{ json_encode([
                                        'id' => $kelurahan->id,
                                        'provinsi_id' => $kelurahan->kecamatan?->kabupaten?->provinsi_id,
                                        'kabupaten_id' => $kelurahan->kecamatan?->kabupaten_id,
                                        'kecamatan_id' => $kelurahan->kecamatan_id,
                                        'tipe' => $kelurahan->tipe,
                                        'kode' => $kelurahan->kode,
                                        'nama' => $kelurahan->nama,
                                        'kode_pos' => $kelurahan->kode_pos,
                                        'status' => (int) $kelurahan->status,
                                    ]) }}"
                                    data-action="{{ route('wilayah.kelurahan.update', $kelurahan) }}"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    class="btn-delete btn-open-delete-kelurahan"
                                    title="Hapus Data Kelurahan / Desa"
                                    data-name="{{ $kelurahan->nama_lengkap }}"
                                    data-kode="{{ $kelurahan->kode }}"
                                    data-action="{{ route('wilayah.kelurahan.destroy', $kelurahan) }}"
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
                        <td colspan="11">
                            <div class="empty-state">
                                <svg class="empty-state-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon>
                                    <line x1="8" y1="2" x2="8" y2="18"></line>
                                    <line x1="16" y1="6" x2="16" y2="22"></line>
                                </svg>
                                <span class="empty-state-text">Belum ada data wilayah kelurahan / desa yang ditemukan.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div class="pagination-summary">
            @if ($kelurahanList->total() > 0)
                Menampilkan <strong>{{ $kelurahanList->firstItem() ?? 1 }}</strong> &ndash; <strong>{{ $kelurahanList->lastItem() ?? $kelurahanList->total() }}</strong> dari <strong>{{ $kelurahanList->total() }}</strong> Wilayah
            @else
                Menampilkan <strong>0</strong> Wilayah
            @endif
        </div>

        @if ($kelurahanList->hasPages())
            <nav class="pagination-nav" role="navigation" aria-label="Navigasi Halaman">
                {{-- Tombol Sebelumnya --}}
                @if ($kelurahanList->onFirstPage())
                    <span class="pagination-btn disabled" aria-disabled="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        <span>Sebelumnya</span>
                    </span>
                @else
                    <a href="{{ $kelurahanList->previousPageUrl() }}" class="pagination-btn" rel="prev">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        <span>Sebelumnya</span>
                    </a>
                @endif

                {{-- Nomor Halaman --}}
                <div class="pagination-pages">
                    @foreach ($kelurahanList->getUrlRange(1, $kelurahanList->lastPage()) as $page => $url)
                        @if ($page == $kelurahanList->currentPage())
                            <span class="pagination-page active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-page">{{ $page }}</a>
                        @endif
                    @endforeach
                </div>

                {{-- Tombol Selanjutnya --}}
                @if ($kelurahanList->hasMorePages())
                    <a href="{{ $kelurahanList->nextPageUrl() }}" class="pagination-btn" rel="next">
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

<!-- Modal Tambah Kelurahan Component -->
@include('wilayah.kelurahan.create')

<!-- Modal Edit Kelurahan Component -->
@include('wilayah.kelurahan.edit')

<!-- Modal Konfirmasi Hapus Bahaya (Danger Confirmation Modal) -->
<div class="modal-backdrop hidden" id="modalDeleteKelurahan" role="dialog" aria-modal="true" aria-labelledby="modalDeleteKelurahanTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title text-danger" id="modalDeleteKelurahanTitle">Konfirmasi Hapus Kelurahan / Desa</h3>
                <p class="modal-subtitle">Tindakan ini akan menghapus data wilayah kelurahan/desa secara permanen dari sistem</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseDeleteKelurahan" aria-label="Tutup Dialog">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="" id="formDeleteKelurahan">
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
                                Anda akan menghapus data <strong id="deleteKelurahanName">-</strong> (<code id="deleteKelurahanKode" class="text-brand">-</code>). Pastikan tidak ada data sekolah yang masih menautkan kelurahan/desa ini.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelDeleteKelurahan">Batal</button>
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
    // Helper: cascading filter kabupaten berdasarkan provinsi
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

    // Helper: cascading filter kecamatan berdasarkan kabupaten
    function filterKecamatanOptions(kabSelect, kecSelect, selectedKecId = null) {
        if (!kabSelect || !kecSelect) return;
        const kabId = kabSelect.value;
        let hasValidSelection = false;

        Array.from(kecSelect.options).forEach(opt => {
            if (opt.value === "") {
                opt.hidden = false;
                return;
            }
            const optKab = opt.dataset.kabupaten;
            const matches = !kabId || optKab === kabId;
            opt.hidden = !matches;

            if (matches && selectedKecId && opt.value === String(selectedKecId)) {
                opt.selected = true;
                hasValidSelection = true;
            }
        });

        if (!hasValidSelection && kecSelect.selectedOptions.length > 0 && kecSelect.selectedOptions[0].hidden) {
            kecSelect.value = "";
        }
    }

    // 1. Cascading pada Toolbar Filter
    const filterProv = document.getElementById('filter_provinsi');
    const filterKab = document.getElementById('filter_kabupaten');
    const filterKec = document.getElementById('filter_kecamatan');
    const formFilter = document.getElementById('formFilterKelurahan');

    if (filterProv && filterKab && filterKec) {
        filterKabupatenOptions(filterProv, filterKab, '{{ request('kabupaten_id') }}');
        filterKecamatanOptions(filterKab, filterKec, '{{ request('kecamatan_id') }}');

        filterProv.addEventListener('change', function() {
            filterKabupatenOptions(filterProv, filterKab);
            filterKecamatanOptions(filterKab, filterKec);
            if (formFilter) formFilter.submit();
        });

        filterKab.addEventListener('change', function() {
            filterKecamatanOptions(filterKab, filterKec);
            if (formFilter) formFilter.submit();
        });

        filterKec.addEventListener('change', function() {
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

    // Helper pemuatan data kecamatan via AJAX untuk SearchableSelect
    function loadKecamatanOptions(kecInstance, kabId, selectedKecId = null) {
        if (!kecInstance) return Promise.resolve();
        if (!kabId) {
            kecInstance.setOptions([], '');
            kecInstance.setDisabled(true);
            return Promise.resolve();
        }

        kecInstance.setDisabled(false);
        kecInstance.setLoading(true);

        return fetch(`{{ route('wilayah.dropdown.kecamatan') }}?kabupaten_id=${encodeURIComponent(kabId)}`)
            .then(res => res.json())
            .then(data => {
                kecInstance.setOptions(data, selectedKecId);
                kecInstance.setLoading(false);
            })
            .catch(err => {
                console.error('Gagal memuat daftar kecamatan:', err);
                kecInstance.setLoading(false);
            });
    }

    // 2. Create Modal Handlers
    const createModal = document.getElementById('modalCreateKelurahan');
    const btnOpenCreate = document.getElementById('btnOpenCreateKelurahan');
    const btnCloseCreate = document.getElementById('btnCloseCreateKelurahan');
    const btnCancelCreate = document.getElementById('btnCancelCreateKelurahan');
    const createProvSelect = document.getElementById('create_provinsi_id');
    const createKabSelect = document.getElementById('create_kabupaten_id');
    const createKecSelect = document.getElementById('create_kecamatan_id');
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
            const createKecInst = window.SearchableSelect ? window.SearchableSelect.getInstance('create_kecamatan_id') : null;

            if (provId) {
                loadKabupatenOptions(createKabInst, provId, null);
                if (createKecInst) {
                    createKecInst.setOptions([], '');
                    createKecInst.setDisabled(true);
                }
            } else {
                if (createKabInst) {
                    createKabInst.clear(true);
                    createKabInst.setDisabled(true);
                }
                if (createKecInst) {
                    createKecInst.clear(true);
                    createKecInst.setDisabled(true);
                }
                if (createKodeInput && createKodeInput.value.length <= 6) {
                    createKodeInput.value = '';
                }
            }
        });
    }

    if (createKabSelect) {
        createKabSelect.addEventListener('searchable-select:change', function(e) {
            const kabId = e.detail?.value || '';
            const createKecInst = window.SearchableSelect ? window.SearchableSelect.getInstance('create_kecamatan_id') : null;

            if (kabId) {
                loadKecamatanOptions(createKecInst, kabId, null);
            } else {
                if (createKecInst) {
                    createKecInst.clear(true);
                    createKecInst.setDisabled(true);
                }
                if (createKodeInput && createKodeInput.value.length <= 6) {
                    createKodeInput.value = '';
                }
            }
        });
    }

    if (createKecSelect) {
        createKecSelect.addEventListener('searchable-select:change', function(e) {
            const kecKode = e.detail?.extra || e.detail?.item?.kode || '';
            if (kecKode && createKodeInput) {
                const currentVal = createKodeInput.value.trim();
                if (!currentVal || currentVal.length <= 6) {
                    createKodeInput.value = kecKode;
                } else if (!currentVal.startsWith(kecKode)) {
                    createKodeInput.value = kecKode + currentVal.slice(6, 10);
                }
            } else if (!e.detail?.value && createKodeInput) {
                if (createKodeInput.value.length <= 6) {
                    createKodeInput.value = '';
                }
            }
        });
    }

    // 3. Edit Modal Handlers
    const editModal = document.getElementById('modalEditKelurahan');
    const formEdit = document.getElementById('formEditKelurahan');
    const btnCloseEdit = document.getElementById('btnCloseEditKelurahan');
    const btnCancelEdit = document.getElementById('btnCancelEditKelurahan');
    const editProvSelect = document.getElementById('edit_provinsi_id');
    const editKabSelect = document.getElementById('edit_kabupaten_id');
    const editKecSelect = document.getElementById('edit_kecamatan_id');
    const editTipeSelect = document.getElementById('edit_tipe');
    const editKodeInput = document.getElementById('edit_kode');
    const editNamaInput = document.getElementById('edit_nama');
    const editKodePosInput = document.getElementById('edit_kode_pos');
    const editStatusCheckbox = document.getElementById('edit_status');

    function openEditModal(kelurahanData, actionUrl) {
        if (!editModal || !formEdit) return;

        formEdit.action = actionUrl;

        const editProvInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_provinsi_id') : null;
        const editKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kabupaten_id') : null;
        const editKecInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kecamatan_id') : null;

        if (editProvInst) {
            editProvInst.setValue(kelurahanData.provinsi_id || '', '', '', false);
        }

        if (kelurahanData.provinsi_id && editKabInst) {
            loadKabupatenOptions(editKabInst, kelurahanData.provinsi_id, kelurahanData.kabupaten_id)
                .then(() => {
                    if (kelurahanData.kabupaten_id && editKecInst) {
                        return loadKecamatanOptions(editKecInst, kelurahanData.kabupaten_id, kelurahanData.kecamatan_id);
                    } else if (editKecInst) {
                        editKecInst.setOptions([], '');
                        editKecInst.setDisabled(true);
                    }
                });
        } else {
            if (editKabInst) { editKabInst.setOptions([], ''); editKabInst.setDisabled(true); }
            if (editKecInst) { editKecInst.setOptions([], ''); editKecInst.setDisabled(true); }
        }

        if (editTipeSelect) {
            editTipeSelect.value = kelurahanData.tipe || 'Kelurahan';
        }

        if (editKodeInput) {
            editKodeInput.value = kelurahanData.kode || '';
        }

        if (editNamaInput) {
            editNamaInput.value = kelurahanData.nama || '';
        }

        if (editKodePosInput) {
            editKodePosInput.value = kelurahanData.kode_pos || '';
        }

        if (editStatusCheckbox) {
            editStatusCheckbox.checked = Boolean(kelurahanData.status);
        }

        editModal.classList.remove('hidden');
        document.body.classList.add('modal-open');
        setTimeout(() => editNamaInput && editNamaInput.focus(), 50);
    }

    function closeEditModal() {
        if (!editModal) return;
        editModal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    document.querySelectorAll('.btn-open-edit-kelurahan').forEach(btn => {
        btn.addEventListener('click', function() {
            const rawData = this.dataset.kelurahan;
            const actionUrl = this.dataset.action;
            if (rawData && actionUrl) {
                try {
                    const kelurahanData = JSON.parse(rawData);
                    openEditModal(kelurahanData, actionUrl);
                } catch (err) {
                    console.error('Gagal parsing data kelurahan:', err);
                }
            }
        });
    });

    btnCloseEdit && btnCloseEdit.addEventListener('click', closeEditModal);
    btnCancelEdit && btnCancelEdit.addEventListener('click', closeEditModal);

    editModal && editModal.addEventListener('click', function(e) {
        if (e.target === editModal) closeEditModal();
    });

    if (editProvSelect) {
        editProvSelect.addEventListener('searchable-select:change', function(e) {
            const provId = e.detail?.value || '';
            const editKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kabupaten_id') : null;
            const editKecInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kecamatan_id') : null;

            if (provId) {
                loadKabupatenOptions(editKabInst, provId, null);
                if (editKecInst) {
                    editKecInst.setOptions([], '');
                    editKecInst.setDisabled(true);
                }
            } else {
                if (editKabInst) {
                    editKabInst.clear(true);
                    editKabInst.setDisabled(true);
                }
                if (editKecInst) {
                    editKecInst.clear(true);
                    editKecInst.setDisabled(true);
                }
                if (editKodeInput && editKodeInput.value.length <= 6) {
                    editKodeInput.value = '';
                }
            }
        });
    }

    if (editKabSelect) {
        editKabSelect.addEventListener('searchable-select:change', function(e) {
            const kabId = e.detail?.value || '';
            const editKecInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kecamatan_id') : null;

            if (kabId) {
                loadKecamatanOptions(editKecInst, kabId, null);
            } else {
                if (editKecInst) {
                    editKecInst.clear(true);
                    editKecInst.setDisabled(true);
                }
                if (editKodeInput && editKodeInput.value.length <= 6) {
                    editKodeInput.value = '';
                }
            }
        });
    }

    if (editKecSelect) {
        editKecSelect.addEventListener('searchable-select:change', function(e) {
            const kecKode = e.detail?.extra || e.detail?.item?.kode || '';
            if (kecKode && editKodeInput) {
                const currentVal = editKodeInput.value.trim();
                if (!currentVal || currentVal.length <= 6) {
                    editKodeInput.value = kecKode;
                } else if (!currentVal.startsWith(kecKode)) {
                    editKodeInput.value = kecKode + currentVal.slice(6, 10);
                }
            }
        });
    }

    // 4. Delete Modal Handlers
    const deleteModal = document.getElementById('modalDeleteKelurahan');
    const formDelete = document.getElementById('formDeleteKelurahan');
    const deleteNameSpan = document.getElementById('deleteKelurahanName');
    const deleteKodeSpan = document.getElementById('deleteKodeSpan');
    const btnCloseDelete = document.getElementById('btnCloseDeleteKelurahan');
    const btnCancelDelete = document.getElementById('btnCancelDeleteKelurahan');

    function openDeleteModal(name, kode, actionUrl) {
        if (!deleteModal || !formDelete) return;
        formDelete.action = actionUrl;
        if (deleteNameSpan) deleteNameSpan.textContent = name;
        const kodeEl = document.getElementById('deleteKelurahanKode');
        if (kodeEl) kodeEl.textContent = kode;
        deleteModal.classList.remove('hidden');
        document.body.classList.add('modal-open');
    }

    function closeDeleteModal() {
        if (!deleteModal) return;
        deleteModal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    document.querySelectorAll('.btn-open-delete-kelurahan').forEach(btn => {
        btn.addEventListener('click', function() {
            const name = this.dataset.name || '-';
            const kode = this.dataset.kode || '-';
            const action = this.dataset.action;
            if (action) {
                openDeleteModal(name, kode, action);
            }
        });
    });

    btnCloseDelete && btnCloseDelete.addEventListener('click', closeDeleteModal);
    btnCancelDelete && btnCancelDelete.addEventListener('click', closeDeleteModal);

    deleteModal && deleteModal.addEventListener('click', function(e) {
        if (e.target === deleteModal) closeDeleteModal();
    });

    // 5. Global Escape Key Listener untuk Menutup Modal Aktif
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
            closeDeleteModal();
        }
    });

    // Otomatis buka modal create bila terdapat validation error saat store
    @if ($errors->any())
        @if (old('_method') === 'PUT')
            @if (old('provinsi_id'))
                (function() {
                    const editKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kabupaten_id') : null;
                    const editKecInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kecamatan_id') : null;
                    loadKabupatenOptions(editKabInst, '{{ old('provinsi_id') }}', '{{ old('kabupaten_id') }}')
                        .then(() => {
                            @if (old('kabupaten_id'))
                                return loadKecamatanOptions(editKecInst, '{{ old('kabupaten_id') }}', '{{ old('kecamatan_id') }}');
                            @endif
                        });
                })();
            @endif
        @else
            openCreateModal();
            @if (old('provinsi_id'))
                (function() {
                    const createKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('create_kabupaten_id') : null;
                    const createKecInst = window.SearchableSelect ? window.SearchableSelect.getInstance('create_kecamatan_id') : null;
                    loadKabupatenOptions(createKabInst, '{{ old('provinsi_id') }}', '{{ old('kabupaten_id') }}')
                        .then(() => {
                            @if (old('kabupaten_id'))
                                return loadKecamatanOptions(createKecInst, '{{ old('kabupaten_id') }}', '{{ old('kecamatan_id') }}');
                            @endif
                        });
                })();
            @endif
        @endif
    @endif
});
</script>
@endsection
