@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Tata Kelola Menu</h2>
        <div class="page-subtitle">Kelola navigasi menu utama dan hierarki induk modul INFORA</div>
    </div>
    <div class="page-actions">
        <button type="button" class="btn-primary" id="btnOpenCreateMenu">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Tambah Menu</span>
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
        <form method="GET" action="{{ route('system.menus.index') }}" class="page-actions">
            <div class="search-box toolbar-search-box">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="Cari nama menu / rute..."
                    value="{{ request('search') }}"
                >
            </div>

            <select name="module_id" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Modul</option>
                @foreach ($modules as $module)
                    <option value="{{ $module->id }}" {{ request('module_id') == $module->id ? 'selected' : '' }}>
                        {{ $module->name }}
                    </option>
                @endforeach
            </select>
        </form>

        <div class="table-cell-muted">
            Total: <strong>{{ $menus->total() }}</strong> Menu
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-w-sm">Urutan</th>
                    <th>Induk Modul</th>
                    <th>Ikon & Menu</th>
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
                        <td>
                            <div class="table-actions table-actions-right">
                                <a href="{{ route('system.menus.edit', $menu) }}" class="btn-edit" title="Edit Menu">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                    </svg>
                                    <span>Edit</span>
                                </a>
                                <form method="POST" action="{{ route('system.menus.destroy', $menu) }}" onsubmit="return confirm('Hapus menu ini beserta seluruh sub-menu di dalamnya?');" class="form-inline-action">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete" title="Hapus Menu">
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
                        <td colspan="7">
                            <div class="empty-state">
                                <svg class="empty-state-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <span class="empty-state-text">Belum ada data menu yang ditemukan.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($menus->hasPages())
        <div class="table-footer">
            {{ $menus->links() }}
        </div>
    @endif
</div>

<!-- Modal Tambah Menu -->
<div class="modal-backdrop hidden" id="modalCreateMenu" role="dialog" aria-modal="true" aria-labelledby="modalCreateMenuTitle">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalCreateMenuTitle">Tambah Menu Baru</h3>
                <p class="modal-subtitle">Daftarkan menu navigasi baru di bawah modul sistem INFORA</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseCreateMenu" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('system.menus.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="modal_module_id" class="form-label">Induk Modul <span class="text-danger">*</span></label>
                    <select
                        id="modal_module_id"
                        name="module_id"
                        class="form-select @error('module_id') border-danger @enderror"
                        required
                    >
                        <option value="">-- Pilih Induk Modul --</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module->id }}" {{ old('module_id', request('module_id')) == $module->id ? 'selected' : '' }}>
                                {{ $module->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('module_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Menu akan dikelompokkan di bawah modul yang Anda tentukan.</div>
                </div>

                <div class="form-group">
                    <label for="modal_menu_name" class="form-label">Nama Menu <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="modal_menu_name"
                        name="name"
                        class="form-input @error('name') border-danger @enderror"
                        placeholder="Contoh: Modul, Menu, Data Siswa, Mata Pelajaran"
                        value="{{ old('name') }}"
                        required
                    >
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Nama item menu yang akan tampil di sidebar.</div>
                </div>

                <div class="form-group">
                    <label for="modal_route_name" class="form-label">Nama Rute (Route Name)</label>
                    <input
                        type="text"
                        id="modal_route_name"
                        name="route_name"
                        list="modal_menu_routes_list"
                        class="form-input @error('route_name') border-danger @enderror"
                        placeholder="Contoh: system.modules, master.data, atau dashboard"
                        value="{{ old('route_name') }}"
                    >
                    <datalist id="modal_menu_routes_list">
                        <option value="dashboard">Dashboard Utama</option>
                        <option value="system.modules">Tata Kelola Modul</option>
                        <option value="system.menus">Tata Kelola Menu</option>
                        <option value="system.sub-menus">Tata Kelola Sub-Menu</option>
                    </datalist>
                    @error('route_name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="route-guide-box">
                        <div class="route-guide-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" x2="12" y1="8" y2="12"></line><line x1="12" x2="12.01" y1="16" y2="16"></line></svg>
                            <span>Panduan Nama Rute</span>
                        </div>
                        <ul class="route-guide-list">
                            <li><strong>Menu Induk (Ber-Submenu):</strong> Biarkan <strong>kosong</strong> jika menu ini hanya menjadi induk dropdown accordion.</li>
                            <li><strong>Menu Langsung:</strong> Cukup gunakan format hierarki <code>modul.menu</code> (contoh: <code>system.modules</code>). Sistem otomatis mencocokkan tanpa perlu akhiran <code>.index</code>.</li>
                        </ul>
                        <div class="route-suggest-pills">
                            <span class="route-suggest-label">Pilihan Cepat:</span>
                            <button type="button" class="route-suggest-pill" onclick="document.getElementById('modal_route_name').value='dashboard'">dashboard</button>
                            <button type="button" class="route-suggest-pill" onclick="document.getElementById('modal_route_name').value='system.modules'">system.modules</button>
                            <button type="button" class="route-suggest-pill" onclick="document.getElementById('modal_route_name').value='system.menus'">system.menus</button>
                            <button type="button" class="route-suggest-pill" onclick="document.getElementById('modal_route_name').value=''">[Kosongkan - Sebagai Induk]</button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Ikon Menu (Lucide)</label>
                    <x-icon-picker name="icon" :value="old('icon', 'menu')" id="modal_menu_icon" />
                    @error('icon')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Pilih ikon visual dari katalog resmi (100% gratis) untuk mewakili menu pada sidebar.</div>
                </div>

                <div class="form-group">
                    <label for="modal_menu_order" class="form-label">Urutan Tampil (Order)</label>
                    <input
                        type="number"
                        id="modal_menu_order"
                        name="order"
                        class="form-input @error('order') border-danger @enderror"
                        value="{{ old('order', 0) }}"
                        min="0"
                    >
                    @error('order')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Urutan numerik dalam modul (0, 1, 2, ...).</div>
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
                        <span class="form-check-label">Aktifkan menu ini pada navigasi sistem</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelCreateMenu">Batal</button>
                <button type="submit" class="btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Menu</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalCreateMenu');
    const btnOpen = document.getElementById('btnOpenCreateMenu');
    const btnClose = document.getElementById('btnCloseCreateMenu');
    const btnCancel = document.getElementById('btnCancelCreateMenu');

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
            const iconModal = document.querySelector('.icon-picker-modal-backdrop:not(.hidden)');
            if (!iconModal) {
                closeModal();
            }
        }
    });

    @if ($errors->any())
        openModal();
    @endif
});
</script>
@endsection
