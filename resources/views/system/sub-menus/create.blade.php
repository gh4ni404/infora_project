@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Tambah Sub-Menu Baru</h2>
        <div class="page-subtitle">Daftarkan item sub-menu baru di bawah induk menu sistem INFORA</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('system.sub-menus.index') }}" class="btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <span>Kembali ke Daftar</span>
        </a>
    </div>
</div>

<div class="card-surface">
    <div class="card-header">
        <span class="user-name-text">Formulir Pendaftaran Sub-Menu</span>
        <span class="badge badge-cyan">Tata Kelola Sub-Menu</span>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('system.sub-menus.store') }}">
            @csrf

            <div class="form-group">
                <label for="menu_id" class="form-label">Induk Menu <span class="text-danger">*</span></label>
                <select
                    id="menu_id"
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
                            {{ old('menu_id', $selectedMenuId) == $menu->id ? 'selected' : '' }}
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
                <label for="name" class="form-label">Nama Sub-Menu <span class="text-danger">*</span></label>
                <input
                    type="text"
                    id="name"
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
                            id="route_prefix"
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
                            id="route_suffix"
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
                    <code class="text-brand font-mono font-semibold" id="route_preview">-</code>
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
                <label for="order" class="form-label">Urutan Tampil (Order)</label>
                <input
                    type="number"
                    id="order"
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

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Sub-Menu</span>
                </button>
                <a href="{{ route('system.sub-menus.index') }}" class="btn-secondary">
                    <span>Batal</span>
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuSelect = document.getElementById('menu_id');
    const orderInput = document.getElementById('order');
    const routePrefix = document.getElementById('route_prefix');
    const routeSuffix = document.getElementById('route_suffix');
    const routePreview = document.getElementById('route_preview');

    function updatePreview() {
        if (!routePreview) return;
        const p = (routePrefix ? routePrefix.value : '').trim();
        const s = (routeSuffix ? routeSuffix.value : '').trim();
        let full = '';
        if (p && s) {
            full = p + '.' + s;
        } else if (p) {
            full = p;
        } else if (s) {
            full = s;
        }
        routePreview.textContent = full || '-';
    }

    window.setCreateRoute = function(prefix, suffix) {
        if (routePrefix) routePrefix.value = prefix;
        if (routeSuffix) routeSuffix.value = suffix;
        updatePreview();
    };

    if (routePrefix) routePrefix.addEventListener('input', updatePreview);
    if (routeSuffix) routeSuffix.addEventListener('input', updatePreview);

    if (menuSelect) {
        menuSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.dataset.nextOrder && orderInput) {
                orderInput.value = opt.dataset.nextOrder;
            }
            if (opt && opt.dataset.routePrefix && routePrefix) {
                routePrefix.value = opt.dataset.routePrefix;
                updatePreview();
            }
        });

        // Inisialisasi awal prefix jika belum terisi
        if (menuSelect.value && routePrefix && !routePrefix.value) {
            const currentOpt = menuSelect.options[menuSelect.selectedIndex];
            if (currentOpt && currentOpt.dataset.routePrefix) {
                routePrefix.value = currentOpt.dataset.routePrefix;
            }
        }
    }

    updatePreview();
});
</script>
@endsection
