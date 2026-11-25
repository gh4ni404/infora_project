@extends('layouts.app')

@section('content')
<x-page-header
    title="Monitoring Sekolah & Tenant"
    subtitle="Pantau perkembangan modul, menu, sub-menu, user, dan distribusi izin menu per database sekolah"
>
    <x-slot:actions>
        <a href="{{ route('monitoring-sekolah.status-server.index') }}" class="btn-secondary">
            <x-icon name="database" size="16" />
            <span>Status Server & DB</span>
        </a>
        <x-crud-button
            action="create"
            id="btnOpenCreateNode"
            text="Tambah Sekolah"
        />
    </x-slot:actions>
</x-page-header>

<x-alert type="success" />
<x-alert type="error" />
<x-alert type="validation" />

<x-stats-grid>
    <x-stat-card icon="school" color="primary" label="Total Node Sekolah" :value="$summaryStats['total']" />
    <x-stat-card icon="activity" color="success" label="Online (Aktif)" :value="$summaryStats['online']" />
    <x-stat-card icon="clock" color="cyan" label="Online (Idle)" :value="$summaryStats['idle']" />
    <x-stat-card icon="alert-triangle" color="amber" label="Offline / Gangguan" :value="$summaryStats['offline'] + $summaryStats['degraded']" />
</x-stats-grid>

<x-tab-pills label="Filter Status Server" class="mb-4">
    <x-tab-pill :href="route('monitoring-sekolah.status-sekolah.index', ['search' => $search])" :active="$statusFilter === ''" :badge="$summaryStats['total']">
        Semua
    </x-tab-pill>
    <x-tab-pill :href="route('monitoring-sekolah.status-sekolah.index', ['status' => 'online', 'search' => $search])" :active="$statusFilter === 'online'" :badge="$summaryStats['online']" badge-class="badge-success">
        Online Aktif
    </x-tab-pill>
    <x-tab-pill :href="route('monitoring-sekolah.status-sekolah.index', ['status' => 'idle', 'search' => $search])" :active="$statusFilter === 'idle'" :badge="$summaryStats['idle']" badge-class="badge-cyan">
        Idle
    </x-tab-pill>
    <x-tab-pill :href="route('monitoring-sekolah.status-sekolah.index', ['status' => 'degraded', 'search' => $search])" :active="$statusFilter === 'degraded'" :badge="$summaryStats['degraded']" badge-class="badge-warning">
        DB Gangguan
    </x-tab-pill>
    <x-tab-pill :href="route('monitoring-sekolah.status-sekolah.index', ['status' => 'offline', 'search' => $search])" :active="$statusFilter === 'offline'" :badge="$summaryStats['offline']" badge-class="badge-rose">
        Offline
    </x-tab-pill>
</x-tab-pills>

<x-data-table
    route="{{ route('monitoring-sekolah.status-sekolah.index') }}"
    search-placeholder="Cari nama sekolah, kode node, atau NPSN..."
    :total="$filtered->count()"
    total-label="Sekolah"
>
    <thead>
        <tr>
            <th>Sekolah & Kode Node</th>
            <th>Status Server</th>
            <th>Modul / Menu / Sub-Menu</th>
            <th>User (Aktif / Total)</th>
            <th>Kebijakan Menu</th>
            <th class="col-w-actions">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($filtered as $node)
            @php $snap = $node->snapshot; @endphp
            <tr>
                <td>
                    <a href="{{ route('monitoring-sekolah.status-sekolah.show', $node) }}" class="table-cell-bold">{{ $node->school_name }}</a>
                    <div class="brand-subtitle">
                        <code>{{ $node->node_code }}</code>
                        @if ($node->npsn)
                            &bull; NPSN {{ $node->npsn }}
                        @endif
                    </div>
                </td>
                <td>
                    <span class="badge {{ $node->computed_status_badge }}">{{ $node->computed_status_label }}</span>
                    <div class="brand-subtitle">{{ $node->last_heartbeat_at?->diffForHumans() ?? 'Belum ada heartbeat' }}</div>
                </td>
                <td>
                    @if ($snap)
                        <span class="badge badge-primary">{{ $snap->modules_count }} Modul</span>
                        <span class="badge badge-cyan">{{ $snap->menus_count }} Menu</span>
                        <span class="badge badge-neutral">{{ $snap->sub_menus_count }} Sub</span>
                    @else
                        <span class="text-muted">Menunggu data</span>
                    @endif
                </td>
                <td>
                    <span class="table-cell-bold">{{ $node->active_users_count }}</span>
                    <span class="text-muted">/ {{ $snap?->users_total ?? 0 }} user</span>
                </td>
                <td>
                    <span class="badge {{ $node->last_applied_policy_version >= $node->policy_version ? 'badge-success' : 'badge-warning' }}">
                        v{{ $node->last_applied_policy_version }} / v{{ $node->policy_version }}
                    </span>
                </td>
                <td>
                    <x-table-actions align="right">
                        <x-crud-button
                            action="view"
                            tag="link"
                            :href="route('monitoring-sekolah.status-sekolah.show', $node)"
                            title="Detail Perkembangan Sekolah"
                        />
                        <a href="{{ route('monitoring-sekolah.status-sekolah.menus.edit', $node) }}" class="btn-edit" title="Atur Menu Admin Sekolah">
                            <x-icon name="sliders" size="16" />
                        </a>
                        <x-crud-button
                            action="delete"
                            class="btn-open-delete-node"
                            title="Hapus Node Sekolah"
                            data-id="{{ $node->id }}"
                            data-name="{{ $node->school_name }}"
                            data-code="{{ $node->node_code }}"
                            data-action="{{ route('monitoring-sekolah.status-sekolah.destroy', $node) }}"
                        />
                    </x-table-actions>
                </td>
            </tr>
        @empty
            <x-empty-state
                icon="school"
                colspan="6"
                title="Belum ada node sekolah terdaftar"
                text="Klik Tambah Sekolah untuk mendaftarkan database sekolah dan mendapatkan kredensial .env."
            />
        @endforelse
    </tbody>
</x-data-table>

<x-modal
    id="modalCreateNode"
    title="Daftarkan Node Sekolah Baru"
    subtitle="Buat identitas dan token API untuk menghubungkan database sekolah ke Portal Master"
    action="{{ route('monitoring-sekolah.status-sekolah.store') }}"
>
    <x-form-input
        name="school_name"
        id="create_school_name"
        label="Nama Sekolah"
        placeholder="Contoh: SMKN 1 Bone"
        required
    />
    <x-form-input
        name="node_code"
        id="create_node_code"
        label="Kode Node (Unik Logis)"
        placeholder="Contoh: smkn1-bone"
        hint="Huruf, angka, tanda hubung (-), atau garis bawah (_). Dipakai sebagai INFORA_NODE_CODE."
        required
    />
    <x-form-input
        name="npsn"
        id="create_npsn"
        label="NPSN"
        placeholder="Contoh: 40312345"
    />
    <x-form-input
        name="base_url"
        id="create_base_url"
        type="url"
        label="URL Aplikasi Sekolah"
        placeholder="https://smkn1bone.infora.id"
        hint="Opsional, dipakai untuk fitur ping /up dari Portal Master."
    />
    <x-form-checkbox
        name="is_active"
        id="create_node_is_active"
        label="Aktifkan node sekolah ini untuk menerima heartbeat"
        value="1"
        :checked="true"
    />

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateNode">Batal</button>
        <button type="submit" class="btn-primary">Simpan & Buat Token</button>
    </x-slot:footer>
</x-modal>

<x-modal
    name="delete"
    id="modalDeleteNode"
    title="Hapus Node Sekolah?"
    form-id="formDeleteNode"
    cancel-btn-id="btnCancelDeleteNode"
    confirm-btn-id="btnConfirmDeleteNode"
    message-id="deleteNodeMessage"
    badge-id="deleteNodeBadge"
    item-title-id="deleteNodeTitle"
/>

<script>
document.addEventListener('DOMContentLoaded', function() {
    InforaModals.setup('modalCreateNode', { cancelBtnId: 'btnCancelCreateNode' });
    InforaModals.bindOpenButton('btnOpenCreateNode', 'modalCreateNode');

    InforaModals.setup('modalDeleteNode', { cancelBtnId: 'btnCancelDeleteNode' });
    InforaModals.bindDeleteButtons('.btn-open-delete-node', 'modalDeleteNode', function(modal, btn) {
        const name = btn.dataset.name || 'Sekolah';
        const code = btn.dataset.code || '-';
        document.getElementById('modalDeleteNodeTitle').textContent = 'Hapus Node ' + name + '?';
        document.getElementById('deleteNodeMessage').textContent = 'Seluruh riwayat snapshot dan kebijakan menu node ini di Master akan ikut dihapus.';
        document.getElementById('deleteNodeBadge').textContent = code;
        document.getElementById('deleteNodeTitle').textContent = name;
    });

    window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
    window.errorMethod = @json(old('_method'));
    InforaModals.reopenOnError({ create: 'modalCreateNode' });
});
</script>
@endsection
