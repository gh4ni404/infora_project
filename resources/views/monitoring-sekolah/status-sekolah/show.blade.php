@extends('layouts.app')

@section('content')
@php
    $snapshot = $tenantNode->snapshot;
    $credentials = session('generated_credentials');
@endphp

<x-page-header
    title="{{ $tenantNode->school_name }}"
    subtitle="Kode Node: {{ $tenantNode->node_code }} &bull; NPSN: {{ $tenantNode->npsn ?? '-' }}"
>
    <x-slot:actions>
        <a href="{{ route('monitoring-sekolah.status-sekolah.index') }}" class="btn-secondary">
            <x-icon name="chevron-left" size="16" />
            <span>Kembali</span>
        </a>
        <a href="{{ route('monitoring-sekolah.status-sekolah.menus.edit', $tenantNode) }}" class="btn-primary">
            <x-icon name="sliders" size="16" />
            <span>Kelola Menu Admin</span>
        </a>
    </x-slot:actions>
</x-page-header>

<x-alert type="success" />
<x-alert type="error" />
<x-alert type="validation" />

@if (is_array($credentials))
    <div class="card-surface mb-6">
        <div class="card-header">
            <div>
                <h2 class="user-name-text">Kredensial Integrasi Server Sekolah (.env)</h2>
                <div class="brand-subtitle">Simpan token & secret ini sekarang. Token hanya ditampilkan satu kali demi keamanan.</div>
            </div>
            <span class="badge badge-warning">Salin Sekarang</span>
        </div>
        <div class="card-body">
            <pre class="text-xs">INFORA_NODE_ROLE=school
INFORA_MASTER_URL={{ url('/') }}
INFORA_NODE_CODE={{ $credentials['node_code'] }}
INFORA_NODE_TOKEN={{ $credentials['node_token'] }}
INFORA_NODE_SECRET={{ $credentials['node_secret'] }}</pre>
        </div>
    </div>
@endif

<x-stats-grid>
    <x-stat-card icon="layers" color="primary" label="Modul / Menu / Sub-Menu" :value="($snapshot?->modules_count ?? 0) . ' / ' . ($snapshot?->menus_count ?? 0) . ' / ' . ($snapshot?->sub_menus_count ?? 0)" />
    <x-stat-card icon="users" color="cyan" label="Total User Terdaftar" :value="$snapshot?->users_total ?? 0" />
    <x-stat-card icon="activity" color="success" label="User Sedang Aktif" :value="$tenantNode->active_users_count" />
    <x-stat-card icon="shield-check" color="amber" label="Sinkron Kebijakan Menu" :value="'v' . $tenantNode->last_applied_policy_version . ' / v' . $tenantNode->policy_version" />
</x-stats-grid>

<div class="grid-split-1-1 mb-6">
    <div class="card-surface">
        <div class="card-header">
            <span class="user-name-text">Status Infrastruktur & Database</span>
            <span class="badge {{ $tenantNode->computed_status_badge }}">{{ $tenantNode->computed_status_label }}</span>
        </div>
        <div class="card-body">
            <div class="form-group">
                <span class="form-label">Status DB:</span>
                <span class="badge {{ $tenantNode->db_status === 'up' ? 'badge-success' : ($tenantNode->db_status === 'down' ? 'badge-rose' : 'badge-neutral') }}">
                    {{ strtoupper($tenantNode->db_status) }}
                </span>
                @if ($tenantNode->db_latency_ms !== null)
                    <span class="text-muted">({{ $tenantNode->db_latency_ms }} ms)</span>
                @endif
            </div>
            <div class="form-group">
                <span class="form-label">Heartbeat Terakhir:</span>
                <span class="text-muted">{{ $tenantNode->last_heartbeat_at?->format('d/m/Y H:i:s') ?? 'Belum pernah diterima' }}</span>
            </div>
            <div class="form-group">
                <span class="form-label">URL Aplikasi:</span>
                <span class="text-muted">{{ $tenantNode->base_url ?? '-' }}</span>
            </div>
            <div class="form-group">
                <span class="form-label">Migrasi Terakhir:</span>
                <code class="text-xs">{{ $tenantNode->last_migration ?? '-' }}</code>
            </div>

            <div class="page-actions mt-4">
                <button type="button" class="btn-secondary" id="btnOpenEditNode">
                    <x-icon name="edit" size="14" />
                    <span>Edit Node</span>
                </button>
                @if ($tenantNode->base_url)
                    <form method="POST" action="{{ route('monitoring-sekolah.status-sekolah.ping', $tenantNode) }}">
                        @csrf
                        <button type="submit" class="btn-secondary">
                            <x-icon name="zap" size="14" />
                            <span>Ping /up</span>
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('monitoring-sekolah.status-sekolah.toggle-status', $tenantNode) }}">
                    @csrf
                    <button type="submit" class="btn-secondary">
                        <x-icon name="power" size="14" />
                        <span>{{ $tenantNode->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                    </button>
                </form>
                <form method="POST" action="{{ route('monitoring-sekolah.status-sekolah.regenerate-credentials', $tenantNode) }}">
                    @csrf
                    <button type="submit" class="btn-secondary">
                        <x-icon name="key" size="14" />
                        <span>Reset Token</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="card-surface">
        <div class="card-header">
            <span class="user-name-text">Statistik User & Data Operasional</span>
            <span class="badge badge-cyan">Snapshot Terbaru</span>
        </div>
        <div class="card-body">
            @php
                $breakdown = $snapshot?->users_breakdown ?? [];
                $operational = $snapshot?->operational_summary ?? [];
            @endphp
            <div class="form-group">
                <span class="form-label">Rincian Pengguna:</span>
                <div class="page-actions">
                    <span class="badge badge-role-super">Super Admin: {{ $breakdown['super_admin'] ?? 0 }}</span>
                    <span class="badge badge-role-admin">Admin: {{ $breakdown['admin'] ?? 0 }}</span>
                    <span class="badge badge-role-guru">Guru: {{ $breakdown['guru'] ?? 0 }}</span>
                    <span class="badge badge-role-staf">Staf: {{ $breakdown['staf'] ?? 0 }}</span>
                    <span class="badge badge-role-murid">Murid: {{ $breakdown['murid'] ?? 0 }}</span>
                </div>
            </div>
            <div class="form-group">
                <span class="form-label">Data Akademik:</span>
                <span class="text-muted">
                    Tahun Ajaran: <strong>{{ $operational['tahun_ajaran_aktif'] ?? '-' }}</strong> &bull;
                    Jurusan: {{ $operational['jurusan_count'] ?? 0 }} &bull;
                    Mapel: {{ $operational['mapel_count'] ?? 0 }} &bull;
                    Kelas: {{ $operational['kelas_count'] ?? 0 }} &bull;
                    Murid: {{ $operational['murids_count'] ?? 0 }}
                </span>
            </div>
            <div class="form-group">
                <span class="form-label">Rata-rata Progres Fitur:</span>
                <span class="badge badge-primary">{{ $snapshot?->dev_summary['average_progress'] ?? 0 }}%</span>
            </div>
        </div>
    </div>
</div>

<div class="card-surface">
    <div class="card-header">
        <div>
            <h2 class="user-name-text">Perkembangan Modul, Menu & Sub-Menu Sekolah</h2>
            <div class="brand-subtitle">Struktur navigasi beserta progres pengembangan yang dilaporkan oleh server sekolah</div>
        </div>
        <a href="{{ route('monitoring-sekolah.status-sekolah.menus.edit', $tenantNode) }}" class="btn-primary">
            <x-icon name="sliders" size="16" />
            <span>Atur Hak Menu Sekolah</span>
        </a>
    </div>
    <div class="card-body">
        @php $tree = $snapshot?->modules_tree ?? []; @endphp
        @forelse ($tree as $module)
            <div class="matrix-module-card mb-4">
                <div class="matrix-module-header">
                    <div class="matrix-module-title">
                        <x-icon name="layers" class="nav-item-icon" />
                        <span>{{ $module['name'] ?? '-' }}</span>
                    </div>
                    <span class="badge {{ ! empty($module['is_active']) ? 'badge-success' : 'badge-neutral' }}">
                        {{ ! empty($module['is_active']) ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Menu / Sub-Menu</th>
                                <th>Rute</th>
                                <th>Status Master</th>
                                <th>Progres Pengembangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (($module['menus'] ?? []) as $menu)
                                <tr>
                                    <td class="table-cell-bold">{{ $menu['name'] ?? '-' }}</td>
                                    <td><code>{{ $menu['route_name'] ?? '-' }}</code></td>
                                    <td>
                                        <span class="badge {{ ($menu['is_master_enabled'] ?? true) ? 'badge-success' : 'badge-rose' }}">
                                            {{ ($menu['is_master_enabled'] ?? true) ? 'Diizinkan' : 'Dikunci Master' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-cyan">{{ strtoupper((string) ($menu['dev_stage'] ?? 'planned')) }}</span>
                                        <span class="text-muted">{{ $menu['dev_progress'] ?? 0 }}%</span>
                                    </td>
                                </tr>
                                @foreach (($menu['sub_menus'] ?? []) as $sub)
                                    <tr>
                                        <td class="pl-8">&rdsh; {{ $sub['name'] ?? '-' }}</td>
                                        <td><code>{{ $sub['route_name'] ?? '-' }}</code></td>
                                        <td>
                                            <span class="badge {{ ($sub['is_master_enabled'] ?? true) ? 'badge-success' : 'badge-rose' }}">
                                                {{ ($sub['is_master_enabled'] ?? true) ? 'Diizinkan' : 'Dikunci Master' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-cyan">{{ strtoupper((string) ($sub['dev_stage'] ?? 'planned')) }}</span>
                                            <span class="text-muted">{{ $sub['dev_progress'] ?? 0 }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <x-empty-state
                icon="layers"
                :colspan="null"
                title="Belum ada snapshot modul dari sekolah ini"
                text="Pastikan server sekolah sudah terkonfigurasi dan menjalankan php artisan infora:heartbeat."
            />
        @endforelse
    </div>
</div>

<x-modal
    id="modalEditNode"
    title="Perbarui Data Node Sekolah"
    subtitle="Ubah identitas atau alamat URL aplikasi sekolah"
    action="{{ route('monitoring-sekolah.status-sekolah.update', $tenantNode) }}"
    method="PUT"
>
    <x-form-input name="school_name" id="edit_school_name" label="Nama Sekolah" :value="$tenantNode->school_name" required />
    <x-form-input name="node_code" id="edit_node_code" label="Kode Node" :value="$tenantNode->node_code" required />
    <x-form-input name="npsn" id="edit_npsn" label="NPSN" :value="$tenantNode->npsn" />
    <x-form-input name="base_url" id="edit_base_url" type="url" label="URL Aplikasi Sekolah" :value="$tenantNode->base_url" />
    <x-form-checkbox name="is_active" id="edit_node_is_active" label="Aktifkan node sekolah ini" value="1" :checked="$tenantNode->is_active" />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditNode">Batal</button>
        <button type="submit" class="btn-primary">Simpan Perubahan</button>
    </x-slot:footer>
</x-modal>

<script>
document.addEventListener('DOMContentLoaded', function() {
    InforaModals.setup('modalEditNode', { cancelBtnId: 'btnCancelEditNode' });
    InforaModals.bindOpenButton('btnOpenEditNode', 'modalEditNode');
});
</script>
@endsection
