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
                                data-ta="{{ json_encode([
                                    'id' => $ta->id,
                                    'tahun' => $ta->tahun,
                                    'keterangan' => $ta->keterangan,
                                    'is_active' => (bool) $ta->is_active,
                                ]) }}"
                                data-action="{{ route('konfigurasi-sekolah.tahun-ajaran.update', $ta) }}"
                                title="Ubah Tahun Ajaran {{ $ta->tahun }}"
                            >
                                <x-icon name="square-pen" size="14" />
                                {{-- <span>Ubah</span> --}}
                            </button>

                            {{-- Tombol Hapus Tahun Ajaran --}}
                            @if (! $ta->is_active && ! $ta->hasActiveSemester())
                                <button
                                    type="button"
                                    class="btn-delete btn-open-delete-ta"
                                    data-action="{{ route('konfigurasi-sekolah.tahun-ajaran.destroy', $ta) }}"
                                    data-tahun="{{ $ta->tahun }}"
                                    data-semesters-count="{{ $ta->semesters_count }}"
                                    title="Hapus Tahun Ajaran {{ $ta->tahun }}"
                                >
                                    <x-icon name="delete" size="14" />
                                </button>
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
                                    data-sem="{{ json_encode([
                                        'id' => $sem->id,
                                        'semester' => $sem->semester,
                                        'tanggal_mulai' => $sem->tanggal_mulai?->format('Y-m-d'),
                                        'tanggal_selesai' => $sem->tanggal_selesai?->format('Y-m-d'),
                                        'keterangan' => $sem->keterangan,
                                        'is_active' => (bool) $sem->is_active,
                                    ]) }}"
                                    data-action="{{ route('konfigurasi-sekolah.tahun-ajaran.semester.update', [$selectedTahunAjaran, $sem]) }}"
                                    title="Ubah Semester {{ $sem->label_semester }}"
                                >
                                    <x-icon name="square-pen" size="14" />
                                    {{-- <span>Ubah</span> --}}
                                </button>

                                {{-- Tombol Hapus Semester --}}
                                @if (! $sem->is_active)
                                    <button
                                        type="button"
                                        class="btn-delete btn-open-delete-sem"
                                        data-action="{{ route('konfigurasi-sekolah.tahun-ajaran.semester.destroy', [$selectedTahunAjaran, $sem]) }}"
                                        data-semester="{{ $sem->label_semester }}"
                                        data-tahun="{{ $selectedTahunAjaran->tahun }}"
                                        data-rentang="{{ $sem->rentang_tanggal_formatted }}"
                                        title="Hapus Semester {{ $sem->label_semester }}"
                                    >
                                        <x-icon name="delete" size="14" />
                                    </button>
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

{{-- Modals Tambah dan Ubah --}}
@include('konfigurasi-sekolah.tahun-ajaran.modal-tahun-ajaran')
@include('konfigurasi-sekolah.tahun-ajaran.modal-semester')
{{-- Modal Konfirmasi Hapus Bersama (Shared Delete Modal) --}}
<x-modal
    name="delete"
    id="modalDeleteShared"
    form-id="formDeleteShared"
    cancel-btn-id="btnCancelDeleteShared"
    confirm-btn-id="btnConfirmDeleteShared"
    badge-id="modalDeleteBadge"
    message-id="modalDeleteMessage"
    item-title-id="modalDeleteItemTitleId" />

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Setup Modals (Close, Cancel, Backdrop Click, dan Escape Key)
    InforaModals.setup('modalCreateTahunAjaran', { cancelBtnId: 'btnCancelCreateTahunAjaran', });
    InforaModals.setup('modalEditTahunAjaran', { cancelBtnId: 'btnCancelEditTahunAjaran', });
    InforaModals.setup('modalCreateSemester', { cancelBtnId: 'btnCancelCreateSemester', });
    InforaModals.setup('modalEditSemester', { cancelBtnId: 'btnCancelEditSemester', });
    InforaModals.setup('modalDeleteShared', { cancelBtnId: 'btnCancelDeleteShared', });

    // 2. Bind Tombol Buka Modal Tambah
    InforaModals.bindOpenButton('btnOpenCreateTahunAjaran', 'modalCreateTahunAjaran');
    InforaModals.bindOpenButton('btnOpenCreateSemester', 'modalCreateSemester');

    // 3. Bind Tombol Ubah Tahun Ajaran
    InforaModals.bindEditButtons('.btn-edit-ta', 'modalEditTahunAjaran', function (modal, data) {
        const inputTahun = document.getElementById('edit_ta_tahun');
        const inputKeterangan = document.getElementById('edit_ta_keterangan');
        const inputIsActive = document.getElementById('edit_ta_is_active');

        if (inputTahun) inputTahun.value = data.tahun || '';
        if (inputKeterangan) inputKeterangan.value = data.keterangan || '';
        if (inputIsActive) inputIsActive.checked = Boolean(data.is_active);
    });

    // 4. Bind Tombol Ubah Semester
    InforaModals.bindEditButtons('.btn-edit-sem', 'modalEditSemester', function (modal, data) {
        const inputSem = document.getElementById('edit_sem_type');
        const inputMulai = document.getElementById('edit_sem_tanggal_mulai');
        const inputSelesai = document.getElementById('edit_sem_tanggal_selesai');
        const inputKeterangan = document.getElementById('edit_sem_keterangan');
        const inputIsActive = document.getElementById('edit_sem_is_active');

        if (inputSem) inputSem.value = data.semester || 'ganjil';
        if (inputMulai) inputMulai.value = data.tanggal_mulai || '';
        if (inputSelesai) inputSelesai.value = data.tanggal_selesai || '';
        if (inputKeterangan) inputKeterangan.value = data.keterangan || '';
        if (inputIsActive) inputIsActive.checked = Boolean(data.is_active);
    });

    // 5. Bind Tombol Hapus Tahun Ajaran
    InforaModals.bindDeleteButtons('.btn-open-delete-ta', 'modalDeleteShared', function (modal, btn) {
        document.getElementById('modalDeleteSharedTitle').textContent = 'Hapus Tahun Ajaran?';
        document.getElementById('modalDeleteMessage').textContent = 'Apakah Anda yakin ingin menghapus Tahun Ajaran berikut beserta seluruh semesternya?';
        document.getElementById('modalDeleteBadge').textContent = 'Tahun Ajaran ' + (btn.dataset.tahun || '-');
        
        const count = parseInt(btn.dataset.semestersCount || '0', 10);
        document.getElementById('modalDeleteItemTitleId').textContent = count > 0
            ? `${count} data semester terkait akan ikut terhapus.`
            : 'Belum ada data semester terdaftar.';
    });

    // 6. Bind Tombol Hapus Semester
    InforaModals.bindDeleteButtons('.btn-open-delete-sem', 'modalDeleteShared', function (modal, btn) {
        document.getElementById('modalDeleteSharedTitle').textContent = 'Hapus Semester?';
        document.getElementById('modalDeleteMessage').textContent = `Apakah Anda yakin ingin menghapus Semester berikut dari Tahun Ajaran ${btn.dataset.tahun || '-'}?`;
        document.getElementById('modalDeleteBadge').textContent = 'Semester ' + (btn.dataset.semester || '-');
        document.getElementById('modalDeleteItemTitleId').textContent = btn.dataset.rentang
            ? `Rentang KBM: ${btn.dataset.rentang}`
            : `Tahun Ajaran ${btn.dataset.tahun || '-'}`;
    });

    // 7. Auto Re-open Modal jika terjadi error validasi
    window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
    window.errorMethod = @json(old('_method'));
    const isSemesterError = {{ $errors->hasAny(['semester', 'tanggal_mulai', 'tanggal_selesai']) ? 'true' : 'false' }};
    InforaModals.reopenOnError({
        create: isSemesterError ? 'modalCreateSemester' : 'modalCreateTahunAjaran',
        edit: isSemesterError ? 'modalEditSemester' : 'modalEditTahunAjaran',
    });

    // 8. Auto-masking format Tahun Ajaran (YYYY/YYYY)
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
