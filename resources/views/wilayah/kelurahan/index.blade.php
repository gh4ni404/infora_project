@extends('layouts.app')

@section('content')
<x-page-header title="Wilayah Kelurahan dan Desa" subtitle="Kelola registri wilayah administratif kelurahan dan desa se-Indonesia dalam platform INFORA">
    <x-slot:actions>
        <button type="button" class="btn-primary" id="btnOpenCreateKelurahan">
            <x-icon name="plus" size="18" />
            <span>Tambah Kelurahan / Desa</span>
        </button>
    </x-slot:actions>
</x-page-header>

{{-- Flash Alerts --}}
<x-alert type="success" />
<x-alert type="error" />
<x-alert type="validation" />

{{-- Data Table Kelurahan / Desa --}}
<x-data-table
    route="{{ route('wilayah.kelurahan') }}"
    search-placeholder="Cari kode, kelurahan, desa, atau kode pos..."
    :per-page="request('per_page', 15)"
    per-page-text="kelurahan"
    :total="$kelurahanList->total()"
    total-label="Kelurahan/Desa"
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

        <select id="filter_kecamatan" name="kecamatan_id" class="form-select-sm">
            <option value="">Semua Kecamatan</option>
            @foreach ($kecamatanList as $kec)
                <option
                    value="{{ $kec->id }}"
                    data-kabupaten="{{ $kec->kabupaten_id }}"
                    {{ request('kecamatan_id') == $kec->id ? 'selected' : '' }}
                >
                    {{ $kec->nama_lengkap }}
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
            <th>Kecamatan</th>
            <th>Tipe</th>
            <th>Nama Kelurahan / Desa</th>
            <th>Kode Pos</th>
            <th>Status</th>
            <th>Diperbarui</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($kelurahanList as $kelurahan)
            <tr>
                <td>
                    <span class="table-cell-muted">{{ $kelurahanList->firstItem() + $loop->index }}</span>
                </td>
                <td>
                    <code class="text-brand font-semibold">{{ $kelurahan->kode }}</code>
                </td>
                <td>
                    <span class="table-cell-muted">{{ $kelurahan->kecamatan->kabupaten->provinsi->nama ?? '-' }}</span>
                </td>
                <td>
                    <span class="table-cell-muted">{{ $kelurahan->kecamatan->kabupaten->nama_lengkap ?? '-' }}</span>
                </td>
                <td>
                    <span class="table-cell-muted">{{ $kelurahan->kecamatan->nama ?? '-' }}</span>
                </td>
                <td>
                    @if ($kelurahan->tipe === 'Kelurahan')
                        <span class="badge badge-primary">Kelurahan</span>
                    @else
                        <span class="badge badge-cyan">Desa</span>
                    @endif
                </td>
                <td>
                    <div class="table-cell-bold">{{ $kelurahan->nama }}</div>
                </td>
                <td>
                    <span class="table-cell-muted">{{ $kelurahan->kode_pos ?? '-' }}</span>
                </td>
                <td>
                    @if ($kelurahan->status)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-neutral">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <span class="table-cell-muted">{{ $kelurahan->updated_at ? $kelurahan->updated_at->diffForHumans() : '-' }}</span>
                </td>
                <td>
                    <x-table-actions align="right">
                        <button
                            type="button"
                            class="btn-edit btn-open-edit-kelurahan"
                            title="Edit Data Kelurahan/Desa"
                            data-kelurahan="{{ json_encode($kelurahan) }}"
                            data-action="{{ route('wilayah.kelurahan.update', $kelurahan) }}"
                        >
                            <x-icon name="edit" size="14" />
                        </button>
                        <button
                            type="button"
                            class="btn-delete btn-open-delete-kelurahan"
                            title="Hapus Data Kelurahan/Desa"
                            data-name="{{ $kelurahan->nama_lengkap }}"
                            data-kode="{{ $kelurahan->kode }}"
                            data-action="{{ route('wilayah.kelurahan.destroy', $kelurahan) }}"
                        >
                            <x-icon name="delete" size="14" />
                        </button>
                    </x-table-actions>
                </td>
            </tr>
        @empty
            <x-empty-state
                icon="map"
                colspan="11"
                title="Belum ada data wilayah kelurahan atau desa yang ditemukan"
                text="Silakan tambahkan data baru."
            />
        @endforelse
    </tbody>

    <x-slot:footer>
        <x-pagination-summary :paginator="$kelurahanList" />
    </x-slot:footer>
</x-data-table>

{{-- Modal Tambah Kelurahan / Desa Component --}}
@include('wilayah.kelurahan.create')

{{-- Modal Edit Kelurahan / Desa Component --}}
@include('wilayah.kelurahan.edit')

{{-- Modal Konfirmasi Hapus Kelurahan / Desa --}}
<x-modal
    name="delete"
    id="modalDeleteKelurahan"
    title="Hapus Kelurahan / Desa?"
    form-id="formDeleteKelurahan"
    cancel-btn-id="btnCancelDeleteKelurahan"
    confirm-btn-id="btnConfirmDeleteKelurahan"
    message-id="deleteKelurahanMessage"
    badge-id="deleteKelurahanBadge"
    item-title-id="deleteKelurahanTitle"
/>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Helper: cascading filter kabupaten berdasarkan provinsi
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

    // Helper: cascading filter kecamatan berdasarkan kabupaten
    function filterKecamatanOptions(kabSelect, kecSelect, selectedKecId = null) {
        if (!kabSelect || !kecSelect) return;
        const kabId = kabSelect.value;
        let hasValidSelection = false;

        Array.from(kecSelect.options).forEach(opt => {
            if (opt.value === "") {
                opt.hidden = false;
                return;
            }
            const optKab = opt.dataset.kabupaten;
            const matches = !kabId || optKab === kabId;
            opt.hidden = !matches;

            if (matches && selectedKecId && opt.value === String(selectedKecId)) {
                opt.selected = true;
                hasValidSelection = true;
            }
        });

        if (!hasValidSelection && kecSelect.selectedOptions.length > 0 && kecSelect.selectedOptions[0].hidden) {
            kecSelect.value = "";
        }
    }

    // 1. Cascading pada Toolbar Filter
    const filterProv = document.getElementById('filter_provinsi');
    const filterKab = document.getElementById('filter_kabupaten');
    const filterKec = document.getElementById('filter_kecamatan');

    if (filterProv && filterKab && filterKec) {
        filterKabupatenOptions(filterProv, filterKab, '{{ request('kabupaten_id') }}');
        filterKecamatanOptions(filterKab, filterKec, '{{ request('kecamatan_id') }}');

        filterProv.addEventListener('change', function() {
            filterKabupatenOptions(filterProv, filterKab);
            filterKecamatanOptions(filterKab, filterKec);
            this.form.submit();
        });

        filterKab.addEventListener('change', function() {
            filterKecamatanOptions(filterKab, filterKec);
            this.form.submit();
        });

        filterKec.addEventListener('change', function() {
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

        return fetch(`{{ route('wilayah.dropdown.kabupaten') }}?provinsi_id=${encodeURIComponent(provId)}`)
            .then(res => res.json())
            .then(data => {
                kabInstance.setOptions(data, selectedKabId);
                kabInstance.setLoading(false);
            })
            .catch(err => {
                console.error('Gagal memuat daftar kabupaten:', err);
                kabInstance.setLoading(false);
            });
    }

    // Helper pemuatan data kecamatan via AJAX untuk SearchableSelect
    function loadKecamatanOptions(kecInstance, kabId, selectedKecId = null) {
        if (!kecInstance) return Promise.resolve();
        if (!kabId) {
            kecInstance.setOptions([], '');
            kecInstance.setDisabled(true);
            return Promise.resolve();
        }

        kecInstance.setDisabled(false);
        kecInstance.setLoading(true);

        return fetch(`{{ route('wilayah.dropdown.kecamatan') }}?kabupaten_id=${encodeURIComponent(kabId)}`)
            .then(res => res.json())
            .then(data => {
                kecInstance.setOptions(data, selectedKecId);
                kecInstance.setLoading(false);
            })
            .catch(err => {
                console.error('Gagal memuat daftar kecamatan:', err);
                kecInstance.setLoading(false);
            });
    }

    // 2. Modal Tambah Kelurahan / Desa
    InforaModals.setup('modalCreateKelurahan', { cancelBtnId: 'btnCancelCreateKelurahan' });
    InforaModals.bindOpenButton('btnOpenCreateKelurahan', 'modalCreateKelurahan');

    const createProvSelect = document.getElementById('create_provinsi_id');
    const createKabSelect = document.getElementById('create_kabupaten_id');
    const createKecSelect = document.getElementById('create_kecamatan_id');
    const createKodeInput = document.getElementById('create_kode');

    if (createProvSelect) {
        createProvSelect.addEventListener('searchable-select:change', function(e) {
            const provId = e.detail?.value || '';
            const createKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('create_kabupaten_id') : null;
            const createKecInst = window.SearchableSelect ? window.SearchableSelect.getInstance('create_kecamatan_id') : null;

            if (provId) {
                loadKabupatenOptions(createKabInst, provId, null);
                if (createKecInst) {
                    createKecInst.clear(true);
                    createKecInst.setDisabled(true);
                }
            } else {
                if (createKabInst) {
                    createKabInst.clear(true);
                    createKabInst.setDisabled(true);
                }
                if (createKecInst) {
                    createKecInst.clear(true);
                    createKecInst.setDisabled(true);
                }
                if (createKodeInput && createKodeInput.value.length <= 6) {
                    createKodeInput.value = '';
                }
            }
        });
    }

    if (createKabSelect) {
        createKabSelect.addEventListener('searchable-select:change', function(e) {
            const kabId = e.detail?.value || '';
            const createKecInst = window.SearchableSelect ? window.SearchableSelect.getInstance('create_kecamatan_id') : null;

            if (kabId) {
                loadKecamatanOptions(createKecInst, kabId, null);
            } else {
                if (createKecInst) {
                    createKecInst.clear(true);
                    createKecInst.setDisabled(true);
                }
                if (createKodeInput && createKodeInput.value.length <= 6) {
                    createKodeInput.value = '';
                }
            }
        });
    }

    if (createKecSelect) {
        createKecSelect.addEventListener('searchable-select:change', function(e) {
            const kecKode = e.detail?.extra || e.detail?.item?.kode || '';
            if (kecKode && createKodeInput) {
                const currentVal = createKodeInput.value.trim();
                if (!currentVal || currentVal.length <= 6) {
                    createKodeInput.value = kecKode;
                } else if (!currentVal.startsWith(kecKode)) {
                    createKodeInput.value = kecKode + currentVal.slice(6, 10);
                }
            } else if (!e.detail?.value && createKodeInput) {
                if (createKodeInput.value.length <= 6) {
                    createKodeInput.value = '';
                }
            }
        });
    }

    // 3. Modal Edit Kelurahan / Desa
    InforaModals.setup('modalEditKelurahan', { cancelBtnId: 'btnCancelEditKelurahan' });
    InforaModals.bindEditButtons('.btn-open-edit-kelurahan', 'modalEditKelurahan', function(modal, btn, formEdit) {
        const data = JSON.parse(btn.dataset.kelurahan || '{}');
        const provId = data.kecamatan?.kabupaten?.provinsi_id || '';
        const kabId = data.kecamatan?.kabupaten_id || '';
        const kecId = data.kecamatan_id || '';

        const editProvInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_provinsi_id') : null;
        const editKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kabupaten_id') : null;
        const editKecInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kecamatan_id') : null;

        if (editProvInst) {
            editProvInst.setValue(provId, '', '', false);
        }

        if (provId && editKabInst) {
            loadKabupatenOptions(editKabInst, provId, kabId).then(() => {
                if (kabId && editKecInst) {
                    loadKecamatanOptions(editKecInst, kabId, kecId);
                }
            });
        }

        document.getElementById('edit_tipe').value = data.tipe || 'Kelurahan';
        const editKodeInput = document.getElementById('edit_kode');
        if (editKodeInput) editKodeInput.value = data.kode || '';
        document.getElementById('edit_nama').value = data.nama || '';
        const editKodePos = document.getElementById('edit_kode_pos');
        if (editKodePos) editKodePos.value = data.kode_pos || '';
        document.getElementById('edit_status').checked = Boolean(data.status);
    });

    const editProvSelect = document.getElementById('edit_provinsi_id');
    const editKabSelect = document.getElementById('edit_kabupaten_id');
    const editKecSelect = document.getElementById('edit_kecamatan_id');
    const editKodeInput = document.getElementById('edit_kode');

    if (editProvSelect) {
        editProvSelect.addEventListener('searchable-select:change', function(e) {
            const provId = e.detail?.value || '';
            const editKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kabupaten_id') : null;
            const editKecInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kecamatan_id') : null;

            if (provId) {
                loadKabupatenOptions(editKabInst, provId, null);
                if (editKecInst) {
                    editKecInst.clear(true);
                    editKecInst.setDisabled(true);
                }
            } else {
                if (editKabInst) {
                    editKabInst.clear(true);
                    editKabInst.setDisabled(true);
                }
                if (editKecInst) {
                    editKecInst.clear(true);
                    editKecInst.setDisabled(true);
                }
                if (editKodeInput && editKodeInput.value.length <= 6) {
                    editKodeInput.value = '';
                }
            }
        });
    }

    if (editKabSelect) {
        editKabSelect.addEventListener('searchable-select:change', function(e) {
            const kabId = e.detail?.value || '';
            const editKecInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kecamatan_id') : null;

            if (kabId) {
                loadKecamatanOptions(editKecInst, kabId, null);
            } else {
                if (editKecInst) {
                    editKecInst.clear(true);
                    editKecInst.setDisabled(true);
                }
                if (editKodeInput && editKodeInput.value.length <= 6) {
                    editKodeInput.value = '';
                }
            }
        });
    }

    if (editKecSelect) {
        editKecSelect.addEventListener('searchable-select:change', function(e) {
            const kecKode = e.detail?.extra || e.detail?.item?.kode || '';
            if (kecKode && editKodeInput) {
                const currentVal = editKodeInput.value.trim();
                if (!currentVal || currentVal.length <= 6) {
                    editKodeInput.value = kecKode;
                } else if (!currentVal.startsWith(kecKode)) {
                    editKodeInput.value = kecKode + currentVal.slice(6, 10);
                }
            }
        });
    }

    // 4. Modal Konfirmasi Hapus Kelurahan / Desa
    InforaModals.setup('modalDeleteKelurahan', { cancelBtnId: 'btnCancelDeleteKelurahan' });
    InforaModals.bindDeleteButtons('.btn-open-delete-kelurahan', 'modalDeleteKelurahan', function(modal, btn) {
        const name = btn.dataset.name || 'Kelurahan/Desa';
        const kode = btn.dataset.kode || '-';
        document.getElementById('modalDeleteKelurahanTitle').textContent = 'Hapus ' + name + '?';
        document.getElementById('deleteKelurahanMessage').textContent = 'Apakah Anda yakin ingin menghapus data wilayah kelurahan/desa berikut?';
        document.getElementById('deleteKelurahanBadge').textContent = kode;
        document.getElementById('deleteKelurahanTitle').textContent = name;
    });

    // 5. Auto Reopen Modal jika Terjadi Error Validasi
    window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
    window.errorMethod = @json(old('_method'));
    InforaModals.reopenOnError({
        create: 'modalCreateKelurahan',
        edit: 'modalEditKelurahan'
    });

    @if ($errors->any())
        @if (old('_method') === 'PUT')
            @if (old('provinsi_id'))
                (function() {
                    const editKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kabupaten_id') : null;
                    const editKecInst = window.SearchableSelect ? window.SearchableSelect.getInstance('edit_kecamatan_id') : null;
                    loadKabupatenOptions(editKabInst, '{{ old('provinsi_id') }}', '{{ old('kabupaten_id') }}').then(() => {
                        @if (old('kabupaten_id'))
                            loadKecamatanOptions(editKecInst, '{{ old('kabupaten_id') }}', '{{ old('kecamatan_id') }}');
                        @endif
                    });
                })();
            @endif
        @else
            @if (old('provinsi_id'))
                (function() {
                    const createKabInst = window.SearchableSelect ? window.SearchableSelect.getInstance('create_kabupaten_id') : null;
                    const createKecInst = window.SearchableSelect ? window.SearchableSelect.getInstance('create_kecamatan_id') : null;
                    loadKabupatenOptions(createKabInst, '{{ old('provinsi_id') }}', '{{ old('kabupaten_id') }}').then(() => {
                        @if (old('kabupaten_id'))
                            loadKecamatanOptions(createKecInst, '{{ old('kabupaten_id') }}', '{{ old('kecamatan_id') }}');
                        @endif
                    });
                })();
            @endif
        @endif
    @endif
});
</script>
@endsection
