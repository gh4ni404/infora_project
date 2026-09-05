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
                    class="form-input @error('route_name') border-danger @enderror"
                    placeholder="Contoh: system.modules.index, dashboard (kosongkan jika menu memiliki sub-menu)"
                    value="{{ old('route_name', $menu->route_name) }}"
                >
                @error('route_name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-hint">Nama rute Laravel terdaftar. Biarkan kosong jika menu ini hanya berfungsi sebagai induk accordion sub-menu.</div>
            </div>

            <div class="form-group">
                <label for="icon" class="form-label">Nama Ikon (Lucide)</label>
                <input
                    type="text"
                    id="icon"
                    name="icon"
                    class="form-input @error('icon') border-danger @enderror"
                    placeholder="Contoh: menu, layers, list-tree, layout-dashboard"
                    value="{{ old('icon', $menu->icon) }}"
                >
                @error('icon')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-hint">Daftar ikon yang didukung: <code>menu</code>, <code>layers</code>, <code>list-tree</code>, <code>layout-dashboard</code>, <code>shield-check</code>, <code>settings</code>.</div>
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
