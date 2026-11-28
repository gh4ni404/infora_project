@extends('layouts.app')

@section('content')
    @php
        $authUser = auth()->user();
        $resolvedMenu = \App\Support\SidebarCache::findByRoute(request());
        $canCreateAbsen = $authUser?->isSuperAdmin() || ($resolvedMenu && ($resolvedMenu['type'] === 'submenu'
            ? $authUser?->hasSubMenuAccess($resolvedMenu['item'], 'create')
            : $authUser?->hasMenuAccess($resolvedMenu['item'], 'create')));
        $isToday = $selectedDate === today()->toDateString();
    @endphp

    <div
        id="absenMuridApp"
        data-school-id="{{ $selectedSchoolId }}"
        data-descriptors-url="{{ route('absensi-kbm.wajah.descriptors') }}"
        data-store-url="{{ route('absensi-kbm.wajah.store') }}"
        data-timezone="{{ config('app.timezone', 'Asia/Makassar') }}"
        data-is-today="{{ $isToday ? '1' : '0' }}"
        data-can-create="{{ $canCreateAbsen ? '1' : '0' }}">

        <x-page-header title="Absen Murid (Face Recognition)">
            <x-slot:subtitle>
                Pemindaian wajah otomatis dengan suara sambutan selamat datang
                @if ($selectedSchool)
                    &ndash; <strong class="text-brand">{{ $selectedSchool->name }}</strong>
                    <span class="badge {{ $selectedSchool->school_type === 'SMK' ? 'badge-primary' : 'badge-cyan' }}">
                        {{ $selectedSchool->school_type }}
                    </span>
                @endif
            </x-slot:subtitle>

            <x-slot:actions>
                @if ($schools->count() > 1)
                    <form method="GET" action="{{ route('absensi-kbm.wajah.index') }}" id="schoolFilterForm">
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

                <button type="button" class="btn-secondary active" id="btnToggleVoice" title="Aktifkan atau matikan suara sambutan">
                    <x-icon name="volume-2" size="16" id="iconVoiceOn" />
                    <x-icon name="volume-x" size="16" id="iconVoiceOff" class="hidden" />
                    <span id="voiceToggleLabel">Suara Aktif</span>
                </button>

                <button type="button" class="btn-secondary" id="btnTestVoice" title="Uji suara sambutan selamat datang">
                    <x-icon name="sparkles" size="16" />
                    <span>Tes Suara</span>
                </button>

                <a href="{{ route('konfigurasi-sekolah.regis-wajah.index') }}" class="btn-primary" title="Kelola pendaftaran wajah murid">
                    <x-icon name="scan-face" size="16" />
                    <span>Daftar Muka</span>
                </a>
            </x-slot:actions>
        </x-page-header>

        {{-- Flash Alerts --}}
        <x-alert type="success" />
        <x-alert type="error" />

        @if ($stats['wajah_terdaftar'] === 0)
            <x-status-banner
                type="warning"
                title="Belum Ada Wajah Murid Terdaftar:"
                badge="Perlu Pendaftaran"
                badge-color="warning"
                action-icon="scan-face"
                action-title="Daftarkan Wajah Sekarang"
                :action-href="route('konfigurasi-sekolah.regis-wajah.index')">
                <br>Unit sekolah ini belum memiliki sampel wajah murid yang aktif. Silakan daftarkan minimal 1 wajah murid di menu <strong>Pendaftaran Muka</strong> sebelum memulai pemindaian absensi.
            </x-status-banner>
        @endif

        {{-- Kartu Metrik Ringkasan Harian --}}
        <x-stats-grid>
            <x-stat-card icon="scan-face" color="primary" label="Wajah Siap Scan" :value="number_format($stats['wajah_terdaftar'], 0, ',', '.')" />
            <x-stat-card icon="user-check" color="success" label="Hadir ({{ \Illuminate\Support\Carbon::parse($selectedDate)->translatedFormat('d M') }})">
                <span id="kpiHadirValue">{{ number_format($stats['hadir_hari_ini'], 0, ',', '.') }}</span>
            </x-stat-card>
            <x-stat-card icon="clock" color="amber" label="Terlambat">
                <span id="kpiTerlambatValue">{{ number_format($stats['terlambat_hari_ini'], 0, ',', '.') }}</span>
            </x-stat-card>
            <x-stat-card icon="users" color="cyan" label="Belum Absen">
                <span id="kpiBelumAbsenValue">{{ number_format($stats['belum_absen'], 0, ',', '.') }}</span>
            </x-stat-card>
        </x-stats-grid>

        {{-- Kiosk Pemindaian Wajah & Kartu Sambutan Live --}}
        <div class="kiosk-grid">
            {{-- Panel Kiri: Kamera Scanner Face Recognition --}}
            <div class="card-surface">
                <div class="card-header">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <x-tab-pills label="Mode Absensi Wajah">
                            <x-tab-pill id="tabKioskMasuk" :active="true" icon="user-check">
                                Absen Masuk
                            </x-tab-pill>
                            <x-tab-pill id="tabKioskPulang" :active="false" icon="log-out">
                                Absen Pulang
                            </x-tab-pill>
                        </x-tab-pills>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <select id="kioskFilterKelasId" class="form-select-sm" title="Filter pencocokan per kelas (opsional)">
                            <option value="">Semua Kelas</option>
                            @foreach ($kelasList as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->nama }}</option>
                            @endforeach
                        </select>

                        <input
                            type="time"
                            id="kioskJamBatasMasuk"
                            class="form-select-sm"
                            title="Batas jam masuk (kosongkan jika selalu Hadir)"
                        >
                    </div>
                </div>

                <div class="card-body space-y-4">
                    <div id="kioskCameraInsecureAlert" class="hidden">
                        <x-alert type="warning" title="Akses Kamera Memerlukan Izin" :dismissible="false">
                            Pastikan kamera diizinkan oleh browser dan halaman diakses melalui <code>localhost</code> atau <strong>HTTPS</strong>.
                        </x-alert>
                    </div>

                    <div class="face-stage">
                        <div class="face-stage-badge" id="kioskStageBadge">
                            <x-icon name="scan-face" size="14" />
                            <span id="kioskStageBadgeText">Kamera Nonaktif</span>
                        </div>

                        <div class="kiosk-clock-pill">
                            <x-icon name="clock" size="14" />
                            <span id="kioskLiveClock">--:--:-- WITA</span>
                        </div>

                        <video id="kioskCameraVideo" class="face-stage-video hidden" playsinline muted></video>
                        <canvas id="kioskOverlayCanvas" class="face-stage-overlay"></canvas>

                        <div class="face-stage-placeholder" id="kioskStagePlaceholder">
                            <x-icon name="camera" size="44" />
                            <span class="text-sm font-medium">
                                Klik <strong>Mulai Kamera Absen</strong> untuk memulai pemindaian wajah otomatis.
                            </span>
                        </div>
                    </div>

                    <div class="face-stage-controls">
                        <div class="flex items-center gap-2 flex-wrap">
                            <button type="button" class="btn-primary" id="btnStartKioskCamera">
                                <x-icon name="camera" size="16" />
                                <span>Mulai Kamera Absen</span>
                            </button>
                            <button type="button" class="btn-delete hidden" id="btnStopKioskCamera">
                                <x-icon name="power" size="16" />
                                <span>Hentikan Kamera</span>
                            </button>
                            <button type="button" class="btn-secondary" id="btnRefreshDescriptors" title="Muat ulang data wajah terbaru">
                                <x-icon name="refresh-cw" size="16" />
                                <span>Refresh Wajah</span>
                            </button>
                        </div>

                        <span class="text-xs text-slate-500">
                            Suara sambutan otomatis menyapa saat wajah murid dikenali.
                        </span>
                    </div>
                </div>
            </div>

            {{-- Panel Kanan: Kartu Sambutan Terakhir & Feed Hadir Hari Ini --}}
            <div class="space-y-4">
                <div class="kiosk-welcome-card" id="kioskWelcomeCard">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="badge badge-cyan" id="kioskWelcomeStatusBadge">Menunggu Pemindaian</span>
                        <span class="text-xs font-mono text-slate-500" id="kioskWelcomeScoreBadge">Kecocokan -</span>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="user-avatar-circle avatar-lg shrink-0" id="kioskWelcomeAvatarWrap">
                            <img id="kioskWelcomeAvatarImg" src="" alt="Foto Murid" class="hidden">
                            <span id="kioskWelcomeInitials">IN</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold uppercase tracking-wider text-brand" id="kioskWelcomeHeadline">
                                Siap Menyambut Murid
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 truncate mt-0.5" id="kioskWelcomeName">
                                Arahkan Wajah ke Kamera
                            </h3>
                            <div class="text-xs text-slate-600 mt-0.5" id="kioskWelcomeMeta">
                                Nama &amp; kelas murid akan muncul otomatis di sini.
                            </div>
                            <div class="text-xs text-slate-500 font-mono mt-1.5" id="kioskWelcomeTime">
                                Suara: "Selamat datang, [Nama Murid]"
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-surface">
                    <div class="card-header">
                        <span class="font-bold text-sm text-slate-900">Kehadiran Terbaru Hari Ini</span>
                        <span class="badge badge-neutral">Live</span>
                    </div>

                    <div class="card-body">
                        <div id="kioskRecentFeedEmpty" class="{{ $recentAttendances->isNotEmpty() ? 'hidden' : '' }}">
                            <div class="text-xs text-slate-500 text-center py-4">
                                Belum ada murid yang tercatat absen hari ini.
                            </div>
                        </div>

                        <div id="kioskRecentFeed" class="kiosk-feed-list">
                            @foreach ($recentAttendances as $recent)
                                <div class="kiosk-feed-item" data-attendance-id="{{ $recent->id }}">
                                    <div class="min-w-0">
                                        <div class="font-semibold text-sm text-slate-900 truncate">
                                            {{ $recent->murid?->nama_lengkap ?: '-' }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ $recent->kelas?->nama ?: 'Tanpa Kelas' }}
                                            &bull;
                                            @if ($recent->jam_pulang)
                                                Masuk {{ $recent->jam_masuk?->format('H:i:s') }} &bull; Pulang {{ $recent->jam_pulang->format('H:i:s') }}
                                            @else
                                                Masuk {{ $recent->jam_masuk?->format('H:i:s') ?: '-' }}
                                            @endif
                                        </div>
                                    </div>
                                    <span class="badge {{ $recent->status === 'terlambat' ? 'badge-amber' : 'badge-success' }} shrink-0">
                                        {{ $recent->jam_pulang ? 'Pulang' : ucfirst($recent->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Rekap Absensi Harian --}}
        <x-data-table
            route="{{ route('absensi-kbm.wajah.index') }}"
            search-placeholder="Cari nama murid, NISN, atau NIS..."
            :per-page="$perPageInput"
            per-page-text="absensi"
            :total="$absensis->total()"
            total-label="Absensi">

            <x-slot:filters>
                <x-form-input type="hidden" name="school_id" :value="$selectedSchoolId" />

                <input
                    type="date"
                    name="tanggal"
                    id="filterTanggal"
                    class="form-select-sm"
                    value="{{ $selectedDate }}"
                    onchange="this.form.submit()"
                    title="Pilih Tanggal Absensi"
                >

                @php
                    $kelasOptions = ['' => 'Semua Rombel'];
                    foreach ($kelasList as $k) {
                        $kelasOptions[(string) $k->id] = $k->nama;
                    }
                @endphp
                <x-form-select
                    name="kelas_id"
                    id="filterRekapKelasId"
                    class="form-select-sm"
                    placeholder="Semua Rombel"
                    onchange="this.form.submit()"
                    :selected="request('kelas_id')"
                    :options="$kelasOptions"
                />

                <x-form-select
                    name="status"
                    id="filterRekapStatus"
                    class="form-select-sm"
                    placeholder="Semua Status"
                    onchange="this.form.submit()"
                    :selected="request('status')"
                    :options="[
                        '' => 'Semua Status',
                        'hadir' => 'Hadir',
                        'terlambat' => 'Terlambat',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'alpha' => 'Alpha',
                    ]"
                />
            </x-slot:filters>

            <thead>
                <tr>
                    <th class="w-12 text-center">No</th>
                    <th>Peserta Didik</th>
                    <th>Rombel / Kelas</th>
                    <th>Jam Masuk</th>
                    <th>Jam Pulang</th>
                    <th class="text-center">Status &amp; Skor</th>
                    <th class="text-right w-24">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($absensis as $index => $absen)
                    <tr>
                        <td class="text-center font-mono text-xs text-slate-500">
                            {{ $absensis->firstItem() + $index }}
                        </td>

                        <td>
                            <div class="flex items-center gap-3">
                                <x-avatar
                                    src="{{ $absen->foto_masuk_url ?: $absen->murid?->foto_url }}"
                                    name="{{ $absen->murid?->nama_lengkap ?: 'MD' }}"
                                />
                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-900 truncate">
                                        {{ $absen->murid?->nama_lengkap ?: '-' }}
                                    </div>
                                    <div class="text-xs text-slate-500 font-mono mt-0.5">
                                        {{ $absen->murid?->nisn ? 'NISN: '.$absen->murid->nisn : ($absen->murid?->nis ? 'NIS: '.$absen->murid->nis : '-') }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if ($absen->kelas)
                                <span class="badge badge-primary">{{ $absen->kelas->nama }}</span>
                            @else
                                <span class="badge badge-neutral">Tanpa Rombel</span>
                            @endif
                        </td>

                        <td class="font-mono text-xs text-slate-700">
                            {{ $absen->jam_masuk ? $absen->jam_masuk->format('H:i:s') : '-' }}
                        </td>

                        <td class="font-mono text-xs text-slate-700">
                            {{ $absen->jam_pulang ? $absen->jam_pulang->format('H:i:s') : '-' }}
                        </td>

                        <td class="text-center">
                            @php
                                $statusBadge = match($absen->status) {
                                    'hadir' => 'badge-success',
                                    'terlambat' => 'badge-amber',
                                    'alpha' => 'badge-danger',
                                    default => 'badge-cyan',
                                };
                            @endphp
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                <span class="badge {{ $statusBadge }}">{{ ucfirst($absen->status) }}</span>
                                @if ($absen->match_score !== null)
                                    <span class="badge badge-neutral font-mono">{{ round($absen->match_score * 100, 1) }}%</span>
                                @endif
                            </div>
                        </td>

                        <td class="text-right">
                            <x-table-actions>
                                <x-crud-button
                                    action="delete"
                                    class="btn-open-delete-absensi"
                                    title="Hapus catatan absensi {{ $absen->murid?->nama_lengkap }}"
                                    data-nama="{{ $absen->murid?->nama_lengkap ?: 'Murid' }}"
                                    data-tanggal="{{ $absen->tanggal?->translatedFormat('d M Y') }}"
                                    data-action="{{ route('absensi-kbm.wajah.destroy', $absen) }}"
                                />
                            </x-table-actions>
                        </td>
                    </tr>
                @empty
                    <x-empty-state
                        icon="clipboard-list"
                        iconSize="48"
                        colspan="7"
                        :text="'Belum ada catatan absensi murid pada tanggal '.\Illuminate\Support\Carbon::parse($selectedDate)->translatedFormat('d F Y').'.'">
                    </x-empty-state>
                @endforelse
            </tbody>

            <x-slot:footer>
                <x-pagination-summary :paginator="$absensis" />
            </x-slot:footer>
        </x-data-table>

        {{-- Modal Konfirmasi Hapus Log Absensi --}}
        <x-modal
            name="delete"
            id="modalDeleteAbsensi"
            form-id="formDeleteAbsensi"
            cancel-btn-id="btnCancelDeleteAbsensi"
            confirm-btn-id="btnConfirmDeleteAbsensi"
            message-id="deleteAbsensiMessage"
            badge-id="deleteAbsensiBadge"
            item-title-id="deleteAbsensiTitle"
        />
    </div>

    @vite(['resources/js/absen-murid.js'])
@endsection
