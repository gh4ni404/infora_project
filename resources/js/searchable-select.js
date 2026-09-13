/**
 * INFORA - Reusable Searchable Select Component (Vanilla JS + Tailwind CSS)
 */

export class SearchableSelect {
    /**
     * Inisialisasi komponen searchable select pada elemen target.
     * @param {HTMLElement} element
     */
    constructor(element) {
        if (!element || element.__searchableSelect) {
            return element?.__searchableSelect;
        }

        this.element = element;
        this.element.__searchableSelect = this;

        this.inputId = element.dataset.inputId;
        this.placeholder = element.dataset.placeholder || '-- Pilih Salah Satu --';
        this.emptyText = element.dataset.emptyText || 'Data tidak ditemukan';
        this.isClearable = element.dataset.clearable === '1';
        this.isDisabled = element.dataset.disabled === '1';

        this.hiddenInput = element.querySelector('.searchable-select-value');
        this.triggerBtn = element.querySelector('.searchable-select-trigger');
        this.labelSpan = element.querySelector('.searchable-select-label');
        this.badgeSpan = element.querySelector('.searchable-select-badge');
        this.loadingSpan = element.querySelector('.searchable-select-loading');
        this.clearBtn = element.querySelector('.searchable-select-clear');
        this.dropdownPanel = element.querySelector('.searchable-select-dropdown');
        this.searchInput = element.querySelector('.searchable-select-search-input');
        this.optionsList = element.querySelector('.searchable-select-options-list');
        this.emptyState = element.querySelector('.searchable-select-empty');

        this.highlightedIndex = -1;

        this.initEvents();
    }

    /**
     * Dapatkan instance SearchableSelect berdasarkan ID input atau elemen kontainer.
     * @param {string|HTMLElement} target
     * @returns {SearchableSelect|null}
     */
    static getInstance(target) {
        let el = null;
        if (typeof target === 'string') {
            el = document.getElementById(`searchableSelect_${target}`)
                || document.querySelector(`.searchable-select-component[data-input-id="${target}"]`)
                || document.getElementById(target)?.closest('.searchable-select-component');
        } else if (target instanceof HTMLElement) {
            el = target.classList.contains('searchable-select-component')
                ? target
                : target.closest('.searchable-select-component');
        }

        if (!el) return null;

        if (!el.__searchableSelect) {
            new SearchableSelect(el);
        }

        return el.__searchableSelect;
    }

    /**
     * Inisialisasi semua elemen searchable select yang belum terinisialisasi di halaman.
     */
    static initAll(root = document) {
        root.querySelectorAll('.searchable-select-component').forEach((el) => {
            if (!el.__searchableSelect) {
                new SearchableSelect(el);
            }
        });
    }

    initEvents() {
        // Toggle dropdown saat trigger diklik
        if (this.triggerBtn) {
            this.triggerBtn.addEventListener('click', (e) => {
                if (this.isDisabled) return;
                this.toggle();
            });
        }

        // Tombol hapus pilihan (clear)
        if (this.clearBtn) {
            this.clearBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (this.isDisabled) return;
                this.clear(true);
            });
        }

        // Input pencarian instan
        if (this.searchInput) {
            this.searchInput.addEventListener('input', () => {
                this.filter(this.searchInput.value);
            });

            this.searchInput.addEventListener('keydown', (e) => {
                this.handleSearchKeydown(e);
            });
        }

        // Klik pada opsi dropdown
        if (this.optionsList) {
            this.optionsList.addEventListener('click', (e) => {
                const optionEl = e.target.closest('.searchable-select-option');
                if (!optionEl) return;

                const val = optionEl.dataset.value;
                const text = optionEl.dataset.text;
                const extra = optionEl.dataset.extra || '';

                this.setValue(val, text, extra, true);
                this.close();
            });
        }

        // Tutup saat klik di luar
        document.addEventListener('click', (e) => {
            if (!this.element.contains(e.target)) {
                this.close();
            }
        });

        // Tutup saat menekan tombol Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close();
                this.triggerBtn?.focus();
            }
        });
    }

    isOpen() {
        return this.element.classList.contains('is-open');
    }

    open() {
        if (this.isDisabled) return;

        // Tutup semua searchable-select lain yang sedang terbuka
        document.querySelectorAll('.searchable-select-component.is-open').forEach((other) => {
            if (other !== this.element && other.__searchableSelect) {
                other.__searchableSelect.close();
            }
        });

        this.element.classList.add('is-open');
        this.dropdownPanel?.classList.remove('hidden');
        this.triggerBtn?.setAttribute('aria-expanded', 'true');

        if (this.searchInput) {
            this.searchInput.value = '';
            this.filter('');
            setTimeout(() => this.searchInput.focus(), 30);
        }
    }

    close() {
        if (!this.isOpen()) return;

        this.element.classList.remove('is-open');
        this.dropdownPanel?.classList.add('hidden');
        this.triggerBtn?.setAttribute('aria-expanded', 'false');
        this.highlightedIndex = -1;
        this.clearHighlight();
    }

    toggle() {
        if (this.isOpen()) {
            this.close();
        } else {
            this.open();
        }
    }

    setDisabled(disabled) {
        this.isDisabled = Boolean(disabled);
        this.element.dataset.disabled = this.isDisabled ? '1' : '0';
        this.element.classList.toggle('is-disabled', this.isDisabled);
        if (this.triggerBtn) {
            this.triggerBtn.disabled = this.isDisabled;
        }
        if (this.isDisabled) {
            this.close();
        }
    }

    setLoading(loading) {
        if (this.loadingSpan) {
            this.loadingSpan.classList.toggle('hidden', !loading);
        }
    }

    /**
     * Memperbarui daftar opsi dropdown secara dinamis (sangat berguna untuk cascading).
     * @param {Array<Object>} options - Format: [{ id, nama, kode, ... }] atau [{ value, text, extra }]
     * @param {string|number|null} selectedValue - Nilai yang ingin langsung dipilih
     */
    setOptions(options = [], selectedValue = null) {
        if (!this.optionsList) return;

        // Kosongkan opsi lama kecuali empty state element
        const emptyEl = this.emptyState;
        this.optionsList.innerHTML = '';
        if (emptyEl) {
            this.optionsList.appendChild(emptyEl);
        }

        let selectedFound = false;

        if (Array.isArray(options) && options.length > 0) {
            options.forEach((opt) => {
                const val = opt.id ?? opt.value ?? '';
                const text = opt.nama ?? opt.label ?? opt.name ?? opt.text ?? val;
                const extra = opt.kode ?? opt.extra ?? opt.tipe ?? opt.kode_pos ?? '';

                const optEl = document.createElement('div');
                optEl.className = 'searchable-select-option';
                optEl.dataset.value = String(val);
                optEl.dataset.text = String(text);
                optEl.dataset.extra = String(extra);
                optEl.setAttribute('role', 'option');

                // Simpan payload asli pada elemen untuk akses metadata (seperti kode pos)
                optEl.__optionData = opt;

                const isSelected = selectedValue !== null && String(val) === String(selectedValue);
                if (isSelected) {
                    optEl.classList.add('is-selected');
                    optEl.setAttribute('aria-selected', 'true');
                    selectedFound = true;
                }

                optEl.innerHTML = `
                    <div class="searchable-select-option-content">
                        <span class="searchable-select-option-text">${this.escapeHtml(text)}</span>
                        ${extra ? `<span class="searchable-select-option-badge">${this.escapeHtml(extra)}</span>` : ''}
                    </div>
                    <svg class="searchable-select-check-icon ${isSelected ? '' : 'hidden'}" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                `;

                this.optionsList.insertBefore(optEl, emptyEl);
            });

            if (emptyEl) emptyEl.classList.add('hidden');
        } else {
            if (emptyEl) emptyEl.classList.remove('hidden');
        }

        // Terapkan nilai terpilih jika ada kecocokan
        if (selectedValue !== null && selectedFound) {
            const selectedOpt = this.optionsList.querySelector(`.searchable-select-option[data-value="${CSS.escape(String(selectedValue))}"]`);
            if (selectedOpt) {
                this.setValue(selectedOpt.dataset.value, selectedOpt.dataset.text, selectedOpt.dataset.extra, false);
            }
        } else if (selectedValue !== null && !selectedFound && selectedValue !== '') {
            // Nilai tidak ditemukan dalam opsi baru, reset
            this.clear(false);
        } else if (selectedValue === '' || selectedValue === null) {
            this.clear(false);
        }
    }

    /**
     * Menetapkan nilai pilihan saat ini secara terprogram.
     */
    setValue(value, text = '', extra = '', triggerChangeEvent = true) {
        const valStr = value !== null && value !== undefined ? String(value) : '';
        if (this.hiddenInput) {
            this.hiddenInput.value = valStr;
        }

        // Cari elemen opsi yang sesuai jika text tidak disediakan
        let matchedOpt = null;
        if (!text && valStr && this.optionsList) {
            matchedOpt = this.optionsList.querySelector(`.searchable-select-option[data-value="${CSS.escape(valStr)}"]`);
            if (matchedOpt) {
                text = matchedOpt.dataset.text || '';
                extra = matchedOpt.dataset.extra || '';
            }
        }

        // Update Label
        if (this.labelSpan) {
            if (valStr && text) {
                this.labelSpan.textContent = text;
                this.labelSpan.classList.remove('is-placeholder');
            } else {
                this.labelSpan.textContent = this.placeholder;
                this.labelSpan.classList.add('is-placeholder');
            }
        }

        // Update Badge
        if (this.badgeSpan) {
            if (valStr && extra) {
                this.badgeSpan.textContent = extra;
                this.badgeSpan.classList.remove('hidden');
            } else {
                this.badgeSpan.textContent = '';
                this.badgeSpan.classList.add('hidden');
            }
        }

        // Update Clear Button
        if (this.clearBtn) {
            this.clearBtn.classList.toggle('hidden', !valStr);
        }

        // Update Highlight / Selected Icon pada Opsi
        if (this.optionsList) {
            this.optionsList.querySelectorAll('.searchable-select-option').forEach((opt) => {
                const isSelected = opt.dataset.value === valStr && valStr !== '';
                opt.classList.toggle('is-selected', isSelected);
                opt.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                const check = opt.querySelector('.searchable-select-check-icon');
                if (check) check.classList.toggle('hidden', !isSelected);
            });
        }

        if (triggerChangeEvent && this.hiddenInput) {
            // Dispatch standard change event
            this.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));

            // Dispatch rich custom event
            const customEvent = new CustomEvent('searchable-select:change', {
                detail: {
                    value: valStr,
                    text: text,
                    extra: extra,
                    item: matchedOpt?.__optionData || null,
                },
                bubbles: true,
            });
            this.hiddenInput.dispatchEvent(customEvent);
        }
    }

    /**
     * Menghapus pilihan (reset ke placeholder).
     */
    clear(triggerChangeEvent = true) {
        this.setValue('', '', '', triggerChangeEvent);
    }

    /**
     * Memfilter opsi berdasarkan kata kunci pencarian (case-insensitive).
     */
    filter(term = '') {
        const query = (term || '').trim().toLowerCase();
        let visibleCount = 0;

        const options = this.optionsList?.querySelectorAll('.searchable-select-option') || [];
        options.forEach((opt) => {
            const text = (opt.dataset.text || '').toLowerCase();
            const extra = (opt.dataset.extra || '').toLowerCase();
            const matches = text.includes(query) || extra.includes(query);

            opt.style.display = matches ? 'flex' : 'none';
            if (matches) visibleCount++;
        });

        if (this.emptyState) {
            this.emptyState.classList.toggle('hidden', visibleCount > 0);
        }

        this.highlightedIndex = -1;
        this.clearHighlight();
    }

    handleSearchKeydown(e) {
        const visibleOptions = Array.from(this.optionsList?.querySelectorAll('.searchable-select-option') || [])
            .filter((opt) => opt.style.display !== 'none');

        if (visibleOptions.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            this.highlightedIndex = (this.highlightedIndex + 1) % visibleOptions.length;
            this.updateHighlight(visibleOptions);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            this.highlightedIndex = (this.highlightedIndex - 1 + visibleOptions.length) % visibleOptions.length;
            this.updateHighlight(visibleOptions);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (this.highlightedIndex >= 0 && this.highlightedIndex < visibleOptions.length) {
                const chosen = visibleOptions[this.highlightedIndex];
                this.setValue(chosen.dataset.value, chosen.dataset.text, chosen.dataset.extra, true);
                this.close();
                this.triggerBtn?.focus();
            }
        }
    }

    updateHighlight(visibleOptions) {
        this.clearHighlight();
        if (this.highlightedIndex >= 0 && this.highlightedIndex < visibleOptions.length) {
            const opt = visibleOptions[this.highlightedIndex];
            opt.classList.add('is-highlighted');
            opt.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }

    clearHighlight() {
        this.optionsList?.querySelectorAll('.is-highlighted').forEach((opt) => {
            opt.classList.remove('is-highlighted');
        });
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Pasang ke window agar dapat diakses dari skrip Blade
if (typeof window !== 'undefined') {
    window.SearchableSelect = SearchableSelect;
}

// Inisialisasi otomatis seluruh komponen saat DOM selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
    SearchableSelect.initAll();
});
