<!-- Universal Global Progress Modal (Zero Inline Styles) -->
<div class="modal-backdrop hidden" id="globalProgressModal" role="dialog" aria-modal="true" aria-labelledby="globalProgressTitle">
    <div class="progress-dialog">
        <!-- Ambient Status Orb -->
        <div class="progress-orb-container">
            <div class="progress-orb" id="globalProgressOrb">
                <span class="progress-orb-ping"></span>
                <span class="progress-orb-core">
                    <svg id="globalProgressIconRunning" class="progress-orb-svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48 2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48 2.83-2.83"/>
                    </svg>
                    <svg id="globalProgressIconSuccess" class="progress-orb-svg hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <svg id="globalProgressIconError" class="progress-orb-svg hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
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

        <!-- Meta Information: Live Detail & Percentage -->
        <div class="progress-meta-row">
            <span class="progress-subtext" id="globalProgressDetail">Menyiapkan koneksi...</span>
            <span class="progress-percent-label" id="globalProgressPercent">0%</span>
        </div>

        <!-- Error Actions (Only displayed on error) -->
        <div class="progress-error-actions hidden" id="globalProgressErrorActions">
            <button type="button" class="btn-secondary" id="btnDismissGlobalProgress">Tutup & Periksa Status</button>
        </div>
    </div>
</div>
