@extends('layouts.app')

@section('content')
<x-page-header title="Dashboard Administrator Sekolah">
    <x-slot:subtitle>
        Selamat datang kembali, <strong class="text-brand">{{ $user->name }}</strong> &ndash; Ringkasan operasional dan tata kelola unit sekolah
    </x-slot:subtitle>

    @if ($user->isSuperAdmin() && $schools->count() > 1)
        <x-slot:actions>
            <form method="GET" action="{{ route('dashboard.admin') }}">
                <x-form-select
                    name="school_id"
                    id="school_id"
                    :selected="$school?->id"
                    :options="$schools->mapWithKeys(fn($s) => [$s->id => $s->name . ' (' . $s->npsn . ')'])"
                    onchange="this.form.submit()"
                    title="Pilih Unit Sekolah untuk Pratinjau"
                />
            </form>
        </x-slot:actions>
    @endif
</x-page-header>

{{-- Flash Alerts --}}
<x-alert type="success" />
<x-alert type="error" />

{{-- 1. Kartu Profil & Identitas Sekolah --}}
<div class="card-surface">
    <div class="card-header">
        <div>
            <h2 class="user-name-text">{{ $school?->name ?? 'Belum Terhubung Unit Sekolah' }}</h2>
            <div class="brand-subtitle">
                @if ($school)
                    NPSN: {{ $school->npsn }} &bull; {{ $school->school_type }} &bull; {{ $school->status }}
                @else
                    Unit sekolah belum dikonfigurasi pada akun ini
                @endif
            </div>
        </div>
        @if ($school)
            <span class="badge badge-success">Akreditasi {{ $school->accreditation ?? '-' }}</span>
        @else
            <span class="badge badge-warning">Perlu Konfigurasi</span>
        @endif
    </div>
    <div class="card-body">
        @if ($school)
            <div class="grid-split-1-1">
                <div>
                    <div class="form-group">
                        <span class="form-label">Kepala Sekolah:</span>
                        <span class="table-cell-bold">{{ $school->principal_name ?? '-' }}</span>
                        @if ($school->principal_nip)
                            <span class="text-muted">NIP. {{ $school->principal_nip }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <span class="form-label">Penyelenggara / Yayasan:</span>
                        <span class="text-muted">{{ $school->foundation_name ?? 'Pemerintah Provinsi / Daerah' }}</span>
                    </div>
                </div>
                <div>
                    <div class="form-group">
                        <span class="form-label">Alamat Lengkap:</span>
                        <span class="text-muted">
                            {{ $school->address ?? '-' }}
                            @if ($school->kelurahan || $school->kecamatan || $school->kabupaten || $school->provinsi)
                                <br>
                                {{ collect([$school->kelurahan?->nama, $school->kecamatan?->nama, $school->kabupaten?->nama, $school->provinsi?->nama])->filter()->join(', ') }}
                            @endif
                        </span>
                    </div>
                    <div class="form-group">
                        <span class="form-label">Kontak Resmi:</span>
                        <span class="text-muted">{{ $school->phone ?? '-' }} &bull; {{ $school->email ?? '-' }}</span>
                    </div>
                </div>
            </div>
        @else
            <x-empty-state
                icon="alert-circle"
                :iconSize="32"
                :colspan="false"
                text="Akun Anda saat ini belum terikat dengan unit sekolah. Silakan hubungi Super Administrator untuk penautan data sekolah."
            />
        @endif
    </div>
</div>

{{-- 2. Stats Grid: 4 Kartu Metrik Ringkas --}}
<x-stats-grid>
    <x-stat-card
        icon="folder"
        color="primary"
        label="Kompetensi Keahlian / Jurusan"
        :value="$totalJurusan"
    />
    <x-stat-card
        icon="users"
        color="success"
        label="Pendidik & Tenaga Kependidikan"
        :value="$totalGuru"
    />
    <x-stat-card
        icon="user"
        color="cyan"
        label="Total Peserta Didik"
        :value="$totalMurid"
    />
    <x-stat-card
        icon="calendar"
        color="amber"
        label="Tahun Ajaran & Semester"
    >
        @if ($activeTahunAjaran && $activeSemester)
            <span>{{ $activeTahunAjaran->tahun }} ({{ ucfirst($activeSemester->semester) }})</span>
        @elseif ($activeTahunAjaran)
            <span>{{ $activeTahunAjaran->tahun }}</span>
        @else
            <span class="text-muted">Belum Aktif</span>
        @endif
    </x-stat-card>
</x-stats-grid>

{{-- 3. Tata Letak 2 Kolom (2:1 Asymmetric Split) --}}
<div class="grid-split-2-1">
    {{-- Kolom Kiri: Agenda Kalender Akademik Terdekat --}}
    <x-data-table
        title="Agenda Akademik Terdekat"
        subtitle="Kegiatan dan jadwal akademik sekolah mendatang"
    >
        <x-slot:actions>
            <a href="{{ route('konfigurasi-sekolah.kalender-akademik') }}" class="btn-secondary btn-sm" title="Buka Kalender Akademik Lengkap">
                <span>Buka Kalender</span>
                <x-icon name="chevron-right" size="14" />
            </a>
        </x-slot:actions>

        <thead>
            <tr>
                <th>Tanggal Kegiatan</th>
                <th>Nama Agenda</th>
                <th>Kategori</th>
                <th>Status KBM</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($upcomingEvents as $event)
                <tr>
                    <td>
                        <span class="table-cell-bold">{{ $event->rentang_tanggal_formatted }}</span>
                    </td>
                    <td>
                        <div class="font-semibold">{{ $event->judul_kegiatan }}</div>
                        <div class="table-cell-muted">Semester {{ ucfirst($event->semester) }} &bull; {{ $event->tahun_ajaran }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $event->badge_class }}">
                            {{ $event->kategori }}
                        </span>
                    </td>
                    <td>
                        @if ($event->libur_kbm)
                            <span class="badge badge-warning">Libur KBM</span>
                        @else
                            <span class="badge badge-neutral">KBM Berjalan</span>
                        @endif
                    </td>
                </tr>
            @empty
                <x-empty-state
                    icon="calendar"
                    :iconSize="32"
                    :colspan="4"
                    text="Tidak ada agenda akademik mendatang dalam waktu dekat."
                />
            @endforelse
        </tbody>
    </x-data-table>

    {{-- Kolom Kanan: Pintasan Navigasi Cepat & Manajemen --}}
    <div class="card-surface">
        <div class="card-header">
            <span class="user-name-text">Pintasan Cepat</span>
            <x-icon name="compass" size="18" class="text-cyan" />
        </div>
        <div class="card-body">
            <div class="form-group">
                <a href="{{ route('konfigurasi-sekolah.tahun-ajaran') }}" class="btn-secondary btn-block" title="Konfigurasi Tahun Ajaran & Semester">
                    <x-icon name="calendar" size="16" />
                    <span>Tahun Ajaran & Semester</span>
                </a>
            </div>
            <div class="form-group">
                <a href="{{ route('konfigurasi-sekolah.kalender-akademik') }}" class="btn-secondary btn-block" title="Kelola Kalender Akademik">
                    <x-icon name="calendar" size="16" />
                    <span>Kalender Akademik</span>
                </a>
            </div>
            <div class="form-group">
                <a href="{{ route('master.data-jurusan.index') }}" class="btn-secondary btn-block" title="Kelola Master Data Jurusan">
                    <x-icon name="folder" size="16" />
                    <span>Master Data Jurusan</span>
                </a>
            </div>
            <div class="form-group">
                <a href="{{ route('master.data-sekolah.index') }}" class="btn-secondary btn-block" title="Kelola Informasi Data Sekolah">
                    <x-icon name="school" size="16" />
                    <span>Profil & Data Sekolah</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
