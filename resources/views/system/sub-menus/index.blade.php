@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Tata Kelola Sub-Menu</h2>
        <div class="page-subtitle">Kelola item navigasi sub-menu dan hierarki menu sistem INFORA</div>
    </div>
    <div class="page-actions">
        <button type="button" class="btn-primary" id="btnOpenCreateSubMenu">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Tambah Sub-Menu</span>
        </button>
    </div>
</div>

@if (session('success'))
    <div class="alert-success">
        <div class="alert-content">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

<div class="table-card">
    <div class="table-toolbar">
        <form method="GET" action="{{ route('system.sub-menus.index') }}" class="page-actions">
            <div class="search-box toolbar-search-box">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="Cari sub-menu / rute..."
                    value="{{ request('search') }}"
                >
            </div>

            <select name="menu_id" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Induk Menu</option>
                @foreach ($menus as $menu)
                    <option value="{{ $menu->id }}" {{ request('menu_id') == $menu->id ? 'selected' : '' }}>
                        {{ $menu->module?->name ? $menu->module->name . ' → ' : '' }}{{ $menu->name }}
                    </option>
                @endforeach
            </select>
        </form>

        <div class="table-cell-muted">
            Total: <strong>{{ $subMenus->total() }}</strong> Sub-Menu
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-w-sm">Urutan</th>
                    <th>Induk Menu</th>
                    <th>Nama Sub-Menu</th>
                    <th>Rute Navigasi</th>
                    <th>Status</th>
                    <th class="col-w-actions">Aksi</th>
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
                            <div class="table-actions table-actions-right">
                                <button
                                    type="button"
                                    class="btn-edit btn-open-edit-sub-menu"
                                    title="Edit Sub-Menu"
                                    data-sub-menu="{{ json_encode($subMenu) }}"
                                    data-action="{{ route('system.sub-menus.update', $subMenu) }}"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                    </svg>
                                    <!-- <span>Edit</span> -->
                                </button>
                                <form method="POST" action="{{ route('system.sub-menus.destroy', $subMenu) }}" onsubmit="return confirm('Hapus sub-menu ini?');" class="form-inline-action">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete" title="Hapus Sub-Menu">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                        <!-- <span>Hapus</span> -->
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <svg class="empty-state-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <span class="empty-state-text">Belum ada data sub-menu yang ditemukan.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($subMenus->hasPages())
        <div class="table-footer">
            {{ $subMenus->links() }}
        </div>
    @endif
</div>

<!-- Modal Tambah Sub-Menu -->
<div class="modal-backdrop hidden" id="modalCreateSubMenu" role="dialog" aria-modal="true" aria-labelledby="modalCreateSubMenuTitle">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalCreateSubMenuTitle">Tambah Sub-Menu Baru</h3>
                <p class="modal-subtitle">Daftarkan item sub-menu baru di bawah induk menu sistem INFORA</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseCreateSubMenu" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('system.sub-menus.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="modal_sub_menu_id" class="form-label">Induk Menu <span class="text-danger">*</span></label>
                    <select
                        id="modal_sub_menu_id"
                        name="menu_id"
                        class="form-select @error('menu_id') border-danger @enderror"
                        required
                    >
                        <option value="">-- Pilih Induk Menu --</option>
                        @foreach ($menus as $menu)
                            <option
                                value="{{ $menu->id }}"
                                data-next-order="{{ \App\Models\SubMenu::nextOrder($menu->id) }}"
                                data-route-prefix="{{ $menu->route_prefix }}"
                                {{ old('menu_id', request('menu_id')) == $menu->id ? 'selected' : '' }}
                            >
                                {{ $menu->module?->name ? $menu->module->name . ' → ' : '' }}{{ $menu->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('menu_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Sub-menu akan ditampilkan di dalam dropdown accordion menu induk ini.</div>
                </div>

                <div class="form-group">
                    <label for="modal_sub_name" class="form-label">Nama Sub-Menu <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="modal_sub_name"
                        name="name"
                        class="form-input @error('name') border-danger @enderror"
                        data-transform="title-case"
                        placeholder="Contoh: Modul, Menu, Sub-Menu, Rekapitulasi"
                        value="{{ old('name') }}"
                        required
                    >
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Nama item sub-menu otomatis diformat Capitalize Each Word (Title Case).</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Rute (Route Name)</label>
                    <div class="flex items-center gap-2">
                        <div class="flex-1">
                            <input
                                type="text"
                                id="modal_sub_route_prefix"
                                name="route_prefix"
                                class="form-input font-mono text-sm @error('route_name') border-danger @enderror"
                                placeholder="Prefix (contoh: master)"
                                value="{{ old('route_prefix') }}"
                                autocomplete="off"
                            >
                        </div>
                        <span class="text-xl font-bold text-slate-400 select-none">.</span>
                        <div class="flex-1">
                            <input
                                type="text"
                                id="modal_sub_route_suffix"
                                name="route_suffix"
                                class="form-input font-mono text-sm @error('route_name') border-danger @enderror"
                                placeholder="Sub-rute (contoh: data-sekolah)"
                                value="{{ old('route_suffix') }}"
                                autocomplete="off"
                            >
                        </div>
                    </div>
                    @error('route_name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint flex items-center gap-2 mt-1.5">
                        <span>Pratinjau Rute:</span>
                        <code class="text-brand font-mono font-semibold" id="modal_sub_route_preview">-</code>
                    </div>
                    <div class="route-guide-box">
                        <div class="route-guide-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" y2="12"></line><line x1="12" x2="12.01" y1="16" y2="16"></line></svg>
                            <span>Panduan Format Rute Sub-Menu</span>
                        </div>
                        <ul class="route-guide-list">
                            <li>Kotak pertama (Prefix) otomatis terisi sesuai nama induk menu dan <strong>tetap dapat diedit</strong> sesuai kebutuhan.</li>
                            <li>Kotak kedua diisi nama rute spesifik fitur (contoh: <code>data-sekolah</code>). Jika rute tunggal tanpa prefix (misal <code>dashboard</code>), cukup isi kotak kedua atau kotak pertama saja.</li>
                        </ul>
                        <div class="route-suggest-pills">
                            <span class="route-suggest-label">Pilihan Cepat:</span>
                            <button type="button" class="route-suggest-pill" onclick="setCreateRoute('system', 'modules')">system.modules</button>
                            <button type="button" class="route-suggest-pill" onclick="setCreateRoute('system', 'menus')">system.menus</button>
                            <button type="button" class="route-suggest-pill" onclick="setCreateRoute('system', 'sub-menus')">system.sub-menus</button>
                            <button type="button" class="route-suggest-pill" onclick="setCreateRoute('', 'dashboard')">dashboard</button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="modal_sub_order" class="form-label">Urutan Tampil (Order)</label>
                    <input
                        type="number"
                        id="modal_sub_order"
                        name="order"
                        class="form-input @error('order') border-danger @enderror"
                        value="{{ old('order', $nextOrder) }}"
                        min="1"
                    >
                    @error('order')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Urutan numerik dalam sub-menu (1, 2, 3, ...).</div>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="form-check-input"
                            {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                        >
                        <span class="form-check-label">Aktifkan sub-menu ini pada navigasi sistem</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelCreateSubMenu">Batal</button>
                <button type="submit" class="btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Sub-Menu</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Sub-Menu Component -->
@include('system.sub-menus.edit')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Create Modal
    const createModal = document.getElementById('modalCreateSubMenu');
    const btnOpenCreate = document.getElementById('btnOpenCreateSubMenu');
    const btnCloseCreate = document.getElementById('btnCloseCreateSubMenu');
    const btnCancelCreate = document.getElementById('btnCancelCreateSubMenu');

    function openCreateModal() {
        if (!createModal) return;
        createModal.classList.remove('hidden');
        document.body.classList.add('modal-open');
        const firstInput = createModal.querySelector('input[name="name"]');
        if (firstInput) setTimeout(() => firstInput.focus(), 50);
    }

    function closeCreateModal() {
        if (!createModal) return;
        createModal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    btnOpenCreate && btnOpenCreate.addEventListener('click', openCreateModal);
    btnCloseCreate && btnCloseCreate.addEventListener('click', closeCreateModal);
    btnCancelCreate && btnCancelCreate.addEventListener('click', closeCreateModal);

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

    createModal && createModal.addEventListener('click', function(e) {
        if (e.target === createModal) {
            closeCreateModal();
        }
    });

    // Edit Modal
    const editModal = document.getElementById('modalEditSubMenu');
    const formEdit = document.getElementById('formEditSubMenu');
    const btnCloseEdit = document.getElementById('btnCloseEditSubMenu');
    const btnCancelEdit = document.getElementById('btnCancelEditSubMenu');

    function openEditModal() {
        if (!editModal) return;
        editModal.classList.remove('hidden');
        document.body.classList.add('modal-open');
        const firstInput = editModal.querySelector('input[name="name"]');
        if (firstInput) setTimeout(() => firstInput.focus(), 50);
    }

    function closeEditModal() {
        if (!editModal) return;
        editModal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    btnCloseEdit && btnCloseEdit.addEventListener('click', closeEditModal);
    btnCancelEdit && btnCancelEdit.addEventListener('click', closeEditModal);

    editModal && editModal.addEventListener('click', function(e) {
        if (e.target === editModal) {
            closeEditModal();
        }
    });

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

    document.querySelectorAll('.btn-open-edit-sub-menu').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const subMenu = JSON.parse(this.dataset.subMenu || '{}');
            const action = this.dataset.action;
            currentEditingSubMenu = subMenu;

            formEdit.action = action;
            document.getElementById('edit_submenu_menu_id').value = subMenu.menu_id || '';
            document.getElementById('edit_submenu_name').value = subMenu.name || '';

            // Split route_name menjadi prefix & suffix
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

            openEditModal();
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (createModal && !createModal.classList.contains('hidden')) {
                closeCreateModal();
            }
            if (editModal && !editModal.classList.contains('hidden')) {
                closeEditModal();
            }
        }
    });

    @if ($errors->any())
        @if (old('_method') === 'PUT')
            openEditModal();
        @else
            openCreateModal();
        @endif
    @endif
});
</script>
@endsection
