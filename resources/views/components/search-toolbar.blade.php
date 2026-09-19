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
                <select id="table_per_page" name="per_page" class="form-select-sm" onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()">
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

        {{ $filters ?? $slot ?? '' }}
    </div>

    <div class="search-box toolbar-search-box">
        <x-icon name="search" size="16" class="search-icon" />
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
