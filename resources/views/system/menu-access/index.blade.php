@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Tata Kelola Menu Akses</h2>
        <div class="page-subtitle">Atur izin navigasi dan hak aksi (Lihat, Tambah, Ubah, Hapus) per pengguna serta konfigurasi template peran</div>
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

<!-- Tab Navigasi Utama: Daftar Pengguna vs Template Peran -->
<div class="card-surface">
    <div class="card-header">
        <div class="tab-pills">
            <a href="{{ route('sistem.menu-akses', ['tab' => 'users']) }}" class="tab-pill {{ $activeTab !== 'templates' ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Hak Akses per Pengguna</span>
            </a>
            <a href="{{ route('sistem.menu-akses', ['tab' => 'templates']) }}" class="tab-pill {{ $activeTab === 'templates' ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                    <path d="m9 12 2 2 4-4"></path>
                </svg>
                <span>Template Peran Sistem</span>
            </a>
        </div>
        <span class="badge badge-cyan">{{ $activeTab === 'templates' ? 'Konfigurasi Template' : 'Otorisasi User' }}</span>
    </div>

    @if ($activeTab === 'templates')
        <!-- TAB 2: DAFTAR TEMPLATE PERAN -->
        <div class="card-body">
            <div class="alert-info">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <div>
                    <strong>Template Peran Dinamis:</strong> Super Admin dapat menentukan menu dan hak akses bawaan untuk setiap peran di bawah ini. Template ini dapat diterapkan langsung ke akun guru atau siswa saat melakukan pengaturan izin.
                </div>
            </div>

            <div class="template-grid">
                @foreach ($templates as $tmpl)
                    <div class="template-card">
                        <div>
                            <div class="template-card-header">
                                <h4 class="template-card-title">{{ $tmpl->role_name }}</h4>
                                <span class="badge {{ $tmpl->role_category === 'guru' ? 'badge-role-guru' : ($tmpl->role_category === 'siswa' ? 'badge-role-siswa' : 'badge-role-admin') }}">
                                    {{ strtoupper($tmpl->role_category) }}
                                </span>
                            </div>
                            <div class="template-card-body">
                                <span>Kunci Peran: <code>{{ $tmpl->role_key }}</code></span>
                            </div>
                        </div>
                        <div class="template-card-footer">
                            <a href="{{ route('sistem.menu-akses.template', $tmpl->role_key) }}" class="btn-secondary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9"></path>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                </svg>
                                <span>Atur Menu Bawaan</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- TAB 1: DAFTAR PENGGUNA -->
        <div class="table-card">
            <div class="table-toolbar">
                <!-- Filter Role Tabs -->
                <div class="tab-pills">
                    <a href="{{ route('sistem.menu-akses', ['search' => $search]) }}" class="tab-pill {{ empty($currentRole) ? 'active' : '' }}">Semua</a>
                    <a href="{{ route('sistem.menu-akses', ['role' => 'super_admin', 'search' => $search]) }}" class="tab-pill {{ $currentRole === 'super_admin' ? 'active' : '' }}">Super Admin</a>
                    <a href="{{ route('sistem.menu-akses', ['role' => 'admin', 'search' => $search]) }}" class="tab-pill {{ $currentRole === 'admin' ? 'active' : '' }}">Admin TU</a>
                    <a href="{{ route('sistem.menu-akses', ['role' => 'guru', 'search' => $search]) }}" class="tab-pill {{ $currentRole === 'guru' ? 'active' : '' }}">Guru</a>
                    <a href="{{ route('sistem.menu-akses', ['role' => 'siswa', 'search' => $search]) }}" class="tab-pill {{ $currentRole === 'siswa' ? 'active' : '' }}">Siswa</a>
                </div>

                <!-- Search Input -->
                <form method="GET" action="{{ route('sistem.menu-akses') }}">
                    @if ($currentRole)
                        <input type="hidden" name="role" value="{{ $currentRole }}">
                    @endif
                    <div class="search-box toolbar-search-box">
                        <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input
                            type="text"
                            name="search"
                            class="search-input"
                            placeholder="Cari nama, username, email..."
                            value="{{ $search }}"
                        >
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Pengguna</th>
                            <th>Tipe Akun</th>
                            <th>Status Izin Navigasi</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $permCount = $user->menuPermissions->where('can_view', true)->count();
                            @endphp
                            <tr>
                                <td>
                                    <div class="user-meta-info">
                                        <div class="user-name-text">{{ $user->name }}</div>
                                        <div class="brand-subtitle">{{ $user->email }} ({{ $user->username }})</div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ 'badge-role-' . $user->user_type }}">
                                        {{ strtoupper(str_replace('_', ' ', $user->user_type)) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($user->isSuperAdmin())
                                        <span class="badge badge-cyan">Akses Penuh (Root Bypass)</span>
                                    @elseif ($permCount > 0)
                                        <span class="badge badge-success">{{ $permCount }} Menu Diizinkan</span>
                                    @else
                                        <span class="badge badge-neutral">Belum Dikonfigurasi</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('sistem.menu-akses.user', $user) }}" class="btn-secondary">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                                            <path d="m9 12 2 2 4-4"></path>
                                        </svg>
                                        <span>Kelola Hak Akses</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    <div class="empty-state">
                                        <span class="empty-state-text">Tidak ada data pengguna yang sesuai dengan filter.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="table-pagination">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
