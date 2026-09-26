<!-- Universal Global Progress Modal (Zero Inline Styles) -->
<div class="modal-backdrop hidden" id="globalProgressModal" role="dialog" aria-modal="true" aria-labelledby="globalProgressTitle">
    <div class="progress-dialog">
        <!-- Ambient Status Orb -->
        <div class="progress-orb-container">
            <div class="progress-orb" id="globalProgressOrb">
                <span class="progress-orb-ping"></span>
                <span class="progress-orb-core">
                    <x-icon name="loader-radiant" id="globalProgressIconRunning" class="progress-orb-svg" size="20" />
                    <x-icon name="check" id="globalProgressIconSuccess" class="progress-orb-svg hidden" size="20" />
                    <x-icon name="x-mark" id="globalProgressIconError" class="progress-orb-svg hidden" size="20" />
                </span>
            </div>
        </div>

        <!-- Headline and Active Status Text -->
        <div class="progress-content-header">
            <h3 class="progress-title" id="globalProgressTitle">Memproses Operasi Sistem</h3>
            <p class="progress-text" id="globalProgressText">Mohon tunggu sejenak, sistem sedang bekerja...</p>
        </div>

        <!-- Sleek Animated Progress Bar -->
        <div class="progress-bar-container">
            <progress id="globalProgressBar" class="infora-progress" value="0" max="100"></progress>
        </div>

        <!-- Meta Information: Live Detail dan Percentage -->
        <div class="progress-meta-row">
            <span class="progress-subtext" id="globalProgressDetail">Menyiapkan koneksi...</span>
            <span class="progress-percent-label" id="globalProgressPercent">0%</span>
        </div>

        <!-- Error Actions (Only displayed on error) -->
        <div class="progress-error-actions hidden" id="globalProgressErrorActions">
            <button type="button" class="btn-secondary" id="btnDismissGlobalProgress">Tutup dan Periksa Status</button>
        </div>
    </div>
</div>
