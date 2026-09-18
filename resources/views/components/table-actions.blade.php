@props([
    'align' => 'right',
])

<div class="table-actions table-actions-{{ $align }}">
    {{ $slot }}
</div>
