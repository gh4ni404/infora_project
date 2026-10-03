@props([
    'action' => 'view', // create, view, edit, delete
    'id' => null,       // id yang akan di-pass ke route (edit/view)
    'title' => null,    // title untuk tooltip &ARIA label
    'tag' => 'button',  // button, link, form, toggle, checkbox, etc
    'href' => '#',      // default untuk link
    'text' => null,     // opsional, akan menggunakan default jika kosong
    'icon' => null,     // opsional, akan menggunakan default jika kosong
    'type' => 'button',
])
@php
    $user = auth()->user();
    $hasAccess = false;

    // Evaluasi akses dengan efisien (Short-circuit evaluation)
    if ($user) {
        if ($user->isSuperAdmin()) {
            $hasAccess = true;
        } else {
            $resolved = \App\Support\SidebarCache::findByRoute(request());
            if ($resolved) {
                $hasAccess = $resolved['type'] === 'submenu' 
                    ? $user->hasSubMenuAccess($resolved['item'], $action) 
                    : $user->hasMenuAccess($resolved['item'], $action);
            }
        }
    }

    // Pemetaan warna konsisten dengan DESIGN.md
    $styles = [
        'create' => 'btn-primary',
        'edit' => 'btn-edit',
        'delete' => 'btn-delete',
        'view' => '',
    ];
    $colorClass = $styles[$action] ?? $styles['view'];

    // Icon default
    $defaultIcons = [
        'create' => 'add',
        'edit' => 'square-pen',
        'delete' => 'delete',
        'view' => 'eye-on',
    ];
    $iconName = $icon ?? $defaultIcons[$action];

    $attributes = $attributes->merge(['id' => $id, 'class' => $colorClass, 'type' => $type, 'aria-label' => $title, 'title' => $title]);
@endphp
@if ($hasAccess)
    @switch($tag)
        @case('link')
            <a {{ $attributes->except('type')->merge(['href' => $href]) }}>
                <x-icon name="{{ $iconName }}" size="16" />
                @if ($text)
                    <span>{{ $text }}</span>
                @endif
                {{ $slot }}
            </a>
            @break

        @case('form')
            <form action="{{ $href }}" method="POST" class="inline">
                @csrf
                <button {{ $attributes }}>
                    <x-icon name="{{ $iconName }}" size="16" />
                    @if ($text)
                        <span>{{ $text }}</span>
                    @endif
                    {{ $slot }}
                </button>
            </form>
            @break

        @default
            <button {{ $attributes }}>
                <x-icon name="{{ $iconName }}" size="16" />
                @if ($text)
                    <span>{{ $text }}</span>
                @endif
                {{ $slot }}
            </button>
    @endswitch
@endif