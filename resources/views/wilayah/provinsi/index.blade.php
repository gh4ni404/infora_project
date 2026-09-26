@extends('layouts.app')

@section('content')
<x-page-header title="Wilayah Provinsi" subtitle="Kelola registri wilayah administratif provinsi se-Indonesia dalam platform INFORA">
    <x-slot:actions>
        <button type="button" class="btn-primary" id="btnOpenCreateProvinsi">
            <x-icon name="plus" size="18" />
            <span>Tambah Provinsi</span>
        </button>
    </x-slot:actions>
</x-page-header>

{{-- Flash Alerts --}}
<x-alert type="success" />
<x-alert type="error" />
<x-alert type="validation" />

{{-- Data Table Provinsi --}}
<x-data-table
    route="{{ route('wilayah.provinsi') }}"
    search-placeholder="Cari kode atau nama provinsi..."
    :per-page="request('per_page', 15)"
    per-page-text="provinsi"
    :total="$provinsiList->total()"
    total-label="Provinsi"
>
    <x-slot:filters>
        <x-form-select
            name="status"
            id="filter_status"
            class="form-select-sm"
            group-class="m-0"
            placeholder="Semua Status"
            :selected="request('status')"
            :options="[
                '1' => 'Aktif',
                '0' => 'Nonaktif',
            ]"
            onchange="this.form.submit()"
        />
    </x-slot:filters>

    <thead>
        <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama Provinsi</th>
            <th>Status</th>
            <th>Diperbarui</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($provinsiList as $provinsi)
            <tr>
                <td>
                    <span class="table-cell-muted">{{ $provinsiList->firstItem() + $loop->index }}</span>
                </td>
                <td>
                    <code class="text-brand font-semibold">{{ $provinsi->kode }}</code>
                </td>
                <td>
                    <div class="table-cell-bold">{{ $provinsi->nama }}</div>
                </td>
                <td>
                    @if ($provinsi->status)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-neutral">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <span class="table-cell-muted">{{ $provinsi->updated_at ? $provinsi->updated_at->diffForHumans() : '-' }}</span>
                </td>
                <td>
                    <x-table-actions align="right">
                        <button
                            type="button"
                            class="btn-edit btn-open-edit-provinsi"
                            title="Edit Data Provinsi"
                            data-provinsi="{{ json_encode($provinsi) }}"
                            data-action="{{ route('wilayah.provinsi.update', $provinsi) }}"
                        >
                            <x-icon name="edit" size="14" />
                        </button>
                        <button
                            type="button"
                            class="btn-delete btn-open-delete-provinsi"
                            title="Hapus Data Provinsi"
                            data-name="{{ $provinsi->nama }}"
                            data-kode="{{ $provinsi->kode }}"
                            data-action="{{ route('wilayah.provinsi.destroy', $provinsi) }}"
                        >
                            <x-icon name="delete" size="14" />
                        </button>
                    </x-table-actions>
                </td>
            </tr>
        @empty
            <x-empty-state
                icon="map"
                colspan="6"
                title="Belum ada data wilayah provinsi yang ditemukan"
                text="Silakan tambahkan data baru."
            />
        @endforelse
    </tbody>

    <x-slot:footer>
        <x-pagination-summary :paginator="$provinsiList" />
    </x-slot:footer>
</x-data-table>

{{-- Modal Tambah Provinsi Component --}}
@include('wilayah.provinsi.create')

{{-- Modal Edit Provinsi Component --}}
@include('wilayah.provinsi.edit')

{{-- Modal Konfirmasi Hapus Provinsi --}}
<x-modal
    name="delete"
    id="modalDeleteProvinsi"
    title="Hapus Wilayah Provinsi?"
    form-id="formDeleteProvinsi"
    cancel-btn-id="btnCancelDeleteProvinsi"
    confirm-btn-id="btnConfirmDeleteProvinsi"
    message-id="deleteProvinsiMessage"
    badge-id="deleteProvinsiBadge"
    item-title-id="deleteProvinsiTitle"
/>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Modal Tambah Provinsi
    InforaModals.setup('modalCreateProvinsi', { cancelBtnId: 'btnCancelCreateProvinsi' });
    InforaModals.bindOpenButton('btnOpenCreateProvinsi', 'modalCreateProvinsi');

    // 2. Modal Edit Provinsi
    InforaModals.setup('modalEditProvinsi', { cancelBtnId: 'btnCancelEditProvinsi' });
    InforaModals.bindEditButtons('.btn-open-edit-provinsi', 'modalEditProvinsi', function(modal, btn, form) {
        const item = JSON.parse(btn.dataset.provinsi || '{}');
        document.getElementById('edit_kode').value = item.kode || '';
        document.getElementById('edit_nama').value = item.nama || '';
        document.getElementById('edit_status').checked = Boolean(item.status);
    });

    // 3. Modal Konfirmasi Hapus Provinsi
    InforaModals.setup('modalDeleteProvinsi', { cancelBtnId: 'btnCancelDeleteProvinsi' });
    InforaModals.bindDeleteButtons('.btn-open-delete-provinsi', 'modalDeleteProvinsi', function(modal, btn) {
        const name = btn.dataset.name || 'Provinsi';
        const kode = btn.dataset.kode || '-';
        document.getElementById('modalDeleteProvinsiTitle').textContent = 'Hapus Provinsi ' + name + '?';
        document.getElementById('deleteProvinsiMessage').textContent = 'Apakah Anda yakin ingin menghapus data wilayah provinsi berikut?';
        document.getElementById('deleteProvinsiBadge').textContent = kode;
        document.getElementById('deleteProvinsiTitle').textContent = name;
    });

    // 4. Auto reopen on validation errors
    window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
    window.errorMethod = @json(old('_method'));
    InforaModals.reopenOnError({
        create: 'modalCreateProvinsi',
        edit: 'modalEditProvinsi'
    });
});
</script>
@endsection
