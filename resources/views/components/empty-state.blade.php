@props([
    'icon' => 'search',
    'iconSize' => 36,
    'title' => null,
    'text' => 'Belum ada data yang ditemukan.',
    'colspan' => 5,
    'action' => null,
])

<tr>
    <td colspan="{{ $colspan }}">
        <div class="empty-state">
            <div class="empty-state-icon">
                <x-icon :name="$icon" :size="$iconSize" class="empty-state-icon" />
            </div>
            @if ($title)
                <h3 class="empty-state-title">{{ $title }}</h3>
            @endif
            <span class="empty-state-text">{{ $text }}</span>
        </div>
    </td>
</tr>

