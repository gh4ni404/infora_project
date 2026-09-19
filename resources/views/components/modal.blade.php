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
        $title = $title ?? ('Hapus ' . ucfirst($itemName) . '?');
        $subtitle = $subtitle ?? null;
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


@switch($variant)
    @case('delete')
        {{-- ============================================================
             TAMPILAN KHUSUS MODAL DELETE (Elegan, Modern & Terpusat)
             ============================================================ --}}
        <div
            class="modal-backdrop hidden"
            id="{{ $id }}"
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $id }}Title"
        >
            <div class="modal-dialog modal-sm relative overflow-hidden text-center">
                <form method="POST" action="{{ $action ?? '' }}" @if ($formId) id="{{ $formId }}" @endif class="m-0 flex flex-col">
                    @csrf
                    @if (in_array($method, ['PUT', 'PATCH', 'DELETE'], true))
                        @method($method)
                    @endif
                    {{-- Body Terpadu: Ikon Ambient Halo, Judul, & Pesan Konfirmasi --}}
                    <div class="modal-body items-center">
                        <!-- Ambient Danger Halo Icon -->
                        <div class="modal-danger-halo w-28 h-28 rounded-full bg-red-50 text-red-600 ring-8 ring-red-50/60 flex items-center justify-center my-8 shrink-0 mx-auto">
                            <x-icon name="trash" size="52"></x-icon>
                        </div>
                        <!-- Judul Modal -->
                        <h3 class="text-base font-bold text-danger mb-1" id="{{ $id }}Title">
                            {{ $title }}
                        </h3>
                        <!-- Konten Pesan Hapus -->
                        <div class="text-sm text-gray-600 leading-relaxed max-w-sm w-full">
                            {{-- @if ($hasSlot) --}}
                                {{ $slot }}
                            {{-- @else
                                <p class="mb-3">
                                    Apakah Anda yakin ingin menghapus {{ $itemName }} <strong id="{{ $itemTitleId }}" class="font-semibold text-gray-900"></strong>{{ $context ? " dari {$context}" : '' }}?
                                </p>
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-100">
                                    <span>Tindakan ini tidak dapat dibatalkan.</span>
                                </div>
                            @endif --}}
                            {{ $hiddenInputs ?? '' }}
                        </div>
                    </div>
                    {{-- Footer: Tombol Aksi Simetris (Grid 50/50) --}}
                    {{-- @if (!$hasFooter) --}}
                        <div class="modal-footer grid grid-cols-2 gap-3 p-4 bg-slate-50/80 border-t border-slate-100">
                            <button type="button" class="btn-secondary w-full justify-center font-medium" id="{{ $cancelBtnId }}">
                                Batal
                            </button>
                            <button type="submit" class="btn-delete w-full justify-center font-medium" id="{{ $confirmBtnId }}">
                                {{-- <x-icon :name="$confirmIcon" size="16" /> --}}
                                <span>{{ $confirmText }}</span>
                            </button>
                        </div>
                    {{-- @else
                        <div class="modal-footer grid grid-cols-2 gap-3 p-4 bg-slate-50/80 border-t border-slate-100">
                            {{ $footer }}
                        </div> --}}
                    {{-- @endif --}}
                </form>
            </div>
        </div>
        @break

    @default
        {{-- ============================================================
             TAMPILAN MODAL STANDAR (Formulir, Tambah, Ubah, Detail, dll)
             ============================================================ --}}
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
                            {{ $slot }}
                        </div>

                        @if ($hasFooter)
                            <div class="modal-footer">
                                {{ $footer }}
                            </div>
                        @endif
                    </form>
                @else
                    <div class="modal-body">
                        {{ $slot }}
                    </div>

                    @if ($hasFooter)
                        <div class="modal-footer">
                            {{ $footer }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
@endswitch
