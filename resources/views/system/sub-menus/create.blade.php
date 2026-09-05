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
                        <option value="{{ $menu->id }}" {{ old('menu_id', $selectedMenuId) == $menu->id ? 'selected' : '' }}>
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
                    placeholder="Contoh: Tambah Data, Rekapitulasi, Riwayat"
                    value="{{ old('name') }}"
                    required
                >
                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-hint">Label nama sub-menu yang tampil di sidebar.</div>
            </div>

            <div class="form-group">
                <label for="route_name" class="form-label">Nama Rute (Route Name)</label>
                <input
                    type="text"
                    id="route_name"
                    name="route_name"
                    class="form-input @error('route_name') border-danger @enderror"
                    placeholder="Contoh: system.modules.create, dashboard"
                    value="{{ old('route_name') }}"
                >
                @error('route_name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-hint">Nama rute Laravel terdaftar yang dituju saat sub-menu ini diklik.</div>
            </div>

            <div class="form-group">
                <label for="order" class="form-label">Urutan Tampil (Order)</label>
                <input
                    type="number"
                    id="order"
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
@endsection
