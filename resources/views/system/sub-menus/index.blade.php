@extends('layouts.app')

@section('content')
<x-page-header title="Tata Kelola Sub-Menu" subtitle="Kelola item navigasi sub-menu dan hierarki menu sistem INFORA">
    <x-slot:actions>
        <button type="button" class="btn-primary" id="btnOpenCreateSubMenu">
            <x-icon name="plus" size="18" />
        </button>
    </x-slot:actions>
</x-page-header>

{{-- Flash Alerts --}}
<x-alert type="success" />
<x-alert type="error" />
<x-alert type="validation" />

{{-- Data Table Sub-Menu --}}
<x-data-table
    route="{{ route('system.sub-menus.index') }}"
    search-placeholder="Cari sub-menu / rute..."
    :total="$subMenus->total()"
    total-label="Sub-Menu"
>
    <x-slot:filters>
        <x-form-select
            name="menu_id"
            id="filter_menu_id"
            class="form-select-sm"
            group-class="m-0"
            placeholder="Semua Induk Menu"
            :selected="request('menu_id')"
            onchange="this.form.submit()"
        >
            @foreach ($menus as $menu)
                <option value="{{ $menu->id }}" {{ request('menu_id') == $menu->id ? 'selected' : '' }}>
                    {{ $menu->module?->name ? $menu->module->name . ' → ' : '' }}{{ $menu->name }}
                </option>
            @endforeach
        </x-form-select>
    </x-slot:filters>

    <thead>
        <tr>
            <th>Urutan</th>
            <th>Induk Menu</th>
            <th>Nama Sub-Menu</th>
            <th>Rute Navigasi</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($subMenus as $subMenu)
            <tr>
                <td class="table-cell-id">
                    #{{ $subMenu->order }}
                </td>
                <td>
                    <div class="alert-content">
                        <span class="badge badge-cyan">
                            {{ $subMenu->menu?->name ?? 'Tanpa Menu' }}
                        </span>
                        @if ($subMenu->menu?->module)
                            <span class="table-cell-muted">({{ $subMenu->menu->module->name }})</span>
                        @endif
                    </div>
                </td>
                <td>
                    <div class="alert-content">
                        <span class="nav-submenu-bullet"></span>
                        <span class="table-cell-bold">{{ $subMenu->name }}</span>
                    </div>
                </td>
                <td>
                    @if ($subMenu->route_name)
                        <code class="text-brand">{{ $subMenu->route_name }}</code>
                    @else
                        <span class="table-cell-muted">-</span>
                    @endif
                </td>
                <td>
                    @if ($subMenu->is_active)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-neutral">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <x-table-actions align="right">
                        <button
                            type="button"
                            class="btn-edit btn-open-edit-sub-menu"
                            title="Edit Sub-Menu"
                            data-sub-menu="{{ json_encode($subMenu) }}"
                            data-action="{{ route('system.sub-menus.update', $subMenu) }}"
                        >
                            <x-icon name="edit" size="14" />
                        </button>
                        <button
                            type="button"
                            class="btn-delete btn-open-delete-sub-menu"
                            title="Hapus Sub-Menu"
                            data-id="{{ $subMenu->id }}"
                            data-name="{{ $subMenu->name }}"
                            data-menu="{{ $subMenu->menu?->name ?? 'Tanpa Menu' }}"
                            data-action="{{ route('system.sub-menus.destroy', $subMenu) }}"
                        >
                            <x-icon name="delete" size="14" />
                        </button>
                    </x-table-actions>
                </td>
            </tr>
        @empty
            <x-empty-state
                icon="search"
                colspan="6"
                title="Belum ada data sub-menu yang ditemukan"
                text="Silakan tambahkan data baru."
            />
        @endforelse
    </tbody>

    <x-slot:footer>
        <x-pagination-summary :paginator="$subMenus" />
    </x-slot:footer>
</x-data-table>

{{-- Modal Tambah Sub-Menu --}}
@include('system.sub-menus.create')

{{-- Modal Edit Sub-Menu Component --}}
@include('system.sub-menus.edit')

{{-- Modal Konfirmasi Hapus Sub-Menu --}}
<x-modal
    name="delete"
    id="modalDeleteSubMenu"
    title="Hapus Sub-Menu?"
    form-id="formDeleteSubMenu"
    cancel-btn-id="btnCancelDeleteSubMenu"
    confirm-btn-id="btnConfirmDeleteSubMenu"
    message-id="deleteSubMenuMessage"
    badge-id="deleteSubMenuBadge"
    item-title-id="deleteSubMenuTitle"
/>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Modal Tambah Sub-Menu
    InforaModals.setup('modalCreateSubMenu', { cancelBtnId: 'btnCancelCreateSubMenu' });
    InforaModals.bindOpenButton('btnOpenCreateSubMenu', 'modalCreateSubMenu');

    const modalSubMenuSelect = document.getElementById('modal_sub_menu_id');
    const modalSubOrder = document.getElementById('modal_sub_order');
    const modalSubRoutePrefix = document.getElementById('modal_sub_route_prefix');
    const modalSubRouteSuffix = document.getElementById('modal_sub_route_suffix');
    const modalSubRoutePreview = document.getElementById('modal_sub_route_preview');

    function updateCreateRoutePreview() {
        if (!modalSubRoutePreview) return;
        const p = (modalSubRoutePrefix ? modalSubRoutePrefix.value : '').trim();
        const s = (modalSubRouteSuffix ? modalSubRouteSuffix.value : '').trim();
        let full = '';
        if (p && s) {
            full = p + '.' + s;
        } else if (p) {
            full = p;
        } else if (s) {
            full = s;
        }
        modalSubRoutePreview.textContent = full || '-';
    }

    window.setCreateRoute = function(prefix, suffix) {
        if (modalSubRoutePrefix) modalSubRoutePrefix.value = prefix;
        if (modalSubRouteSuffix) modalSubRouteSuffix.value = suffix;
        updateCreateRoutePreview();
    };

    if (modalSubRoutePrefix) modalSubRoutePrefix.addEventListener('input', updateCreateRoutePreview);
    if (modalSubRouteSuffix) modalSubRouteSuffix.addEventListener('input', updateCreateRoutePreview);

    if (modalSubMenuSelect) {
        modalSubMenuSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.dataset.nextOrder && modalSubOrder) {
                modalSubOrder.value = opt.dataset.nextOrder;
            }
            if (opt && opt.dataset.routePrefix && modalSubRoutePrefix) {
                modalSubRoutePrefix.value = opt.dataset.routePrefix;
                updateCreateRoutePreview();
            }
        });

        // Set prefix awal jika sudah ada opsi terpilih saat buka create
        if (modalSubMenuSelect.value && modalSubRoutePrefix && !modalSubRoutePrefix.value) {
            const currentOpt = modalSubMenuSelect.options[modalSubMenuSelect.selectedIndex];
            if (currentOpt && currentOpt.dataset.routePrefix) {
                modalSubRoutePrefix.value = currentOpt.dataset.routePrefix;
            }
        }
    }
    updateCreateRoutePreview();

    // 2. Modal Edit Sub-Menu
    InforaModals.setup('modalEditSubMenu', { cancelBtnId: 'btnCancelEditSubMenu' });

    let currentEditingSubMenu = null;

    const editSubMenuSelect = document.getElementById('edit_submenu_menu_id');
    const editSubOrder = document.getElementById('edit_submenu_order');
    const editSubRoutePrefix = document.getElementById('edit_submenu_route_prefix');
    const editSubRouteSuffix = document.getElementById('edit_submenu_route_suffix');
    const editSubRoutePreview = document.getElementById('edit_submenu_route_preview');

    function updateEditRoutePreview() {
        if (!editSubRoutePreview) return;
        const p = (editSubRoutePrefix ? editSubRoutePrefix.value : '').trim();
        const s = (editSubRouteSuffix ? editSubRouteSuffix.value : '').trim();
        let full = '';
        if (p && s) {
            full = p + '.' + s;
        } else if (p) {
            full = p;
        } else if (s) {
            full = s;
        }
        editSubRoutePreview.textContent = full || '-';
    }

    window.setEditRoute = function(prefix, suffix) {
        if (editSubRoutePrefix) editSubRoutePrefix.value = prefix;
        if (editSubRouteSuffix) editSubRouteSuffix.value = suffix;
        updateEditRoutePreview();
    };

    if (editSubRoutePrefix) editSubRoutePrefix.addEventListener('input', updateEditRoutePreview);
    if (editSubRouteSuffix) editSubRouteSuffix.addEventListener('input', updateEditRoutePreview);

    if (editSubMenuSelect && editSubOrder) {
        editSubMenuSelect.addEventListener('change', function() {
            const selectedMenuId = this.value;
            if (currentEditingSubMenu && String(selectedMenuId) === String(currentEditingSubMenu.menu_id)) {
                editSubOrder.value = currentEditingSubMenu.order ?? 1;
                // Pulihkan prefix asli sub-menu
                if (editSubRoutePrefix) {
                    const originalDot = (currentEditingSubMenu.route_name || '').indexOf('.');
                    editSubRoutePrefix.value = originalDot !== -1 
                        ? currentEditingSubMenu.route_name.substring(0, originalDot) 
                        : '';
                }
            } else {
                const opt = this.options[this.selectedIndex];
                if (opt && opt.dataset.nextOrder) {
                    editSubOrder.value = opt.dataset.nextOrder;
                }
                if (opt && opt.dataset.routePrefix && editSubRoutePrefix) {
                    editSubRoutePrefix.value = opt.dataset.routePrefix;
                }
            }
            updateEditRoutePreview();
        });
    }

    InforaModals.bindEditButtons('.btn-open-edit-sub-menu', 'modalEditSubMenu', function(modal, subMenu, formEdit) {
        currentEditingSubMenu = subMenu;

        document.getElementById('edit_submenu_menu_id').value = subMenu.menu_id || '';
        document.getElementById('edit_submenu_name').value = subMenu.name || '';

        // Split route_name menjadi prefix dan suffix
        let prefix = '';
        let suffix = '';
        if (subMenu.route_name) {
            const dotIndex = subMenu.route_name.indexOf('.');
            if (dotIndex !== -1) {
                prefix = subMenu.route_name.substring(0, dotIndex);
                suffix = subMenu.route_name.substring(dotIndex + 1);
            } else {
                prefix = '';
                suffix = subMenu.route_name;
            }
        }
        if (editSubRoutePrefix) editSubRoutePrefix.value = prefix;
        if (editSubRouteSuffix) editSubRouteSuffix.value = suffix;
        updateEditRoutePreview();

        document.getElementById('edit_submenu_order').value = subMenu.order ?? 1;
        document.getElementById('edit_submenu_is_active').checked = Boolean(subMenu.is_active);
    });

    // 3. Modal Konfirmasi Hapus Sub-Menu
    InforaModals.setup('modalDeleteSubMenu', { cancelBtnId: 'btnCancelDeleteSubMenu' });
    InforaModals.bindDeleteButtons('.btn-open-delete-sub-menu', 'modalDeleteSubMenu', function(modal, btn) {
        const name = btn.dataset.name || 'Sub-Menu';
        const menu = btn.dataset.menu || '-';
        document.getElementById('modalDeleteSubMenuTitle').textContent = 'Hapus Sub-Menu ' + name + '?';
        document.getElementById('deleteSubMenuMessage').textContent = 'Apakah Anda yakin ingin menghapus sub-menu ini?';
        document.getElementById('deleteSubMenuBadge').textContent = menu;
        document.getElementById('deleteSubMenuTitle').textContent = name;
    });

    // 4. Reopen modal otomatis saat terjadi error validasi
    window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
    window.errorMethod = @json(old('_method'));
    InforaModals.reopenOnError({
        create: 'modalCreateSubMenu',
        edit: 'modalEditSubMenu'
    });
});
</script>
@endsection
