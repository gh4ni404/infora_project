@extends('layouts.app')

@section('content')
    @php
        $authUser = auth()->user();
        $resolvedMenu = \App\Support\SidebarCache::findByRoute(request());
        $canCreateFace = $authUser?->isSuperAdmin() || ($resolvedMenu && ($resolvedMenu['type'] === 'submenu'
            ? $authUser?->hasSubMenuAccess($resolvedMenu['item'], 'create')
            : $authUser?->hasMenuAccess($resolvedMenu['item'], 'create')));
        $canEditFace = $authUser?->isSuperAdmin() || ($resolvedMenu && ($resolvedMenu['type'] === 'submenu'
            ? $authUser?->hasSubMenuAccess($resolvedMenu['item'], 'edit')
            : $authUser?->hasMenuAccess($resolvedMenu['item'], 'edit')));
        $canDeleteFace = $authUser?->isSuperAdmin() || ($resolvedMenu && ($resolvedMenu['type'] === 'submenu'
            ? $authUser?->hasSubMenuAccess($resolvedMenu['item'], 'delete')
            : $authUser?->hasMenuAccess($resolvedMenu['item'], 'delete')));
    @endphp

    <div
        id="pendaftaranMukaApp"
        data-can-create="{{ $canCreateFace ? '1' : '0' }}"
        data-can-edit="{{ $canEditFace ? '1' : '0' }}"
        data-can-delete="{{ $canDeleteFace ? '1' : '0' }}">

        <x-page-header title="Pendaftaran Muka Murid">
            <x-slot:subtitle>
                Daftarkan sampel wajah peserta didik untuk pengenalan wajah (face recognition) absensi
                @if ($selectedSchool)
                    &ndash; <strong class="text-brand">{{ $selectedSchool->name }}</strong>
                    <span class="badge {{ $selectedSchool->school_type === 'SMK' ? 'badge-primary' : 'badge-cyan' }}">
                        {{ $selectedSchool->school_type }}
                    </span>
                @endif
            </x-slot:subtitle>

            <x-slot:actions>
                @if ($schools->count() > 1)
                    <form method="GET" action="{{ route('pendaftaran_muka.index') }}" id="schoolFilterForm">
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
                    id="btnOpenRegisterFace"
                    icon="camera"
                    text="Daftarkan Wajah"
                    title="Daftarkan sampel wajah murid baru"
                />
            </x-slot:actions>
        </x-page-header>

        {{-- Flash Alerts --}}
        <x-alert type="success" />
        <x-alert type="error" />
        <x-alert type="validation" />

        {{-- Info Mesin AI Ringan --}}
        <x-status-banner
            type="info"
            title="Mesin Face Recognition Ringan (Client-Side):"
            badge="Siap di Browser"
            badge-color="success">
            Ekstraksi fitur wajah (128-dimensi) berjalan langsung di browser menggunakan model ringan lokal tanpa membebani CPU/RAM server.
            Kamera webcam aktif di <code>localhost</code> atau <strong>HTTPS</strong>, dan unggah foto selalu tersedia kapan saja.
        </x-status-banner>

        {{-- Kartu Metrik Ringkasan (KPI Cards) --}}
        <x-stats-grid>
            <x-stat-card icon="users" color="primary" label="Murid Aktif" :value="number_format($totalMuridAktif, 0, ',', '.')" />
            <x-stat-card icon="user-check" color="success" label="Sudah Terdaftar Wajah" :value="number_format($sudahTerdaftarCount, 0, ',', '.')" />
            <x-stat-card icon="alert-circle" color="amber" label="Belum Terdaftar" :value="number_format($belumTerdaftarCount, 0, ',', '.')" />
            <x-stat-card icon="scan-face" color="cyan" label="Total Sampel Aktif" :value="number_format($totalSampelAktif, 0, ',', '.')" />
        </x-stats-grid>

        {{-- Tabel Status Pendaftaran Muka Murid --}}
        <x-data-table
            route="{{ route('pendaftaran_muka.index') }}"
            search-placeholder="Cari nama lengkap, NISN, atau NIS murid..."
            :per-page="$perPageInput"
            per-page-text="murid"
            :total="$murids->total()"
            total-label="Murid">

            <x-slot:filters>
                <x-form-input type="hidden" name="school_id" :value="$selectedSchoolId" />

                @php
                    $kelasOptions = ['' => 'Semua Rombel', 'tanpa_kelas' => 'Tanpa Rombel'];
                    foreach ($kelasList as $k) {
                        $kelasOptions[(string) $k->id] = $k->nama . ($k->tingkatan ? ' (' . $k->tingkatan->nama . ')' : '');
                    }
                @endphp
                <x-form-select
                    name="kelas_id"
                    id="filterKelasId"
                    class="form-select-sm"
                    placeholder="Semua Rombel"
                    onchange="this.form.submit()"
                    :selected="request('kelas_id')"
                    :options="$kelasOptions"
                />

                <x-form-select
                    name="face_status"
                    id="filterFaceStatus"
                    class="form-select-sm"
                    placeholder="Semua Status Wajah"
                    onchange="this.form.submit()"
                    :selected="request('face_status')"
                    :options="[
                        '' => 'Semua Status Wajah',
                        'terdaftar' => 'Sudah Terdaftar',
                        'belum' => 'Belum Terdaftar',
                    ]"
                />
            </x-slot:filters>

            <thead>
                <tr>
                    <th class="w-12 text-center">No</th>
                    <th>Peserta Didik</th>
                    <th>Rombel / Kelas</th>
                    <th>Sampel Wajah</th>
                    <th>Pembaruan Terakhir</th>
                    <th class="text-right w-40">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($murids as $index => $murid)
                    @php
                        $latestFace = $murid->faceProfiles->first();
                        $maxSamples = \App\Models\MuridFaceProfile::MAX_SAMPLES_PER_MURID;
                        $reachedLimit = $murid->face_profiles_count >= $maxSamples;
                    @endphp
                    <tr>
                        <td class="text-center font-mono text-xs text-slate-500">
                            {{ $murids->firstItem() + $index }}
                        </td>

                        <td>
                            <div class="flex items-center gap-3">
                                <x-avatar src="{{ $murid->foto_url }}" name="{{ $murid->nama_lengkap }}" />
                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-900 truncate">
                                        {{ $murid->nama_lengkap }}
                                    </div>
                                    <div class="text-xs text-slate-500 font-mono mt-0.5">
                                        @if ($murid->nisn)
                                            <span>NISN: {{ $murid->nisn }}</span>
                                        @endif
                                        @if ($murid->nisn && $murid->nis)
                                            &bull;
                                        @endif
                                        @if ($murid->nis)
                                            <span>{{ $selectedSchool?->isMadrasah() ? 'NISM' : 'NIS' }}: {{ $murid->nis }}</span>
                                        @endif
                                        @if (! $murid->nisn && ! $murid->nis)
                                            <span class="text-slate-400 italic">Belum ada nomor induk</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if ($murid->kelas)
                                <span class="badge badge-primary font-medium">{{ $murid->kelas->nama }}</span>
                                @if ($murid->kelas->tingkatan)
                                    <div class="text-xs text-slate-500 mt-1">{{ $murid->kelas->tingkatan->nama }}</div>
                                @endif
                            @else
                                <span class="badge badge-neutral">Tanpa Rombel</span>
                            @endif
                        </td>

                        <td>
                            @if ($murid->face_profiles_count > 0)
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <div class="face-thumb-stack">
                                        @foreach ($murid->faceProfiles->take(4) as $faceSample)
                                            <img
                                                src="{{ $faceSample->foto_url }}"
                                                alt="{{ $faceSample->label }}"
                                                title="{{ $faceSample->label }}{{ $faceSample->is_active ? '' : ' (Nonaktif)' }}"
                                                class="face-thumb-item {{ $faceSample->is_active ? '' : 'is-inactive' }}"
                                            >
                                        @endforeach
                                    </div>
                                    <span class="badge {{ $murid->active_face_profiles_count > 0 ? 'badge-success' : 'badge-warning' }}">
                                        {{ $murid->active_face_profiles_count }}/{{ $maxSamples }} Aktif
                                    </span>
                                </div>
                            @else
                                <span class="badge badge-amber">Belum Terdaftar</span>
                            @endif
                        </td>

                        <td class="text-xs text-slate-500">
                            @if ($latestFace)
                                <div class="font-medium text-slate-700">{{ $latestFace->label }}</div>
                                <div>{{ $latestFace->created_at?->translatedFormat('d M Y, H:i') }}</div>
                            @else
                                <span class="text-slate-400 italic">-</span>
                            @endif
                        </td>

                        <td class="text-right">
                            <x-table-actions>
                                @if (! $reachedLimit)
                                    <x-crud-button
                                        action="create"
                                        class="btn-open-register-face"
                                        icon="camera"
                                        title="Daftarkan atau tambah sampel wajah {{ $murid->nama_lengkap }}"
                                        data-murid-id="{{ $murid->id }}"
                                        data-murid-nama="{{ $murid->nama_lengkap }}"
                                        data-sample-count="{{ $murid->face_profiles_count }}"
                                    />
                                @endif

                                <x-crud-button
                                    action="view"
                                    class="btn-open-manage-faces"
                                    title="Kelola sampel wajah {{ $murid->nama_lengkap }}"
                                    data-url="{{ route('pendaftaran_muka.show', $murid) }}"
                                    data-murid-id="{{ $murid->id }}"
                                    data-murid-nama="{{ $murid->nama_lengkap }}"
                                />
                            </x-table-actions>
                        </td>
                    </tr>
                @empty
                    <x-empty-state
                        icon="scan-face"
                        iconSize="48"
                        colspan="6"
                        :text="request()->filled('search') || request()->filled('kelas_id') || request()->filled('face_status')
                            ? 'Tidak ada murid aktif yang cocok dengan filter pencarian Anda.'
                            : 'Belum terdapat data murid aktif pada unit sekolah ini. Tambahkan data murid terlebih dahulu di Master Data Murid.'">
                    </x-empty-state>
                @endforelse
            </tbody>

            <x-slot:footer>
                <x-pagination-summary :paginator="$murids" />
            </x-slot:footer>
        </x-data-table>

        {{-- Dialog Modals --}}
        @include('face-recognition.pendaftaran-muka.register-modal')
        @include('face-recognition.pendaftaran-muka.manage-modal')

        {{-- Modal Konfirmasi Hapus Sampel Wajah --}}
        <x-modal
            name="delete"
            id="modalDeleteFaceProfile"
            form-id="formDeleteFaceProfile"
            cancel-btn-id="btnCancelDeleteFaceProfile"
            confirm-btn-id="btnConfirmDeleteFaceProfile"
            message-id="deleteFaceProfileMessage"
            badge-id="deleteFaceProfileBadge"
            item-title-id="deleteFaceProfileTitle"
        />

        {{-- Formulir Trigger Toggle Status Sampel --}}
        <form id="formToggleFaceProfile" method="POST" class="hidden">
            @csrf
            @method('PATCH')
        </form>
    </div>

    <script>
        window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
        window.errorMethod = @json(old('_method'));
    </script>

    @vite(['resources/js/face-registration.js'])
@endsection
