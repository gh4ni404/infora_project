@extends('layouts.app')

@section('content')
<x-page-header title="Wilayah Kabupaten dan Kota" subtitle="Kelola registri wilayah administratif kabupaten dan kota se-Indonesia dalam platform INFORA">
    <x-slot:actions>
        <button type="button" class="btn-primary" id="btnOpenCreateKabupaten">
            <x-icon name="plus" size="18" />
            <span>Tambah Kabupaten / Kota</span>
        </button>
    </x-slot:actions>
</x-page-header>

{{-- Flash Alerts --}}
<x-alert type="success" />
<x-alert type="error" />
<x-alert type="validation" />

{{-- Data Table Kabupaten / Kota --}}
<x-data-table
    route="{{ route('wilayah.kabupaten') }}"
    search-placeholder="Cari kode, kabupaten, kota, atau provinsi..."
    :per-page="request('per_page', 15)"
    per-page-text="kabupaten"
    :total="$kabupatenList->total()"
    total-label="Kabupaten/Kota"
>
    <x-slot:filters>
        <x-form-select
            name="provinsi_id"
            id="filter_provinsi"
            class="form-select-sm"
            group-class="m-0"
            placeholder="Semua Provinsi"
            :selected="request('provinsi_id')"
            onchange="this.form.submit()"
        >
            @foreach ($provinsiList as $prov)
                <option value="{{ $prov->id }}" {{ request('provinsi_id') == $prov->id ? 'selected' : '' }}>
                    {{ $prov->kode }} - {{ $prov->nama }}
                </option>
            @endforeach
        </x-form-select>

        <x-form-select
            name="tipe"
            id="filter_tipe"
            class="form-select-sm"
            group-class="m-0"
            placeholder="Semua Tipe"
            :selected="request('tipe')"
            :options="[
                'Kabupaten' => 'Kabupaten',
                'Kota' => 'Kota',
            ]"
            onchange="this.form.submit()"
        />

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
            <th>Provinsi</th>
            <th>Tipe</th>
            <th>Nama Wilayah</th>
            <th>Status</th>
            <th>Diperbarui</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($kabupatenList as $kabupaten)
            <tr>
                <td>
                    <span class="table-cell-muted">{{ $kabupatenList->firstItem() + $loop->index }}</span>
                </td>
                <td>
                    <code class="text-brand font-semibold">{{ $kabupaten->kode }}</code>
                </td>
                <td>
                    <span class="table-cell-muted">{{ $kabupaten->provinsi->nama ?? '-' }}</span>
                </td>
                <td>
                    @if ($kabupaten->tipe === 'Kota')
                        <span class="badge badge-primary">Kota</span>
                    @else
                        <span class="badge badge-cyan">Kabupaten</span>
                    @endif
                </td>
                <td>
                    <div class="table-cell-bold">{{ $kabupaten->nama_lengkap }}</div>
                </td>
                <td>
                    @if ($kabupaten->status)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-neutral">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <span class="table-cell-muted">{{ $kabupaten->updated_at ? $kabupaten->updated_at->diffForHumans() : '-' }}</span>
                </td>
                <td>
                    <x-table-actions align="right">
                        <button
                            type="button"
                            class="btn-edit btn-open-edit-kabupaten"
                            title="Edit Data Kabupaten/Kota"
                            data-kabupaten="{{ json_encode($kabupaten) }}"
                            data-action="{{ route('wilayah.kabupaten.update', $kabupaten) }}"
                        >
                            <x-icon name="edit" size="14" />
                        </button>
                        <button
                            type="button"
                            class="btn-delete btn-open-delete-kabupaten"
                            title="Hapus Data Kabupaten/Kota"
                            data-name="{{ $kabupaten->nama_lengkap }}"
                            data-kode="{{ $kabupaten->kode }}"
                            data-action="{{ route('wilayah.kabupaten.destroy', $kabupaten) }}"
                        >
                            <x-icon name="delete" size="14" />
                        </button>
                    </x-table-actions>
                </td>
            </tr>
        @empty
            <x-empty-state
                icon="map"
                colspan="8"
                title="Belum ada data wilayah kabupaten atau kota yang ditemukan"
                text="Silakan tambahkan data baru."
            />
        @endforelse
    </tbody>

    <x-slot:footer>
        <x-pagination-summary :paginator="$kabupatenList" />
    </x-slot:footer>
</x-data-table>

{{-- Modal Tambah Kabupaten Component --}}
@include('wilayah.kabupaten.create')

{{-- Modal Edit Kabupaten Component --}}
@include('wilayah.kabupaten.edit')

{{-- Modal Konfirmasi Hapus Kabupaten --}}
<x-modal
    name="delete"
    id="modalDeleteKabupaten"
    title="Hapus Kabupaten / Kota?"
    form-id="formDeleteKabupaten"
    cancel-btn-id="btnCancelDeleteKabupaten"
    confirm-btn-id="btnConfirmDeleteKabupaten"
    message-id="deleteKabupatenMessage"
    badge-id="deleteKabupatenBadge"
    item-title-id="deleteKabupatenTitle"
/>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Modal Tambah Kabupaten
    InforaModals.setup('modalCreateKabupaten', { cancelBtnId: 'btnCancelCreateKabupaten' });
    InforaModals.bindOpenButton('btnOpenCreateKabupaten', 'modalCreateKabupaten');

    const createProvSelect = document.getElementById('create_provinsi_id');
    const createKodeInput = document.getElementById('create_kode');

    // Auto-prefixing kode wilayah pada Create Modal
    if (createProvSelect && createKodeInput) {
        createProvSelect.addEventListener('searchable-select:change', function(e) {
            const provKode = e.detail?.extra || '';
            if (provKode) {
                const currentVal = createKodeInput.value.trim();
                if (!currentVal || currentVal.length <= 2) {
                    createKodeInput.value = provKode;
                } else if (!currentVal.startsWith(provKode)) {
                    createKodeInput.value = provKode + currentVal.slice(2, 4);
                }
            } else if (!e.detail?.value) {
                if (createKodeInput.value.length <= 2) {
                    createKodeInput.value = '';
                }
            }
        });
    }

    // 2. Modal Edit Kabupaten
    InforaModals.setup('modalEditKabupaten', { cancelBtnId: 'btnCancelEditKabupaten' });
    InforaModals.bindEditButtons('.btn-open-edit-kabupaten', 'modalEditKabupaten', function(modal, btn, formEdit) {
        const data = JSON.parse(btn.dataset.kabupaten || '{}');
        const editProvInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_provinsi_id') : null;
        if (editProvInst) {
            editProvInst.setValue(data.provinsi_id || '', '', '', false);
        } else {
            const fallbackInput = document.getElementById('edit_provinsi_id');
            if (fallbackInput) fallbackInput.value = data.provinsi_id || '';
        }
        document.getElementById('edit_tipe').value = data.tipe || 'Kabupaten';
        document.getElementById('edit_kode').value = data.kode || '';
        document.getElementById('edit_nama').value = data.nama || '';
        document.getElementById('edit_status').checked = Boolean(data.status);
    });

    const editProvSelect = document.getElementById('edit_provinsi_id');
    const editKodeInput = document.getElementById('edit_kode');

    // Auto-prefixing kode wilayah pada Edit Modal
    if (editProvSelect && editKodeInput) {
        editProvSelect.addEventListener('searchable-select:change', function(e) {
            const provKode = e.detail?.extra || '';
            if (provKode) {
                const currentVal = editKodeInput.value.trim();
                if (!currentVal || currentVal.length <= 2) {
                    editKodeInput.value = provKode;
                } else if (!currentVal.startsWith(provKode)) {
                    editKodeInput.value = provKode + currentVal.slice(2, 4);
                }
            }
        });
    }

    // 3. Modal Konfirmasi Hapus Kabupaten
    InforaModals.setup('modalDeleteKabupaten', { cancelBtnId: 'btnCancelDeleteKabupaten' });
    InforaModals.bindDeleteButtons('.btn-open-delete-kabupaten', 'modalDeleteKabupaten', function(modal, btn) {
        const name = btn.dataset.name || 'Kabupaten/Kota';
        const kode = btn.dataset.kode || '-';
        document.getElementById('modalDeleteKabupatenTitle').textContent = 'Hapus ' + name + '?';
        document.getElementById('deleteKabupatenMessage').textContent = 'Apakah Anda yakin ingin menghapus data wilayah kabupaten/kota berikut?';
        document.getElementById('deleteKabupatenBadge').textContent = kode;
        document.getElementById('deleteKabupatenTitle').textContent = name;
    });

    // 4. Auto reopen on validation errors
    window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
    window.errorMethod = @json(old('_method'));
    InforaModals.reopenOnError({
        create: 'modalCreateKabupaten',
        edit: 'modalEditKabupaten'
    });
});
</script>
@endsection
