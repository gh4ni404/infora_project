@props([
    'name',
    'id' => null,
    'value' => null,
    'placeholder' => '-- Pilih Salah Satu --',
    'searchPlaceholder' => 'Ketik untuk mencari...',
    'options' => [],
    'required' => false,
    'disabled' => false,
    'clearable' => true,
    'emptyText' => 'Data tidak ditemukan',
])

@php
    $inputId = $id ?? $name;
    $currentValue = old($name, $value);

    // Temukan label awal jika options sudah disediakan dari backend
    $currentLabel = '';
    $currentExtra = '';
    if (! empty($options)) {
        foreach ($options as $opt) {
            $optVal = is_array($opt) ? ($opt['id'] ?? $opt['value'] ?? '') : (is_object($opt) ? ($opt->id ?? $opt->value ?? '') : $opt);
            $optText = is_array($opt) ? ($opt['nama'] ?? $opt['label'] ?? $opt['name'] ?? $opt['text'] ?? $optVal) : (is_object($opt) ? ($opt->nama ?? $opt->label ?? $opt->name ?? $opt->text ?? $optVal) : $opt);
            $optExtra = is_array($opt) ? ($opt['kode'] ?? $opt['extra'] ?? $opt['kode_pos'] ?? '') : (is_object($opt) ? ($opt->kode ?? $opt->extra ?? $opt->kode_pos ?? '') : '');

            if ((string) $optVal === (string) $currentValue && (string) $currentValue !== '') {
                $currentLabel = $optText;
                $currentExtra = $optExtra;
                break;
            }
        }
    }
@endphp

<div
    class="searchable-select-component {{ $disabled ? 'is-disabled' : '' }}"
    id="searchableSelect_{{ $inputId }}"
    data-input-id="{{ $inputId }}"
    data-placeholder="{{ $placeholder }}"
    data-search-placeholder="{{ $searchPlaceholder }}"
    data-empty-text="{{ $emptyText }}"
    data-clearable="{{ $clearable ? '1' : '0' }}"
    data-disabled="{{ $disabled ? '1' : '0' }}"
>
    <!-- Hidden input untuk pengiriman form -->
    <input
        type="hidden"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ $currentValue }}"
        class="searchable-select-value"
        @if ($required) required @endif
    >

    <!-- Tombol Pemicu Dropdown (Trigger) -->
    <button
        type="button"
        class="searchable-select-trigger form-input @error($name) border-danger @enderror"
        id="trigger_{{ $inputId }}"
        aria-haspopup="listbox"
        aria-expanded="false"
        @if ($disabled) disabled @endif
    >
        <div class="searchable-select-trigger-content">
            <span class="searchable-select-label {{ empty($currentValue) ? 'is-placeholder' : '' }}" id="label_{{ $inputId }}">
                {{ ! empty($currentLabel) ? $currentLabel : $placeholder }}
            </span>
            @if (! empty($currentExtra))
                <span class="searchable-select-badge" id="badge_{{ $inputId }}">{{ $currentExtra }}</span>
            @else
                <span class="searchable-select-badge hidden" id="badge_{{ $inputId }}"></span>
            @endif
        </div>

        <div class="searchable-select-actions">
            <span class="searchable-select-loading hidden" id="loading_{{ $inputId }}" title="Memuat data...">
                <svg class="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-linecap="round"></circle>
                </svg>
            </span>
            @if ($clearable)
                <span
                    role="button"
                    tabindex="0"
                    class="searchable-select-clear {{ empty($currentValue) ? 'hidden' : '' }}"
                    id="clear_{{ $inputId }}"
                    title="Hapus pilihan"
                    aria-label="Hapus pilihan"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </span>
            @endif
            <span class="searchable-select-arrow" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </span>
        </div>
    </button>

    <!-- Panel Menu Pilihan Dropdown -->
    <div
        class="searchable-select-dropdown hidden"
        id="dropdown_{{ $inputId }}"
        role="listbox"
    >
        <!-- Bar Pencarian Input -->
        <div class="searchable-select-search-bar">
            <svg class="searchable-select-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input
                type="text"
                class="searchable-select-search-input"
                id="search_{{ $inputId }}"
                placeholder="{{ $searchPlaceholder }}"
                autocomplete="off"
            >
        </div>

        <!-- Wadah Daftar Opsi -->
        <div class="searchable-select-options-list" id="options_{{ $inputId }}">
            @if (! empty($options))
                @foreach ($options as $opt)
                    @php
                        $optVal = is_array($opt) ? ($opt['id'] ?? $opt['value'] ?? '') : (is_object($opt) ? ($opt->id ?? $opt->value ?? '') : $opt);
                        $optText = is_array($opt) ? ($opt['nama'] ?? $opt['label'] ?? $opt['name'] ?? $opt['text'] ?? $optVal) : (is_object($opt) ? ($opt->nama ?? $opt->label ?? $opt->name ?? $opt->text ?? $optVal) : $opt);
                        $optExtra = is_array($opt) ? ($opt['kode'] ?? $opt['extra'] ?? $opt['kode_pos'] ?? '') : (is_object($opt) ? ($opt->kode ?? $opt->extra ?? $opt->kode_pos ?? '') : '');
                        $isSelected = (string) $optVal === (string) $currentValue && (string) $currentValue !== '';
                    @endphp
                    <div
                        class="searchable-select-option {{ $isSelected ? 'is-selected' : '' }}"
                        data-value="{{ $optVal }}"
                        data-text="{{ $optText }}"
                        data-extra="{{ $optExtra }}"
                        role="option"
                        aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                    >
                        <div class="searchable-select-option-content">
                            <span class="searchable-select-option-text">{{ $optText }}</span>
                            @if (! empty($optExtra))
                                <span class="searchable-select-option-badge">{{ $optExtra }}</span>
                            @endif
                        </div>
                        <svg class="searchable-select-check-icon {{ $isSelected ? '' : 'hidden' }}" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                @endforeach
            @endif
            <div class="searchable-select-empty {{ ! empty($options) ? 'hidden' : '' }}" id="empty_{{ $inputId }}">
                {{ $emptyText }}
            </div>
        </div>
    </div>
</div>

@once
<script>
(function() {
    if (window.SearchableSelect) return;

    class SearchableSelect {
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
            this.isDisabled = element.dataset.disabled === '1' || element.classList.contains('is-disabled');

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

        static initAll(root = document) {
            root.querySelectorAll('.searchable-select-component').forEach((el) => {
                if (!el.__searchableSelect) {
                    new SearchableSelect(el);
                }
            });
        }

        initEvents() {
            this.triggerBtn?.addEventListener('click', (e) => {
                if (this.isDisabled) return;
                this.toggle();
            });

            this.clearBtn?.addEventListener('click', (e) => {
                e.stopPropagation();
                if (this.isDisabled) return;
                this.clear(true);
            });

            this.searchInput?.addEventListener('input', () => {
                this.filter(this.searchInput.value);
            });

            this.searchInput?.addEventListener('keydown', (e) => {
                this.handleSearchKeydown(e);
            });

            this.optionsList?.addEventListener('click', (e) => {
                const optionEl = e.target.closest('.searchable-select-option');
                if (!optionEl) return;

                const val = optionEl.dataset.value;
                const text = optionEl.dataset.text;
                const extra = optionEl.dataset.extra || '';

                this.setValue(val, text, extra, true);
                this.close();
            });

            document.addEventListener('click', (e) => {
                if (!this.element.contains(e.target)) {
                    this.close();
                }
            });

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

        setOptions(options = [], selectedValue = null) {
            if (!this.optionsList) return;

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

            if (selectedValue !== null && selectedFound) {
                const selectedOpt = this.optionsList.querySelector(`.searchable-select-option[data-value="${CSS.escape(String(selectedValue))}"]`);
                if (selectedOpt) {
                    this.setValue(selectedOpt.dataset.value, selectedOpt.dataset.text, selectedOpt.dataset.extra, false);
                }
            } else if (selectedValue !== null && !selectedFound && selectedValue !== '') {
                this.clear(false);
            } else if (selectedValue === '' || selectedValue === null) {
                this.clear(false);
            }
        }

        setValue(value, text = '', extra = '', triggerChangeEvent = true) {
            const valStr = value !== null && value !== undefined ? String(value) : '';
            if (this.hiddenInput) {
                this.hiddenInput.value = valStr;
            }

            let matchedOpt = null;
            if (!text && valStr && this.optionsList) {
                matchedOpt = this.optionsList.querySelector(`.searchable-select-option[data-value="${CSS.escape(valStr)}"]`);
                if (matchedOpt) {
                    text = matchedOpt.dataset.text || '';
                    extra = matchedOpt.dataset.extra || '';
                }
            }

            if (this.labelSpan) {
                if (valStr && text) {
                    this.labelSpan.textContent = text;
                    this.labelSpan.classList.remove('is-placeholder');
                } else {
                    this.labelSpan.textContent = this.placeholder;
                    this.labelSpan.classList.add('is-placeholder');
                }
            }

            if (this.badgeSpan) {
                if (valStr && extra) {
                    this.badgeSpan.textContent = extra;
                    this.badgeSpan.classList.remove('hidden');
                } else {
                    this.badgeSpan.textContent = '';
                    this.badgeSpan.classList.add('hidden');
                }
            }

            if (this.clearBtn) {
                this.clearBtn.classList.toggle('hidden', !valStr);
            }

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
                const detail = {
                    value: valStr,
                    text: text,
                    extra: extra,
                    item: matchedOpt?.__optionData || null,
                };
                this.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                this.hiddenInput.dispatchEvent(new CustomEvent('searchable-select:change', { detail, bubbles: true }));
                this.element.dispatchEvent(new CustomEvent('searchable-select:change', { detail, bubbles: true }));
            }
        }

        clear(triggerChangeEvent = true) {
            this.setValue('', '', '', triggerChangeEvent);
        }

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

    window.SearchableSelect = SearchableSelect;

    // Inisialisasi on-demand saat klik trigger apa pun jika belum terinisialisasi
    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('.searchable-select-trigger');
        if (trigger) {
            const comp = trigger.closest('.searchable-select-component');
            if (comp && !comp.__searchableSelect) {
                new SearchableSelect(comp);
            }
        }
    }, true);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => SearchableSelect.initAll());
    } else {
        SearchableSelect.initAll();
    }
})();
</script>
@endonce
