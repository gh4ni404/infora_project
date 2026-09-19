@props([
    'label' => null,
    'role' => 'tablist',
])

<div {{ $attributes->class(['tab-pills']) }}
    @if ($role) role="{{ $role }}" @endif
    @if ($label) aria-label="{{ $label }}" @endif>
    {{ $slot }}
</div>
