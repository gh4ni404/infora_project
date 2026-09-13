@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Master Data Jurusan</h2>
        <div class="page-subtitle">
            Kelola data konsentrasi keahlian (SMK) dan peminatan (SMA) per unit sekolah
            @if ($selectedSchool)
                &ndash; <strong class="text-primary">{{ $selectedSchool->name }}</strong>
                <span class="badge {{ $selectedSchool->school_type === 'SMK' ? 'badge-primary' : 'badge-purple' }}">{{ $selectedSchool->school_type }}</span>
            @endif
        </div>
    </div>
    <div class="page-actions">
        {{-- Selector Unit Sekolah --}}
        @if ($schools->count() > 1)
            <form method="GET" action="{{ route('master.data-jurusan') }}" id="schoolFilterForm">
                <select name="school_id" class="form-select-sm" onchange="this.form.submit()" title="Pilih Unit Sekolah">
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}" {{ $selectedSchoolId === $school->id ? 'selected' : '' }}>
                            {{ $school->name }} ({{ $school->school_type }})
                        </option>
                    @endforeach
                </select>
            </form>
        @endif

        <button type="button" class="btn-primary" id="btnOpenCreateJurusan">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Tambah Jurusan</span>
        </button>
    </div>
</div>

{{-- Flash Alerts --}}
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

{{-- Kartu Metrik Ringkasan (KPI Cards) --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon is-primary">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Total Jurusan</span>
            <span class="stat-value">{{ number_format($totalCount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon is-success">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Jurusan Aktif</span>
            <span class="stat-value">{{ number_format($activeCount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon is-amber">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Non-Aktif</span>
            <span class="stat-value">{{ number_format($inactiveCount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon is-cyan">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Bidang Keahlian</span>
            <span class="stat-value">{{ number_format($bidangCount, 0, ',', '.') }}</span>
        </div>
    </div>
</div>

{{-- Tabel Data Jurusan --}}
<div class="table-card">
    <div class="table-toolbar">
        <form method="GET" action="{{ route('master.data-jurusan') }}" class="table-toolbar-form">
            <input type="hidden" name="school_id" value="{{ $selectedSchoolId }}">

            <div class="page-actions">
                <div class="per-page-selector">
                    <label for="table_per_page" class="table-cell-muted">Tampilkan:</label>
                    <select id="table_per_page" name="per_page" class="form-select-sm" onchange="this.form.submit()">
                        <option value="15" {{ $perPageInput === '15' ? 'selected' : '' }}>15</option>
                        <option value="30" {{ $perPageInput === '30' ? 'selected' : '' }}>30</option>
                        <option value="90" {{ $perPageInput === '90' ? 'selected' : '' }}>90</option>
                        <option value="semua" {{ in_array($perPageInput, ['semua', 'all'], true) ? 'selected' : '' }}>Semua</option>
                    </select>
                    <span class="table-cell-muted">data</span>
                </div>

                <select name="status" class="form-select-sm" onchange="this.form.submit()" title="Filter Status">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Status Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Status Nonaktif</option>
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
                    placeholder="Cari kode, nama jurusan, bidang, atau kaprog..."
                    value="{{ request('search') }}"
                >
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-w-idx">No</th>
                    <th>Kode &amp; Inisial</th>
                    <th>Nama Jurusan &amp; Jenjang</th>
                    <th>Bidang &amp; Program Keahlian</th>
                    <th>Kepala Jurusan (Kaprog)</th>
                    <th>Status</th>
                    <th class="col-w-actions">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jurusans as $index => $jurusan)
                    <tr>
                        <td class="col-w-idx text-center">
                            {{ $jurusans->firstItem() + $index }}
                        </td>
                        <td>
                            <span class="badge badge-blue">{{ $jurusan->kode }}</span>
                            @if ($jurusan->singkatan && $jurusan->singkatan !== $jurusan->kode)
                                <div class="table-cell-muted">{{ $jurusan->singkatan }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="table-cell-title">{{ $jurusan->nama }}</div>
                            <span class="badge {{ $jurusan->jenjang === 'SMK' ? 'badge-primary' : 'badge-purple' }}">
                                {{ $jurusan->jenjang }}
                            </span>
                        </td>
                        <td>
                            @if ($jurusan->bidang_keahlian || $jurusan->program_keahlian)
                                <div class="table-cell-title">{{ $jurusan->bidang_keahlian ?? '-' }}</div>
                                <div class="table-cell-subtitle">{{ $jurusan->program_keahlian ?? '-' }}</div>
                            @else
                                <span class="table-cell-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if ($jurusan->kepala_jurusan)
                                <span class="table-cell-title">{{ $jurusan->kepala_jurusan }}</span>
                            @else
                                <span class="table-cell-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if ($jurusan->is_active)
                                <span class="badge badge-emerald">Aktif</span>
                            @else
                                <span class="badge badge-rose">Nonaktif</span>
                            @endif
                        </td>
                        <td class="col-w-actions">
                            <div class="table-actions table-actions-right">
                                {{-- Quick Toggle Status --}}
                                <form method="POST" action="{{ route('master.data-jurusan.toggle-status', $jurusan) }}" class="form-inline-action">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="btn-icon"
                                        title="{{ $jurusan->is_active ? 'Nonaktifkan status jurusan' : 'Aktifkan status jurusan' }}"
                                    >
                                        @if ($jurusan->is_active)
                                            <svg class="text-success" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path>
                                                <line x1="12" y1="2" x2="12" y2="12"></line>
                                            </svg>
                                        @else
                                            <svg class="text-muted" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path>
                                                <line x1="12" y1="2" x2="12" y2="12"></line>
                                            </svg>
                                        @endif
                                    </button>
                                </form>

                                {{-- Ubah Data (Edit Modal) --}}
                                <button
                                    type="button"
                                    class="btn-edit btn-open-edit-jurusan"
                                    title="Ubah Data Jurusan"
                                    data-jurusan="{{ json_encode($jurusan) }}"
                                    data-action="{{ route('master.data-jurusan.update', $jurusan) }}"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                    </svg>
                                </button>

                                {{-- Hapus Data (Delete Modal) --}}
                                <button
                                    type="button"
                                    class="btn-delete btn-open-delete-jurusan"
                                    title="Hapus Data Jurusan"
                                    data-id="{{ $jurusan->id }}"
                                    data-kode="{{ $jurusan->kode }}"
                                    data-nama="{{ $jurusan->nama }}"
                                    data-action="{{ route('master.data-jurusan.destroy', $jurusan) }}"
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
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="empty-state-title">Belum Ada Data Jurusan</h3>
                                <p class="empty-state-text">
                                    @if (request()->filled('search') || request()->filled('status'))
                                        Tidak ada data jurusan yang cocok dengan kriteria filter pencarian Anda.
                                    @else
                                        Belum terdapat data jurusan yang terdaftar pada unit sekolah ini. Silakan klik tombol di bawah untuk menambahkan.
                                    @endif
                                </p>
                                @if (request()->filled('search') || request()->filled('status'))
                                    <a href="{{ route('master.data-jurusan', ['school_id' => $selectedSchoolId]) }}" class="btn-secondary">
                                        Reset Filter Pencarian
                                    </a>
                                @else
                                    <!-- <button type="button" class="btn-primary" id="btnEmptyCreateJurusan">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        <span>Tambah Jurusan Baru</span>
                                    </button> -->
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginasi Tabel --}}
    @if ($jurusans->hasPages() || $jurusans->total() > 0)
        <div class="table-footer">
            <div class="pagination-wrapper">
                <div class="pagination-summary">
                    Menampilkan <strong>{{ $jurusans->firstItem() ?? 0 }}</strong> - <strong>{{ $jurusans->lastItem() ?? 0 }}</strong> dari <strong>{{ $jurusans->total() }}</strong> jurusan
                </div>
                @if ($jurusans->hasPages())
                    <div class="pagination-nav">
                        {{ $jurusans->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

{{-- Komponen Dialog Modals --}}
@include('master.data-jurusan.modal-create')
@include('master.data-jurusan.modal-edit')
@include('master.data-jurusan.modal-delete')

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Elemen Modal Tambah
    const modalCreate = document.getElementById('modalCreateJurusan');
    const btnOpenCreate = document.getElementById('btnOpenCreateJurusan');
    const btnEmptyCreate = document.getElementById('btnEmptyCreateJurusan');
    const btnCloseCreate = document.getElementById('btnCloseCreateJurusan');
    const btnCancelCreate = document.getElementById('btnCancelCreateJurusan');

    function openCreateModal() {
        if (modalCreate) {
            modalCreate.classList.remove('hidden');
            const firstInput = document.getElementById('create_kode');
            if (firstInput) firstInput.focus();
        }
    }

    function closeCreateModal() {
        if (modalCreate) {
            modalCreate.classList.add('hidden');
        }
    }

    if (btnOpenCreate) btnOpenCreate.addEventListener('click', openCreateModal);
    if (btnEmptyCreate) btnEmptyCreate.addEventListener('click', openCreateModal);
    if (btnCloseCreate) btnCloseCreate.addEventListener('click', closeCreateModal);
    if (btnCancelCreate) btnCancelCreate.addEventListener('click', closeCreateModal);

    // 2. Elemen Modal Edit
    const modalEdit = document.getElementById('modalEditJurusan');
    const formEdit = document.getElementById('formEditJurusan');
    const btnCloseEdit = document.getElementById('btnCloseEditJurusan');
    const btnCancelEdit = document.getElementById('btnCancelEditJurusan');
    const editBtns = document.querySelectorAll('.btn-open-edit-jurusan');

    function closeEditModal() {
        if (modalEdit) {
            modalEdit.classList.add('hidden');
        }
    }

    if (btnCloseEdit) btnCloseEdit.addEventListener('click', closeEditModal);
    if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEditModal);

    editBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const rawData = this.getAttribute('data-jurusan');
            const actionUrl = this.getAttribute('data-action');
            if (!rawData || !formEdit || !modalEdit) return;

            try {
                const jurusan = JSON.parse(rawData);
                formEdit.action = actionUrl;

                document.getElementById('edit_jenjang').value = jurusan.jenjang || 'SMK';
                document.getElementById('edit_kode').value = jurusan.kode || '';
                document.getElementById('edit_singkatan').value = jurusan.singkatan || '';
                document.getElementById('edit_nama').value = jurusan.nama || '';
                document.getElementById('edit_bidang_keahlian').value = jurusan.bidang_keahlian || '';
                document.getElementById('edit_program_keahlian').value = jurusan.program_keahlian || '';
                document.getElementById('edit_kepala_jurusan').value = jurusan.kepala_jurusan || '';
                document.getElementById('edit_deskripsi').value = jurusan.deskripsi || '';
                document.getElementById('edit_is_active').checked = Boolean(jurusan.is_active);

                modalEdit.classList.remove('hidden');
                document.getElementById('edit_nama').focus();
            } catch (e) {
                console.error('Gagal memproses data jurusan:', e);
            }
        });
    });

    // 3. Elemen Modal Konfirmasi Hapus
    const modalDelete = document.getElementById('modalDeleteJurusan');
    const formDelete = document.getElementById('formDeleteJurusan');
    const deleteNama = document.getElementById('deleteJurusanNama');
    const deleteKode = document.getElementById('deleteJurusanKode');
    const btnCloseDelete = document.getElementById('btnCloseDeleteJurusan');
    const btnCancelDelete = document.getElementById('btnCancelDeleteJurusan');
    const deleteBtns = document.querySelectorAll('.btn-open-delete-jurusan');

    function closeDeleteModal() {
        if (modalDelete) {
            modalDelete.classList.add('hidden');
        }
    }

    if (btnCloseDelete) btnCloseDelete.addEventListener('click', closeDeleteModal);
    if (btnCancelDelete) btnCancelDelete.addEventListener('click', closeDeleteModal);

    deleteBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const actionUrl = this.getAttribute('data-action');
            const nama = this.getAttribute('data-nama');
            const kode = this.getAttribute('data-kode');

            if (!formDelete || !modalDelete) return;

            formDelete.action = actionUrl;
            if (deleteNama) deleteNama.textContent = nama || '-';
            if (deleteKode) deleteKode.textContent = kode || '-';

            modalDelete.classList.remove('hidden');
        });
    });

    // 4. Tutup modal ketika tombol Escape ditekan atau klik backdrop luar
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
            closeDeleteModal();
        }
    });

    [modalCreate, modalEdit, modalDelete].forEach(function (modal) {
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        }
    });
});
</script>
@endsection
