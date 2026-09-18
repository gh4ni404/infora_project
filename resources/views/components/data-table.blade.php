@props([
    'route',
    'searchPlaceholder' => 'Cari data...',
    'perPage' => null,
    'perPageText' => 'data',
    'total' => null,
    'totalLabel' => 'data',
])

<div class="table-card">
    <div class="table-toolbar">
        <x-search-toolbar
            :action="$route"
            :search-placeholder="$searchPlaceholder"
            :per-page="$perPage"
            :per-page-text="$perPageText"
            :total="$total"
            :total-label="$totalLabel"
        >
            {{ $filters ?? '' }}
        </x-search-toolbar>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            {{ $slot }}
        </table>
    </div>

    {{ $footer ?? '' }}
</div>
