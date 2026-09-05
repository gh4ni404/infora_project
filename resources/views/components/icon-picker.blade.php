@props([
    'name' => 'icon',
    'value' => 'layers',
    'id' => null,
])

@php
    $inputId = $id ?? $name;
    $currentValue = old($name, $value ?: 'layers');
    $categories = config('icons.categories', []);
    $icons = config('icons.icons', []);

    // Find active icon info
    $currentIconData = collect($icons)->firstWhere('name', $currentValue);
    $currentLabel = $currentIconData['label'] ?? ucfirst(str_replace('-', ' ', $currentValue));
@endphp

<div class="icon-picker-component" id="iconPicker_{{ $inputId }}" data-picker-id="{{ $inputId }}">
    <!-- Hidden Input for Form Submission -->
    <input type="hidden" name="{{ $name }}" id="{{ $inputId }}" value="{{ $currentValue }}" class="icon-picker-input">

    <!-- Selected Icon Preview Card -->
    <div class="icon-picker-selected-card">
        <div class="icon-picker-preview-box">
            <span class="icon-picker-preview-icon" id="previewIcon_{{ $inputId }}">
                <x-icon :name="$currentValue" class="picker-svg" />
            </span>
            <div class="icon-picker-info">
                <div class="icon-picker-name" id="previewLabel_{{ $inputId }}">{{ $currentLabel }}</div>
                <code class="icon-picker-code" id="previewCode_{{ $inputId }}">{{ $currentValue }}</code>
            </div>
        </div>
        <button type="button" class="btn-secondary icon-picker-btn-open" id="btnOpen_{{ $inputId }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="7" height="7" x="3" y="3" rx="1"></rect>
                <rect width="7" height="7" x="14" y="3" rx="1"></rect>
                <rect width="7" height="7" x="14" y="14" rx="1"></rect>
                <rect width="7" height="7" x="3" y="14" rx="1"></rect>
            </svg>
            <span>Pilih Ikon</span>
        </button>
    </div>

    <!-- Icon Selection Modal -->
    <div class="icon-picker-modal-backdrop hidden" id="modal_{{ $inputId }}" role="dialog" aria-modal="true" aria-labelledby="modalTitle_{{ $inputId }}">
        <div class="icon-picker-modal-dialog">
            <!-- Modal Header -->
            <div class="icon-picker-modal-header">
                <div>
                    <h3 class="icon-picker-modal-title" id="modalTitle_{{ $inputId }}">Katalog Ikon Navigasi</h3>
                    <p class="icon-picker-modal-subtitle">Pilih ikon visual (100% Gratis & Bebas Lisensi - Lucide Icons)</p>
                </div>
                <button type="button" class="icon-picker-close-btn" id="btnClose_{{ $inputId }}" aria-label="Tutup Katalog">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Modal Search & Filters -->
            <div class="icon-picker-modal-toolbar">
                <div class="icon-picker-search-wrapper">
                    <svg class="icon-picker-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input
                        type="text"
                        class="icon-picker-search-input"
                        id="searchInput_{{ $inputId }}"
                        placeholder="Cari ikon atau kata kunci (contoh: sekolah, siswa, data, laporan)..."
                        autocomplete="off"
                    >
                </div>

                <!-- Category Filters -->
                <div class="icon-picker-categories" id="categories_{{ $inputId }}">
                    @foreach ($categories as $catKey => $catLabel)
                        <button
                            type="button"
                            class="icon-picker-category-chip {{ $catKey === 'all' ? 'active' : '' }}"
                            data-category="{{ $catKey }}"
                        >
                            {{ $catLabel }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Modal Icon Grid -->
            <div class="icon-picker-grid-wrapper">
                <div class="icon-picker-grid" id="grid_{{ $inputId }}">
                    @foreach ($icons as $iconItem)
                        @php
                            $isSelected = $iconItem['name'] === $currentValue;
                        @endphp
                        <button
                            type="button"
                            class="icon-picker-card {{ $isSelected ? 'is-selected' : '' }}"
                            data-icon-name="{{ $iconItem['name'] }}"
                            data-icon-label="{{ $iconItem['label'] }}"
                            data-icon-category="{{ $iconItem['category'] }}"
                            data-icon-keywords="{{ $iconItem['keywords'] ?? '' }}"
                            title="{{ $iconItem['label'] }} ({{ $iconItem['name'] }})"
                        >
                            <span class="icon-picker-card-symbol">
                                <x-icon :name="$iconItem['name']" class="picker-card-svg" />
                            </span>
                            <span class="icon-picker-card-label">{{ $iconItem['label'] }}</span>
                            <span class="icon-picker-card-code">{{ $iconItem['name'] }}</span>
                            @if ($isSelected)
                                <span class="icon-picker-check-badge">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>
                <div class="icon-picker-empty hidden" id="empty_{{ $inputId }}">
                    <p>Ikon tidak ditemukan dengan kata kunci tersebut.</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="icon-picker-modal-footer">
                <span class="icon-picker-footer-hint">Klik pada kartu ikon untuk langsung memilih dan menerapkan.</span>
                <button type="button" class="btn-secondary" id="btnCancel_{{ $inputId }}">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const pickerId = "{{ $inputId }}";
    const container = document.getElementById('iconPicker_' + pickerId);
    if (!container) return;

    const hiddenInput = document.getElementById(pickerId);
    const previewIcon = document.getElementById('previewIcon_' + pickerId);
    const previewLabel = document.getElementById('previewLabel_' + pickerId);
    const previewCode = document.getElementById('previewCode_' + pickerId);
    const modal = document.getElementById('modal_' + pickerId);
    const btnOpen = document.getElementById('btnOpen_' + pickerId);
    const btnClose = document.getElementById('btnClose_' + pickerId);
    const btnCancel = document.getElementById('btnCancel_' + pickerId);
    const searchInput = document.getElementById('searchInput_' + pickerId);
    const categoryChips = container.querySelectorAll('.icon-picker-category-chip');
    const cards = container.querySelectorAll('.icon-picker-card');
    const emptyState = document.getElementById('empty_' + pickerId);

    let activeCategory = 'all';

    function openModal() {
        modal.classList.remove('hidden');
        document.body.classList.add('modal-open');
        setTimeout(() => searchInput && searchInput.focus(), 50);
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.classList.remove('modal-open');
        if (searchInput) searchInput.value = '';
        activeCategory = 'all';
        categoryChips.forEach(chip => {
            chip.classList.toggle('active', chip.dataset.category === 'all');
        });
        filterIcons();
    }

    function filterIcons() {
        const query = (searchInput.value || '').toLowerCase().trim();
        let visibleCount = 0;

        cards.forEach(card => {
            const name = (card.dataset.iconName || '').toLowerCase();
            const label = (card.dataset.iconLabel || '').toLowerCase();
            const cat = (card.dataset.iconCategory || '').toLowerCase();
            const keywords = (card.dataset.iconKeywords || '').toLowerCase();

            const matchesCategory = activeCategory === 'all' || cat === activeCategory;
            const matchesQuery = !query || name.includes(query) || label.includes(query) || keywords.includes(query);

            if (matchesCategory && matchesQuery) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        if (emptyState) {
            emptyState.classList.toggle('hidden', visibleCount > 0);
        }
    }

    function selectIcon(card) {
        const iconName = card.dataset.iconName;
        const iconLabel = card.dataset.iconLabel;

        // Update hidden input
        hiddenInput.value = iconName;

        // Update labels
        if (previewLabel) previewLabel.textContent = iconLabel;
        if (previewCode) previewCode.textContent = iconName;

        // Clone SVG to preview
        const svgClone = card.querySelector('.picker-card-svg').cloneNode(true);
        svgClone.setAttribute('class', 'picker-svg');
        previewIcon.innerHTML = '';
        previewIcon.appendChild(svgClone);

        // Update selected state in grid
        cards.forEach(c => {
            c.classList.remove('is-selected');
            const badge = c.querySelector('.icon-picker-check-badge');
            if (badge) badge.remove();
        });

        card.classList.add('is-selected');
        const badge = document.createElement('span');
        badge.className = 'icon-picker-check-badge';
        badge.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
        card.appendChild(badge);

        closeModal();
    }

    btnOpen && btnOpen.addEventListener('click', openModal);
    btnClose && btnClose.addEventListener('click', closeModal);
    btnCancel && btnCancel.addEventListener('click', closeModal);

    // Click outside to close
    modal && modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    // Search input
    searchInput && searchInput.addEventListener('input', filterIcons);

    // Category chips
    categoryChips.forEach(chip => {
        chip.addEventListener('click', function() {
            categoryChips.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            activeCategory = this.dataset.category;
            filterIcons();
        });
    });

    // Card selection
    cards.forEach(card => {
        card.addEventListener('click', function() {
            selectIcon(this);
        });
    });
})();
</script>
