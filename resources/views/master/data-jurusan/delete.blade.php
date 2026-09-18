{{-- Modal Konfirmasi Hapus Data Jurusan --}}
<x-modal
    id="modalDeleteJurusan"
    title="Konfirmasi Hapus Jurusan"
    subtitle="Tindakan ini akan menghapus data jurusan secara permanen dari sistem"
    method="DELETE"
    form-id="formDeleteJurusan"
    danger
>
    <x-alert type="error" title="Peringatan Penghapusan Permanen!">
        Anda akan menghapus data jurusan <strong id="deleteJurusanNama">-</strong> (<span id="deleteJurusanKode" class="text-primary">-</span>). Tindakan ini tidak dapat dibatalkan.
    </x-alert>

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelDeleteJurusan">Batal</button>
        <button type="submit" class="btn-delete" id="btnConfirmDeleteJurusan">
            <x-icon name="trash" size="18" />
            <span>Ya, Hapus Jurusan</span>
        </button>
    </x-slot:footer>
</x-modal>