@extends('layouts.app')

@section('content')
<x-page-header title="Data Referensi Sistem" subtitle="Kelola parameter lookup, tipe sekolah, akreditasi, dan referensi global platform INFORA">
    <x-slot:actions>
        <button type="button" class="btn btn-secondary btn-sm" id="btnOpenCreateKategori">
            <x-icon name="plus" size="14" />
            <span>Tambah Kategori</span>
        </button>
        @if ($activeCategory)
            <button type="button" class="btn btn-primary btn-sm" id="btnOpenCreateItem">
                <x-icon name="plus" size="14" />
                <span>Tambah Data</span>
            </button>
        @endif
    </x-slot:actions>
</x-page-header>

{{-- Flash Alerts --}}
<x-alert type="success" />
<x-alert type="error" />
<x-alert type="validation" />

{{-- Layout 2-Kolom (Master-Detail): Panel Kategori & Panel Item Referensi --}}
<div class="grid-split-sidebar mt-4">
    {{-- Sisi Kiri: Daftar Kategori Referensi --}}
    <div>
        <div class="card-surface p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h3 class="font-bold text-sm tracking-wide text-muted text-uppercase mb-0">Daftar Kategori</h3>
                <span class="badge badge-primary">{{ $categories->count() }}</span>
            </div>

            <x-tab-pills class="tab-pills-vertical w-full" role="tablist">
                @forelse ($categories as $cat)
                    @php
                        $isActiveCat = $activeCategory && $activeCategory->id === $cat->id;
                    @endphp
                    <x-tab-pill
                        :active="$isActiveCat"
                        :href="route('system.referensi.index', ['kategori_id' => $cat->id])"
                        :badge="$cat->children_count"
                        :badge-class="$isActiveCat ? 'badge-primary' : 'badge-cyan'"
                        icon="folder"
                        :icon-size="16"
                        class="justify-content-between text-left"
                    >
                        <span class="truncate">{{ $cat->nama }}</span>
                    </x-tab-pill>
                @empty
                    <div class="p-3 text-center text-muted font-size-xs">
                        Belum ada kategori referensi.
                    </div>
                @endforelse
            </x-tab-pills>

            @if ($activeCategory)
                <div class="pt-3 mt-3 border-t border-dashed">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="font-size-xs text-muted">
                            Kode: <code class="text-xs">{{ $activeCategory->kode }}</code>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <button
                                type="button"
                                class="btn-action btn-action-edit btn-open-edit-kategori"
                                title="Edit Kategori"
                                data-action="{{ route('system.referensi.kategori.update', $activeCategory) }}"
                                data-kategori='@json($activeCategory)'
                            >
                                <x-icon name="pencil" size="14" />
                            </button>
                            @if (! $activeCategory->is_system)
                                <button
                                    type="button"
                                    class="btn-action btn-action-delete btn-open-delete-kategori"
                                    title="Hapus Kategori"
                                    data-action="{{ route('system.referensi.kategori.destroy', $activeCategory) }}"
                                    data-name="{{ $activeCategory->nama }}"
                                >
                                    <x-icon name="trash" size="14" />
                                </button>
                            @else
                                <span class="badge badge-warning text-xs" title="Kategori sistem tidak dapat dihapus">
                                    <x-icon name="lock" size="12" /> Sistem
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Sisi Kanan: Tabel Data Item untuk Kategori Aktif --}}
    <div>
        @if ($activeCategory)
            <x-data-table
                route="{{ route('system.referensi.index', ['kategori_id' => $activeCategory->id]) }}"
                search-placeholder="Cari kode atau nama referensi..."
                :total="$items->total()"
                total-label="Item Referensi ({{ $activeCategory->nama }})"
            >
                <thead>
                    <tr>
                        <th style="width: 120px;">Kode</th>
                        <th>Nama Referensi</th>
                        @if ($activeCategory->kode === 'school_type')
                            <th style="width: 140px;">Kemenag / Madrasah</th>
                        @endif
                        <th style="width: 100px;">Status</th>
                        <th style="width: 100px;">Tipe</th>
                        <th class="col-w-actions">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>
                                <code class="font-bold text-sm">{{ $item->kode }}</code>
                            </td>
                            <td>
                                <div class="font-semibold text-sm">{{ $item->nama }}</div>
                            </td>
                            @if ($activeCategory->kode === 'school_type')
                                <td>
                                    @if (! empty($item->extra_attributes['is_madrasah']))
                                        <span class="badge badge-success font-size-xs">
                                            <x-icon name="check" size="12" /> Ya (Kemenag)
                                        </span>
                                    @else
                                        <span class="text-muted font-size-xs">-</span>
                                    @endif
                                </td>
                            @endif
                            <td>
                                <form action="{{ route('system.referensi.toggle-status', $item) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        class="badge {{ $item->is_active ? 'badge-success' : 'badge-danger' }} border-0 cursor-pointer"
                                        title="Klik untuk mengubah status aktif"
                                    >
                                        {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                @if ($item->is_system)
                                    <span class="badge badge-warning font-size-xs" title="Item bawaan sistem">
                                        <x-icon name="lock" size="11" /> Sistem
                                    </span>
                                @else
                                    <span class="badge badge-cyan font-size-xs">Kustom</span>
                                @endif
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button
                                        type="button"
                                        class="btn-action btn-action-edit btn-open-edit-item"
                                        title="Edit Data Referensi"
                                        data-action="{{ route('system.referensi.item.update', $item) }}"
                                        data-item='@json($item)'
                                    >
                                        <x-icon name="pencil" size="14" />
                                    </button>

                                    @if (! $item->is_system)
                                        <button
                                            type="button"
                                            class="btn-action btn-action-delete btn-open-delete-item"
                                            title="Hapus Data Referensi"
                                            data-action="{{ route('system.referensi.item.destroy', $item) }}"
                                            data-name="{{ $item->nama }}"
                                            data-code="{{ $item->kode }}"
                                        >
                                            <x-icon name="trash" size="14" />
                                        </button>
                                    @else
                                        <button type="button" class="btn-action opacity-30 cursor-not-allowed" title="Item sistem bawaan tidak dapat dihapus" disabled>
                                            <x-icon name="trash" size="14" />
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $activeCategory->kode === 'school_type' ? 6 : 5 }}" class="text-center py-5">
                                <x-empty-state
                                    title="Belum Ada Item Referensi"
                                    description="Kategori {{ $activeCategory->nama }} belum memiliki item data referensi."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-data-table>
        @else
            <div class="card-surface p-5 text-center">
                <x-empty-state
                    title="Pilih Kategori Referensi"
                    description="Silakan buat atau pilih salah satu kategori di sebelah kiri untuk mengelola data referensi."
                />
            </div>
        @endif
    </div>
</div>

{{-- =========================================================================
     MODAL SECTION
     ========================================================================= --}}

{{-- 1. Modal Tambah Kategori --}}
<x-modal
    id="modalCreateKategori"
    title="Tambah Kategori Referensi"
    subtitle="Daftarkan kelompok kategori referensi baru ke dalam sistem"
    action="{{ route('system.referensi.kategori.store') }}"
    form-id="formCreateKategori"
>
    <div class="form-grid-1col">
        <x-form-input
            name="nama"
            id="create_kategori_nama"
            label="Nama Kategori"
            placeholder="Contoh: Status Kepegawaian"
            required
        />

        <x-form-input
            name="kode"
            id="create_kategori_kode"
            label="Kode Unik Kategori"
            placeholder="Contoh: employment_status"
            hint="Hanya huruf, angka, garis bawah (_), dan tanda hubung (-)."
            required
        />

        <x-form-checkbox
            name="is_active"
            id="create_kategori_is_active"
            label="Aktifkan Kategori Ini"
            :checked="true"
        />
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-secondary" id="btnCancelCreateKategori">Batal</button>
        <button type="submit" form="formCreateKategori" class="btn btn-primary">Simpan Kategori</button>
    </x-slot:footer>
</x-modal>

{{-- 2. Modal Edit Kategori --}}
<x-modal
    id="modalEditKategori"
    title="Edit Kategori Referensi"
    subtitle="Perbarui informasi nama atau status keaktifan kategori referensi"
    action=""
    form-id="formEditKategori"
    method="PUT"
>
    <div class="form-grid-1col">
        <x-form-input
            name="nama"
            id="edit_kategori_nama"
            label="Nama Kategori"
            required
        />

        <x-form-input
            name="kode"
            id="edit_kategori_kode"
            label="Kode Unik Kategori"
            hint="Kode kategori sistem bawaan tidak dapat diubah."
            required
        />

        <x-form-checkbox
            name="is_active"
            id="edit_kategori_is_active"
            label="Kategori Aktif"
            :checked="true"
        />
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-secondary" id="btnCancelEditKategori">Batal</button>
        <button type="submit" form="formEditKategori" class="btn btn-primary">Simpan Perubahan</button>
    </x-slot:footer>
</x-modal>

{{-- 3. Modal Hapus Kategori --}}
<x-modal
    id="modalDeleteKategori"
    title="Hapus Kategori Referensi?"
    subtitle="Tindakan ini akan menghapus kategori beserta seluruh item anak di dalamnya."
    action=""
    form-id="formDeleteKategori"
    method="DELETE"
>
    <div class="alert-content p-3">
        <p class="mb-2">Apakah Anda yakin ingin menghapus kategori referensi <strong id="deleteKategoriName">-</strong>?</p>
        <p class="text-danger font-size-xs mb-0">Perhatian: Seluruh item yang terhubung ke kategori ini juga akan dihapus permanen.</p>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-secondary" id="btnCancelDeleteKategori">Batal</button>
        <button type="submit" form="formDeleteKategori" class="btn btn-danger">Hapus Kategori</button>
    </x-slot:footer>
</x-modal>

{{-- 4. Modal Tambah Item Referensi --}}
@if ($activeCategory)
<x-modal
    id="modalCreateItem"
    title="Tambah Data Referensi: {{ $activeCategory->nama }}"
    subtitle="Tambahkan entri data referensi baru untuk kategori {{ $activeCategory->nama }}"
    action="{{ route('system.referensi.item.store') }}"
    form-id="formCreateItem"
>
    <input type="hidden" name="referensi_id" value="{{ $activeCategory->id }}">

    <div class="form-grid-1col">
        <x-form-input
            name="kode"
            id="create_item_kode"
            label="Kode / Singkatan"
            placeholder="Contoh: {{ $activeCategory->kode === 'school_type' ? 'SMK' : 'A' }}"
            hint="Identifikasi unik kode referensi (contoh: SMA, SMK, A, B, dll)."
            required
        />

        <x-form-input
            name="nama"
            id="create_item_nama"
            label="Nama Lengkap / Label"
            placeholder="Contoh: {{ $activeCategory->kode === 'school_type' ? 'Sekolah Menengah Kejuruan' : 'A (Unggul)' }}"
            required
        />

        @if ($activeCategory->kode === 'school_type')
            <div class="p-3 bg-surface-subtle border rounded mb-3">
                <x-form-checkbox
                    name="is_madrasah"
                    id="create_item_is_madrasah"
                    label="Satuan Pendidikan Kemenag / Madrasah"
                    hint="Centang jika jenjang ini berada di bawah Kemenag dan wajib menggunakan nomor registrasi 18 digit (NISM)."
                    :checked="false"
                />
            </div>
        @endif

        <x-form-checkbox
            name="is_active"
            id="create_item_is_active"
            label="Status Aktif"
            :checked="true"
        />
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-secondary" id="btnCancelCreateItem">Batal</button>
        <button type="submit" form="formCreateItem" class="btn btn-primary">Simpan Data</button>
    </x-slot:footer>
</x-modal>
@endif

{{-- 5. Modal Edit Item Referensi --}}
<x-modal
    id="modalEditItem"
    title="Edit Data Referensi"
    subtitle="Perbarui data referensi"
    action=""
    form-id="formEditItem"
    method="PUT"
>
    <div class="form-grid-1col">
        <x-form-input
            name="kode"
            id="edit_item_kode"
            label="Kode / Singkatan"
            hint="Kode item sistem bawaan tidak dapat diubah."
            required
        />

        <x-form-input
            name="nama"
            id="edit_item_nama"
            label="Nama Lengkap / Label"
            required
        />

        @if ($activeCategory && $activeCategory->kode === 'school_type')
            <div class="p-3 bg-surface-subtle border rounded mb-3">
                <x-form-checkbox
                    name="is_madrasah"
                    id="edit_item_is_madrasah"
                    label="Satuan Pendidikan Kemenag / Madrasah"
                    hint="Centang jika jenjang ini berada di bawah Kemenag dan wajib menggunakan nomor registrasi 18 digit (NISM)."
                    :checked="false"
                />
            </div>
        @endif

        <x-form-checkbox
            name="is_active"
            id="edit_item_is_active"
            label="Status Aktif"
            :checked="true"
        />
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-secondary" id="btnCancelEditItem">Batal</button>
        <button type="submit" form="formEditItem" class="btn btn-primary">Simpan Perubahan</button>
    </x-slot:footer>
</x-modal>

{{-- 6. Modal Hapus Item Referensi --}}
<x-modal
    id="modalDeleteItem"
    title="Hapus Data Referensi?"
    subtitle="Tindakan ini akan menghapus data referensi dari sistem."
    action=""
    form-id="formDeleteItem"
    method="DELETE"
>
    <div class="alert-content p-3">
        <p class="mb-2">Apakah Anda yakin ingin menghapus referensi <strong id="deleteItemName">-</strong> (<code id="deleteItemCode">-</code>)?</p>
        <p class="text-danger font-size-xs mb-0">Perhatian: Pastikan referensi ini sedang tidak digunakan oleh data sekolah aktif.</p>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-secondary" id="btnCancelDeleteItem">Batal</button>
        <button type="submit" form="formDeleteItem" class="btn btn-danger">Hapus Data</button>
    </x-slot:footer>
</x-modal>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Modal Tambah Kategori & Item
    InforaModals.setup('modalCreateKategori', { cancelBtnId: 'btnCancelCreateKategori' });
    InforaModals.bindOpenButton('btnOpenCreateKategori', 'modalCreateKategori');

    InforaModals.setup('modalCreateItem', { cancelBtnId: 'btnCancelCreateItem' });
    InforaModals.bindOpenButton('btnOpenCreateItem', 'modalCreateItem');

    // 2. Modal Edit Kategori
    InforaModals.setup('modalEditKategori', { cancelBtnId: 'btnCancelEditKategori' });
    InforaModals.bindEditButtons('.btn-open-edit-kategori', 'modalEditKategori', function (modal, data) {
        document.getElementById('edit_kategori_nama').value = data.nama || '';
        const codeInput = document.getElementById('edit_kategori_kode');
        codeInput.value = data.kode || '';
        codeInput.readOnly = Boolean(data.is_system);
        document.getElementById('edit_kategori_is_active').checked = Boolean(data.is_active);
    });

    // 3. Modal Hapus Kategori
    InforaModals.setup('modalDeleteKategori', { cancelBtnId: 'btnCancelDeleteKategori' });
    InforaModals.bindDeleteButtons('.btn-open-delete-kategori', 'modalDeleteKategori', {
        titleId: 'deleteKategoriName',
        titleKey: 'name'
    });

    // 4. Modal Edit Item
    InforaModals.setup('modalEditItem', { cancelBtnId: 'btnCancelEditItem' });
    InforaModals.bindEditButtons('.btn-open-edit-item', 'modalEditItem', function (modal, data) {
        document.getElementById('edit_item_nama').value = data.nama || '';
        const codeInput = document.getElementById('edit_item_kode');
        codeInput.value = data.kode || '';
        codeInput.readOnly = Boolean(data.is_system);

        const isMadrasahCheck = document.getElementById('edit_item_is_madrasah');
        if (isMadrasahCheck) {
            isMadrasahCheck.checked = Boolean(data.extra_attributes && data.extra_attributes.is_madrasah);
        }

        document.getElementById('edit_item_is_active').checked = Boolean(data.is_active);
    });

    // 5. Modal Hapus Item
    InforaModals.setup('modalDeleteItem', { cancelBtnId: 'btnCancelDeleteItem' });
    InforaModals.bindDeleteButtons('.btn-open-delete-item', 'modalDeleteItem', function (modal, btn) {
        document.getElementById('deleteItemName').textContent = btn.dataset.name || '';
        document.getElementById('deleteItemCode').textContent = btn.dataset.code || '';
    });

    // 6. Auto re-open modal jika error validasi
    window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
    window.errorMethod = @json(old('_method'));
    InforaModals.reopenOnError({
        create: 'modalCreateItem',
        edit: 'modalEditItem'
    });
});
</script>
@endsection
