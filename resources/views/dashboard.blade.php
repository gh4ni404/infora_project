@extends('layouts.app')

@section('content')
<x-page-header
    title="Dashboard Super Administrator"
    subtitle="Tata Kelola Pusat Platform dan Infrastruktur SIM INFORA"
>
    <x-slot:actions>
        <span class="badge {{ ($isMasterMode ?? false) ? 'badge-primary' : 'badge-success' }}">
            {{ ($isMasterMode ?? false) ? 'Mode Portal Master' : 'Sistem Aktif' }}
        </span>
    </x-slot:actions>
</x-page-header>

@if (($isMasterMode ?? false) && ! empty($masterSummary))
    <x-stats-grid>
        <x-stat-card icon="school" color="primary" label="Total Sekolah Terdaftar" :value="$masterSummary['total']" />
        <x-stat-card icon="activity" color="success" label="Online (Aktif)" :value="$masterSummary['online']" />
        <x-stat-card icon="clock" color="cyan" label="Online (Idle)" :value="$masterSummary['idle']" />
        <x-stat-card icon="alert-triangle" color="amber" label="Offline / Gangguan" :value="$masterSummary['offline'] + $masterSummary['degraded']" />
    </x-stats-grid>

    <div class="card-surface mb-6">
        <div class="card-header">
            <div>
                <h2 class="user-name-text">Pemantauan Cepat Node Sekolah</h2>
                <div class="brand-subtitle">Status heartbeat terbaru dari seluruh database sekolah terhubung</div>
            </div>
            <div class="page-actions">
                <a href="{{ route('monitoring-sekolah.status-server.index') }}" class="btn-secondary">
                    <x-icon name="database" size="16" />
                    <span>Status Server & DB</span>
                </a>
                <a href="{{ route('monitoring-sekolah.status-sekolah.index') }}" class="btn-primary">
                    <x-icon name="activity" size="16" />
                    <span>Kelola Semua Sekolah</span>
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Sekolah / Node</th>
                            <th>Status Server</th>
                            <th>Database</th>
                            <th>User Aktif</th>
                            <th>Heartbeat Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($masterSummary['nodes'] as $node)
                            <tr>
                                <td>
                                    <a href="{{ route('monitoring-sekolah.status-sekolah.show', $node) }}" class="table-cell-bold">{{ $node->school_name }}</a>
                                    <div class="brand-subtitle">{{ $node->node_code }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $node->computed_status_badge }}">{{ $node->computed_status_label }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $node->db_status === 'up' ? 'badge-success' : ($node->db_status === 'down' ? 'badge-rose' : 'badge-neutral') }}">
                                        {{ strtoupper($node->db_status) }}
                                    </span>
                                    @if ($node->db_latency_ms !== null)
                                        <span class="text-muted text-xs">{{ $node->db_latency_ms }} ms</span>
                                    @endif
                                </td>
                                <td>{{ $node->active_users_count }}</td>
                                <td class="text-muted">{{ $node->last_heartbeat_at?->diffForHumans() ?? 'Belum ada' }}</td>
                            </tr>
                        @empty
                            <x-empty-state
                                icon="school"
                                colspan="5"
                                title="Belum ada node sekolah terdaftar"
                                text="Daftarkan sekolah melalui menu Daftar & Perkembangan Sekolah untuk mulai memantau."
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<div class="card-surface">
    <div class="card-header">
        <div>
            <h2 class="user-name-text">Ringkasan Lingkungan Sistem</h2>
            <div class="brand-subtitle">Identitas akun pengelola platform dan runtime aplikasi aktif</div>
        </div>
        <span class="badge badge-cyan">Infrastruktur</span>
    </div>

    <div class="card-body">
        <div class="alert-info">
            <strong>Catatan Otoritas:</strong> Akun ini berstatus <strong>Super Administrator</strong> (Entitas Pengembang / Pemilik Platform, bukan pihak sekolah).
        </div>

        <div class="grid-split-1-1">
            <div>
                <div class="form-group">
                    <span class="form-label">Nama Akun Aktif:</span>
                    <span class="user-name-text">{{ $user->name }} ({{ $user->email }})</span>
                </div>
                <div class="form-group">
                    <span class="form-label">Username Pengembang:</span>
                    <span class="text-cyan">{{ $user->username }}</span>
                </div>
                <div class="form-group">
                    <span class="form-label">Peran Node Aplikasi:</span>
                    <span class="badge badge-primary">{{ strtoupper((string) ($systemInfo['node_role'] ?? config('infora.node_role', 'school'))) }}</span>
                </div>
            </div>
            <div>
                <div class="form-group">
                    <span class="form-label">Tipe Akun:</span>
                    <span class="text-success">{{ strtoupper($user->user_type) }}</span>
                </div>
                <div class="form-group">
                    <span class="form-label">Framework dan Runtime:</span>
                    <span class="text-muted">Laravel v{{ $systemInfo['laravel_version'] }} (PHP v{{ $systemInfo['php_version'] }})</span>
                </div>
                <div class="form-group">
                    <span class="form-label">Environment:</span>
                    <span class="text-muted">{{ strtoupper($systemInfo['environment']) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
