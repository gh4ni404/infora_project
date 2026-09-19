@extends('layouts.app')

@section('content')
<x-page-header title="Tahun Ajaran dan Semester">
    <x-slot:subtitle>
        Konfigurasi tahun ajaran dan semester aktif untuk seluruh modul operasional sekolah
        @if ($selectedSchool)
            &ndash; <strong class="text-primary">{{ $selectedSchool->name }}</strong>
        @endif
    </x-slot:subtitle>
    <x-slot:actions>
        @if ($schools->count() > 1)
            <form action="{{ route('konfigurasi-sekolah.tahun-ajaran') }}" id="schoolFilterForm" method="GET">
                <x-form-select 
                    name="school_id" 
                    id="school_id" 
                    :selected="$selectedSchoolId" 
                    :options="$schools->mapWithKeys(fn($school) => [$school->id => $school->name . ' (' . $school->school_type . ')'])"
                    onchange="this.form.submit()" />
            </form>
        @endif
    </x-slot:actions>
</x-page-header>

{{-- Flash Alerts --}}
<x-alert type="success" />
<x-alert type="error" />
<x-alert type="validation" />

{{-- Status Banner Semester Aktif Global --}}
@if ($activeSemester)
    <div class="alert-info">
        <div class="alert-content">
            <x-icon name="info-circle" size="20" />
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
            <x-icon name="info-circle" size="20" />
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
    <x-data-table
        id="cardTahunAjaranMaster"
        title="Daftar Tahun Ajaran"
        :total="$tahunAjaranList->count()"
        total-label="Data"
    >
        <x-slot:actions>
            <button type="button" class="btn-primary" id="btnOpenCreateTahunAjaran" title="Tambah Tahun Ajaran Baru">
                <x-icon name="add" size="16" />
                {{-- <span>Tambah</span> --}}
            </button>
        </x-slot:actions>

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
                        <x-table-actions align="right">
                            {{-- Tombol Pilih/Kelola Semester --}}
                            <a
                                href="{{ route('konfigurasi-sekolah.tahun-ajaran', ['school_id' => $selectedSchoolId, 'tahun_ajaran_id' => $ta->id]) }}"
                                class="btn-activate {{ $isSelected ? 'is-disabled' : '' }}"
                                title="{{ $isSelected ? 'Sedang aktif dipilih di tabel kanan' : 'Pilih tahun ajaran ini untuk mengelola semester di sebelah kanan' }}"
                            >
                                <x-icon name="chevron-right" size="14" />
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
                                <x-icon name="square-pen" size="14" />
                                {{-- <span>Ubah</span> --}}
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
                                        <x-icon name="delete" size="14" />
                                        {{-- <span>Hapus</span> --}}
                                    </button>
                                </form>
                            @endif
                        </x-table-actions>
                    </td>
                </tr>
            @empty
                <x-empty-state
                    icon="calendar"
                    :colspan="5"
                    :text="'Belum ada data Tahun Ajaran untuk unit sekolah ini.'"
                />
            @endforelse
        </tbody>
    </x-data-table>

    {{-- ========================================================================
         KOLOM KANAN (DETAIL): TABEL SEMESTER UNTUK TAHUN AJARAN TERPILIH
         ======================================================================== --}}
    <x-data-table id="cardSemesterDetail" title="Daftar Semester">
        <x-slot:subtitle>
            @if ($selectedTahunAjaran)
                Tahun: <strong class="text-primary">{{ $selectedTahunAjaran->tahun }}</strong>
            @else
                Pilih Tahun Ajaran di Kiri
            @endif
        </x-slot:subtitle>

        <x-slot:actions>
            <button
                type="button"
                class="btn-primary"
                id="btnOpenCreateSemester"
                {{ $selectedTahunAjaran ? '' : 'disabled' }}
                title="{{ $selectedTahunAjaran ? 'Tambah Semester Baru' : 'Pilih Tahun Ajaran terlebih dahulu' }}"
            >
                <x-icon name="add" size="16" />
                {{-- <span>Tambah</span> --}}
            </button>
        </x-slot:actions>

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
                <x-empty-state
                    icon="info-circle"
                    :colspan="5"
                    text="Belum ada Tahun Ajaran yang dipilih. Silakan pilih atau buat Tahun Ajaran pada tabel di sebelah kiri."
                />
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
                            <x-table-actions align="right">
                                {{-- Tombol Switch Jadikan Aktif --}}
                                @if (! $sem->is_active)
                                    <form
                                        method="POST"
                                        action="{{ route('konfigurasi-sekolah.tahun-ajaran.semester.activate', [$selectedTahunAjaran, $sem]) }}"
                                        onsubmit="return confirm('Tetapkan Semester {{ $sem->label_semester }} ({{ $selectedTahunAjaran->tahun }}) sebagai Semester Aktif? Semester aktif lain pada sekolah ini akan dinonaktifkan.');"
                                    >
                                        @csrf
                                        <button type="submit" class="btn-activate" title="Jadikan semester ini sebagai Semester Aktif tunggal">
                                            <x-icon name="check" size="14" />
                                            {{-- <span>Aktifkan</span> --}}
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
                                    <x-icon name="square-pen" size="14" />
                                    {{-- <span>Ubah</span> --}}
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
                                            <x-icon name="delete" size="14" />
                                            {{-- <span>Hapus</span> --}}
                                        </button>
                                    </form>
                                @endif
                            </x-table-actions>
                        </td>
                    </tr>
                @empty
                    <x-empty-state
                        icon="calendar"
                        :colspan="5"
                        :text="'Belum ada semester terdaftar untuk tahun ajaran ' . $selectedTahunAjaran->tahun . '.'"
                    />
                @endforelse
            @endif
        </tbody>
    </x-data-table>
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
