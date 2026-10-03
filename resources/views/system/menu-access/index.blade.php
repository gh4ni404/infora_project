@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Tata Kelola Menu Akses</h2>
        <div class="page-subtitle">Atur izin navigasi dan hak aksi (Lihat, Tambah, Ubah, Hapus) per pengguna serta konfigurasi template peran</div>
    </div>
</div>

<x-alert type="success" />

<!-- Tab Navigasi Utama: Daftar Pengguna vs Template Peran -->
<div class="card-surface">
    <div class="card-header">
        <div class="tab-pills">
            <a href="{{ route('sistem.user', ['tab' => 'users']) }}" class="tab-pill {{ $activeTab !== 'templates' ? 'active' : '' }}">
                <x-icon name="users" size="16" />
                <span>Hak Akses per Pengguna</span>
            </a>
            <a href="{{ route('sistem.user', ['tab' => 'templates']) }}" class="tab-pill {{ $activeTab === 'templates' ? 'active' : '' }}">
                <x-icon name="square-check" size="16" />
                <span>Template Peran Sistem</span>
            </a>
        </div>
        <span class="badge badge-cyan">{{ $activeTab === 'templates' ? 'Konfigurasi Template' : 'Otorisasi User' }}</span>
    </div>

    @if ($activeTab === 'templates')
        <!-- TAB 2: DAFTAR TEMPLATE PERAN -->
        <div class="card-body">
            <x-alert type="info" :dismissible="false" title="Template Peran Dinamis:" >
                <x-slot>
                    Super Admin dapat menentukan menu dan hak akses bawaan untuk setiap peran di bawah ini. Template ini dapat diterapkan langsung ke akun pengguna (Admin Sekolah, Guru, Staf, atau Murid) saat melakukan pengaturan izin.
                </x-slot>
            </x-alert>

            <div class="template-grid">
                @foreach ($templates as $tmpl)
                    <div class="template-card">
                        <div>
                            <div class="template-card-header">
                                <h4 class="template-card-title">{{ $tmpl->role_name }}</h4>
                                <span class="badge {{ $tmpl->role_category === 'guru' ? 'badge-role-guru' : ($tmpl->role_category === 'murid' || $tmpl->role_category === 'siswa' ? 'badge-role-murid' : 'badge-role-admin') }}">
                                    {{ strtoupper($tmpl->role_category === 'siswa' ? 'murid' : $tmpl->role_category) }}
                                </span>
                            </div>
                            <div class="template-card-body">
                                <span>Kunci Peran: <code>{{ $tmpl->role_key }}</code></span>
                            </div>
                        </div>
                        <div class="template-card-footer">
                            <a href="{{ route('sistem.user.template', $tmpl->role_key) }}" class="btn-secondary">
                                <x-icon name="pencil" size="16" />
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
                    <a href="{{ route('sistem.user', ['search' => $search]) }}" class="tab-pill {{ empty($currentRole) ? 'active' : '' }}">Semua</a>
                    <a href="{{ route('sistem.user', ['role' => 'super_admin', 'search' => $search]) }}" class="tab-pill {{ $currentRole === 'super_admin' ? 'active' : '' }}">Super Admin</a>
                    <a href="{{ route('sistem.user', ['role' => 'admin', 'search' => $search]) }}" class="tab-pill {{ $currentRole === 'admin' ? 'active' : '' }}">Admin</a>
                    <a href="{{ route('sistem.user', ['role' => 'guru', 'search' => $search]) }}" class="tab-pill {{ $currentRole === 'guru' ? 'active' : '' }}">Guru</a>
                    <a href="{{ route('sistem.user', ['role' => 'murid', 'search' => $search]) }}" class="tab-pill {{ $currentRole === 'murid' ? 'active' : '' }}">Murid</a>
                </div>

                <!-- Search Input -->
                <form method="GET" action="{{ route('sistem.user') }}">
                    @if ($currentRole)
                        <input type="hidden" name="role" value="{{ $currentRole }}">
                    @endif
                    <div class="search-box toolbar-search-box">
                        <x-icon name="search" class="search-icon" size="16" />
                        <input type="text" name="search" class="search-input" placeholder="Cari nama, username, email..." value="{{ $search }}">
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
                            <th class="text-center">Aksi</th>
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
                                        <div class="brand-subtitle">{{ $user->email ?? 'Tanpa Email' }} ({{ $user->username }})</div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $user->user_type === 'murid' || $user->user_type === 'siswa' ? 'badge-role-murid' : 'badge-role-' . $user->user_type }}">
                                        {{ strtoupper(str_replace('_', ' ', $user->user_type === 'siswa' ? 'murid' : $user->user_type)) }}
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
                                <td class="text-center">
                                    <a href="{{ route('sistem.user.user', $user) }}" class="btn-secondary">
                                        <x-icon name="square-check" size="16" />
                                        <span>Kelola Hak Akses</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <x-empty-state icon="help-circle" colspan="4" text="Tidak ada data pengguna yang sesuai dengan filter." />
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
