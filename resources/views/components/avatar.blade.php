@props([
    'src' => null,
    'name' => null,
    'size' => null, // sm (28px), md (38px, default), lg (72px), xl (84px)
])

@php
    $url = $src ? (str_starts_with($src, 'http') || str_starts_with($src, '/') ? $src : asset('storage/' . $src)) : null;
    $initials = strtoupper(substr($name, 0, 2));
    $sizeClass = $size ? "avatar-{$size}" : '';
@endphp

<div {{ $attributes->merge(['class' => trim("user-avatar-circle {$sizeClass} " . ($url ? 'has-image' : ''))]) }}>
    @if ($url)
        <img src="{{ $url }}" alt="{{ $name }}">
    @else
        <span>{{ $initials }}</span>
    @endif
</div>
