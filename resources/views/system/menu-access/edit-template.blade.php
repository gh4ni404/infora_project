@extends('layouts.app')

@section('content')
<x-page-header
    :title="'Konfigurasi Template Peran: ' . $templateInfo->role_name"
    subtitle="Atur menu dan wewenang aksi bawaan yang akan disalin saat menerapkan template ini ke pengguna"
>
    <x-slot:actions>
        <a href="{{ route('sistem.user', ['tab' => 'templates']) }}" class="btn-secondary">
            <x-icon name="chevron-left" size="18" />
            <span>Kembali ke Template</span>
        </a>
    </x-slot:actions>
</x-page-header>

{{-- Flash Alerts --}}
<x-alert type="success" />
<x-alert type="error" />
<x-alert type="validation" />

<div class="card-surface">
    <div class="card-header">
        <span class="user-name-text">Pengaturan Standar Template</span>
        <span class="badge badge-cyan">{{ strtoupper($templateInfo->role_category) }}</span>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('sistem.user.template.update', $roleKey) }}" id="templateForm">
            @csrf
            @method('PUT')

            <x-form-input
                name="role_name"
                id="role_name"
                label="Nama Tampilan Template"
                :value="$templateInfo->role_name"
                data-transform="title-case"
                hint="Nama deskriptif peran yang mudah dikenali (contoh: Guru Pengajar, Wali Kelas, Wakasek Kurikulum)."
                required
            />

            <x-form-select
                name="role_category"
                id="role_category"
                label="Kategori Peran"
                :selected="$templateInfo->role_category"
                :options="[
                    'admin' => 'Administrator Sekolah',
                    'guru' => 'Guru dan Tenaga Pendidik',
                    'staf' => 'Staf Tata Usaha',
                    'murid' => 'Murid',
                ]"
                required
            />

            <!-- Toolbar Pintas Matriks -->
            <div class="matrix-action-bar">
                <div class="user-name-text">Matriks Menu dan Wewenang Template</div>
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
                <a href="{{ route('sistem.user', ['tab' => 'templates']) }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Simpan Template --}}
<x-modal
    id="modalConfirmSave"
    title="Konfirmasi Simpan Perubahan"
    subtitle="Pastikan konfigurasi wewenang template sudah sesuai sebelum disimpan"
    size="sm"
>
    <div class="p-3.5 bg-slate-50 border border-slate-200/90 rounded-xl text-center mb-4">
        <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold bg-blue-50 text-brand border border-blue-100 rounded-md mb-1.5">
            Template Peran
        </span>
        <div class="text-sm font-bold text-slate-900 leading-snug">
            {{ $templateInfo->role_name }}
        </div>
        <div class="text-xs text-slate-500 mt-1">
            Kategori: {{ strtoupper($templateInfo->role_category) }}
        </div>
    </div>

    <p class="text-sm text-gray-600 leading-relaxed text-center mb-0">
        Apakah Anda yakin ingin menyimpan seluruh perubahan konfigurasi wewenang untuk template peran ini?
    </p>

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelConfirmSave">Batal</button>
        <button type="submit" form="templateForm" class="btn-primary" id="btnSubmitConfirmSave">
            <x-icon name="save" size="18" />
            <span>Ya, Simpan</span>
        </button>
    </x-slot:footer>
</x-modal>

<script>
document.addEventListener('DOMContentLoaded', function () {
    InforaModals.setup('modalConfirmSave', { cancelBtnId: 'btnCancelConfirmSave' });
    InforaModals.bindOpenButton('btnOpenConfirmModal', 'modalConfirmSave');
});
</script>
@endsection
