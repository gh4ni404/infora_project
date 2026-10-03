@extends('layouts.app')

@section('content')
<x-page-header title="Wilayah Kecamatan" subtitle="Kelola registri wilayah administratif kecamatan se-Indonesia dalam platform INFORA">
    <x-slot:actions>
        <x-crud-button 
            action="create"
            id="btnOpenCreateKecamatan"
        />
    </x-slot:actions>
</x-page-header>

{{-- Flash Alerts --}}
<x-alert type="success" />
<x-alert type="error" />
<x-alert type="validation" />

{{-- Data Table Kecamatan --}}
<x-data-table
    route="{{ route('wilayah.kecamatan') }}"
    search-placeholder="Cari kode, kecamatan, kabupaten, atau provinsi..."
    :per-page="request('per_page', 15)"
    per-page-text="kecamatan"
    :total="$kecamatanList->total()"
    total-label="Kecamatan"
>
    <x-slot:filters>
        <select id="filter_provinsi" name="provinsi_id" class="form-select-sm">
            <option value="">Semua Provinsi</option>
            @foreach ($provinsiList as $prov)
                <option value="{{ $prov->id }}" {{ request('provinsi_id') == $prov->id ? 'selected' : '' }}>
                    {{ $prov->kode }} - {{ $prov->nama }}
                </option>
            @endforeach
        </select>

        <select id="filter_kabupaten" name="kabupaten_id" class="form-select-sm">
            <option value="">Semua Kabupaten / Kota</option>
            @foreach ($kabupatenList as $kab)
                <option
                    value="{{ $kab->id }}"
                    data-provinsi="{{ $kab->provinsi_id }}"
                    {{ request('kabupaten_id') == $kab->id ? 'selected' : '' }}
                >
                    {{ $kab->nama_lengkap }}
                </option>
            @endforeach
        </select>

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
            <th>Kabupaten / Kota</th>
            <th>Nama Kecamatan</th>
            <th>Status</th>
            <th>Diperbarui</th>
            <th class="col-w-actions">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($kecamatanList as $kecamatan)
            <tr>
                <td>
                    <span class="table-cell-muted">{{ $kecamatanList->firstItem() + $loop->index }}</span>
                </td>
                <td>
                    <code class="text-brand font-semibold">{{ $kecamatan->kode }}</code>
                </td>
                <td>
                    <span class="table-cell-muted">{{ $kecamatan->kabupaten->provinsi->nama ?? '-' }}</span>
                </td>
                <td>
                    <span class="table-cell-muted">{{ $kecamatan->kabupaten->nama_lengkap ?? '-' }}</span>
                </td>
                <td>
                    <div class="table-cell-bold">{{ $kecamatan->nama_lengkap }}</div>
                </td>
                <td>
                    @if ($kecamatan->status)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-neutral">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <span class="table-cell-muted">{{ $kecamatan->updated_at ? $kecamatan->updated_at->diffForHumans() : '-' }}</span>
                </td>
                <td>
                    <x-table-actions align="right">
                        <x-crud-button
                            action="edit"
                            class="btn-open-edit-kecamatan"
                            title="Edit Data Kecamatan"
                            data-kecamatan="{{ json_encode($kecamatan) }}"
                            data-action="{{ route('wilayah.kecamatan.update', $kecamatan) }}"
                        />
                        
                        <x-crud-button
                            action="delete"
                            class="btn-open-delete-kecamatan"
                            title="Hapus Data Kecamatan"
                            data-name="{{ $kecamatan->nama_lengkap }}"
                            data-kode="{{ $kecamatan->kode }}"
                            data-action="{{ route('wilayah.kecamatan.destroy', $kecamatan) }}"
                        />
                    </x-table-actions>
                </td>
            </tr>
        @empty
            <x-empty-state
                icon="map"
                colspan="8"
                title="Belum ada data wilayah kecamatan yang ditemukan"
                text="Silakan tambahkan data baru."
            />
        @endforelse
    </tbody>

    <x-slot:footer>
        <x-pagination-summary :paginator="$kecamatanList" />
    </x-slot:footer>
</x-data-table>

{{-- Modal Tambah Kecamatan Component --}}
@include('wilayah.kecamatan.create')

{{-- Modal Edit Kecamatan Component --}}
@include('wilayah.kecamatan.edit')

{{-- Modal Konfirmasi Hapus Kecamatan --}}
<x-modal
    name="delete"
    id="modalDeleteKecamatan"
    title="Hapus Kecamatan?"
    form-id="formDeleteKecamatan"
    cancel-btn-id="btnCancelDeleteKecamatan"
    confirm-btn-id="btnConfirmDeleteKecamatan"
    message-id="deleteKecamatanMessage"
    badge-id="deleteKecamatanBadge"
    item-title-id="deleteKecamatanTitle"
/>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Helper fungsi penyaring opsi dropdown cascading pada Toolbar
    function filterKabupatenOptions(provSelect, kabSelect, selectedKabId = null) {
        if (!provSelect || !kabSelect) return;
        const provId = provSelect.value;
        let hasValidSelection = false;

        Array.from(kabSelect.options).forEach(opt => {
            if (opt.value === "") {
                opt.hidden = false;
                return;
            }
            const optProv = opt.dataset.provinsi;
            const matches = !provId || optProv === provId;
            opt.hidden = !matches;

            if (matches && selectedKabId && opt.value === String(selectedKabId)) {
                opt.selected = true;
                hasValidSelection = true;
            }
        });

        if (!hasValidSelection && kabSelect.selectedOptions.length > 0 && kabSelect.selectedOptions[0].hidden) {
            kabSelect.value = "";
        }
    }

    // 1. Cascading pada Toolbar Filter
    const filterProv = document.getElementById('filter_provinsi');
    const filterKab = document.getElementById('filter_kabupaten');

    if (filterProv && filterKab) {
        filterKabupatenOptions(filterProv, filterKab, '{{ request('kabupaten_id') }}');

        filterProv.addEventListener('change', function() {
            filterKabupatenOptions(filterProv, filterKab);
            this.form.submit();
        });

        filterKab.addEventListener('change', function() {
            this.form.submit();
        });
    }

    // Helper pemuatan data kabupaten via AJAX untuk SearchableSelect
    function loadKabupatenOptions(kabInstance, provId, selectedKabId = null) {
        if (!kabInstance) return Promise.resolve();
        if (!provId) {
            kabInstance.setOptions([], '');
            kabInstance.setDisabled(true);
            return Promise.resolve();
        }

        kabInstance.setDisabled(false);
        kabInstance.setLoading(true);

        return (window.InforaWilayah ? window.InforaWilayah.fetchKabupaten(provId) : fetch(`{{ route('wilayah.dropdown.kabupaten') }}?provinsi_id=${encodeURIComponent(provId)}`).then(res => res.json()))
            .then(data => {
                kabInstance.setOptions(data, selectedKabId);
                kabInstance.setLoading(false);
            })
            .catch(err => {
                console.error('Gagal memuat daftar kabupaten:', err);
                kabInstance.setLoading(false);
            });
    }

    // 2. Modal Tambah Kecamatan
    InforaModals.setup('modalCreateKecamatan', { cancelBtnId: 'btnCancelCreateKecamatan' });
    InforaModals.bindOpenButton('btnOpenCreateKecamatan', 'modalCreateKecamatan');

    const createProvSelect = document.getElementById('create_provinsi_id');
    const createKabSelect = document.getElementById('create_kabupaten_id');
    const createKodeInput = document.getElementById('create_kode');

    if (createProvSelect) {
        createProvSelect.addEventListener('searchable-select:change', function(e) {
            const provId = e.detail?.value || '';
            const createKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('create_kabupaten_id') : null;

            if (provId) {
                loadKabupatenOptions(createKabInst, provId, null);
            } else {
                if (createKabInst) {
                    createKabInst.clear(true);
                    createKabInst.setDisabled(true);
                }
                if (createKodeInput && createKodeInput.value.length <= 4) {
                    createKodeInput.value = '';
                }
            }
        });
    }

    if (createKabSelect) {
        createKabSelect.addEventListener('searchable-select:change', function(e) {
            const kabKode = e.detail?.extra || e.detail?.item?.kode || '';
            if (kabKode && createKodeInput) {
                const currentVal = createKodeInput.value.trim();
                if (!currentVal || currentVal.length <= 4) {
                    createKodeInput.value = kabKode;
                } else if (!currentVal.startsWith(kabKode)) {
                    createKodeInput.value = kabKode + currentVal.slice(4, 6);
                }
            } else if (!e.detail?.value && createKodeInput) {
                if (createKodeInput.value.length <= 4) {
                    createKodeInput.value = '';
                }
            }
        });
    }

    // 3. Modal Edit Kecamatan
    InforaModals.setup('modalEditKecamatan', { cancelBtnId: 'btnCancelEditKecamatan' });
    InforaModals.bindEditButtons('.btn-open-edit-kecamatan', 'modalEditKecamatan', function(modal, formEdit, btn) {
        const data = JSON.parse(btn.dataset.kecamatan || '{}');

        const editProvInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_provinsi_id') : null;
        const editKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kabupaten_id') : null;

        if (editProvInst) {
            editProvInst.setValue(data.provinsi_id || '', '', '', false);
        }

        if (data.provinsi_id && editKabInst) {
            loadKabupatenOptions(editKabInst, data.provinsi_id, data.kabupaten_id);
        } else if (editKabInst) {
            editKabInst.setOptions([], '');
            editKabInst.setDisabled(true);
        }

        const editKodeInput = document.getElementById('edit_kode');
        if (editKodeInput) editKodeInput.value = data.kode || '';
        document.getElementById('edit_nama').value = data.nama || '';
        document.getElementById('edit_status').checked = Boolean(data.status);
    });

    const editProvSelect = document.getElementById('edit_provinsi_id');
    const editKabSelect = document.getElementById('edit_kabupaten_id');
    const editKodeInput = document.getElementById('edit_kode');

    if (editProvSelect) {
        editProvSelect.addEventListener('searchable-select:change', function(e) {
            const provId = e.detail?.value || '';
            const editKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kabupaten_id') : null;

            if (provId) {
                loadKabupatenOptions(editKabInst, provId, null);
            } else {
                if (editKabInst) {
                    editKabInst.clear(true);
                    editKabInst.setDisabled(true);
                }
                if (editKodeInput && editKodeInput.value.length <= 4) {
                    editKodeInput.value = '';
                }
            }
        });
    }

    if (editKabSelect) {
        editKabSelect.addEventListener('searchable-select:change', function(e) {
            const kabKode = e.detail?.extra || e.detail?.item?.kode || '';
            if (kabKode && editKodeInput) {
                const currentVal = editKodeInput.value.trim();
                if (!currentVal || currentVal.length <= 4) {
                    editKodeInput.value = kabKode;
                } else if (!currentVal.startsWith(kabKode)) {
                    editKodeInput.value = kabKode + currentVal.slice(4, 6);
                }
            }
        });
    }

    // 4. Modal Konfirmasi Hapus Kecamatan
    InforaModals.setup('modalDeleteKecamatan', { cancelBtnId: 'btnCancelDeleteKecamatan' });
    InforaModals.bindDeleteButtons('.btn-open-delete-kecamatan', 'modalDeleteKecamatan', function(modal, btn) {
        const name = btn.dataset.name || 'Kecamatan';
        const kode = btn.dataset.kode || '-';
        document.getElementById('modalDeleteKecamatanTitle').textContent = 'Hapus ' + name + '?';
        document.getElementById('deleteKecamatanMessage').textContent = 'Apakah Anda yakin ingin menghapus data wilayah kecamatan berikut?';
        document.getElementById('deleteKecamatanBadge').textContent = kode;
        document.getElementById('deleteKecamatanTitle').textContent = name;
    });

    // 5. Auto Reopen Modal jika Terjadi Error Validasi
    window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
    window.errorMethod = @json(old('_method'));
    InforaModals.reopenOnError({
        create: 'modalCreateKecamatan',
        edit: 'modalEditKecamatan'
    });

    @if ($errors->any())
        @if (old('_method') === 'PUT')
            @if (old('provinsi_id'))
                (function() {
                    const editKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kabupaten_id') : null;
                    loadKabupatenOptions(editKabInst, '{{ old('provinsi_id') }}', '{{ old('kabupaten_id') }}');
                })();
            @endif
        @else
            @if (old('provinsi_id'))
                (function() {
                    const createKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('create_kabupaten_id') : null;
                    loadKabupatenOptions(createKabInst, '{{ old('provinsi_id') }}', '{{ old('kabupaten_id') }}');
                })();
            @endif
        @endif
    @endif
});
</script>
@endsection
