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
                                <div class="brand-subtitle">
                                    @if ($user->email)
                                        {{ $user->email }} ({{ $user->username }})
                                    @else
                                        Tanpa Email ({{ $user->username }})
                                    @endif
                                </div>
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
                        <td class="text-right">
                            <x-table-actions align="right">
                                @if ($user->is_active)
                                    <a href="{{ route('sistem.user.user', $user) }}" class="btn-secondary" title="Kelola Hak Akses">
                                        <x-icon name="square-check" size="16" />
                                        <span>Hak Akses</span>
                                    </a>
                                @endif

                                @if ($user->id !== auth()->id())
                                    {{-- Tombol Toggle Status (Aktifkan / Nonaktifkan) --}}
                                    <button
                                        type="button"
                                        class="btn-icon btn-open-toggle-user"
                                        title="{{ $user->is_active ? 'Nonaktifkan Akun ' . $user->name : 'Aktifkan Akun ' . $user->name }}"
                                        data-action="{{ route('sistem.user.toggle-status', $user) }}"
                                        data-name="{{ $user->name }}"
                                        data-username="{{ $user->username }}"
                                        data-status="{{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }}"
                                        data-user-type="{{ strtoupper($user->user_type === 'siswa' ? 'murid' : $user->user_type) }}"
                                    >
                                        <x-icon name="{{ $user->is_active ? 'power' : 'check-circle' }}" class="{{ $user->is_active ? 'text-danger' : 'text-success' }}" size="16" />
                                    </button>

                                    {{-- Tombol Hapus Permanen --}}
                                    <x-crud-button
                                        action="delete"
                                        title="Hapus Permanen Akun {{ $user->name }}"
                                        class="btn-open-delete-user"
                                        data-action="{{ route('sistem.user.destroy', $user) }}"
                                        data-name="{{ $user->name }}"
                                        data-username="{{ $user->username }}"
                                        data-user-type="{{ strtoupper($user->user_type === 'siswa' ? 'murid' : $user->user_type) }}"
                                    />
                                @endif
                            </x-table-actions>
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

{{-- Modal Konfirmasi Ubah Status Keaktifan Pengguna --}}
<x-modal
    name="confirm"
    id="modalToggleStatusUser"
    form-id="formToggleStatusUser"
    method="PATCH"
    title="Ubah Status Keaktifan Pengguna"
    cancel-btn-id="btnCancelToggleStatusUser"
    confirm-btn-id="btnConfirmToggleStatusUser"
    message-id="toggleStatusUserMessage"
    badge-id="toggleStatusUserBadge"
    item-title-id="toggleStatusUserItemTitle"
    hint-id="toggleStatusUserHint"
/>

{{-- Modal Konfirmasi Hapus Permanen Pengguna --}}
<x-modal
    name="delete"
    id="modalDeleteUser"
    form-id="formDeleteUser"
    title="Hapus Permanen Pengguna"
    cancel-btn-id="btnCancelDeleteUser"
    confirm-btn-id="btnConfirmDeleteUser"
    message-id="deleteUserMessage"
    badge-id="deleteUserBadge"
    item-title-id="deleteUserItemTitle"
    hint-id="deleteUserHint"
/>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Setup & Binding Modal Ubah Status Keaktifan Pengguna
        InforaModals.setup('modalToggleStatusUser', { cancelBtnId: 'btnCancelToggleStatusUser' });
        InforaModals.bindConfirmButtons('.btn-open-toggle-user', 'modalToggleStatusUser', {
            formId: 'formToggleStatusUser',
            title: 'Ubah Status Keaktifan Akun?',
            message: (el) => `Apakah Anda yakin ingin ${el.dataset.status} akun pengguna berikut?`,
            badge: (el) => el.dataset.userType,
            itemTitle: (el) => `${el.dataset.name} (${el.dataset.username})`,
            hint: (el) => el.dataset.status === 'menonaktifkan'
                ? 'Pengguna tidak akan dapat masuk ke sistem sampai akun diaktifkan kembali.'
                : 'Pengguna akan dapat masuk kembali ke sistem menggunakan kredensial yang ada.',
            confirmText: (el) => el.dataset.status === 'menonaktifkan' ? 'Nonaktifkan' : 'Aktifkan',
            confirmBtnClass: (el) => el.dataset.status === 'menonaktifkan'
                ? 'btn-delete w-full justify-center font-medium'
                : 'btn-primary w-full justify-center font-medium',
            badgeId: 'toggleStatusUserBadge',
            messageId: 'toggleStatusUserMessage',
            itemTitleId: 'toggleStatusUserItemTitle',
            hintId: 'toggleStatusUserHint',
        });

        // Setup & Binding Modal Hapus Permanen Pengguna
        InforaModals.setup('modalDeleteUser', { cancelBtnId: 'btnCancelDeleteUser' });
        InforaModals.bindDeleteButtons('.btn-open-delete-user', 'modalDeleteUser', {
            titleId: 'deleteUserItemTitle',
            onOpen: function (modal, btn) {
                const name = btn.dataset.name;
                const username = btn.dataset.username;
                const userType = btn.dataset.userType;

                const titleHeading = document.getElementById('modalDeleteUserTitle');
                if (titleHeading) {
                    titleHeading.textContent = `Hapus Pengguna ${name}?`;
                }

                const messageEl = document.getElementById('deleteUserMessage');
                if (messageEl) {
                    messageEl.textContent = 'Apakah Anda yakin ingin menghapus akun pengguna ini secara permanen dari sistem?';
                }

                const badgeEl = document.getElementById('deleteUserBadge');
                if (badgeEl) {
                    badgeEl.textContent = userType;
                }

                const itemTitleEl = document.getElementById('deleteUserItemTitle');
                if (itemTitleEl) {
                    itemTitleEl.textContent = `${name} (${username})`;
                }

                const hintEl = document.getElementById('deleteUserHint');
                if (hintEl) {
                    hintEl.textContent = 'Seluruh data izin navigasi dan berkas foto profil terkait akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.';
                }
            }
        });
    });
</script>
@endsection
