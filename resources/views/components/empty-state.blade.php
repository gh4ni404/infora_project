@props([
    'icon' => 'search',
    'iconSize' => 36,
    'title' => null,
    'text' => 'Belum ada data yang ditemukan.',
    'colspan' => 5,
    'action' => null,
])

@if ($colspan)
<tr>
    <td colspan="{{ $colspan }}">
@endif
        <div class="empty-state">
            <div class="empty-state-icon">
                <x-icon :name="$icon" :size="$iconSize" />
            </div>
            @if ($title)
                <h3 class="empty-state-title">{{ $title }}</h3>
            @endif
            <span class="empty-state-text">{{ $text }}</span>
            @if ($action || (isset($slot) && $slot->isNotEmpty()))
                <div class="empty-state-action">
                    {{ $action ?? $slot }}
                </div>
            @endif
        </div>
@if ($colspan)
    </td>
</tr>
@endif

