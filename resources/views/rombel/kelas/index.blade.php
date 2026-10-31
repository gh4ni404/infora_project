@extends('layouts.app')

@section('content')
    <x-page-header title="Data Kelas (Rombongan Belajar)">
        <x-slot:subtitle>
            Kelola rombongan belajar, alokasi wali kelas, ruangan, dan kapasitas murid per unit sekolah
            @if ($selectedSchool)
                &ndash; <strong class="text-brand">{{ $selectedSchool->name }}</strong>
                <span class="badge {{ $selectedSchool->school_type === 'SMK' ? 'badge-primary' : 'badge-cyan' }}">
                    {{ $selectedSchool->school_type }}
                </span>
            @endif
        </x-slot:subtitle>

        <x-slot:actions>
            {{-- Selector Unit Sekolah (Khusus Super Admin) --}}
            @if ($schools->count() > 1)
                <form method="GET" action="{{ route('rombel.kelas') }}" id="schoolFilterForm">
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
                id="btnOpenCreateKelas"
                title="Tambah Kelas"
            />
        </x-slot:actions>
    </x-page-header>

    {{-- Flash Alerts --}}
    <x-alert type="success" />
    <x-alert type="error" />
    <x-alert type="validation" />

    {{-- Kartu Metrik Ringkasan (KPI Cards) --}}
    <x-stats-grid>
        <x-stat-card icon="users" color="primary" label="Total Rombel" :value="number_format($totalCount, 0, ',', '.')" />
        <x-stat-card icon="check-circle" color="success" label="Kelas Aktif" :value="number_format($activeCount, 0, ',', '.')" />
        <x-stat-card icon="building" color="cyan" label="Total Kapasitas" :value="number_format($totalKapasitas, 0, ',', '.') . ' Murid'" />
        <x-stat-card icon="award" color="amber" label="Wali Kelas Ditugaskan" :value="number_format($assignedWaliCount, 0, ',', '.') . ' / ' . $totalCount" />
    </x-stats-grid>

    {{-- Tabel Data Rombel / Kelas --}}
    <x-data-table route="{{ route('rombel.kelas') }}"
        search-placeholder="Cari nama kelas, kode, ruangan, atau nama wali kelas..." :per-page="$perPageInput">
        <x-slot:filters>
            <x-form-input type="hidden" name="school_id" :value="$selectedSchoolId" />

            {{-- Filter Tahun Ajaran --}}
            @if ($tahunAjarans->isNotEmpty())
                <x-form-select
                    name="tahun_ajaran_id"
                    id="filterTahunAjaran"
                    class="form-select-sm"
                    onchange="this.form.submit()"
                    :selected="request('tahun_ajaran_id', $selectedTahunAjaranId)"
                    :options="[
                        'semua' => 'Semua Tahun Ajaran',
                        ...$tahunAjarans->mapWithKeys(fn($ta) => [$ta->id => $ta->tahun . ($ta->is_active ? ' [Aktif]' : '')])->all()
                    ]"
                />
            @endif

            {{-- Filter Tingkatan --}}
            @if ($tingkatans->isNotEmpty())
                <x-form-select
                    name="tingkatan_id"
                    id="filterTingkatan"
                    class="form-select-sm"
                    placeholder="Semua Tingkatan"
                    onchange="this.form.submit()"
                    :selected="request('tingkatan_id')"
                    :options="$tingkatans->mapWithKeys(fn($t) => [$t->id => $t->nama])->all()"
                />
            @endif

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
                        'umum' => 'Kelas Umum (Tanpa Jurusan)',
                        ...$jurusans->mapWithKeys(fn($j) => [$j->id => ($j->singkatan ? '[' . $j->singkatan . '] ' : '') . $j->nama])->all()
                    ]"
                />
            @endif

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
        </x-slot:filters>

        <thead>
            <tr>
                <th class="w-12 text-center">No</th>
                <th>Rombongan Belajar</th>
                <th>Tahun Ajaran</th>
                <th>Wali Kelas</th>
                <th>Ruangan & Kapasitas</th>
                <th class="text-center">Status</th>
                <th class="text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kelasList as $index => $kelas)
                <tr>
                    <td class="text-center text-muted">
                        {{ $kelasList->firstItem() + $index }}
                    </td>
                    <td>
                        <div class="font-semibold text-main flex items-center gap-2">
                            <span>{{ $kelas->nama }}</span>
                            @if ($kelas->kode)
                                <span class="badge badge-gray text-xs">{{ $kelas->kode }}</span>
                            @endif
                        </div>
                        <div class="text-xs text-muted flex items-center gap-1.5 mt-0.5">
                            <span class="badge badge-cyan text-xs">
                                {{ $kelas->tingkatan?->nama ?? 'Jenjang -' }}
                            </span>
                            @if ($kelas->jurusan)
                                <span class="badge badge-primary text-xs">
                                    {{ $kelas->jurusan->singkatan ?: $kelas->jurusan->nama }}
                                </span>
                            @else
                                <span class="badge badge-gray text-xs">Umum</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="font-medium">
                            {{ $kelas->tahunAjaran?->tahun ?? '-' }}
                        </div>
                        @if ($kelas->tahunAjaran?->is_active)
                            <span class="badge badge-success text-xs mt-0.5">T.A. Aktif</span>
                        @endif
                    </td>
                    <td>
                        @if ($kelas->waliKelas)
                            <div class="font-medium text-main">
                                {{ trim(($kelas->waliKelas->gelar_depan ? $kelas->waliKelas->gelar_depan . ' ' : '') . $kelas->waliKelas->nama_lengkap . ($kelas->waliKelas->gelar_belakang ? ', ' . $kelas->waliKelas->gelar_belakang : '')) }}
                            </div>
                            @if ($kelas->waliKelas->nip)
                                <div class="text-xs text-muted">NIP: {{ $kelas->waliKelas->nip }}</div>
                            @endif
                        @else
                            <span class="badge badge-amber text-xs">Belum Ditugaskan</span>
                        @endif
                    </td>
                    <td>
                        <div class="font-medium text-main">
                            {{ $kelas->ruangan ?: 'Ruangan -' }}
                        </div>
                        <div class="text-xs text-muted mt-0.5">
                            Daya tampung: <strong>{{ $kelas->kapasitas }}</strong> murid
                        </div>
                    </td>
                    <td class="text-center">
                        @if ($kelas->is_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-muted">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <x-table-actions>
                            {{-- Toggle Status Cepat --}}
                            <form method="POST" action="{{ route('rombel.kelas.toggle-status', $kelas) }}"
                                class="form-inline-action">
                                @csrf
                                <button type="submit" class="btn-icon"
                                    title="{{ $kelas->is_active ? 'Nonaktifkan rombel' : 'Aktifkan rombel' }}">
                                    @if ($kelas->is_active)
                                        <x-icon class="text-success" name="power" size="16"></x-icon>
                                    @else
                                        <x-icon class="text-muted" name="power" size="16"></x-icon>
                                    @endif
                                </button>
                            </form>

                            {{-- Ubah Data (Edit Modal) --}}
                            <x-crud-button
                                action="edit"
                                class="btn-open-edit-kelas"
                                title="Ubah Data Kelas {{ $kelas->nama }}"
                                data-kelas="{{ json_encode($kelas) }}"
                                data-action="{{ route('rombel.kelas.update', $kelas) }}"
                            />

                            {{-- Hapus Data (Delete Modal) --}}
                            <x-crud-button
                                action="delete"
                                class="btn-open-delete-kelas"
                                title="Hapus Data Kelas {{ $kelas->nama }}"
                                data-id="{{ $kelas->id }}"
                                data-nama="{{ $kelas->nama }}"
                                data-action="{{ route('rombel.kelas.destroy', $kelas) }}"
                            />
                        </x-table-actions>
                    </td>
                </tr>
            @empty
                <x-empty-state icon="users" iconSize="48" colspan="7" :text="request()->filled('search') || request()->filled('status') || request()->filled('tingkatan_id') || request()->filled('jurusan_id')
                    ? 'Tidak ada data rombel / kelas yang cocok dengan kriteria filter pencarian Anda.'
                    : 'Belum terdapat data rombongan belajar yang terdaftar pada unit sekolah ini.'">
                </x-empty-state>
            @endforelse
        </tbody>

        <x-slot:footer>
            <x-pagination-summary :paginator="$kelasList" />
        </x-slot:footer>
    </x-data-table>

    {{-- Komponen Dialog Modals --}}
    @include('rombel.kelas.create')
    @include('rombel.kelas.edit')

    {{-- Modal Konfirmasi Hapus Data Kelas --}}
    <x-modal 
        name="delete" 
        id="modalDeleteKelas" 
        form-id="formDeleteKelas" 
        cancel-btn-id="btnCancelDeleteKelas"
        confirm-btn-id="btnConfirmDeleteKelas"
        message-id="deleteKelasMessage"
        badge-id="deleteKelasBadge"
        item-title-id="deleteKelasTitle" />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal Tambah
            InforaModals.setup('modalCreateKelas', { cancelBtnId: 'btnCancelCreateKelas' });
            InforaModals.bindOpenButton('btnOpenCreateKelas', 'modalCreateKelas');

            // Asisten Pembuatan Nama / Kode Kelas Cerdas pada Modal Create
            const tingkatanSelect = document.getElementById('create_tingkatan_id');
            const jurusanSelect = document.getElementById('create_jurusan_id');
            const namaInput = document.getElementById('create_nama');
            const kodeInput = document.getElementById('create_kode');

            function suggestClassName() {
                if (!tingkatanSelect || !namaInput || namaInput.value.trim() !== '') {
                    return;
                }

                const selectedTingkatanText = tingkatanSelect.options[tingkatanSelect.selectedIndex]?.text || '';
                const selectedJurusanText = jurusanSelect && jurusanSelect.selectedIndex > 0
                    ? jurusanSelect.options[jurusanSelect.selectedIndex]?.text
                    : '';

                // Ekstrak angka/jenjang roman (X, XI, XII atau 10, 11, 12)
                let jenjang = '';
                if (selectedTingkatanText.includes('XII') || selectedTingkatanText.includes('12')) jenjang = 'XII';
                else if (selectedTingkatanText.includes('XI') || selectedTingkatanText.includes('11')) jenjang = 'XI';
                else if (selectedTingkatanText.includes('X') || selectedTingkatanText.includes('10')) jenjang = 'X';

                // Ekstrak singkatan jurusan (misal: [PPLG] -> PPLG)
                let jurusanCode = '';
                const matchJurusan = selectedJurusanText.match(/\[(.*?)\]/);
                if (matchJurusan) {
                    jurusanCode = matchJurusan[1];
                } else if (selectedJurusanText) {
                    jurusanCode = selectedJurusanText.split(' ')[0] || '';
                }

                if (jenjang && jurusanCode) {
                    namaInput.value = jenjang + ' ' + jurusanCode + ' 1';
                    if (kodeInput && kodeInput.value.trim() === '') {
                        kodeInput.value = jenjang + '-' + jurusanCode + '-1';
                    }
                } else if (jenjang) {
                    namaInput.value = jenjang + ' 1';
                    if (kodeInput && kodeInput.value.trim() === '') {
                        kodeInput.value = jenjang + '-1';
                    }
                }
            }

            if (tingkatanSelect) tingkatanSelect.addEventListener('change', suggestClassName);
            if (jurusanSelect) jurusanSelect.addEventListener('change', suggestClassName);

            // Modal Ubah Data
            InforaModals.setup('modalEditKelas', { cancelBtnId: 'btnCancelEditKelas' });
            InforaModals.bindEditButtons('.btn-open-edit-kelas', 'modalEditKelas', function(modal, data, formEdit) {
                document.getElementById('edit_tahun_ajaran_id').value = data.tahun_ajaran_id || '';
                document.getElementById('edit_tingkatan_id').value = data.tingkatan_id || '';
                document.getElementById('edit_jurusan_id').value = data.jurusan_id || '';
                document.getElementById('edit_wali_kelas_id').value = data.wali_kelas_id || '';
                document.getElementById('edit_nama').value = data.nama || '';
                document.getElementById('edit_kode').value = data.kode || '';
                document.getElementById('edit_ruangan').value = data.ruangan || '';
                document.getElementById('edit_kapasitas').value = data.kapasitas !== undefined && data.kapasitas !== null ? data.kapasitas : 36;
                document.getElementById('edit_keterangan').value = data.keterangan || '';
                document.getElementById('edit_is_active').checked = Boolean(data.is_active);
            });

            // Modal Konfirmasi Hapus
            InforaModals.setup('modalDeleteKelas', { cancelBtnId: 'btnCancelDeleteKelas' });
            InforaModals.bindDeleteButtons('.btn-open-delete-kelas', 'modalDeleteKelas', function(modal, btn) {
                document.getElementById('modalDeleteKelasTitle').textContent = 'Hapus Rombel ' + btn.dataset.nama + '?';
                document.getElementById('deleteKelasMessage').textContent = 'Apakah Anda yakin ingin menghapus data rombongan belajar berikut?';
                document.getElementById('deleteKelasBadge').textContent = 'Rombel';
                document.getElementById('deleteKelasTitle').textContent = btn.dataset.nama;
            });

            // Reopen modal otomatis saat terjadi error validasi
            window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
            window.errorMethod = @json(old('_method'));
            InforaModals.reopenOnError({
                create: 'modalCreateKelas',
                edit: 'modalEditKelas'
            });
        });
    </script>
@endsection
