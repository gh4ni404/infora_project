@extends('layouts.app')

@section('content')
<x-page-header
    title="Status Server & Database Sekolah"
    subtitle="Indikator kesehatan server, koneksi database, latency, dan aktivitas heartbeat lintas hosting"
>
    <x-slot:actions>
        <a href="{{ route('monitoring-sekolah.status-sekolah.index') }}" class="btn-secondary">
            <x-icon name="chevron-left" size="16" />
            <span>Daftar Sekolah</span>
        </a>
    </x-slot:actions>
</x-page-header>

<x-alert type="success" />
<x-alert type="error" />

<x-stats-grid>
    <x-stat-card icon="activity" color="success" label="Online (Aktif)" :value="$summaryStats['online']" />
    <x-stat-card icon="clock" color="cyan" label="Online (Idle)" :value="$summaryStats['idle']" />
    <x-stat-card icon="database" color="amber" label="DB Gangguan" :value="$summaryStats['degraded']" />
    <x-stat-card icon="power" color="primary" label="Offline" :value="$summaryStats['offline']" />
</x-stats-grid>

<x-tab-pills label="Filter Kesehatan Server" class="mb-4">
    <x-tab-pill :href="route('monitoring-sekolah.status-server.index')" :active="$statusFilter === ''" :badge="$summaryStats['total']">
        Semua
    </x-tab-pill>
    <x-tab-pill :href="route('monitoring-sekolah.status-server.index', ['status' => 'online'])" :active="$statusFilter === 'online'" :badge="$summaryStats['online']" badge-class="badge-success">
        Online
    </x-tab-pill>
    <x-tab-pill :href="route('monitoring-sekolah.status-server.index', ['status' => 'idle'])" :active="$statusFilter === 'idle'" :badge="$summaryStats['idle']" badge-class="badge-cyan">
        Idle
    </x-tab-pill>
    <x-tab-pill :href="route('monitoring-sekolah.status-server.index', ['status' => 'degraded'])" :active="$statusFilter === 'degraded'" :badge="$summaryStats['degraded']" badge-class="badge-warning">
        DB Gangguan
    </x-tab-pill>
    <x-tab-pill :href="route('monitoring-sekolah.status-server.index', ['status' => 'offline'])" :active="$statusFilter === 'offline'" :badge="$summaryStats['offline']" badge-class="badge-rose">
        Offline
    </x-tab-pill>
</x-tab-pills>

<x-data-table
    title="Indikator Koneksi Server & Database"
    :total="$nodes->count()"
    total-label="Server"
>
    <thead>
        <tr>
            <th>Sekolah / Hosting</th>
            <th>Status Realtime</th>
            <th>Status Database</th>
            <th>Heartbeat Terakhir</th>
            <th>Migrasi Terakhir</th>
            <th class="col-w-actions">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($nodes as $node)
            <tr>
                <td>
                    <a href="{{ route('monitoring-sekolah.status-sekolah.show', $node) }}" class="table-cell-bold">{{ $node->school_name }}</a>
                    <div class="brand-subtitle">{{ $node->base_url ?? $node->node_code }}</div>
                </td>
                <td>
                    <span class="badge {{ $node->computed_status_badge }}">{{ $node->computed_status_label }}</span>
                    <div class="brand-subtitle">{{ $node->active_users_count }} user aktif saat ini</div>
                </td>
                <td>
                    <span class="badge {{ $node->db_status === 'up' ? 'badge-success' : ($node->db_status === 'down' ? 'badge-rose' : 'badge-neutral') }}">
                        {{ strtoupper($node->db_status) }}
                    </span>
                    @if ($node->db_latency_ms !== null)
                        <span class="text-muted text-xs">{{ $node->db_latency_ms }} ms</span>
                    @endif
                    @if ($node->last_error_message)
                        <div class="text-danger text-xs">{{ \Illuminate\Support\Str::limit($node->last_error_message, 60) }}</div>
                    @endif
                </td>
                <td>
                    <div>{{ $node->last_heartbeat_at?->diffForHumans() ?? 'Belum ada' }}</div>
                    <div class="brand-subtitle">{{ $node->last_heartbeat_at?->format('d/m/Y H:i:s') ?? '-' }}</div>
                </td>
                <td>
                    <code class="text-xs">{{ \Illuminate\Support\Str::limit($node->last_migration ?? '-', 36) }}</code>
                </td>
                <td>
                    <x-table-actions align="right">
                        @if ($node->base_url)
                            <form method="POST" action="{{ route('monitoring-sekolah.status-sekolah.ping', $node) }}">
                                @csrf
                                <button type="submit" class="btn-secondary" title="Ping /up Server Sekolah">
                                    <x-icon name="zap" size="14" />
                                    <span>Ping</span>
                                </button>
                            </form>
                        @endif
                        <x-crud-button
                            action="view"
                            tag="link"
                            :href="route('monitoring-sekolah.status-sekolah.show', $node)"
                            title="Lihat Detail Node"
                        />
                    </x-table-actions>
                </td>
            </tr>
        @empty
            <x-empty-state
                icon="database"
                colspan="6"
                title="Tidak ada server pada filter ini"
                text="Pilih tab lain atau daftarkan node sekolah terlebih dahulu."
            />
        @endforelse
    </tbody>
</x-data-table>
@endsection
