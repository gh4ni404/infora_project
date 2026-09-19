@props([
    'type' => 'success',
    'dismissible' => true,
    'title' => null,
    'message' => null,
])

@php
    $sessionKey = $type === 'error' ? 'error' : $type;
    $flashMessage = $message ?? session($sessionKey);

    if ($type === 'validation' && $errors->any()) {
        $flashMessage = $errors->first();
        $type = 'error';
    }

    $iconMap = [
        'success' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>',
        'error' => '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>',
        'warning' => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>',
        'info' => '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>',
    ];

    $classMap = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
    ];

    $cssClass = $classMap[$type] ?? 'alert-info';
    $iconSvg = $iconMap[$type] ?? $iconMap['info'];
    $hasSlot = isset($slot) && $slot->isNotEmpty();
    $shouldRender = $hasSlot || !empty($flashMessage) || (!empty($title) && $type !== 'validation');
@endphp

@if ($shouldRender)
    <div {{ $attributes->merge(['class' => 'alert ' . $cssClass]) }}>
        <div class="alert-content">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="alert-icon">
                {!! $iconSvg !!}
            </svg>
            <div class="alert-body">
                @if ($title)
                    <strong>{{ $title }}</strong>
                    @if ($hasSlot)
                        <div class="text-sm mt-1">{{ $slot }}</div>
                    @elseif (!empty($flashMessage))
                        <div class="text-sm mt-1">{{ $flashMessage }}</div>
                    @endif
                @else
                    @if ($hasSlot)
                        {{ $slot }}
                    @else
                        <span>{{ $flashMessage }}</span>
                    @endif
                @endif
            </div>
        </div>
        @if ($dismissible)
            <button type="button" class="alert-close-btn" aria-label="Tutup pemberitahuan">
                <x-icon name="close" size="16" />
            </button>
        @endif
    </div>
@endif
