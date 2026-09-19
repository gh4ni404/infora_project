@props([
    'route' => null,
    'title' => null,
    'subtitle' => null,
    'searchPlaceholder' => 'Cari data...',
    'perPage' => null,
    'perPageText' => 'data',
    'total' => null,
    'totalLabel' => 'Data',
    'totalPrefix' => 'Total:',
])

<div {{ $attributes->merge(['class' => 'table-card']) }}>
    <div class="table-toolbar">
        @if (isset($toolbar))
            {{ $toolbar }}
        @elseif ($title)
            <div class="page-actions">
                <span class="table-cell-bold">{{ $title }}</span>
                @if (isset($subtitle) && (is_string($subtitle) ? trim($subtitle) !== '' : true))
                    <span class="table-cell-muted">&bull; {!! $subtitle !!}</span>
                @elseif ($total !== null)
                    <span class="table-cell-muted">&bull; {{ number_format($total) }} {{ $totalLabel }}</span>
                @endif
            </div>

            @if (isset($actions))
                <div class="page-actions">
                    {{ $actions }}
                </div>
            @endif
        @else
            <x-search-toolbar
                :action="$route"
                :search-placeholder="$searchPlaceholder"
                :per-page="$perPage"
                :per-page-text="$perPageText"
                :total="$total"
                :total-label="$totalLabel"
                :total-prefix="$totalPrefix"
            >
                <x-slot:filters>
                    {{ $filters ?? '' }}
                </x-slot:filters>
            </x-search-toolbar>
        @endif
    </div>

    <div class="table-responsive table-responsive-scroll">
        <table class="data-table">
            {{ $slot }}
        </table>
    </div>

    {{ $footer ?? '' }}
</div>
