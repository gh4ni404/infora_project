@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Kelola Hak Akses Pengguna</h2>
        <div class="page-subtitle">Atur visibilitas menu dan wewenang aksi (Lihat, Tambah, Ubah, Hapus) untuk {{ $user->name }}</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('sistem.menu-akses', ['role' => $user->user_type]) }}" class="btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <span>Kembali</span>
        </a>
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
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <div>
                    <strong>Pemberitahuan Super Admin:</strong> Akun ini memiliki status <strong>Super Administrator</strong> (Entitas Pengembang). Super Admin secara permanen memiliki akses penuh (*root bypass*) ke seluruh modul, menu, dan seluruh aksi tanpa batasan konfigurasi.
                </div>
            </div>
        @endif

        <!-- Toolbar Pintas & Terapkan Template -->
        <div class="matrix-action-bar">
            <!-- Form Salin Template -->
            <form method="POST" action="{{ route('sistem.menu-akses.user.apply-template', $user) }}" class="page-actions">
                @csrf
                <select name="role_key" class="form-select" required>
                    <option value="">-- Pilih Template Peran --</option>
                    @foreach ($availableTemplates as $tmpl)
                        <option value="{{ $tmpl->role_key }}">
                            {{ $tmpl->role_name }} ({{ strtoupper($tmpl->role_category) }})
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn-secondary" onclick="return confirm('Terapkan template peran ini? Seluruh izin saat ini akan ditimpa dengan template yang dipilih.');">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                    </svg>
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
        <form method="POST" action="{{ route('sistem.menu-akses.user.update', $user) }}" id="permissionForm">
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
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <polyline points="9 18 15 12 9 6"></polyline>
                                                            </svg>
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
                <button type="submit" class="btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Hak Akses</span>
                </button>
                <a href="{{ route('sistem.menu-akses', ['role' => $user->user_type]) }}" class="btn-secondary">
                    <span>Batal</span>
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Sinkronisasi: jika can_view mati, disable aksi CRUD
    document.querySelectorAll('.perm-view').forEach(function (viewBox) {
        viewBox.addEventListener('change', function () {
            var rowId = this.dataset.targetRow;
            var isChecked = this.checked;
            var actionBoxes = document.querySelectorAll('.perm-action[data-target-row="' + rowId + '"]');

            actionBoxes.forEach(function (box) {
                box.disabled = !isChecked;
                if (!isChecked) {
                    box.checked = false;
                }
            });

            // Jika ini sub-menu dan diaktifkan, otomatis aktifkan parent menu view
            if (isChecked && viewBox.dataset.parentRow) {
                var parentView = document.querySelector('.perm-view[data-target-row="' + viewBox.dataset.parentRow + '"]');
                if (parentView && !parentView.checked) {
                    parentView.checked = true;
                    parentView.dispatchEvent(new Event('change'));
                }
            }
        });
    });

    // Jika aksi CRUD dicentang, otomatis aktifkan can_view
    document.querySelectorAll('.perm-action').forEach(function (actionBox) {
        actionBox.addEventListener('change', function () {
            if (this.checked) {
                var rowId = this.dataset.targetRow;
                var viewBox = document.querySelector('.perm-view[data-target-row="' + rowId + '"]');
                if (viewBox && !viewBox.checked) {
                    viewBox.checked = true;
                    viewBox.dispatchEvent(new Event('change'));
                }
            }
        });
    });

    // Tombol Cepat: Centang Semua Lihat
    var btnSelectAllView = document.getElementById('btnSelectAllView');
    if (btnSelectAllView) {
        btnSelectAllView.addEventListener('click', function () {
            document.querySelectorAll('.perm-view').forEach(function (box) {
                box.checked = true;
                box.dispatchEvent(new Event('change'));
            });
        });
    }

    // Tombol Cepat: Centang Semua CRUD
    var btnSelectAllCRUD = document.getElementById('btnSelectAllCRUD');
    if (btnSelectAllCRUD) {
        btnSelectAllCRUD.addEventListener('click', function () {
            document.querySelectorAll('.perm-view').forEach(function (box) {
                box.checked = true;
                box.dispatchEvent(new Event('change'));
            });
            document.querySelectorAll('.perm-action').forEach(function (box) {
                box.disabled = false;
                box.checked = true;
            });
        });
    }

    // Tombol Cepat: Kosongkan Semua
    var btnUncheckAll = document.getElementById('btnUncheckAll');
    if (btnUncheckAll) {
        btnUncheckAll.addEventListener('click', function () {
            document.querySelectorAll('.matrix-checkbox').forEach(function (box) {
                box.checked = false;
            });
            document.querySelectorAll('.perm-action').forEach(function (box) {
                box.disabled = true;
            });
        });
    }

    // Tombol Cepat per Modul
    document.querySelectorAll('.btn-check-module').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var modId = this.dataset.moduleId;
            var table = document.querySelector('table[data-module-id="' + modId + '"]');
            if (table) {
                var views = table.querySelectorAll('.perm-view');
                var allChecked = Array.from(views).every(function (v) { return v.checked; });
                views.forEach(function (v) {
                    v.checked = !allChecked;
                    v.dispatchEvent(new Event('change'));
                });
            }
        });
    });
});
</script>
@endsection
