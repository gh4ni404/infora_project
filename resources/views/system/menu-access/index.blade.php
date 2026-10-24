@extends('layouts.app')

@section('content')
<x-page-header
    title="Tata Kelola Menu Akses"
    subtitle="Atur izin navigasi dan hak aksi (Lihat, Tambah, Ubah, Hapus) per pengguna serta konfigurasi template peran"
/>

<x-alert type="success" />

<!-- Tab Navigasi Utama: Daftar Pengguna vs Template Peran -->
<div class="card-surface">
    <div class="card-header">
        <x-tab-pills>
            <x-tab-pill :href="route('sistem.user', ['tab' => 'users'])" :active="$activeTab !== 'templates'" icon="users">
                Hak Akses per Pengguna
            </x-tab-pill>
            <x-tab-pill :href="route('sistem.user', ['tab' => 'templates'])" :active="$activeTab === 'templates'" icon="square-check">
                Template Peran Sistem
            </x-tab-pill>
        </x-tab-pills>
        <span class="badge badge-cyan">{{ $activeTab === 'templates' ? 'Konfigurasi Template' : 'Otorisasi User' }}</span>
    </div>

    @if ($activeTab === 'templates')
        <!-- TAB 2: DAFTAR TEMPLATE PERAN -->
        <div class="card-body">
            <x-alert type="info" :dismissible="false" title="Template Peran Dinamis:">
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
                                <span class="badge badge-role-{{ $tmpl->role_category === 'siswa' ? 'murid' : $tmpl->role_category }}">
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
        <x-data-table>
            <x-slot:toolbar>
                <!-- Filter Role Tabs -->
                <x-tab-pills>
                    <x-tab-pill :href="route('sistem.user', ['search' => $search])" :active="empty($currentRole)">Semua</x-tab-pill>
                    <x-tab-pill :href="route('sistem.user', ['role' => 'super_admin', 'search' => $search])" :active="$currentRole === 'super_admin'">Super Admin</x-tab-pill>
                    <x-tab-pill :href="route('sistem.user', ['role' => 'admin', 'search' => $search])" :active="$currentRole === 'admin'">Admin</x-tab-pill>
                    <x-tab-pill :href="route('sistem.user', ['role' => 'guru', 'search' => $search])" :active="$currentRole === 'guru'">Guru</x-tab-pill>
                    <x-tab-pill :href="route('sistem.user', ['role' => 'staf', 'search' => $search])" :active="$currentRole === 'staf'">Staf (Tendik)</x-tab-pill>
                    <x-tab-pill :href="route('sistem.user', ['role' => 'murid', 'search' => $search])" :active="$currentRole === 'murid'">Murid</x-tab-pill>
                </x-tab-pills>

                <!-- Search Input -->
                <form method="GET" action="{{ route('sistem.user') }}">
                    @if ($currentRole)
                        <x-form-input type="hidden" name="role" :value="$currentRole" />
                    @endif
                    <div class="search-box toolbar-search-box">
                        <x-icon name="search" class="search-icon" size="16" />
                        <input type="text" name="search" class="search-input" placeholder="Cari nama, username, email..." value="{{ $search }}">
                    </div>
                </form>
            </x-slot:toolbar>

            <thead>
                <tr>
                    <th>Pengguna</th>
                    <th>Tipe Akun</th>
                    <th>Status Akun</th>
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
                            <span class="badge badge-role-{{ $user->user_type }}">
                                {{ strtoupper(str_replace('_', ' ', $user->user_type === 'siswa' ? 'murid' : $user->user_type)) }}
                            </span>
                        </td>
                        <td>
                            @if ($user->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-neutral">Nonaktif</span>
                            @endif
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
                            @if ($user->is_active)
                                <a href="{{ route('sistem.user.user', $user) }}" class="btn-secondary">
                                    <x-icon name="square-check" size="16" />
                                    <span>Kelola Hak Akses</span>
                                </a>
                            @else
                                <form method="POST" action="{{ route('sistem.user.activate', $user) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-primary" title="Aktifkan Akun {{ $user->name }}">
                                        <x-icon name="check-circle" size="16" />
                                        <span>Aktifkan Akun</span>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <x-empty-state icon="help-circle" colspan="5" text="Tidak ada data pengguna yang sesuai dengan filter." />
                @endforelse
            </tbody>

            @if ($users->hasPages())
                <x-slot:footer>
                    <x-pagination-summary :paginator="$users" />
                </x-slot:footer>
            @endif
        </x-data-table>
    @endif
</div>
@endsection
