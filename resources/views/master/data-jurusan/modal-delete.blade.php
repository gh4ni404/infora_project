<!-- Modal Konfirmasi Hapus Data Jurusan Component -->
<div class="modal-backdrop hidden" id="modalDeleteJurusan" role="dialog" aria-modal="true" aria-labelledby="modalDeleteJurusanTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title text-danger" id="modalDeleteJurusanTitle">Konfirmasi Hapus Jurusan</h3>
                <p class="modal-subtitle">Tindakan ini akan menghapus data jurusan secara permanen dari sistem</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseDeleteJurusan" aria-label="Tutup Dialog">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form method="POST" action="" id="formDeleteJurusan">
            @csrf
            @method('DELETE')

            <div class="modal-body">
                <div class="alert-danger">
                    <div class="alert-content">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <div>
                            <strong>Peringatan Penghapusan Permanen!</strong>
                            <div>
                                Anda akan menghapus data jurusan <strong id="deleteJurusanNama">-</strong> (<span id="deleteJurusanKode" class="text-primary">-</span>). Tindakan ini tidak dapat dibatalkan.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelDeleteJurusan">Batal</button>
                <button type="submit" class="btn-danger" id="btnConfirmDeleteJurusan">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    <span>Ya, Hapus Jurusan</span>
                </button>
            </div>
        </form>
    </div>
</div>
