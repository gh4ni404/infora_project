@extends('layouts.app')

@section('content')
<x-page-header title="Tata Kelola Menu" subtitle="Kelola navigasi menu utama dan hierarki induk modul INFORA">
    <x-slot:actions>
        <x-crud-button 
            action="create"
            id="btnOpenCreateMenu"
        />
    </x-slot:actions>
</x-page-header>

{{-- Flash Alerts --}}
<x-alert type="success" />
<x-alert type="error" />
<x-alert type="validation" />

{{-- Data Table Menu --}}
<x-data-table
    route="{{ route('system.menus.index') }}"
    search-placeholder="Cari nama menu / rute..."
    :total="$menus->total()"
    total-label="Menu"
>
    <x-slot:filters>
        <x-form-select
            name="module_id"
            id="filter_module_id"
            class="form-select-sm"
            group-class="m-0"
            placeholder="Semua Modul"
            :selected="request('module_id')"
            :options="$modules"
            onchange="this.form.submit()"
        />
    </x-slot:filters>

    <thead>
        <tr>
            <th>Urutan</th>
            <th>Induk Modul</th>
            <th>Ikon dan Menu</th>
            <th>Rute Navigasi</th>
            <th>Sub-Menu</th>
            <th>Status</th>
            <th class="col-w-actions">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($menus as $menu)
            <tr>
                <td class="table-cell-id">
                    #{{ $menu->order }}
                </td>
                <td>
                    <span class="badge badge-cyan">
                        {{ $menu->module?->name ?? 'Tanpa Modul' }}
                    </span>
                </td>
                <td>
                    <div class="alert-content">
                        <span class="badge-icon-preview">
                            <x-icon :name="$menu->icon" class="badge-icon-svg" />
                        </span>
                        <span class="table-cell-bold">{{ $menu->name }}</span>
                    </div>
                </td>
                <td>
                    @if ($menu->route_name)
                        <code class="text-brand">{{ $menu->route_name }}</code>
                    @else
                        <span class="table-cell-muted">-</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('system.sub-menus.index', ['menu_id' => $menu->id]) }}" class="badge badge-neutral" title="Lihat sub-menu">
                        {{ $menu->sub_menus_count }} Sub-Menu
                    </a>
                </td>
                <td>
                    @if ($menu->is_active)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-neutral">Nonaktif</span>
                    @endif
                </td>
                <td class="col-w-actions">
                    <x-table-actions>
                        <x-crud-button
                            action="edit"
                            class="btn-open-edit-menu"
                            title="Edit Menu"
                            data-menu="{{ json_encode($menu) }}"
                            data-action="{{ route('system.menus.update', $menu) }}"
                        />

                        <x-crud-button
                            action="delete"
                            class="btn-open-delete-menu"
                            title="Hapus Menu"
                            data-id="{{ $menu->id }}"
                            data-name="{{ $menu->name }}"
                            data-module="{{ $menu->module?->name ?? 'Tanpa Modul' }}"
                            data-action="{{ route('system.menus.destroy', $menu) }}"
                        />
                    </x-table-actions>
                </td>
            </tr>
        @empty
            <x-empty-state
                icon="search"
                colspan="7"
                title="Belum ada data menu yang ditemukan"
                text="Silakan tambahkan data baru."
            />
        @endforelse
    </tbody>

    <x-slot:footer>
        <x-pagination-summary :paginator="$menus" />
    </x-slot:footer>
</x-data-table>

{{-- include menu creation modal --}}
@include('system.menus.create')

{{-- include menu edit modal --}}
@include('system.menus.edit')

{{-- Modal Konfirmasi Hapus Menu --}}
<x-modal
    name="delete"
    id="modalDeleteMenu"
    title="Hapus Menu?"
    form-id="formDeleteMenu"
    cancel-btn-id="btnCancelDeleteMenu"
    confirm-btn-id="btnConfirmDeleteMenu"
    message-id="deleteMenuMessage"
    badge-id="deleteMenuBadge"
    item-title-id="deleteMenuTitle"
/>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Modal Tambah Menu
    InforaModals.setup('modalCreateMenu', { cancelBtnId: 'btnCancelCreateMenu' });
    InforaModals.bindOpenButton('btnOpenCreateMenu', 'modalCreateMenu');

    const modalModuleSelect = document.getElementById('modal_module_id');
    const modalMenuOrder = document.getElementById('modal_menu_order');
    if (modalModuleSelect && modalMenuOrder) {
        modalModuleSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.dataset.nextOrder) {
                modalMenuOrder.value = opt.dataset.nextOrder;
            }
        });
    }

    // 2. Modal Edit Menu
    InforaModals.setup('modalEditMenu', { cancelBtnId: 'btnCancelEditMenu' });

    let currentEditingMenu = null;

    const editModuleSelect = document.getElementById('edit_menu_module_id');
    const editMenuOrder = document.getElementById('edit_menu_order');
    if (editModuleSelect && editMenuOrder) {
        editModuleSelect.addEventListener('change', function() {
            const selectedModuleId = this.value;
            if (currentEditingMenu && String(selectedModuleId) === String(currentEditingMenu.module_id)) {
                editMenuOrder.value = currentEditingMenu.order ?? 1;
            } else {
                const opt = this.options[this.selectedIndex];
                if (opt && opt.dataset.nextOrder) {
                    editMenuOrder.value = opt.dataset.nextOrder;
                }
            }
        });
    }

    InforaModals.bindEditButtons('.btn-open-edit-menu', 'modalEditMenu', function(modal, menu, formEdit) {
        currentEditingMenu = menu;
        document.getElementById('edit_menu_module_id').value = menu.module_id || '';
        document.getElementById('edit_menu_name').value = menu.name || '';
        document.getElementById('edit_menu_route_name').value = menu.route_name || '';
        document.getElementById('edit_menu_order').value = menu.order ?? 1;
        document.getElementById('edit_menu_is_active').checked = Boolean(menu.is_active);

        if (typeof window['setIconValue_edit_menu_icon'] === 'function') {
            window['setIconValue_edit_menu_icon'](menu.icon || 'menu');
        }
    });

    // 3. Modal Konfirmasi Hapus Menu
    InforaModals.setup('modalDeleteMenu', { cancelBtnId: 'btnCancelDeleteMenu' });
    InforaModals.bindDeleteButtons('.btn-open-delete-menu', 'modalDeleteMenu', function(modal, btn) {
        const name = btn.dataset.name || 'Menu';
        const module = btn.dataset.module || '-';
        document.getElementById('modalDeleteMenuTitle').textContent = 'Hapus Menu ' + name + '?';
        document.getElementById('deleteMenuMessage').textContent = 'Hapus menu ini beserta seluruh sub-menu di dalamnya?';
        document.getElementById('deleteMenuBadge').textContent = module;
        document.getElementById('deleteMenuTitle').textContent = name;
    });

    // 4. Reopen modal otomatis saat terjadi error validasi
    window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
    window.errorMethod = @json(old('_method'));
    InforaModals.reopenOnError({
        create: 'modalCreateMenu',
        edit: 'modalEditMenu'
    });
});
</script>
@endsection
