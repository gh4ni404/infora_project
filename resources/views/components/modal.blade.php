@props([
    'name' => null,
    'type' => null,
    'id' => null,
    'title' => null,
    'subtitle' => null,
    'size' => null,
    'closeBtnId' => null,
    'closeLabel' => 'Tutup',
    'method' => null,
    'action' => null,
    'formId' => null,
    'danger' => false,
    // Props khusus untuk preset (seperti name/type="delete")
    'itemTitleId' => 'deleteItemTitle',
    'itemName' => 'data ini',
    'context' => null,
    'cancelBtnId' => null,
    'confirmBtnId' => null,
    'confirmText' => 'Hapus Sekarang',
    'confirmIcon' => 'trash',
])

@php
    $variant = strtolower($name ?? $type ?? 'default');

    // Preset nilai berdasarkan variant / case
    if ($variant === 'delete') {
        $id = $id ?? 'modalDelete';
        $title = $title ?? 'Konfirmasi Hapus Data';
        $subtitle = $subtitle ?? 'Tindakan ini akan menghapus data secara permanen dari sistem';
        $size = $size ?? 'sm';
        $method = $method ?? 'DELETE';
        $danger = true;
        $formId = $formId ?? 'formDelete';
        $cancelBtnId = $cancelBtnId ?? ('btnCancel' . Str::studly($id));
        $confirmBtnId = $confirmBtnId ?? ('btnConfirm' . Str::studly($id));
    } else {
        $size = $size ?? 'default';
    }

    $sizeClass = match ($size) {
        'sm' => 'modal-sm',
        'lg' => 'modal-lg',
        default => '',
    };
    $method = strtoupper($method ?? '');
    $resolvedCloseBtnId = $closeBtnId ?? ('btnClose' . Str::studly($id ?? 'modal'));
    $hasSlot = isset($slot) && $slot->isNotEmpty();
    $hasFooter = isset($footer) && $footer->isNotEmpty();
@endphp

<div
    class="modal-backdrop hidden"
    id="{{ $id }}"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}Title"
>
    <div class="modal-dialog {{ $sizeClass }}">
        <div class="modal-header">
            <div>
                <h3 class="modal-title {{ $danger ? 'text-danger' : '' }}" id="{{ $id }}Title">{{ $title }}</h3>
                @if ($subtitle)
                    <p class="modal-subtitle" id="{{ $id }}Subtitle">{{ $subtitle }}</p>
                @endif
            </div>
            <button type="button" class="modal-close-btn" id="{{ $resolvedCloseBtnId }}" aria-label="{{ $closeLabel }}">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        @if ($action !== null || $method)
            <form method="POST" action="{{ $action ?? '' }}" @if ($formId) id="{{ $formId }}" @endif>
                @csrf
                @if (in_array($method, ['PUT', 'PATCH', 'DELETE'], true))
                    @method($method)
                @endif

                <div class="modal-body">
                    @switch($variant)
                        @case('delete')
                            @if ($hasSlot)
                                {{ $slot }}
                            @else
                                <p class="text-secondary">
                                    Apakah Anda yakin ingin menghapus {{ $itemName }} <strong id="{{ $itemTitleId }}" class="text-primary"></strong>{{ $context ? " dari {$context}" : '' }}?
                                </p>
                                <x-alert type="error" class="alert-compact">
                                    <span>Tindakan ini tidak dapat dibatalkan.</span>
                                </x-alert>
                            @endif

                            {{ $hiddenInputs ?? '' }}
                            @break

                        @default
                            {{ $slot }}
                    @endswitch
                </div>

                @if ($variant === 'delete' && !$hasFooter)
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" id="{{ $cancelBtnId }}">Batal</button>
                        <button type="submit" class="btn-delete" id="{{ $confirmBtnId }}">
                            <x-icon :name="$confirmIcon" size="16" />
                            <span>{{ $confirmText }}</span>
                        </button>
                    </div>
                @elseif ($hasFooter)
                    <div class="modal-footer">
                        {{ $footer }}
                    </div>
                @endif
            </form>
        @else
            <div class="modal-body">
                @switch($variant)
                    @case('delete')
                        @if ($hasSlot)
                            {{ $slot }}
                        @else
                            <p class="text-secondary">
                                Apakah Anda yakin ingin menghapus {{ $itemName }} <strong id="{{ $itemTitleId }}" class="text-primary"></strong>{{ $context ? " dari {$context}" : '' }}?
                            </p>
                            <x-alert type="error" class="alert-compact">
                                <span>Tindakan ini tidak dapat dibatalkan.</span>
                            </x-alert>
                        @endif

                        {{ $hiddenInputs ?? '' }}
                        @break

                    @default
                        {{ $slot }}
                @endswitch
            </div>

            @if ($variant === 'delete' && !$hasFooter)
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="{{ $cancelBtnId }}">Batal</button>
                    <button type="submit" class="btn-delete" id="{{ $confirmBtnId }}">
                        <x-icon :name="$confirmIcon" size="16" />
                        <span>{{ $confirmText }}</span>
                    </button>
                </div>
            @elseif ($hasFooter)
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endif
        @endif
    </div>
</div>
