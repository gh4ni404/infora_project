@props([
    'title',
    'subtitle' => null,
    'badgePage' => null,
])

<div class="page-header">
    <div>
        <h2 class="page-title">{{ $title }}</h2>
        @if ($subtitle)
            <div class="page-subtitle">{{ $subtitle }}</div>
        @endif
    </div>
    <div class="page-actions">
        {{ $actions ?? '' }}
        @if ($badgePage)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/70 uppercase">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>{{ $badgePage }}</span>
            </span>
        @endif
    </div>
</div>
