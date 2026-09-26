{{-- Modal Edit Modul Component --}}
<x-modal
    id="modalEditModule"
    title="Formulir Perubahan Modul"
    subtitle="Perbarui data konfigurasi modul navigasi sistem INFORA"
    action=""
    method="PUT"
    form-id="formEditModule"
>
    {{-- Nama Modul --}}
    <x-form-input
        name="name"
        id="edit_module_name"
        label="Nama Modul"
        placeholder="Contoh: PENGATURAN SISTEM, AKADEMIK, KESISWAAN"
        data-transform="uppercase"
        hint="Nama modul otomatis diformat kapital (UPPERCASE) dan ditampilkan sebagai pemisah kategori pada sidebar."
        required
    />

    {{-- Urutan Tampil --}}
    <x-form-input
        type="number"
        name="order"
        id="edit_module_order"
        label="Urutan Tampil (Order)"
        :value="old('order', 1)"
        min="1"
        hint="Urutan numerik dari yang terkecil (1, 2, 3, ...) untuk penataan posisi di sidebar."
    />

    {{-- Status Aktif --}}
    <x-form-checkbox
        name="is_active"
        id="edit_module_is_active"
        label="Aktifkan modul ini pada sidebar sistem"
        value="1"
        :checked="old('is_active', '1') == '1'"
    />

    {{-- Footer Modal --}}
    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditModule">Batal</button>
        <button type="submit" class="btn-primary">Update</button>
    </x-slot:footer>
</x-modal>
