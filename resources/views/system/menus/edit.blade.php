@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Edit Menu: {{ $menu->name }}</h2>
        <div class="page-subtitle">Perbarui data navigasi menu dan relasi modul</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('system.menus.index') }}" class="btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <span>Kembali ke Daftar</span>
        </a>
    </div>
</div>

<div class="card-surface">
    <div class="card-header">
        <span class="user-name-text">Formulir Perubahan Menu</span>
        <span class="badge badge-cyan">ID #{{ $menu->id }}</span>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('system.menus.update', $menu) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="module_id" class="form-label">Induk Modul <span class="text-danger">*</span></label>
                <select
                    id="module_id"
                    name="module_id"
                    class="form-select @error('module_id') border-danger @enderror"
                    required
                >
                    <option value="">-- Pilih Induk Modul --</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module->id }}" {{ old('module_id', $menu->module_id) == $module->id ? 'selected' : '' }}>
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
                <label for="name" class="form-label">Nama Menu <span class="text-danger">*</span></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-input @error('name') border-danger @enderror"
                    placeholder="Contoh: Modul, Menu, Data Siswa, Mata Pelajaran"
                    value="{{ old('name', $menu->name) }}"
                    required
                >
                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-hint">Nama item menu yang akan tampil di sidebar.</div>
            </div>

            <div class="form-group">
                <label for="route_name" class="form-label">Nama Rute (Route Name)</label>
                <input
                    type="text"
                    id="route_name"
                    name="route_name"
                    list="registered_routes_list"
                    class="form-input @error('route_name') border-danger @enderror"
                    placeholder="Contoh: system.modules, master.data, atau dashboard"
                    value="{{ old('route_name', $menu->route_name) }}"
                >
                <datalist id="registered_routes_list">
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
                        <li><strong>Menu Langsung:</strong> Cukup gunakan format hierarki <code>modul.menu</code> (contoh: <code>system.modules</code>). Sistem otomatis mencocokkan tanpa Anda perlu repot menentukan akhiran <code>.index</code>.</li>
                    </ul>
                    <div class="route-suggest-pills">
                        <span class="route-suggest-label">Pilihan Cepat:</span>
                        <button type="button" class="route-suggest-pill" onclick="document.getElementById('route_name').value='dashboard'">dashboard</button>
                        <button type="button" class="route-suggest-pill" onclick="document.getElementById('route_name').value='system.modules'">system.modules</button>
                        <button type="button" class="route-suggest-pill" onclick="document.getElementById('route_name').value='system.menus'">system.menus</button>
                        <button type="button" class="route-suggest-pill" onclick="document.getElementById('route_name').value=''">[Kosongkan - Sebagai Induk]</button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Ikon Menu (Lucide)</label>
                <x-icon-picker name="icon" :value="old('icon', $menu->icon ?? 'menu')" />
                @error('icon')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-hint">Pilih ikon visual dari katalog resmi (100% gratis) untuk mewakili menu pada sidebar.</div>
            </div>

            <div class="form-group">
                <label for="order" class="form-label">Urutan Tampil (Order)</label>
                <input
                    type="number"
                    id="order"
                    name="order"
                    class="form-input @error('order') border-danger @enderror"
                    value="{{ old('order', $menu->order) }}"
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
                        {{ old('is_active', $menu->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                    >
                    <span class="form-check-label">Aktifkan menu ini pada navigasi sistem</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Perubahan</span>
                </button>
                <a href="{{ route('system.menus.index') }}" class="btn-secondary">
                    <span>Batal</span>
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
