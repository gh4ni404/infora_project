@extends('layouts.app')

@section('content')
<x-page-header
    title="Kelola Hak Akses Pengguna"
    subtitle="Atur visibilitas menu dan wewenang aksi (Lihat, Tambah, Ubah, Hapus) untuk {{ $user->name }}"
>
    <x-slot:actions>
        <a href="{{ route('sistem.user', ['role' => $user->user_type]) }}" class="btn-secondary">
            <x-icon name="chevron-left" size="18" />
            <span>Kembali</span>
        </a>
    </x-slot:actions>
</x-page-header>

<x-alert type="success" />
<x-alert type="error" />
<x-alert type="validation" />

<!-- Ringkasan Akun Pengguna -->
<div class="card-surface">
    <div class="card-header">
        <div class="user-meta-info">
            <span class="user-name-text">{{ $user->name }}</span>
            <span class="brand-subtitle">{{ $user->email }} ({{ $user->username }})</span>
        </div>
        <span class="badge {{ 'badge-role-' . $user->user_type }}">
            {{ strtoupper(str_replace('_', ' ', $user->user_type)) }}
        </span>
    </div>

    <div class="card-body">
        @if ($user->isSuperAdmin())
            <div class="alert-info">
                <x-icon name="info" size="36" />
                <div>
                    <strong>Pemberitahuan Super Admin:</strong> Akun ini memiliki status <strong>Super Administrator</strong> (Entitas Pengembang). Super Admin secara permanen memiliki akses penuh (*root bypass*) ke seluruh modul, menu, dan seluruh aksi tanpa batasan konfigurasi.
                </div>
            </div>
        @endif

        <!-- Toolbar Pintas dan Terapkan Template -->
        <div class="matrix-action-bar">
            <!-- Form Salin Template -->
            <form method="POST" action="{{ route('sistem.user.user.apply-template', $user) }}" class="page-actions">
                @csrf
                <select name="role_key" class="form-select" required>
                    <option value="">Pilih Template Peran</option>
                    @foreach ($availableTemplates as $tmpl)
                        <option value="{{ $tmpl->role_key }}">
                            {{ $tmpl->role_name }} ({{ strtoupper($tmpl->role_category) }})
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn-secondary" onclick="return confirm('Terapkan template peran ini? Seluruh izin saat ini akan ditimpa dengan template yang dipilih.');">
                    <x-icon name="zap" size="16" />
                    <span>Terapkan Template</span>
                </button>
            </form>

            <!-- Tombol Pilih Massal -->
            <div class="page-actions">
                <button type="button" class="btn-secondary" id="btnSelectAllView">
                    <span>Centang Semua Lihat</span>
                </button>
                <button type="button" class="btn-secondary" id="btnSelectAllCRUD">
                    <span>Centang Semua CRUD</span>
                </button>
                <button type="button" class="btn-secondary" id="btnUncheckAll">
                    <span>Kosongkan Semua</span>
                </button>
            </div>
        </div>

        <!-- Form Matriks Perizinan Granular -->
        <form method="POST" action="{{ route('sistem.user.user.update', $user) }}" id="permissionForm">
            @csrf
            @method('PUT')

            <div class="matrix-container">
                @php $rowIndex = 0; @endphp
                @foreach ($modules as $module)
                    <div class="matrix-module-card">
                        <div class="matrix-module-header">
                            <div class="matrix-module-title">
                                <x-icon name="layers" class="nav-item-icon" />
                                <span>{{ $module->name }}</span>
                            </div>
                            <button type="button" class="btn-secondary btn-check-module" data-module-id="{{ $module->id }}">
                                <span>Pilih Semua Modul Ini</span>
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="matrix-table" data-module-id="{{ $module->id }}">
                                <thead>
                                    <tr>
                                        <th>Item Menu Navigasi</th>
                                        <th class="matrix-th-center">Lihat</th>
                                        <th class="matrix-th-center">Tambah</th>
                                        <th class="matrix-th-center">Ubah</th>
                                        <th class="matrix-th-center">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($module->menus as $menu)
                                        @php
                                            $hasSub = $menu->subMenus && $menu->subMenus->isNotEmpty();
                                            $permKey = 'menu_' . $menu->id;
                                            $perm = $userPermissions->get($permKey);
                                        @endphp

                                        <tr class="matrix-row-menu" data-row-id="menu_{{ $menu->id }}">
                                            <td>
                                                <div class="matrix-item-name">
                                                    <x-icon :name="$menu->icon" class="nav-item-icon" />
                                                    <span>{{ $menu->name }}</span>
                                                    @if ($hasSub)
                                                        <span class="badge badge-neutral">{{ $menu->subMenus->count() }} Sub-Menu</span>
                                                    @endif
                                                </div>
                                                <input type="hidden" name="permissions[{{ $rowIndex }}][menu_id]" value="{{ $menu->id }}">
                                                <input type="hidden" name="permissions[{{ $rowIndex }}][sub_menu_id]" value="">
                                            </td>
                                            <td class="matrix-td-center">
                                                <input
                                                    type="checkbox"
                                                    name="permissions[{{ $rowIndex }}][can_view]"
                                                    value="1"
                                                    class="matrix-checkbox perm-view"
                                                    data-target-row="menu_{{ $menu->id }}"
                                                    {{ old("permissions.{$rowIndex}.can_view", $perm?->can_view) ? 'checked' : '' }}
                                                >
                                            </td>
                                            <td class="matrix-td-center">
                                                <input
                                                    type="checkbox"
                                                    name="permissions[{{ $rowIndex }}][can_create]"
                                                    value="1"
                                                    class="matrix-checkbox perm-action perm-create"
                                                    data-target-row="menu_{{ $menu->id }}"
                                                    {{ old("permissions.{$rowIndex}.can_create", $perm?->can_create) ? 'checked' : '' }}
                                                    {{ !old("permissions.{$rowIndex}.can_view", $perm?->can_view) ? 'disabled' : '' }}
                                                >
                                            </td>
                                            <td class="matrix-td-center">
                                                <input
                                                    type="checkbox"
                                                    name="permissions[{{ $rowIndex }}][can_edit]"
                                                    value="1"
                                                    class="matrix-checkbox perm-action perm-edit"
                                                    data-target-row="menu_{{ $menu->id }}"
                                                    {{ old("permissions.{$rowIndex}.can_edit", $perm?->can_edit) ? 'checked' : '' }}
                                                    {{ !old("permissions.{$rowIndex}.can_view", $perm?->can_view) ? 'disabled' : '' }}
                                                >
                                            </td>
                                            <td class="matrix-td-center">
                                                <input
                                                    type="checkbox"
                                                    name="permissions[{{ $rowIndex }}][can_delete]"
                                                    value="1"
                                                    class="matrix-checkbox perm-action perm-delete"
                                                    data-target-row="menu_{{ $menu->id }}"
                                                    {{ old("permissions.{$rowIndex}.can_delete", $perm?->can_delete) ? 'checked' : '' }}
                                                    {{ !old("permissions.{$rowIndex}.can_view", $perm?->can_view) ? 'disabled' : '' }}
                                                >
                                            </td>
                                        </tr>
                                        @php $rowIndex++; @endphp

                                        @if ($hasSub)
                                            @foreach ($menu->subMenus as $subMenu)
                                                @php
                                                    $subPermKey = 'sub_' . $subMenu->id;
                                                    $subPerm = $userPermissions->get($subPermKey);
                                                @endphp
                                                <tr class="matrix-row-submenu" data-row-id="sub_{{ $subMenu->id }}" data-parent-menu="menu_{{ $menu->id }}">
                                                    <td>
                                                        <div class="matrix-item-name matrix-submenu-indent">
                                                            <span>{{ $subMenu->name }}</span>
                                                        </div>
                                                        <input type="hidden" name="permissions[{{ $rowIndex }}][menu_id]" value="">
                                                        <input type="hidden" name="permissions[{{ $rowIndex }}][sub_menu_id]" value="{{ $subMenu->id }}">
                                                    </td>
                                                    <td class="matrix-td-center">
                                                        <input
                                                            type="checkbox"
                                                            name="permissions[{{ $rowIndex }}][can_view]"
                                                            value="1"
                                                            class="matrix-checkbox perm-view"
                                                            data-target-row="sub_{{ $subMenu->id }}"
                                                            data-parent-row="menu_{{ $menu->id }}"
                                                            {{ old("permissions.{$rowIndex}.can_view", $subPerm?->can_view) ? 'checked' : '' }}
                                                        >
                                                    </td>
                                                    <td class="matrix-td-center">
                                                        <input
                                                            type="checkbox"
                                                            name="permissions[{{ $rowIndex }}][can_create]"
                                                            value="1"
                                                            class="matrix-checkbox perm-action perm-create"
                                                            data-target-row="sub_{{ $subMenu->id }}"
                                                            {{ old("permissions.{$rowIndex}.can_create", $subPerm?->can_create) ? 'checked' : '' }}
                                                            {{ !old("permissions.{$rowIndex}.can_view", $subPerm?->can_view) ? 'disabled' : '' }}
                                                        >
                                                    </td>
                                                    <td class="matrix-td-center">
                                                        <input
                                                            type="checkbox"
                                                            name="permissions[{{ $rowIndex }}][can_edit]"
                                                            value="1"
                                                            class="matrix-checkbox perm-action perm-edit"
                                                            data-target-row="sub_{{ $subMenu->id }}"
                                                            {{ old("permissions.{$rowIndex}.can_edit", $subPerm?->can_edit) ? 'checked' : '' }}
                                                            {{ !old("permissions.{$rowIndex}.can_view", $subPerm?->can_view) ? 'disabled' : '' }}
                                                        >
                                                    </td>
                                                    <td class="matrix-td-center">
                                                        <input
                                                            type="checkbox"
                                                            name="permissions[{{ $rowIndex }}][can_delete]"
                                                            value="1"
                                                            class="matrix-checkbox perm-action perm-delete"
                                                            data-target-row="sub_{{ $subMenu->id }}"
                                                            {{ old("permissions.{$rowIndex}.can_delete", $subPerm?->can_delete) ? 'checked' : '' }}
                                                            {{ !old("permissions.{$rowIndex}.can_view", $subPerm?->can_view) ? 'disabled' : '' }}
                                                        >
                                                    </td>
                                                </tr>
                                                @php $rowIndex++; @endphp
                                            @endforeach
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Tombol Aksi Simpan -->
            <div class="form-actions">
                <button type="button" class="btn-primary" id="btnOpenConfirmModal">Simpan</button>
                <a href="{{ route('sistem.user', ['role' => $user->user_type]) }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Simpan Hak Akses Pengguna --}}
<x-modal
    id="modalConfirmSave"
    title="Konfirmasi Simpan Hak Akses"
    subtitle="Pastikan konfigurasi wewenang pengguna sudah sesuai sebelum disimpan"
    size="sm"
>
    <div class="p-3.5 bg-slate-50 border border-slate-200/90 rounded-xl text-center mb-4">
        <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold bg-blue-50 text-primary border border-blue-100 rounded-md mb-1.5">
            Pengguna
        </span>
        <div class="text-sm font-bold text-slate-900 leading-snug">
            {{ $user->name }}
        </div>
        <div class="text-xs text-slate-500 mt-1">
            {{ $user->email }} &bull; {{ strtoupper(str_replace('_', ' ', $user->user_type)) }}
        </div>
    </div>

    <p class="text-sm text-gray-600 leading-relaxed text-center mb-0">
        Apakah Anda yakin ingin menyimpan perubahan konfigurasi wewenang untuk pengguna ini?
    </p>

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelConfirmSave">Batal</button>
        <button type="submit" form="permissionForm" class="btn-primary" id="btnSubmitConfirmSave">
            <x-icon name="save" size="18" />
            <span>Ya, Simpan</span>
        </button>
    </x-slot:footer>
</x-modal>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    InforaModals.setup('modalConfirmSave', { cancelBtnId: 'btnCancelConfirmSave' });
    InforaModals.bindOpenButton('btnOpenConfirmModal', 'modalConfirmSave');
});
</script>
@endsection
