@props([
    'title',
    'subtitle' => null,
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
    </div>
</div>
