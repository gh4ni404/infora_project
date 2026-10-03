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
            <x-alert type="info" :dismissible="false" title="Pemberitahuan Super Admin:">
                <x-slot>
                    Akun ini memiliki status <strong>Super Administrator</strong> (Entitas Pengembang). Super Admin secara permanen memiliki akses penuh (<em>root bypass</em>) ke seluruh modul, menu, dan seluruh aksi tanpa batasan konfigurasi.
                </x-slot>
            </x-alert>
        @endif

        <!-- Toolbar Pintas dan Terapkan Template -->
        <div class="matrix-action-bar">
            <!-- Form Salin Template -->
            <form method="POST" action="{{ route('sistem.user.user.apply-template', $user) }}" class="page-actions" id="applyTemplateForm">
                @csrf
                @php
                    $templateOptions = $availableTemplates->mapWithKeys(fn($tmpl) => [
                        $tmpl->role_key => $tmpl->role_name . ' (' . strtoupper($tmpl->role_category) . ')'
                    ]);
                @endphp
                <x-form-select
                    name="role_key"
                    id="role_key"
                    placeholder="Pilih Template Peran"
                    :options="$templateOptions"
                    required
                />
                <button type="button" class="btn-secondary btn-submit-apply-template" id="btnOpenApplyTemplateModal">
                    <x-icon name="zap" size="16" />
                    <span>Terapkan Template</span>
                </button>
            </form>

            <!-- Tombol Pilih Massal -->
            <div class="page-actions">
                <button type="button" class="btn-secondary" id="btnSelectAllView">
                    <x-icon name="check" size="14" />
                    <span>Centang Semua Lihat</span>
                </button>
                <button type="button" class="btn-secondary" id="btnSelectAllCRUD">
                    <x-icon name="check-circle" size="14" />
                    <span>Centang Semua CRUD</span>
                </button>
                <button type="button" class="btn-secondary" id="btnUncheckAll">
                    <x-icon name="close" size="14" />
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
                @forelse ($modules as $module)
                    <div class="matrix-module-card">
                        <div class="matrix-module-header">
                            <div class="matrix-module-title">
                                <x-icon name="layers" class="nav-item-icon" />
                                <span>{{ $module->name }}</span>
                            </div>
                            <button type="button" class="btn-secondary btn-check-module" data-module-id="{{ $module->id }}">
                                <x-icon name="check" size="14" />
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
                @empty
                    <div class="card-surface p-8 text-center text-slate-500">
                        <x-icon name="layers" size="36" class="mx-auto mb-2 text-slate-400" />
                        <p class="font-medium text-slate-700">Belum ada modul atau menu navigasi aktif</p>
                        <p class="text-xs text-slate-500 mt-1">Silakan aktifkan modul dan menu terlebih dahulu pada Pengaturan Sistem.</p>
                    </div>
                @endforelse
            </div>

            <!-- Tombol Aksi Simpan -->
            <div class="form-actions">
                <button type="button" class="btn-primary btn-submit-access-permission" id="btnOpenConfirmModal">Simpan</button>
                <a href="{{ route('sistem.user', ['role' => $user->user_type]) }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Simpan Hak Akses Pengguna --}}
<x-modal
    name="confirm"
    id="modalConfirmSaveAccess"
    size="lg"
    :with-form="false"
    form-id="permissionForm"
    title="Konfirmasi Simpan Hak Akses"
    badge-id="modalConfirmSaveAccessBadge"
    item-title-id="modalConfirmSaveAccessItemTitle"
    message-id="modalConfirmSaveAccessMessage"
    hint-id="modalConfirmSaveAccessHint"
    cancel-btn-id="btnCancelConfirmSaveAccess"
    confirm-btn-id="btnSubmitConfirmSaveAccess"
/>

{{-- Modal Konfirmasi Terapkan Template Peran --}}
<x-modal
    name="confirm"
    id="modalConfirmApplyTemplate"
    :with-form="false"
    form-id="applyTemplateForm"
    title="Terapkan Template Peran"
    badge-id="modalConfirmApplyTemplateBadge"
    item-title-id="modalConfirmApplyTemplateItemTitle"
    message-id="modalConfirmApplyTemplateMessage"
    hint-id="modalConfirmApplyTemplateHint"
    cancel-btn-id="btnCancelConfirmApplyTemplate"
    confirm-btn-id="btnSubmitConfirmApplyTemplate"
/>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Inisialisasi Modal Konfirmasi Simpan
    InforaModals.setup('modalConfirmSaveAccess', { cancelBtnId: 'btnCancelConfirmSaveAccess' });
    // InforaModals.bindOpenButton('btnOpenConfirmModal', 'modalConfirmSaveAccess');
    InforaModals.bindConfirmButtons('.btn-submit-access-permission', 'modalConfirmSaveAccess', {
        message: 'Apakah Anda yakin ingin menyimpan perubahan konfigurasi wewenang untuk pengguna ini?',
        badge: '{{ $user->name }}',
        hint: 'Pastikan konfigurasi wewenang pengguna sudah sesuai sebelum disimpan',
        badgeId: 'modalConfirmSaveAccessBadge',
        messageId: 'modalConfirmSaveAccessMessage',
        hintId: 'modalConfirmSaveAccessHint',
        itemTitleId: 'modalConfirmSaveAccessItemTitle',
        onOpen: function (modal) {
            const itemTitleEl = document.getElementById('modalConfirmSaveAccessItemTitle');
            if (!itemTitleEl) return;

            // Kumpulkan semua baris yang memiliki minimal satu izin aktif
            const rows = document.querySelectorAll('#permissionForm tr[data-row-id]');
            const activeItems = [];
            rows.forEach(row => {
                const checked = row.querySelectorAll('input[type="checkbox"]:checked');
                if (!checked.length) return;

                const nameEl = row.querySelector('.matrix-item-name span');
                const name   = nameEl ? nameEl.textContent.trim() : '-';

                const labels = [];
                checked.forEach(cb => {
                    if (cb.name.includes('can_view'))   labels.push('Lihat');
                    if (cb.name.includes('can_create')) labels.push('Tambah');
                    if (cb.name.includes('can_edit'))   labels.push('Ubah');
                    if (cb.name.includes('can_delete')) labels.push('Hapus');
                });

                activeItems.push({ name, labels });
            });

            if (!activeItems.length) {
                itemTitleEl.className = 'text-sm font-bold text-slate-900 leading-snug text-center';
                itemTitleEl.innerHTML = '<span class="text-slate-400 text-xs italic">Tidak ada wewenang yang dikonfigurasi.</span>';
                return;
            }

            // Atur layout list dan scroll jika item lebih dari 4 menggunakan Tailwind utilities
            itemTitleEl.className = activeItems.length > 4
                ? 'text-left max-h-40 overflow-y-auto divide-y divide-slate-100 pr-1'
                : 'text-left divide-y divide-slate-100';

            itemTitleEl.innerHTML = activeItems.map(item => `
                <div class="flex items-center justify-between gap-2 py-1.5">
                    <span class="text-xs font-medium text-slate-800 truncate">${item.name}</span>
                    <span class="flex flex-wrap gap-1 shrink-0">${item.labels.map(l => `<span class="badge badge-neutral">${l}</span>`).join('')}</span>
                </div>
            `).join('');
        },
    });


    // 2. Inisialisasi Modal Konfirmasi Terapkan Template
    InforaModals.setup('modalConfirmApplyTemplate', { cancelBtnId: 'btnCancelConfirmApplyTemplate' });
    InforaModals.bindConfirmButtons('.btn-submit-apply-template', 'modalConfirmApplyTemplate', {
        message: 'Apakah Anda yakin ingin menerapkan template peran ini ke akun {{ $user->name }}?',
        badge: 'Peringatan Timpa Izin',
        hint: 'Seluruh konfigurasi wewenang saat ini akan ditimpa dengan template peran yang dipilih.',
        itemTitle: () => {
            const select = document.getElementById('role_key');
            return select.options[select.selectedIndex].text;
        },
        confirmText: 'Ya, Terapkan',
        badgeId: 'modalConfirmApplyTemplateBadge',
        messageId: 'modalConfirmApplyTemplateMessage',
        hintId: 'modalConfirmApplyTemplateHint',
        itemTitleId: 'modalConfirmApplyTemplateItemTitle',
        onOpen: function () {
            const select = document.getElementById('role_key');
            if (!select.value) {
                select.focus();
                select.reportValidity();
                return false;
            }
        },
    });
});
</script>
@endsection
