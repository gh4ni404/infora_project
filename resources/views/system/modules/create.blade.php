{{-- Modal Tambah Modul Baru --}}
<x-modal id="modalCreateModule" title="Tambah Modul Baru"
    subtitle="Formulir Pendaftaran Modul navigasi baru ke dalam hierarki sistem INFORA"
    action="{{ route('system.modules.store') }}">
    {{-- Nama Modul --}}
    <x-form-input name="name" id="name" label="Nama Modul"
        placeholder="Contoh: PENGATURAN SISTEM, AKADEMIK, KESISWAAN" data-transform="uppercase"
        hint="Nama modul otomatis diformat kapital (UPPERCASE) dan ditampilkan sebagai pemisah kategori pada sidebar."
        required />

    {{-- Urutan Tampil --}}
    <x-form-input type="number" name="order" id="order" label="Urutan Tampil (Order)" :value="old('order', $nextOrder)"
        min="1" hint="Urutan numerik dari yang terkecil (1, 2, 3, ...) untuk posisi di sidebar." />

    {{-- Status Aktif --}}
    <x-form-checkbox name="is_active" id="create_is_active" label="Aktifkan modul ini pada sidebar sistem"
        value="1" :checked="old('is_active', '1') == '1'" />

    {{-- Footer Modal --}}
    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelCreateModule">Batal</button>
        <button type="submit" class="btn-primary">Simpan</button>
    </x-slot:footer>
</x-modal>
