<!-- Modal Rincian Kegiatan per Tanggal Component -->
<div class="modal-backdrop hidden" id="modalDateDetail" role="dialog" aria-modal="true" aria-labelledby="modalDateDetailTitle">
    <div class="modal-dialog modal-md">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalDateDetailTitle">Rincian Agenda Tanggal</h3>
                <p class="modal-subtitle" id="modalDateDetailSubtitle">Daftar kegiatan akademik yang terjadwal</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseDateDetail" aria-label="Tutup Ringkasan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="dateDetailEventsList">
                <!-- Diisi secara dinamis via JavaScript -->
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" id="btnDismissDateDetail">Tutup</button>
            <button type="button" class="btn-primary" id="btnAddEventFromDateDetail">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Tambah Kegiatan pada Tanggal Ini</span>
            </button>
        </div>
    </div>
</div>

