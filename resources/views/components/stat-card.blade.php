@props([
    'icon' => null,
    'color' => 'primary',
    'label' => null,
    'value' => null,
])

<div {{ $attributes->merge(['class' => 'stat-card']) }}>
    <div class="stat-icon is-{{ $color }}">
        <x-icon :name="$icon" size="22" />
    </div>
    <div class="stat-content">
        <span class="stat-label">{{ $label }}</span>
        @if ($value !== null)
            <span class="stat-value">{{ $value }}</span>
        @else
            <span class="stat-value">{{ $slot }}</span>
        @endif
    </div>
</div>
