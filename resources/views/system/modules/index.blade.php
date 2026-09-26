@extends('layouts.app')

@section('content')
<x-page-header title="Tata Kelola Modul" subtitle="Kelola modul navigasi utama dan struktur sistem INFORA">
    <x-slot:actions>
        <button type="button" class="btn-primary" id="btnOpenCreateModule">
            <x-icon name="plus" size="18" />
        </button>
    </x-slot:actions>
</x-page-header>

{{-- Flash Alerts --}}
<x-alert type="success" />
<x-alert type="error" />
<x-alert type="validation" />

{{-- Data Table Modul --}}
<x-data-table
    route="{{ route('system.modules.index') }}"
    search-placeholder="Cari modul..."
    :total="$modules->total()"
    total-label="Modul"
>
    <thead>
        <tr>
            <th>Urutan</th>
            <th>Nama Modul</th>
            <th>Jumlah Menu</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($modules as $module)
            <tr>
                <td class="table-cell-id">
                    #{{ $module->order }}
                </td>
                <td>
                    <div class="table-cell-bold">{{ $module->name }}</div>
                </td>
                <td>
                    <a href="{{ route('system.menus.index', ['module_id' => $module->id]) }}" class="badge badge-cyan" title="Lihat menu modul ini">
                        {{ $module->menus_count }} Menu
                    </a>
                </td>
                <td>
                    @if ($module->is_active)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-neutral">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <x-table-actions align="right">
                        <button
                            type="button"
                            class="btn-edit btn-open-edit-module"
                            title="Edit Modul"
                            data-module="{{ json_encode($module) }}"
                            data-action="{{ route('system.modules.update', $module) }}"
                        >
                            <x-icon name="edit" size="14" />
                        </button>
                        <button
                            type="button"
                            class="btn-delete btn-open-delete-module"
                            title="Hapus Modul"
                            data-id="{{ $module->id }}"
                            data-name="{{ $module->name }}"
                            data-order="{{ $module->order }}"
                            data-action="{{ route('system.modules.destroy', $module) }}"
                        >
                            <x-icon name="delete" size="14" />
                        </button>
                    </x-table-actions>
                </td>
            </tr>
        @empty
            <x-empty-state
                icon="search"
                colspan="5"
                title="Belum ada data modul yang ditemukan"
                text="Silakan tambahkan data baru."
            />
        @endforelse
    </tbody>

    <x-slot:footer>
        <x-pagination-summary :paginator="$modules" />
    </x-slot:footer>
</x-data-table>

{{-- Modal Tambah Modul --}}
@include('system.modules.create')

{{-- Modal Edit Modul Component --}}
@include('system.modules.edit')

{{-- Modal Konfirmasi Hapus Modul --}}
<x-modal
    name="delete"
    id="modalDeleteModule"
    title="Hapus Modul?"
    form-id="formDeleteModule"
    cancel-btn-id="btnCancelDeleteModule"
    confirm-btn-id="btnConfirmDeleteModule"
    message-id="deleteModuleMessage"
    badge-id="deleteModuleBadge"
    item-title-id="deleteModuleTitle"
/>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Modal Tambah Modul
    InforaModals.setup('modalCreateModule', { cancelBtnId: 'btnCancelCreateModule' });
    InforaModals.bindOpenButton('btnOpenCreateModule', 'modalCreateModule');

    // 2. Modal Edit Modul
    InforaModals.setup('modalEditModule', { cancelBtnId: 'btnCancelEditModule' });
    InforaModals.bindEditButtons('.btn-open-edit-module', 'modalEditModule', function(modal, module, formEdit) {
        document.getElementById('edit_module_name').value = module.name || '';
        document.getElementById('edit_module_order').value = module.order ?? 1;
        document.getElementById('edit_module_is_active').checked = Boolean(module.is_active);
    });

    // 3. Modal Konfirmasi Hapus Modul
    InforaModals.setup('modalDeleteModule', { cancelBtnId: 'btnCancelDeleteModule' });
    InforaModals.bindDeleteButtons('.btn-open-delete-module', 'modalDeleteModule', function(modal, btn) {
        const name = btn.dataset.name || 'Modul';
        const order = btn.dataset.order ? '#' + btn.dataset.order : 'Modul';
        document.getElementById('modalDeleteModuleTitle').textContent = 'Hapus Modul ' + name + '?';
        document.getElementById('deleteModuleMessage').textContent = 'Hapus modul ini beserta seluruh menu di dalamnya?';
        document.getElementById('deleteModuleBadge').textContent = order;
        document.getElementById('deleteModuleTitle').textContent = name;
    });

    // 4. Reopen modal otomatis saat terjadi error validasi
    window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
    window.errorMethod = @json(old('_method'));
    InforaModals.reopenOnError({
        create: 'modalCreateModule',
        edit: 'modalEditModule'
    });
});
</script>
@endsection
