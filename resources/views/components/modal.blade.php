@props([
    'name' => null, // dipakai di case delete, confirm, dan standar
    'id' => null, // dipakai di case delete, confirm, dan standar
    'title' => null,
    'subtitle' => null, // dipakai di case standar
    'size' => 'default',
    'method' => null,
    'action' => null,
    'formId' => null,
    'messageId' => null, // dipakai di case delete dan confirm
    'badgeId' => null, // dipakai di case delete dan confirm
    'itemTitleId' => null, // dipakai di case delete dan confirm
    'cancelBtnId' => null,
    'confirmBtnId' => null,
    'hintId' => null,
])

@php
    $variant = strtolower($name ?? 'default');

    $sizeClass = match ($size) {
        'sm' => 'modal-sm',
        'lg' => 'modal-lg',
        default => '',
    };
    $method = strtoupper($method ?? '');
    $hasFooter = isset($footer) && $footer->isNotEmpty();

    // for case delete dan confirm
    $isDelete = $variant === 'delete';
    $defaultIcon = $isDelete ? 'delete-2' : 'alert-triangle';
    $defaultModal = $isDelete ? 'modal-danger-halo' : 'modal-confirm-halo';
    $defaultHaloClass = $isDelete
        ? 'bg-red-50 text-red-600 ring-8 ring-red-50/60'
        : 'bg-blue-50 text-blue-600 ring-8 ring-blue-50/60';
    $defaultTitleColor = $isDelete ? 'text-danger' : 'text-slate-900';
    $defaultMessage = $isDelete ? 'Tindakan ini tidak dapat dibatalkan.' : 'Pastikan data dan tindakan yang Anda ambil sudah benar.';
    $defaultMessageClass = $isDelete
        ? 'bg-red-50 text-red-700 border border-red-100'
        : 'bg-amber-50 text-amber-700 border border-amber-200';
    $defaultBtnClass = $isDelete ? 'btn-delete' : 'btn-primary';
    $defaultConfirmText = $isDelete ? 'Hapus' : 'Lanjutkan';
    $defaultMethod = $isDelete ? 'DELETE' : (in_array($method, ['PUT', 'PATCH'], true) ? $method : 'POST');

@endphp

@switch($variant)
    @case('delete')
    @case('confirm')
        {{-- ============================================================
             TAMPILAN GABUNGAN MODAL DELETE & CONFIRM (DRY)
             ============================================================ --}}
        <div class="modal-backdrop hidden" id="{{ $id }}" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}Title">
            <div class="modal-dialog modal-sm relative overflow-hidden text-center">
                <form method="POST" action="{{ $action }}" @if ($formId) id="{{ $formId }}" @endif class="m-0 flex flex-col">
                    @csrf
                    @if (in_array($defaultMethod, ['PUT', 'PATCH', 'DELETE'], true))
                        @method($defaultMethod)
                    @endif

                    <div class="modal-body items-center">
                        <div class="{{ $defaultModal }} w-28 h-28 rounded-full {{ $defaultHaloClass }} flex items-center justify-center my-8 shrink-0 mx-auto">
                            <x-icon name="{{ $defaultIcon }}" size="52" class="animate-handdrawn" />
                        </div>
                        
                        <h3 class="text-base font-bold {{ $defaultTitleColor }} mb-1" id="{{ $id }}Title">{{ $title }}</h3>

                        <div class="text-sm text-gray-600 leading-relaxed max-w-sm w-full">
                            <p class="text-sm text-gray-600 mb-2.5" id="{{ $messageId }}">-</p>

                            <div class="p-3.5 my-8 mx-6 bg-slate-50 border border-slate-200/90 rounded-xl text-center shadow-xs">
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-mono font-bold bg-slate-200/80 text-slate-700 rounded-md mb-1.5">
                                    <span id="{{ $badgeId }}">-</span>
                                </span>
                                <div class="text-sm font-bold text-slate-900 leading-snug" id="{{ $itemTitleId }}">-</div>
                            </div>

                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium {{ $defaultMessageClass }} text-left w-full">
                                <x-icon name="alert-circle" size="14" class="shrink-0" />
                                <span @if($hintId) id="{{ $hintId }}" @endif>{{ $defaultMessage }}</span>
                            </div>
                            {{ $hiddenInputs ?? '' }}
                        </div>
                    </div>

                    <div class="modal-footer grid grid-cols-2 gap-3 p-4 bg-slate-50/80 border-t border-slate-100">
                        <button type="button" class="btn-secondary w-full justify-center font-medium" id="{{ $cancelBtnId }}">Batal</button>
                        <button type="submit" class="{{ $defaultBtnClass }} w-full justify-center font-medium" id="{{ $confirmBtnId }}">{{ $defaultConfirmText }}</button>
                    </div>
                </form>
            </div>
        </div>
        @break

    @default
        {{-- ============================================================
             TAMPILAN MODAL STANDAR (Formulir, Tambah, Ubah, Detail, dll)
             ============================================================ --}}
        <div class="modal-backdrop hidden" id="{{ $id }}" role="dialog" aria-modal="true"
            aria-labelledby="{{ $id }}Title">
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
                    <form method="POST" action="{{ $action }}" @if ($formId) id="{{ $formId }}" @endif>
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
