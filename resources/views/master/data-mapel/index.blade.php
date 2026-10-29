@extends('layouts.app')

@section('content')
    <x-page-header title="Master Data Mata Pelajaran">
        <x-slot:subtitle>
            Kelola kurikulum dan mata pelajaran (Umum, Kejuruan, Muatan Lokal, dan Pilihan) per unit sekolah
            @if ($selectedSchool)
                &ndash; <strong class="text-brand">{{ $selectedSchool->name }}</strong>
                <span
                    class="badge {{ $selectedSchool->school_type === 'SMK' ? 'badge-primary' : 'badge-cyan' }}">{{ $selectedSchool->school_type }}</span>
            @endif
        </x-slot:subtitle>

        <x-slot:actions>
            {{-- Selector Unit Sekolah --}}
            @if ($schools->count() > 1)
                <form method="GET" action="{{ route('master.data-mapel.index') }}" id="schoolFilterForm">
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
                id="btnOpenCreateMapel"
                title="Tambah Mata Pelajaran"
            />
        </x-slot:actions>
    </x-page-header>

    {{-- Flash Alerts --}}
    <x-alert type="success" />
    <x-alert type="error" />
    <x-alert type="validation" />

    {{-- Kartu Metrik Ringkasan (KPI Cards) --}}
    <x-stats-grid>
        <x-stat-card icon="book-open" color="primary" label="Total Mapel" :value="number_format($totalCount, 0, ',', '.')" />
        <x-stat-card icon="check-circle" color="success" label="Mapel Aktif" :value="number_format($activeCount, 0, ',', '.')" />
        <x-stat-card icon="info-circle" color="amber" label="Non-Aktif" :value="number_format($inactiveCount, 0, ',', '.')" />
        <x-stat-card icon="clock" color="cyan" label="Total Beban Jam" :value="number_format($totalBebanJam, 0, ',', '.') . ' JP'" />
    </x-stats-grid>

    {{-- Tabel Data Mata Pelajaran --}}
    <x-data-table route="{{ route('master.data-mapel.index') }}"
        search-placeholder="Cari kode, singkatan, atau nama mapel..." :per-page="$perPageInput">
        <x-slot:filters>
            <x-form-input type="hidden" name="school_id" :value="$selectedSchoolId" />

            {{-- Filter Status --}}
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

            {{-- Filter Kelompok Mapel --}}
            <x-form-select 
                name="kelompok" 
                id="filterKelompok"
                class="form-select-sm"
                placeholder="Semua Kelompok"
                onchange="this.form.submit()"
                :selected="request('kelompok')"
                :options="[
                    'Umum'                  => 'Umum',
                    'Kejuruan / Peminatan'  => 'Kejuruan / Peminatan',
                    'Muatan Lokal'          => 'Muatan Lokal',
                    'Pilihan'               => 'Pilihan'
                ]"
            />

            {{-- Filter Jurusan --}}
            @if ($jurusans->isNotEmpty())
                <x-form-select 
                    name="jurusan_id" 
                    id="filterJurusan"
                    class="form-select-sm"
                    placeholder="Semua Jurusan"
                    onchange="this.form.submit()"
                    :selected="request('jurusan_id')"
                    :options="[
                        'umum' => 'Mapel Umum (Tanpa Jurusan)',
                        ...$jurusans->mapWithKeys(fn($j) => [$j->id => ($j->singkatan ? '[' . $j->singkatan . '] ' : '') . $j->nama])->all()
                    ]"
                />
            @endif
        </x-slot:filters>

        <thead>
            <tr>
                <th class="col-w-idx">No</th>
                <th>Kode</th>
                <th>Nama Mata Pelajaran</th>
                <th>Kelompok</th>
                <th>Jurusan Terkait</th>
                <th class="text-center">Beban Jam</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th class="col-w-actions">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mapels as $index => $mapel)
                <tr>
                    <td class="col-w-idx text-center">
                        {{ $mapels->firstItem() + $index }}
                    </td>
                    <td>
                        <div class="flex items-center gap-1.5">
                            <span class="badge badge-blue font-mono font-semibold">{{ $mapel->kode }}</span>
                            @if ($mapel->singkatan)
                                <span class="badge badge-slate text-xs font-mono">{{ $mapel->singkatan }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="table-cell-title">{{ $mapel->nama }}</div>
                    </td>
                    <td>
                        @switch($mapel->kelompok)
                            @case('Umum')
                                <span class="badge badge-blue">Umum</span>
                                @break
                            @case('Kejuruan / Peminatan')
                                <span class="badge badge-purple">Kejuruan / Peminatan</span>
                                @break
                            @case('Muatan Lokal')
                                <span class="badge badge-amber">Muatan Lokal</span>
                                @break
                            @case('Pilihan')
                                <span class="badge badge-cyan">Pilihan</span>
                                @break
                            @default
                                <span class="badge badge-slate">{{ $mapel->kelompok }}</span>
                        @endswitch
                    </td>
                    <td>
                        @if ($mapel->jurusan)
                            <span class="badge badge-slate" title="{{ $mapel->jurusan->nama }}">
                                {{ $mapel->jurusan->singkatan ?: $mapel->jurusan->kode }}
                            </span>
                        @else
                            <span class="table-cell-muted">Umum / Semua</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-slate font-medium">{{ $mapel->beban_jam ?? 2 }} JP</span>
                    </td>
                    <td>
                        @if ($mapel->keterangan)
                            <span class="table-cell-subtitle">{{ $mapel->keterangan }}</span>
                        @else
                            <span class="table-cell-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if ($mapel->is_active)
                            <span class="badge badge-emerald">Aktif</span>
                        @else
                            <span class="badge badge-rose">Nonaktif</span>
                        @endif
                    </td>
                    <td class="col-w-actions">
                        <x-table-actions align="right">
                            {{-- Quick Toggle Status --}}
                            <form method="POST" action="{{ route('master.data-mapel.toggle-status', $mapel) }}"
                                class="form-inline-action">
                                @csrf
                                <button type="submit" class="btn-icon"
                                    title="{{ $mapel->is_active ? 'Nonaktifkan mata pelajaran' : 'Aktifkan mata pelajaran' }}">
                                    @if ($mapel->is_active)
                                        <x-icon class="text-success" name="power" size="16"></x-icon>
                                    @else
                                        <x-icon class="text-muted" name="power" size="16"></x-icon>
                                    @endif
                                </button>
                            </form>

                            {{-- Ubah Data (Edit Modal) --}}
                            <x-crud-button
                                action="edit"
                                class="btn-open-edit-mapel"
                                title="Ubah Data Mapel {{ $mapel->nama }}"
                                data-mapel="{{ json_encode($mapel) }}"
                                data-action="{{ route('master.data-mapel.update', $mapel) }}"
                            />

                            {{-- Hapus Data (Delete Modal) --}}
                            <x-crud-button
                                action="delete"
                                class="btn-open-delete-mapel"
                                title="Hapus Data Mapel {{ $mapel->nama }}"
                                data-id="{{ $mapel->id }}"
                                data-kode="{{ $mapel->kode }}"
                                data-nama="{{ $mapel->nama }}"
                                data-action="{{ route('master.data-mapel.destroy', $mapel) }}"
                            />
                        </x-table-actions>
                    </td>
                </tr>
            @empty
                <x-empty-state icon="book-open" iconSize="48" colspan="9" :text="request()->filled('search') || request()->filled('status') || request()->filled('kelompok') || request()->filled('jurusan_id')
                    ? 'Tidak ada data mata pelajaran yang cocok dengan kriteria filter pencarian Anda.'
                    : 'Belum terdapat data mata pelajaran yang terdaftar pada unit sekolah ini.'">
                </x-empty-state>
            @endforelse
        </tbody>

        <x-slot:footer>
            <x-pagination-summary :paginator="$mapels" />
        </x-slot:footer>
    </x-data-table>

    {{-- Komponen Dialog Modals --}}
    @include('master.data-mapel.create')
    @include('master.data-mapel.edit')

    {{-- Modal Konfirmasi Hapus Data Mata Pelajaran --}}
    <x-modal 
        name="delete" 
        id="modalDeleteMapel" 
        form-id="formDeleteMapel" 
        cancel-btn-id="btnCancelDeleteMapel"
        confirm-btn-id="btnConfirmDeleteMapel"
        message-id="deleteMapelMessage"
        badge-id="deleteMapelBadge"
        item-title-id="deleteMapelTitle" />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal Tambah
            InforaModals.setup('modalCreateMapel', { cancelBtnId: 'btnCancelCreateMapel' });
            InforaModals.bindOpenButton('btnOpenCreateMapel', 'modalCreateMapel');

            // Modal Ubah Data
            InforaModals.setup('modalEditMapel', { cancelBtnId: 'btnCancelEditMapel' });
            InforaModals.bindEditButtons('.btn-open-edit-mapel', 'modalEditMapel', function(modal, data, formEdit) {
                document.getElementById('edit_kode').value = data.kode || '';
                document.getElementById('edit_singkatan').value = data.singkatan || '';
                document.getElementById('edit_nama').value = data.nama || '';
                document.getElementById('edit_kelompok').value = data.kelompok || 'Umum';
                document.getElementById('edit_jurusan_id').value = data.jurusan_id || '';
                document.getElementById('edit_beban_jam').value = data.beban_jam !== undefined && data.beban_jam !== null ? data.beban_jam : 2;
                document.getElementById('edit_keterangan').value = data.keterangan || '';
                document.getElementById('edit_is_active').checked = Boolean(data.is_active);
            });

            // Modal Konfirmasi Hapus
            InforaModals.setup('modalDeleteMapel', { cancelBtnId: 'btnCancelDeleteMapel' });
            InforaModals.bindDeleteButtons('.btn-open-delete-mapel', 'modalDeleteMapel', function(modal, btn) {
                document.getElementById('modalDeleteMapelTitle').textContent = 'Hapus Mata Pelajaran ' + btn.dataset.kode + '?';
                document.getElementById('deleteMapelMessage').textContent = 'Apakah Anda yakin ingin menghapus data mata pelajaran berikut?';
                document.getElementById('deleteMapelBadge').textContent = btn.dataset.kode;
                document.getElementById('deleteMapelTitle').textContent = btn.dataset.nama;
            });

            // Reopen modal otomatis saat terjadi error validasi
            window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
            window.errorMethod = @json(old('_method'));
            InforaModals.reopenOnError({
                create: 'modalCreateMapel',
                edit: 'modalEditMapel'
            });
        });
    </script>
@endsection
