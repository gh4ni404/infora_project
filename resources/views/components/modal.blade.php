@props([
    'name' => null, //dipakai di case delete dan standar
    'id' => null, //dipakai di case delete dan standar
    'title' => null, //dipakai di case standar
    'subtitle' => null, //dipakai di case standar
    'size' => null, //dipakai di case standar
    'method' => null,
    'action' => null, //dipakai di case standar
    'formId' => null, //dipakai di case delete dan standar
    'messageId' => null, // dipakai di case delete
    'badgeId' => null, // dipakai di case delete
    'itemTitleId' => null, //dipakai di case delete
    'cancelBtnId' => null,
    'confirmBtnId' => null,
])

@php
    $variant = strtolower($name ?? 'default');

    // Preset nilai berdasarkan variant / case
    if ($variant === 'delete') {
        $size = $size ?? 'sm';
        $method = $method ?? 'DELETE';
    } else {
        $size = $size ?? 'default';
    }

    $sizeClass = match ($size) {
        'sm' => 'modal-sm',
        'lg' => 'modal-lg',
        default => '',
    };
    $method = strtoupper($method ?? '');
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
                            @if ($hasSlot)
                                {{ $slot }}
                            @else
                                <p class="text-sm text-gray-600 mb-2.5" id="{{ $messageId }}">
                                    -
                                </p>

                                <div class="p-3.5 my-8 mx-6 bg-slate-50 border border-slate-200/90 rounded-xl text-center shadow-xs">
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-mono font-bold bg-slate-200/80 text-slate-700 rounded-md mb-1.5">
                                        <span id="{{ $badgeId }}">-</span>
                                    </span>
                                    <div class="text-sm font-bold text-slate-900 leading-snug" id="{{ $itemTitleId }}">-</div>
                                </div>

                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-700 border border-red-100">
                                    <x-icon name="alert-circle" size="14" />
                                    <span>Tindakan ini tidak dapat dibatalkan.</span>
                                </div>
                            @endif
                            {{ $hiddenInputs ?? '' }}
                        </div>
                    </div>
                    {{-- Footer: Tombol Aksi Simetris (Grid 50/50) --}}
                    <div class="modal-footer grid grid-cols-2 gap-3 p-4 bg-slate-50/80 border-t border-slate-100">
                        <button type="button" class="btn-secondary w-full justify-center font-medium" id="{{ $cancelBtnId }}">
                            <span>Batal</span>
                        </button>
                        <button type="submit" class="btn-delete w-full justify-center font-medium" id="{{ $confirmBtnId }}">
                            <span>Hapus</span>
                        </button>
                    </div>
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
                        <h3 class="modal-title" id="{{ $id }}Title">{{ $title }}</h3>
                        @if ($subtitle)
                            <p class="modal-subtitle" id="{{ $id }}Subtitle">{{ $subtitle }}</p>
                        @endif
                    </div>
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
