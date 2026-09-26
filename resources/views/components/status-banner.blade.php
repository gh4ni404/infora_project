@props([
    'type' => 'info',
    'title' => null,
    'message' => null,
    'badge' => null,
    'badgeColor' => null,
    'actionTitle' => null,
    'actionHref' => null,
    'actionType' => null, // 'a', 'link', 'button'
    'actionIcon' => null,
    'actionId' => null,
    'actionOnClick' => null,
])

@php
    $classMap = [
        'info' => 'alert-info',
        'success' => 'alert-success',
        'warning' => 'alert-warning',
        'danger' => 'alert-danger',
        'error' => 'alert-danger',
    ];

    $iconMap = [
        'info' => 'info-circle',
        'success' => 'check-circle',
        'warning' => 'alert-triangle',
        'danger' => 'alert-circle',
        'error' => 'alert-circle',
    ];

    $badgeMap = [
        'info' => 'warning',
        'success' => 'success',
        'warning' => 'warning',
        'danger' => 'danger',
        'error' => 'danger',
    ];

    $alertClass = $classMap[$type];
    $iconName = $iconMap[$type];
    $badgeClass = $badgeColor ?? $badgeMap[$type];

    // Tentukan elemen aksi: 'button' atau 'a' / 'link'
    if ($actionType) {
        $isButtonAction = in_array(strtolower($actionType), ['button', 'btn']);
    } else {
        $isButtonAction = empty($actionHref);
    }

    $hasSlot = isset($slot) && $slot->isNotEmpty();
    $hasRightSlot = isset($right) && $right->isNotEmpty();
    $hasBadgeSlot = isset($badgeSlot) && $badgeSlot->isNotEmpty();
    $hasActions = (isset($actions) && $actions->isNotEmpty()) || !empty($actionTitle);
@endphp

<div {{ $attributes->merge(['class' => $alertClass]) }}>
    @if ($iconName)
        <x-icon :name="$iconName" size="20" />
    @endif
    <div>
        @if ($title)
            <strong>{{ $title }}</strong>
        @endif
        @if ($hasSlot)
            {{ $slot }}
        @elseif (!empty($message))
            {{ $message }}
        @endif

        @if ($hasRightSlot)
            {{ $right }}
        @elseif ($hasBadgeSlot)
            {{ $badgeSlot }}
        @elseif ($badge)
            <span class="badge badge-{{ $badgeClass }}">{{ $badge }}</span>
        @endif

    </div>
    @if ($hasActions)
        <div class="alert-actions">
            @if (isset($actions) && $actions->isNotEmpty())
                {{ $actions }}
            @elseif ($isButtonAction)
                <button type="button" @if ($actionId) id="{{ $actionId }}" @endif
                    @if ($actionOnClick) onclick="{{ $actionOnClick }}" @endif class="btn-primary">
                    @if ($actionIcon)
                        <x-icon :name="$actionIcon" size="14" />
                    @endif
                    <span>{{ $actionTitle }}</span>
                </button>
            @else
                <a href="{{ $actionHref }}" @if ($actionId) id="{{ $actionId }}" @endif
                    class="btn-secondary btn-sm">
                    @if ($actionIcon)
                        <x-icon :name="$actionIcon" size="14" />
                    @endif
                    <span>{{ $actionTitle }}</span>
                </a>
            @endif
        </div>
    @endif
</div>
