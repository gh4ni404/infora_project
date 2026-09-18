@props([
    'icon' => 'search',
    'title' => null,
    'text' => 'Belum ada data yang ditemukan.',
    'colspan' => 5,
    'action' => null,
])

<tr>
    <td colspan="{{ $colspan }}">
        <div class="empty-state">
            <div class="empty-state-icon">
                <x-icon :name="$icon" class="" />
            </div>
            @if ($title)
                <h3 class="empty-state-title">{{ $title }}</h3>
            @endif
            <p class="empty-state-text">{{ $text }}</p>
            @if ($action)
                {{ $action }}
            @endif
        </div>
    </td>
</tr>
