{{-- Modal Rincian Kegiatan per Tanggal Component --}}
<x-modal
    id="modalDateDetail"
    title="Rincian Agenda Tanggal"
    subtitle="Daftar kegiatan akademik yang terjadwal"
>
    <div id="dateDetailEventsList">
        <!-- Diisi secara dinamis via JavaScript -->
    </div>

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnDismissDateDetail">Tutup</button>
        <button type="button" class="btn-primary" id="btnAddEventFromDateDetail">
            <x-icon name="add" size="18" />
            <span>Tambah Kegiatan pada Tanggal Ini</span>
        </button>
    </x-slot:footer>
</x-modal>
