@extends('layouts.app')

@section('content')
    <x-page-header title="Master Data Jurusan">
        <x-slot:subtitle>
            Kelola data konsentrasi keahlian (SMK) dan peminatan (SMA) per unit sekolah
            @if ($selectedSchool)
                &ndash; <strong class="text-primary">{{ $selectedSchool->name }}</strong>
                <span
                    class="badge {{ $selectedSchool->school_type === 'SMK' ? 'badge-primary' : 'badge-cyan' }}">{{ $selectedSchool->school_type }}</span>
            @endif
        </x-slot:subtitle>

        <x-slot:actions>
            {{-- Selector Unit Sekolah --}}
            @if ($schools->count() > 1)
                <form method="GET" action="{{ route('master.data-jurusan') }}" id="schoolFilterForm">
                    <x-form-select name="school_id" id="school_id" :selected="$selectedSchoolId"
                        onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()"
                        title="Pilih Unit Sekolah">
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}" {{ $selectedSchoolId === $school->id ? 'selected' : '' }}>
                                {{ $school->name }} ({{ $school->school_type }})
                            </option>
                        @endforeach
                    </x-form-select>
                </form>
            @endif

            <button type="button" class="btn-primary" id="btnOpenCreateJurusan">
                <x-icon name="add" size="18" />
                <span>Tambah Jurusan</span>
            </button>
        </x-slot:actions>
    </x-page-header>

    {{-- Flash Alerts --}}
    <x-alert type="success" />
    <x-alert type="error" />
    <x-alert type="validation" />

    {{-- Kartu Metrik Ringkasan (KPI Cards) --}}
    <x-stats-grid>
        <x-stat-card icon="book" color="primary" label="Total Jurusan" :value="number_format($totalCount, 0, ',', '.')" />
        <x-stat-card icon="check-circle" color="success" label="Jurusan Aktif" :value="number_format($activeCount, 0, ',', '.')" />
        <x-stat-card icon="info-circle" color="amber" label="Non-Aktif" :value="number_format($inactiveCount, 0, ',', '.')" />
        <x-stat-card icon="briefcase" color="cyan" label="Bidang Keahlian" :value="number_format($bidangCount, 0, ',', '.')" />
    </x-stats-grid>

    {{-- Tabel Data Jurusan --}}
    <x-data-table route="{{ route('master.data-jurusan') }}"
        search-placeholder="Cari kode, nama jurusan, bidang, atau kaprog..." :per-page="$perPageInput">
        <x-slot:filters>
            <input type="hidden" name="school_id" value="{{ $selectedSchoolId }}">
            <select name="status" class="form-select-sm"
                onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()" title="Filter Status">
                <option value="">Semua Status</option>
                <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Status Aktif</option>
                <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Status Nonaktif</option>
            </select>
        </x-slot:filters>

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
                        <x-table-actions align="right">
                            {{-- Quick Toggle Status --}}
                            <form method="POST" action="{{ route('master.data-jurusan.toggle-status', $jurusan) }}"
                                class="form-inline-action">
                                @csrf
                                <button type="submit" class="btn-icon"
                                    title="{{ $jurusan->is_active ? 'Nonaktifkan status jurusan' : 'Aktifkan status jurusan' }}">
                                    @if ($jurusan->is_active)
                                        <x-icon class="text-success" name="power" size="16"></x-icon>
                                    @else
                                        <x-icon class="text-muted" name="power" size="16"></x-icon>
                                    @endif
                                </button>
                            </form>

                            {{-- Ubah Data (Edit Modal) --}}
                            <button type="button" class="btn-edit btn-open-edit-jurusan" title="Ubah Data Jurusan"
                                data-jurusan="{{ json_encode($jurusan) }}"
                                data-action="{{ route('master.data-jurusan.update', $jurusan) }}">
                                <x-icon name="pencil" size="16" />
                            </button>

                            {{-- Hapus Data (Delete Modal) --}}
                            <button type="button" class="btn-delete btn-open-delete-jurusan" title="Hapus Data Jurusan"
                                data-id="{{ $jurusan->id }}" data-kode="{{ $jurusan->kode }}"
                                data-nama="{{ $jurusan->nama }}"
                                data-action="{{ route('master.data-jurusan.destroy', $jurusan) }}">
                                <x-icon name="trash" size="16" />
                            </button>
                        </x-table-actions>
                    </td>
                </tr>
            @empty
                <x-empty-state icon="book" iconSize="48" colspan="7" :text="request()->filled('search') || request()->filled('status')
                    ? 'Tidak ada data jurusan yang cocok dengan kriteria filter pencarian Anda.'
                    : 'Belum terdapat data jurusan yang terdaftar pada unit sekolah ini.'">
                    {{-- @if (request()->filled('search') || request()->filled('status'))
                    <x-slot:action>
                        <a href="{{ route('master.data-jurusan', ['school_id' => $selectedSchoolId]) }}" class="btn-secondary">
                            Reset Filter Pencarian
                        </a>
                    </x-slot:action>
                @endif --}}
                </x-empty-state>
            @endforelse
        </tbody>

        <x-slot:footer>
            <x-pagination-summary :paginator="$jurusans" />
        </x-slot:footer>
    </x-data-table>

    {{-- Komponen Dialog Modals --}}
    @include('master.data-jurusan.create')
    @include('master.data-jurusan.edit')

    {{-- Modal Konfirmasi Hapus Data Jurusan --}}
    <x-modal 
        name="delete" 
        id="modalDeleteJurusan" 
        form-id="formDeleteJurusan" 
        cancel-btn-id="btnCancelDeleteJurusan"
        confirm-btn-id="btnConfirmDeleteJurusan"
        message-id="deleteJurusanMessage"
        badge-id="deleteJurusanBadge"
        item-title-id="deleteJurusanTitle" />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal Tambah
            InforaModals.setup('modalCreateJurusan', { cancelBtnId: 'btnCancelCreateJurusan', });
            InforaModals.bindOpenButton('btnOpenCreateJurusan', 'modalCreateJurusan');
            InforaModals.bindOpenButton('btnEmptyCreateJurusan', 'modalCreateJurusan');

            // Modal Ubah Data
            InforaModals.setup('modalEditJurusan', { cancelBtnId: 'btnCancelEditJurusan', });
            InforaModals.bindEditButtons('.btn-open-edit-jurusan', 'modalEditJurusan', function(modal, data,
                formEdit) {
                document.getElementById('edit_jenjang').value = data.jenjang || 'SMK';
                document.getElementById('edit_kode').value = data.kode || '';
                document.getElementById('edit_singkatan').value = data.singkatan || '';
                document.getElementById('edit_nama').value = data.nama || '';
                document.getElementById('edit_bidang_keahlian').value = data.bidang_keahlian || '';
                document.getElementById('edit_program_keahlian').value = data.program_keahlian || '';
                document.getElementById('edit_kepala_jurusan').value = data.kepala_jurusan || '';
                document.getElementById('edit_deskripsi').value = data.deskripsi || '';
                document.getElementById('edit_is_active').checked = Boolean(data.is_active);
            });

            // Modal Konfirmasi Hapus
            InforaModals.setup('modalDeleteJurusan', { cancelBtnId: 'btnCancelDeleteJurusan' });
            InforaModals.bindDeleteButtons('.btn-open-delete-jurusan', 'modalDeleteJurusan', function(modal, btn) {
                document.getElementById('modalDeleteJurusanTitle').textContent = 'Hapus Jurusan ' + btn.dataset.kode + '?';
                document.getElementById('deleteJurusanMessage').textContent = 'Apakah Anda yakin ingin menghapus jurusan berikut?';
                document.getElementById('deleteJurusanBadge').textContent = btn.dataset.kode;
                document.getElementById('deleteJurusanTitle').textContent = btn.dataset.nama;
            });

            // Reopen modal otomatis saat terjadi error validasi
            window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
            window.errorMethod = @json(old('_method'));
            InforaModals.reopenOnError({
                create: 'modalCreateJurusan',
                edit: 'modalEditJurusan'
            });
        });
    </script>
@endsection
