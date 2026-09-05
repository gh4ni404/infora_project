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
                                <a href="{{ route('system.sub-menus.edit', $subMenu) }}" class="btn-edit" title="Edit Sub-Menu">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                    </svg>
                                    <span>Edit</span>
                                </a>
                                <form method="POST" action="{{ route('system.sub-menus.destroy', $subMenu) }}" onsubmit="return confirm('Hapus sub-menu ini?');" class="form-inline-action">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete" title="Hapus Sub-Menu">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                        <span>Hapus</span>
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
                            <option value="{{ $menu->id }}" {{ old('menu_id', request('menu_id')) == $menu->id ? 'selected' : '' }}>
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
                    <label for="modal_sub_route_name" class="form-label">Nama Rute (Route Name)</label>
                    <input
                        type="text"
                        id="modal_sub_route_name"
                        name="route_name"
                        list="modal_subroutes_list"
                        class="form-input @error('route_name') border-danger @enderror"
                        placeholder="Contoh: system.modules, master.data.sekolah, atau dashboard"
                        value="{{ old('route_name') }}"
                    >
                    <datalist id="modal_subroutes_list">
                        <option value="system.modules">Tata Kelola Modul</option>
                        <option value="system.menus">Tata Kelola Menu</option>
                        <option value="system.sub-menus">Tata Kelola Sub-Menu</option>
                        <option value="dashboard">Dashboard Utama</option>
                    </datalist>
                    @error('route_name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="route-guide-box">
                        <div class="route-guide-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" x2="12" y1="8" y2="12"></line><line x1="12" x2="12.01" y1="16" y2="16"></line></svg>
                            <span>Panduan Format Rute Sub-Menu</span>
                        </div>
                        <ul class="route-guide-list">
                            <li>Cukup gunakan format hierarki praktis <code>modul.menu.submenu</code> (contoh: <code>system.modules</code> atau <code>master.data.sekolah</code>).</li>
                            <li>Sistem otomatis mencocokkan tanpa Anda perlu menentukan akhiran teknis <code>.index</code>.</li>
                        </ul>
                        <div class="route-suggest-pills">
                            <span class="route-suggest-label">Pilihan Cepat Sistem:</span>
                            <button type="button" class="route-suggest-pill" onclick="document.getElementById('modal_sub_route_name').value='system.modules'">system.modules</button>
                            <button type="button" class="route-suggest-pill" onclick="document.getElementById('modal_sub_route_name').value='system.menus'">system.menus</button>
                            <button type="button" class="route-suggest-pill" onclick="document.getElementById('modal_sub_route_name').value='system.sub-menus'">system.sub-menus</button>
                            <button type="button" class="route-suggest-pill" onclick="document.getElementById('modal_sub_route_name').value='dashboard'">dashboard</button>
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
                        value="{{ old('order', 0) }}"
                        min="0"
                    >
                    @error('order')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Urutan numerik dalam sub-menu (0, 1, 2, ...).</div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalCreateSubMenu');
    const btnOpen = document.getElementById('btnOpenCreateSubMenu');
    const btnClose = document.getElementById('btnCloseCreateSubMenu');
    const btnCancel = document.getElementById('btnCancelCreateSubMenu');

    function openModal() {
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.classList.add('modal-open');
        const firstInput = modal.querySelector('input[name="name"]');
        if (firstInput) setTimeout(() => firstInput.focus(), 50);
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    btnOpen && btnOpen.addEventListener('click', openModal);
    btnClose && btnClose.addEventListener('click', closeModal);
    btnCancel && btnCancel.addEventListener('click', closeModal);

    modal && modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    @if ($errors->any())
        openModal();
    @endif
});
</script>
@endsection
