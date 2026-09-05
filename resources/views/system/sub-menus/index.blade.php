@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Tata Kelola Sub-Menu</h2>
        <div class="page-subtitle">Kelola item navigasi sub-menu dan hierarki menu sistem INFORA</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('system.sub-menus.create') }}" class="btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Tambah Sub-Menu</span>
        </a>
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
@endsection
