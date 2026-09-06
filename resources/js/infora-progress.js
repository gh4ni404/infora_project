/**
 * INFORA - Universal Smooth Real-Time Progressive Indicator Engine
 * Global API: window.InforaProgress
 */

class InforaProgressController {
    constructor() {
        this.modal = null;
        this.progressBar = null;
        this.percentLabel = null;
        this.titleEl = null;
        this.textEl = null;
        this.detailEl = null;
        this.orbEl = null;
        this.iconRunning = null;
        this.iconSuccess = null;
        this.iconError = null;
        this.errorActions = null;
        this.dismissBtn = null;

        this.currentPercent = 0;
        this.targetPercent = 0;
        this.animFrameId = null;
        this.isInitialized = false;
    }

    init() {
        if (this.isInitialized) return;

        this.modal = document.getElementById('globalProgressModal');
        if (!this.modal) return;

        this.progressBar = document.getElementById('globalProgressBar');
        this.percentLabel = document.getElementById('globalProgressPercent');
        this.titleEl = document.getElementById('globalProgressTitle');
        this.textEl = document.getElementById('globalProgressText');
        this.detailEl = document.getElementById('globalProgressDetail');
        this.orbEl = document.getElementById('globalProgressOrb');
        this.iconRunning = document.getElementById('globalProgressIconRunning');
        this.iconSuccess = document.getElementById('globalProgressIconSuccess');
        this.iconError = document.getElementById('globalProgressIconError');
        this.errorActions = document.getElementById('globalProgressErrorActions');
        this.dismissBtn = document.getElementById('btnDismissGlobalProgress');

        if (this.dismissBtn) {
            this.dismissBtn.addEventListener('click', () => {
                this.hide();
                window.location.reload();
            });
        }

        this.isInitialized = true;
    }

    /**
     * Show the universal progress modal.
     */
    show(options = {}) {
        this.init();
        if (!this.modal) return;

        if (this.animFrameId) {
            cancelAnimationFrame(this.animFrameId);
            this.animFrameId = null;
        }

        this.currentPercent = 0;
        this.targetPercent = typeof options.percent === 'number' ? options.percent : 0;

        if (this.progressBar) this.progressBar.value = this.currentPercent;
        if (this.percentLabel) this.percentLabel.textContent = `${this.currentPercent}%`;

        if (this.titleEl) {
            this.titleEl.textContent = options.title || 'Memproses Operasi Sistem';
        }
        if (this.textEl) {
            this.textEl.textContent = options.statusText || 'Mohon tunggu sejenak, sistem sedang bekerja...';
        }
        if (this.detailEl) {
            this.detailEl.textContent = options.detail || 'Menyiapkan parameter sistem...';
        }

        // Reset orb state to running
        if (this.orbEl) {
            this.orbEl.classList.remove('is-success', 'is-error');
        }
        if (this.iconRunning) this.iconRunning.classList.remove('hidden');
        if (this.iconSuccess) this.iconSuccess.classList.add('hidden');
        if (this.iconError) this.iconError.classList.add('hidden');
        if (this.errorActions) this.errorActions.classList.add('hidden');

        this.modal.classList.remove('hidden');
        document.body.classList.add('modal-open');

        if (this.targetPercent > 0) {
            this.animateToPercent(this.targetPercent);
        }
    }

    /**
     * Update progress with smooth numerical easing.
     */
    set(target, statusText, detail) {
        this.init();
        if (!this.modal) return;

        if (statusText && this.textEl) {
            this.textEl.textContent = statusText;
        }
        if (detail && this.detailEl) {
            this.detailEl.textContent = detail;
        }

        const clamped = Math.max(0, Math.min(100, Math.round(target)));
        this.animateToPercent(clamped);
    }

    /**
     * Smoothly animate numerical counter towards target.
     */
    animateToPercent(target) {
        this.targetPercent = target;

        // Native <progress> element transitions width smoothly via CSS
        if (this.progressBar) {
            this.progressBar.value = target;
        }

        if (this.animFrameId) {
            cancelAnimationFrame(this.animFrameId);
        }

        const step = () => {
            if (this.currentPercent === this.targetPercent) {
                if (this.percentLabel) {
                    this.percentLabel.textContent = `${this.targetPercent}%`;
                }
                return;
            }

            const diff = this.targetPercent - this.currentPercent;
            const increment = diff > 0 ? Math.ceil(diff * 0.18) : Math.floor(diff * 0.18);

            this.currentPercent += increment;
            if ((diff > 0 && this.currentPercent > this.targetPercent) ||
                (diff < 0 && this.currentPercent < this.targetPercent)) {
                this.currentPercent = this.targetPercent;
            }

            if (this.percentLabel) {
                this.percentLabel.textContent = `${this.currentPercent}%`;
            }

            if (this.currentPercent !== this.targetPercent) {
                this.animFrameId = requestAnimationFrame(step);
            }
        };

        this.animFrameId = requestAnimationFrame(step);
    }

    /**
     * Mark operation as 100% completed with success badge.
     */
    finish(message, callback) {
        this.init();
        if (!this.modal) return;

        this.set(100);

        if (this.titleEl) {
            this.titleEl.textContent = 'Operasi Selesai Berhasil';
        }
        if (this.textEl && message) {
            this.textEl.textContent = message;
        }
        if (this.detailEl) {
            this.detailEl.textContent = 'Seluruh proses telah berhasil dirampungkan.';
        }

        if (this.orbEl) {
            this.orbEl.classList.remove('is-error');
            this.orbEl.classList.add('is-success');
        }
        if (this.iconRunning) this.iconRunning.classList.add('hidden');
        if (this.iconSuccess) this.iconSuccess.classList.remove('hidden');
        if (this.iconError) this.iconError.classList.add('hidden');

        setTimeout(() => {
            if (typeof callback === 'function') {
                callback();
            } else {
                window.location.reload();
            }
        }, 1200);
    }

    /**
     * Display error state without closing modal.
     */
    error(errorMessage) {
        this.init();
        if (!this.modal) return;

        if (this.animFrameId) {
            cancelAnimationFrame(this.animFrameId);
        }

        if (this.titleEl) {
            this.titleEl.textContent = 'Operasi Terhenti';
        }
        if (this.textEl) {
            this.textEl.textContent = 'Terjadi kesalahan sistem saat memproses operasi.';
        }
        if (this.detailEl) {
            this.detailEl.textContent = errorMessage || 'Terjadi galat teknis tidak terduga.';
        }

        if (this.orbEl) {
            this.orbEl.classList.remove('is-success');
            this.orbEl.classList.add('is-error');
        }
        if (this.iconRunning) this.iconRunning.classList.add('hidden');
        if (this.iconSuccess) this.iconSuccess.classList.add('hidden');
        if (this.iconError) this.iconError.classList.remove('hidden');

        if (this.errorActions) {
            this.errorActions.classList.remove('hidden');
        }
    }

    /**
     * Hide modal.
     */
    hide() {
        this.init();
        if (!this.modal) return;

        this.modal.classList.add('hidden');
        document.body.classList.remove('modal-open');

        if (this.animFrameId) {
            cancelAnimationFrame(this.animFrameId);
            this.animFrameId = null;
        }
    }

    /**
     * Stream an HTTP action with real-time SSE progress events.
     */
    async stream(url, formData, options = {}) {
        this.show({
            title: options.title || 'Memproses Operasi Sistem',
            statusText: options.statusText || 'Menghubungkan ke server...',
            detail: options.detail || 'Memulai pertukaran data...',
            percent: 0,
        });

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'text/event-stream, application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                let errorText = `Terjadi galat pada server (Status ${response.status})`;
                try {
                    const errData = await response.json();
                    if (errData.message) errorText = errData.message;
                    if (errData.errors) {
                        const firstKey = Object.keys(errData.errors)[0];
                        if (firstKey && errData.errors[firstKey][0]) {
                            errorText = errData.errors[firstKey][0];
                        }
                    }
                } catch (_) {}
                throw new Error(errorText);
            }

            const reader = response.body.getReader();
            const decoder = new TextDecoder('utf-8');
            let buffer = '';

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                buffer += decoder.decode(value, { stream: true });
                const lines = buffer.split('\n');
                buffer = lines.pop(); // keep partial line

                for (const line of lines) {
                    const trimmed = line.trim();
                    if (!trimmed.startsWith('data:')) continue;
                    const jsonStr = trimmed.substring(5).trim();
                    if (!jsonStr) continue;

                    try {
                        const eventData = JSON.parse(jsonStr);

                        if (typeof eventData.percent === 'number') {
                            this.set(
                                eventData.percent,
                                eventData.stage || eventData.detail,
                                eventData.detail
                            );
                        }

                        if (eventData.status === 'completed') {
                            this.finish(
                                eventData.message || 'Operasi berhasil diselesaikan.',
                                options.onComplete
                            );
                            return;
                        } else if (eventData.status === 'error') {
                            this.error(eventData.message || eventData.detail);
                            if (typeof options.onError === 'function') {
                                options.onError(eventData);
                            }
                            return;
                        }
                    } catch (e) {
                        console.error('InforaProgress: JSON parse error on stream chunk:', e);
                    }
                }
            }
        } catch (err) {
            this.error(err.message || 'Koneksi ke server terputus.');
            if (typeof options.onError === 'function') {
                options.onError(err);
            }
        }
    }
}

// Instantiate and expose globally
const inforaProgress = new InforaProgressController();
window.InforaProgress = inforaProgress;

export default inforaProgress;
