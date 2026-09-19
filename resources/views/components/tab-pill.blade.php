@props([
    'active' => false,
    'href' => null,
    'icon' => null,
    'iconSize' => 15,
    'badge' => null,
    'badgeClass' => 'badge-cyan',
])

@php
    $isActive = (bool) $active;
@endphp

@if ($href)
    <a href="{{ $href }}"
        role="tab"
        aria-selected="{{ $isActive ? 'true' : 'false' }}"
        @if ($isActive) aria-current="page" @endif
        {{ $attributes->class(['tab-pill', 'active' => $isActive]) }}>
        @if ($icon)
            <x-icon :name="$icon" :size="$iconSize" />
            @if (trim((string) $slot) !== '')
                <span>{{ $slot }}</span>
            @endif
        @else
            {{ $slot }}
        @endif
        @if ($badge !== null)
            <span class="badge {{ $badgeClass }}">{{ $badge }}</span>
        @endif
    </a>
@else
    <button type="button"
        role="tab"
        aria-selected="{{ $isActive ? 'true' : 'false' }}"
        {{ $attributes->class(['tab-pill', 'active' => $isActive]) }}>
        @if ($icon)
            <x-icon :name="$icon" :size="$iconSize" />
            @if (trim((string) $slot) !== '')
                <span>{{ $slot }}</span>
            @endif
        @else
            {{ $slot }}
        @endif
        @if ($badge !== null)
            <span class="badge {{ $badgeClass }}">{{ $badge }}</span>
        @endif
    </button>
@endif
