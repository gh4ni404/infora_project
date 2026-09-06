@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Konfigurasi Template Peran: {{ $templateInfo->role_name }}</h2>
        <div class="page-subtitle">Atur menu dan wewenang aksi bawaan yang akan disalin saat menerapkan template ini ke pengguna</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('sistem.menu-akses', ['tab' => 'templates']) }}" class="btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <span>Kembali ke Template</span>
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

<div class="card-surface">
    <div class="card-header">
        <span class="user-name-text">Pengaturan Standar Template</span>
        <span class="badge badge-cyan">{{ strtoupper($templateInfo->role_category) }}</span>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('sistem.menu-akses.template.update', $roleKey) }}" id="templateForm">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="role_name" class="form-label">Nama Tampilan Template <span class="text-danger">*</span></label>
                <input
                    type="text"
                    id="role_name"
                    name="role_name"
                    class="form-input @error('role_name') border-danger @enderror"
                    data-transform="title-case"
                    value="{{ old('role_name', $templateInfo->role_name) }}"
                    required
                >
                @error('role_name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-hint">Nama deskriptif peran yang mudah dikenali (contoh: Guru Pengajar, Wali Kelas, Wakasek Kurikulum).</div>
            </div>

            <div class="form-group">
                <label for="role_category" class="form-label">Kategori Peran <span class="text-danger">*</span></label>
                <select id="role_category" name="role_category" class="form-select @error('role_category') border-danger @enderror" required>
                    <option value="guru" {{ old('role_category', $templateInfo->role_category) === 'guru' ? 'selected' : '' }}>Guru & Tenaga Pendidik</option>
                    <option value="staf" {{ old('role_category', $templateInfo->role_category) === 'staf' ? 'selected' : '' }}>Staf Tata Usaha</option>
                    <option value="siswa" {{ old('role_category', $templateInfo->role_category) === 'siswa' ? 'selected' : '' }}>Siswa</option>
                </select>
                @error('role_category')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Toolbar Pintas Matriks -->
            <div class="matrix-action-bar">
                <div class="user-name-text">Matriks Menu & Wewenang Template</div>
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

            <!-- Matriks Hierarki Menu -->
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
                                            $perm = $templatePermissions->get($permKey);
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
                                                    $subPerm = $templatePermissions->get($subPermKey);
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
                    <span>Simpan Template Peran</span>
                </button>
                <a href="{{ route('sistem.menu-akses', ['tab' => 'templates']) }}" class="btn-secondary">
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

            if (isChecked && viewBox.dataset.parentRow) {
                var parentView = document.querySelector('.perm-view[data-target-row="' + viewBox.dataset.parentRow + '"]');
                if (parentView && !parentView.checked) {
                    parentView.checked = true;
                    parentView.dispatchEvent(new Event('change'));
                }
            }
        });
    });

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

    var btnSelectAllView = document.getElementById('btnSelectAllView');
    if (btnSelectAllView) {
        btnSelectAllView.addEventListener('click', function () {
            document.querySelectorAll('.perm-view').forEach(function (box) {
                box.checked = true;
                box.dispatchEvent(new Event('change'));
            });
        });
    }

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
