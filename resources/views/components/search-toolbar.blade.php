@props([
    'action' => null,
    'method' => 'GET',
    'searchPlaceholder' => 'Cari data...',
    'perPage' => null,
    'perPageOptions' => [15, 30, 90],
    'perPageAllLabel' => 'Semua',
    'perPageText' => 'data',
    'total' => null,
    'totalLabel' => 'data',
    'showTotal' => true,
])

<form method="{{ $method }}" action="{{ $action }}" class="table-toolbar-form" {{ $attributes }}>
    <div class="page-actions">
        @if ($perPage !== null)
            <div class="per-page-selector">
                <label for="table_per_page" class="table-cell-muted">Tampilkan:</label>
                <select id="table_per_page" name="per_page" class="form-select-sm" onchange="this.form.submit()">
                    @foreach ($perPageOptions as $option)
                        <option value="{{ $option }}" {{ request('per_page', (string) $perPage) === (string) $option ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endforeach
                    <option value="semua" {{ in_array(request('per_page'), ['semua', 'all'], true) ? 'selected' : '' }}>
                        {{ $perPageAllLabel }}
                    </option>
                </select>
                <span class="table-cell-muted">{{ $perPageText }}</span>
            </div>
        @endif

        {{ $filters ?? '' }}
    </div>

    <div class="search-box toolbar-search-box">
        <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input
            type="text"
            name="search"
            class="search-input"
            placeholder="{{ $searchPlaceholder }}"
            value="{{ request('search') }}"
        >
    </div>

    @if ($showTotal && $total !== null)
        <div class="table-cell-muted">
            Total: <strong>{{ number_format($total) }}</strong> {{ $totalLabel }}
        </div>
    @endif
</form>
