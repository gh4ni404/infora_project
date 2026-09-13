@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Tahun Ajaran & Semester</h2>
        <div class="page-subtitle">
            Konfigurasi tahun ajaran dan semester aktif untuk seluruh modul operasional sekolah
            @if ($selectedSchool)
                &ndash; <strong class="text-primary">{{ $selectedSchool->name }}</strong>
            @endif
        </div>
    </div>
    <div class="page-actions">
        {{-- Selector Unit Sekolah --}}
        @if ($schools->count() > 1)
            <form method="GET" action="{{ route('konfigurasi-sekolah.tahun-ajaran') }}" id="schoolFilterForm">
                <select name="school_id" class="form-select-sm" onchange="this.form.submit()" title="Pilih Unit Sekolah">
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}" {{ $selectedSchoolId === $school->id ? 'selected' : '' }}>
                            {{ $school->name }} ({{ $school->school_type }})
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
    </div>
</div>

{{-- Flash Messages --}}
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

{{-- Status Banner Semester Aktif Global --}}
@if ($activeSemester)
    <div class="alert-info">
        <div class="alert-content">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <div>
                <strong>Semester Aktif Saat Ini:</strong>
                {{ $activeSemester->nama_lengkap }}
                &bull; Rentang KBM: <strong>{{ $activeSemester->rentang_tanggal_formatted }}</strong>
                &bull; Unit: <strong>{{ $selectedSchool?->name }}</strong>
            </div>
        </div>
        <span class="badge badge-success">Aktif</span>
    </div>
@else
    <div class="alert-info">
        <div class="alert-content">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <div>
                <strong>Belum Ada Semester Aktif:</strong>
                Unit sekolah ini belum memiliki semester yang aktif. Silakan pilih salah satu semester pada tabel di bawah lalu klik tombol <strong>Jadikan Aktif</strong>.
            </div>
        </div>
        <span class="badge badge-warning">Belum Aktif</span>
    </div>
@endif

{{-- ========================================================================
     TAMPILAN SPLIT KANAN - KIRI (MASTER-DETAIL DUA TABEL BERDAMPINGAN)
     ======================================================================== --}}
<div class="grid-split-1-1">
    {{-- ========================================================================
         KOLOM KIRI (MASTER): TABEL TAHUN AJARAN
         ======================================================================== --}}
    <div class="table-card" id="cardTahunAjaranMaster">
        <div class="table-toolbar">
            <div class="page-actions">
                <span class="table-cell-bold">Daftar Tahun Ajaran</span>
                <span class="table-cell-muted">&bull; {{ $tahunAjaranList->count() }} Data</span>
            </div>
            <div class="page-actions">
                <button type="button" class="btn-primary" id="btnOpenCreateTahunAjaran" title="Tambah Tahun Ajaran Baru">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Tambah</span>
                </button>
            </div>
        </div>

        <div class="table-responsive table-responsive-scroll">
            <table class="data-table" id="tableTahunAjaran">
                <thead>
                    <tr>
                        <th class="table-cell-id">No</th>
                        <th>Tahun Ajaran</th>
                        <th>Status</th>
                        <th>Semester</th>
                        <th class="table-actions-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tahunAjaranList as $ta)
                        @php
                            $isSelected = (int) $selectedTahunAjaranId === (int) $ta->id;
                        @endphp
                        <tr class="{{ $isSelected ? 'is-selected' : '' }}" id="row-ta-{{ $ta->id }}">
                            <td class="table-cell-id">{{ $loop->iteration }}</td>
                            <td>
                                <span class="table-cell-bold">{{ $ta->tahun }}</span>
                                @if ($isSelected)
                                    <span class="badge badge-primary">Terpilih</span>
                                @endif
                                @if ($ta->keterangan)
                                    <div class="form-hint">{{ Str::limit($ta->keterangan, 35) }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($ta->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-neutral">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <span class="table-cell-muted">{{ $ta->semesters_count }} Sem</span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    {{-- Tombol Pilih/Kelola Semester --}}
                                    <a
                                        href="{{ route('konfigurasi-sekolah.tahun-ajaran', ['school_id' => $selectedSchoolId, 'tahun_ajaran_id' => $ta->id]) }}"
                                        class="btn-activate {{ $isSelected ? 'is-disabled' : '' }}"
                                        title="{{ $isSelected ? 'Sedang aktif dipilih di tabel kanan' : 'Pilih tahun ajaran ini untuk mengelola semester di sebelah kanan' }}"
                                    >
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                        <span>{{ $isSelected ? 'Terpilih' : 'Pilih' }}</span>
                                    </a>

                                    {{-- Tombol Ubah Tahun Ajaran --}}
                                    <button
                                        type="button"
                                        class="btn-edit btn-edit-ta"
                                        data-id="{{ $ta->id }}"
                                        data-tahun="{{ $ta->tahun }}"
                                        data-is-active="{{ $ta->is_active ? '1' : '0' }}"
                                        data-keterangan="{{ $ta->keterangan }}"
                                        data-action="{{ route('konfigurasi-sekolah.tahun-ajaran.update', $ta) }}"
                                        title="Ubah Tahun Ajaran {{ $ta->tahun }}"
                                    >
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                        <span>Ubah</span>
                                    </button>

                                    {{-- Tombol Hapus Tahun Ajaran --}}
                                    @if (! $ta->is_active && ! $ta->hasActiveSemester())
                                        <form
                                            method="POST"
                                            action="{{ route('konfigurasi-sekolah.tahun-ajaran.destroy', $ta) }}"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus Tahun Ajaran {{ $ta->tahun }} beserta semesternya? Tindakan ini tidak dapat dibatalkan.');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete" title="Hapus Tahun Ajaran {{ $ta->tahun }}">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <svg class="empty-state-icon" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <div class="empty-state-text">Belum ada data Tahun Ajaran untuk unit sekolah ini.</div>
                                    <!-- <button type="button" class="btn-primary" onclick="document.getElementById('btnOpenCreateTahunAjaran').click()">
                                        <span>Tambah Tahun Ajaran</span>
                                    </button> -->
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================================================
         KOLOM KANAN (DETAIL): TABEL SEMESTER UNTUK TAHUN AJARAN TERPILIH
         ======================================================================== --}}
    <div class="table-card" id="cardSemesterDetail">
        <div class="table-toolbar">
            <div class="page-actions">
                <span class="table-cell-bold">Daftar Semester</span>
                @if ($selectedTahunAjaran)
                    <span class="table-cell-muted">&bull; Tahun: <strong class="text-primary">{{ $selectedTahunAjaran->tahun }}</strong></span>
                @else
                    <span class="table-cell-muted">&bull; Pilih Tahun Ajaran di Kiri</span>
                @endif
            </div>
            <div class="page-actions">
                <button
                    type="button"
                    class="btn-primary"
                    id="btnOpenCreateSemester"
                    {{ $selectedTahunAjaran ? '' : 'disabled' }}
                    title="{{ $selectedTahunAjaran ? 'Tambah Semester Baru' : 'Pilih Tahun Ajaran terlebih dahulu' }}"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Tambah</span>
                </button>
            </div>
        </div>

        <div class="table-responsive table-responsive-scroll">
            <table class="data-table" id="tableSemester">
                <thead>
                    <tr>
                        <th class="table-cell-id">No</th>
                        <th>Semester</th>
                        <th>Rentang Pelaksanaan</th>
                        <th>Status</th>
                        <th class="table-actions-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @if (! $selectedTahunAjaran)
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <svg class="empty-state-icon" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    <div class="empty-state-text">Belum ada Tahun Ajaran yang dipilih. Silakan pilih atau buat Tahun Ajaran pada tabel di sebelah kiri.</div>
                                </div>
                            </td>
                        </tr>
                    @else
                        @forelse ($semesters as $sem)
                            <tr id="row-sem-{{ $sem->id }}">
                                <td class="table-cell-id">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="table-cell-bold">{{ $sem->label_semester }}</span>
                                    <div class="form-hint">{{ $selectedTahunAjaran->tahun }}</div>
                                </td>
                                <td>
                                    <span class="table-cell-muted">{{ $sem->rentang_tanggal_formatted }}</span>
                                </td>
                                <td>
                                    @if ($sem->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-neutral">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions">
                                        {{-- Tombol Switch Jadikan Aktif --}}
                                        @if (! $sem->is_active)
                                            <form
                                                method="POST"
                                                action="{{ route('konfigurasi-sekolah.tahun-ajaran.semester.activate', [$selectedTahunAjaran, $sem]) }}"
                                                onsubmit="return confirm('Tetapkan Semester {{ $sem->label_semester }} ({{ $selectedTahunAjaran->tahun }}) sebagai Semester Aktif? Semester aktif lain pada sekolah ini akan dinonaktifkan.');"
                                            >
                                                @csrf
                                                <button type="submit" class="btn-activate" title="Jadikan semester ini sebagai Semester Aktif tunggal">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="20 6 9 17 4 12"></polyline>
                                                    </svg>
                                                    <span>Aktifkan</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge badge-primary">Aktif</span>
                                        @endif

                                        {{-- Tombol Ubah Semester --}}
                                        <button
                                            type="button"
                                            class="btn-edit btn-edit-sem"
                                            data-id="{{ $sem->id }}"
                                            data-semester="{{ $sem->semester }}"
                                            data-tanggal-mulai="{{ $sem->tanggal_mulai?->format('Y-m-d') }}"
                                            data-tanggal-selesai="{{ $sem->tanggal_selesai?->format('Y-m-d') }}"
                                            data-is-active="{{ $sem->is_active ? '1' : '0' }}"
                                            data-keterangan="{{ $sem->keterangan }}"
                                            data-action="{{ route('konfigurasi-sekolah.tahun-ajaran.semester.update', [$selectedTahunAjaran, $sem]) }}"
                                            title="Ubah Semester {{ $sem->label_semester }}"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                            <span>Ubah</span>
                                        </button>

                                        {{-- Tombol Hapus Semester --}}
                                        @if (! $sem->is_active)
                                            <form
                                                method="POST"
                                                action="{{ route('konfigurasi-sekolah.tahun-ajaran.semester.destroy', [$selectedTahunAjaran, $sem]) }}"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus Semester {{ $sem->label_semester }} dari Tahun Ajaran {{ $selectedTahunAjaran->tahun }}?');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-delete" title="Hapus Semester {{ $sem->label_semester }}">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <svg class="empty-state-icon" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        <div class="empty-state-text">Belum ada semester terdaftar untuk tahun ajaran {{ $selectedTahunAjaran->tahun }}.</div>
                                        <button type="button" class="btn-primary" onclick="document.getElementById('btnOpenCreateSemester').click()">
                                            <span>Tambah Semester</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modals Tambah & Ubah --}}
@include('konfigurasi-sekolah.tahun-ajaran.modal-tahun-ajaran')
@include('konfigurasi-sekolah.tahun-ajaran.modal-semester')

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Utility Helper Modal Open & Close
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.classList.add('modal-open');
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('modal-open');
        }
    }

    // Modal Create Tahun Ajaran
    const btnOpenCreateTa = document.getElementById('btnOpenCreateTahunAjaran');
    const btnCloseCreateTa = document.getElementById('btnCloseCreateTahunAjaran');
    const btnCancelCreateTa = document.getElementById('btnCancelCreateTahunAjaran');
    const modalCreateTa = document.getElementById('modalCreateTahunAjaran');

    if (btnOpenCreateTa) {
        btnOpenCreateTa.addEventListener('click', function () {
            openModal('modalCreateTahunAjaran');
            const inputTahun = document.getElementById('create_tahun');
            if (inputTahun) {
                setTimeout(() => inputTahun.focus(), 100);
            }
        });
    }
    if (btnCloseCreateTa) btnCloseCreateTa.addEventListener('click', () => closeModal('modalCreateTahunAjaran'));
    if (btnCancelCreateTa) btnCancelCreateTa.addEventListener('click', () => closeModal('modalCreateTahunAjaran'));

    // Modal Edit Tahun Ajaran
    const btnCloseEditTa = document.getElementById('btnCloseEditTahunAjaran');
    const btnCancelEditTa = document.getElementById('btnCancelEditTahunAjaran');
    const formEditTa = document.getElementById('formEditTahunAjaran');

    document.querySelectorAll('.btn-edit-ta').forEach(function (button) {
        button.addEventListener('click', function () {
            const data = this.dataset;
            if (formEditTa) {
                formEditTa.action = data.action;
                const inputTahun = document.getElementById('edit_ta_tahun');
                const inputKeterangan = document.getElementById('edit_ta_keterangan');
                const inputIsActive = document.getElementById('edit_ta_is_active');

                if (inputTahun) inputTahun.value = data.tahun || '';
                if (inputKeterangan) inputKeterangan.value = data.keterangan || '';
                if (inputIsActive) inputIsActive.checked = data.isActive === '1';

                openModal('modalEditTahunAjaran');
            }
        });
    });

    if (btnCloseEditTa) btnCloseEditTa.addEventListener('click', () => closeModal('modalEditTahunAjaran'));
    if (btnCancelEditTa) btnCancelEditTa.addEventListener('click', () => closeModal('modalEditTahunAjaran'));

    // Modal Create Semester
    const btnOpenCreateSem = document.getElementById('btnOpenCreateSemester');
    const btnCloseCreateSem = document.getElementById('btnCloseCreateSemester');
    const btnCancelCreateSem = document.getElementById('btnCancelCreateSemester');

    if (btnOpenCreateSem) {
        btnOpenCreateSem.addEventListener('click', function () {
            if (!this.disabled) {
                openModal('modalCreateSemester');
            }
        });
    }
    if (btnCloseCreateSem) btnCloseCreateSem.addEventListener('click', () => closeModal('modalCreateSemester'));
    if (btnCancelCreateSem) btnCancelCreateSem.addEventListener('click', () => closeModal('modalCreateSemester'));

    // Modal Edit Semester
    const btnCloseEditSem = document.getElementById('btnCloseEditSemester');
    const btnCancelEditSem = document.getElementById('btnCancelEditSemester');
    const formEditSem = document.getElementById('formEditSemester');

    document.querySelectorAll('.btn-edit-sem').forEach(function (button) {
        button.addEventListener('click', function () {
            const data = this.dataset;
            if (formEditSem) {
                formEditSem.action = data.action;
                const inputSem = document.getElementById('edit_sem_type');
                const inputMulai = document.getElementById('edit_sem_tanggal_mulai');
                const inputSelesai = document.getElementById('edit_sem_tanggal_selesai');
                const inputKeterangan = document.getElementById('edit_sem_keterangan');
                const inputIsActive = document.getElementById('edit_sem_is_active');

                if (inputSem) inputSem.value = data.semester || 'ganjil';
                if (inputMulai) inputMulai.value = data.tanggalMulai || '';
                if (inputSelesai) inputSelesai.value = data.tanggalSelesai || '';
                if (inputKeterangan) inputKeterangan.value = data.keterangan || '';
                if (inputIsActive) inputIsActive.checked = data.isActive === '1';

                openModal('modalEditSemester');
            }
        });
    });

    if (btnCloseEditSem) btnCloseEditSem.addEventListener('click', () => closeModal('modalEditSemester'));
    if (btnCancelEditSem) btnCancelEditSem.addEventListener('click', () => closeModal('modalEditSemester'));

    // Global Overlay & Escape Close
    const allModals = ['modalCreateTahunAjaran', 'modalEditTahunAjaran', 'modalCreateSemester', 'modalEditSemester'];
    allModals.forEach(function (id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeModal(id);
                }
            });
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            allModals.forEach(closeModal);
        }
    });

    // Auto-masking format Tahun Ajaran (YYYY/YYYY)
    function attachYearMask(inputElement) {
        if (!inputElement) return;
        inputElement.addEventListener('input', function (e) {
            let val = this.value.replace(/[^\d/]/g, '');
            if (val.length === 4 && !val.includes('/') && e.inputType !== 'deleteContentBackward') {
                val = val + '/';
            }
            this.value = val;
        });
    }

    attachYearMask(document.getElementById('create_tahun'));
    attachYearMask(document.getElementById('edit_ta_tahun'));
});
</script>
@endsection
