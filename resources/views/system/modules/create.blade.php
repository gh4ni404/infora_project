@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Tambah Modul Baru</h2>
        <div class="page-subtitle">Daftarkan modul navigasi baru ke dalam hierarki sistem INFORA</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('system.modules.index') }}" class="btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <span>Kembali ke Daftar</span>
        </a>
    </div>
</div>

<div class="card-surface">
    <div class="card-header">
        <span class="user-name-text">Formulir Pendaftaran Modul</span>
        <span class="badge badge-cyan">Tata Kelola Modul</span>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('system.modules.store') }}">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">Nama Modul <span class="text-danger">*</span></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-input @error('name') border-danger @enderror"
                    placeholder="Contoh: Tata Kelola Sistem, Akademik, Kesiswaan"
                    value="{{ old('name') }}"
                    required
                >
                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-hint">Nama modul akan ditampilkan sebagai judul grup kategori pada sidebar.</div>
            </div>

            <div class="form-group">
                <label class="form-label">Ikon Modul (Lucide)</label>
                <x-icon-picker name="icon" :value="old('icon', 'layers')" />
                @error('icon')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-hint">Pilih ikon visual dari katalog resmi (100% gratis) untuk mewakili modul pada sidebar.</div>
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
                <div class="form-hint">Urutan numerik dari yang terkecil (0, 1, 2, ...) untuk penataan posisi di sidebar.</div>
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
                    <span class="form-check-label">Aktifkan modul ini pada sidebar sistem</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Modul</span>
                </button>
                <a href="{{ route('system.modules.index') }}" class="btn-secondary">
                    <span>Batal</span>
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
