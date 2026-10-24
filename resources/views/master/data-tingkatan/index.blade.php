@extends('layouts.app')

@section('content')
    <x-page-header title="Master Data Tingkatan">
        <x-slot:subtitle>
            Kelola data penjenjangan tingkatan kelas (contoh: Tingkat X, XI, XII) per unit sekolah
            @if ($selectedSchool)
                &ndash; <strong class="text-brand">{{ $selectedSchool->name }}</strong>
                <span
                    class="badge {{ $selectedSchool->school_type === 'SMK' ? 'badge-primary' : 'badge-cyan' }}">{{ $selectedSchool->school_type }}</span>
            @endif
        </x-slot:subtitle>

        <x-slot:actions>
            {{-- Selector Unit Sekolah --}}
            @if ($schools->count() > 1)
                <form method="GET" action="{{ route('master.data-tingkatan.index') }}" id="schoolFilterForm">
                    <x-form-select
                        name="school_id"
                        id="school_id"
                        :selected="$selectedSchoolId"
                        :options="$schools->mapWithKeys(fn($school) => [$school->id => $school->name . ' (' . $school->school_type . ')'])"
                        onchange="this.form.submit()"
                        title="Pilih Unit Sekolah"
                    />
                </form>
            @endif

            <x-crud-button
                action="create"
                id="btnOpenCreateTingkatan"
                title="Tambah Tingkatan"
            />
        </x-slot:actions>
    </x-page-header>

    {{-- Flash Alerts --}}
    <x-alert type="success" />
    <x-alert type="error" />
    <x-alert type="validation" />

    {{-- Kartu Metrik Ringkasan (KPI Cards) --}}
    <x-stats-grid>
        <x-stat-card icon="layers" color="primary" label="Total Tingkatan" :value="number_format($totalCount, 0, ',', '.')" />
        <x-stat-card icon="check-circle" color="success" label="Tingkatan Aktif" :value="number_format($activeCount, 0, ',', '.')" />
        <x-stat-card icon="info-circle" color="amber" label="Non-Aktif" :value="number_format($inactiveCount, 0, ',', '.')" />
        <x-stat-card icon="award" color="cyan" label="Fase Kurikulum" :value="number_format($faseCount, 0, ',', '.')" />
    </x-stats-grid>

    {{-- Tabel Data Tingkatan --}}
    <x-data-table route="{{ route('master.data-tingkatan.index') }}"
        search-placeholder="Cari kode, nama, angka, atau fase tingkatan..." :per-page="$perPageInput">
        <x-slot:filters>
            <x-form-input type="hidden" name="school_id" :value="$selectedSchoolId" />
            <x-form-select 
                name="status" 
                id="filterStatus"
                class="form-select-sm"
                placeholder="Semua Status"
                onchange="this.form.submit()"
                :selected="request('status')"
                :options="[
                    'aktif'     => 'Aktif',
                    'nonaktif'  => 'Nonaktif'
                ]"
            />
        </x-slot:filters>

        <thead>
            <tr>
                <th class="col-w-idx">No</th>
                <th>Kode</th>
                <th>Tingkat Angka</th>
                <th>Nama Tingkatan</th>
                <th>Fase Kurikulum</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th class="col-w-actions">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tingkatans as $index => $tingkatan)
                <tr>
                    <td class="col-w-idx text-center">
                        {{ $tingkatans->firstItem() + $index }}
                    </td>
                    <td>
                        <span class="badge badge-blue font-mono font-semibold">{{ $tingkatan->kode }}</span>
                    </td>
                    <td>
                        <span class="badge badge-slate">{{ $tingkatan->tingkat_angka }}</span>
                    </td>
                    <td>
                        <div class="table-cell-title">{{ $tingkatan->nama }}</div>
                    </td>
                    <td>
                        @if ($tingkatan->fase)
                            <span class="badge badge-purple">{{ $tingkatan->fase }}</span>
                        @else
                            <span class="table-cell-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if ($tingkatan->keterangan)
                            <span class="table-cell-subtitle">{{ $tingkatan->keterangan }}</span>
                        @else
                            <span class="table-cell-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if ($tingkatan->is_active)
                            <span class="badge badge-emerald">Aktif</span>
                        @else
                            <span class="badge badge-rose">Nonaktif</span>
                        @endif
                    </td>
                    <td class="col-w-actions">
                        <x-table-actions align="right">
                            {{-- Quick Toggle Status --}}
                            <form method="POST" action="{{ route('master.data-tingkatan.toggle-status', $tingkatan) }}"
                                class="form-inline-action">
                                @csrf
                                <button type="submit" class="btn-icon"
                                    title="{{ $tingkatan->is_active ? 'Nonaktifkan tingkatan' : 'Aktifkan tingkatan' }}">
                                    @if ($tingkatan->is_active)
                                        <x-icon class="text-success" name="power" size="16"></x-icon>
                                    @else
                                        <x-icon class="text-muted" name="power" size="16"></x-icon>
                                    @endif
                                </button>
                            </form>

                            {{-- Ubah Data (Edit Modal) --}}
                            <x-crud-button
                                action="edit"
                                class="btn-open-edit-tingkatan"
                                title="Ubah Data Tingkatan {{ $tingkatan->nama }}"
                                data-tingkatan="{{ json_encode($tingkatan) }}"
                                data-action="{{ route('master.data-tingkatan.update', $tingkatan) }}"
                            />

                            {{-- Hapus Data (Delete Modal) --}}
                            <x-crud-button
                                action="delete"
                                class="btn-open-delete-tingkatan"
                                title="Hapus Data Tingkatan {{ $tingkatan->nama }}"
                                data-id="{{ $tingkatan->id }}"
                                data-kode="{{ $tingkatan->kode }}"
                                data-nama="{{ $tingkatan->nama }}"
                                data-action="{{ route('master.data-tingkatan.destroy', $tingkatan) }}"
                            />
                        </x-table-actions>
                    </td>
                </tr>
            @empty
                <x-empty-state icon="layers" iconSize="48" colspan="8" :text="request()->filled('search') || request()->filled('status')
                    ? 'Tidak ada data tingkatan yang cocok dengan kriteria filter pencarian Anda.'
                    : 'Belum terdapat data tingkatan kelas yang terdaftar pada unit sekolah ini.'">
                </x-empty-state>
            @endforelse
        </tbody>

        <x-slot:footer>
            <x-pagination-summary :paginator="$tingkatans" />
        </x-slot:footer>
    </x-data-table>

    {{-- Komponen Dialog Modals --}}
    @include('master.data-tingkatan.create')
    @include('master.data-tingkatan.edit')

    {{-- Modal Konfirmasi Hapus Data Tingkatan --}}
    <x-modal 
        name="delete" 
        id="modalDeleteTingkatan" 
        form-id="formDeleteTingkatan" 
        cancel-btn-id="btnCancelDeleteTingkatan"
        confirm-btn-id="btnConfirmDeleteTingkatan"
        message-id="deleteTingkatanMessage"
        badge-id="deleteTingkatanBadge"
        item-title-id="deleteTingkatanTitle" />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal Tambah
            InforaModals.setup('modalCreateTingkatan', { cancelBtnId: 'btnCancelCreateTingkatan' });
            InforaModals.bindOpenButton('btnOpenCreateTingkatan', 'modalCreateTingkatan');

            // Modal Ubah Data
            InforaModals.setup('modalEditTingkatan', { cancelBtnId: 'btnCancelEditTingkatan' });
            InforaModals.bindEditButtons('.btn-open-edit-tingkatan', 'modalEditTingkatan', function(modal, data, formEdit) {
                document.getElementById('edit_kode').value = data.kode || '';
                document.getElementById('edit_tingkat_angka').value = data.tingkat_angka || '';
                document.getElementById('edit_nama').value = data.nama || '';
                document.getElementById('edit_fase').value = data.fase || '';
                document.getElementById('edit_keterangan').value = data.keterangan || '';
                document.getElementById('edit_is_active').checked = Boolean(data.is_active);
            });

            // Modal Konfirmasi Hapus
            InforaModals.setup('modalDeleteTingkatan', { cancelBtnId: 'btnCancelDeleteTingkatan' });
            InforaModals.bindDeleteButtons('.btn-open-delete-tingkatan', 'modalDeleteTingkatan', function(modal, btn) {
                document.getElementById('modalDeleteTingkatanTitle').textContent = 'Hapus Tingkatan ' + btn.dataset.kode + '?';
                document.getElementById('deleteTingkatanMessage').textContent = 'Apakah Anda yakin ingin menghapus data tingkatan berikut?';
                document.getElementById('deleteTingkatanBadge').textContent = btn.dataset.kode;
                document.getElementById('deleteTingkatanTitle').textContent = btn.dataset.nama;
            });

            // Reopen modal otomatis saat terjadi error validasi
            window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
            window.errorMethod = @json(old('_method'));
            InforaModals.reopenOnError({
                create: 'modalCreateTingkatan',
                edit: 'modalEditTingkatan'
            });
        });
    </script>
@endsection
