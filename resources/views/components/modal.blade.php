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
    'withForm' => true, // true = modal membungkus form sendiri, false = tanpa form sendiri (submit form luar via targetForm/formId)
    'cancelBtnId' => null,
    'confirmBtnId' => null,
    'hintId' => null,
    'templateUrl' => null, // dipakai di case import
    'guidelines' => [], // dipakai di case import
    'schoolId' => null, // dipakai di case import
    'enctype' => null, // dipakai di case standar
])

@php
    $variant = strtolower($name ?? 'default');

    $sizeClass = match ($size) {
        'sm' => 'modal-sm',
        'lg' => 'modal-lg',
        default => '',
    };

    // Untuk variant delete/confirm, default ke modal-sm jika tidak di-override
    $confirmSizeClass = match ($size) {
        'lg' => 'modal-lg',
        'xl' => 'modal-xl',
        default => 'modal-sm',
    };
    $isLargeConfirm = in_array($size, ['lg', 'xl']);
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
    $defaultMessage = $isDelete
        ? 'Tindakan ini tidak dapat dibatalkan.'
        : 'Pastikan data dan tindakan yang Anda ambil sudah benar.';
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
            <div class="modal-dialog {{ $confirmSizeClass }} relative overflow-hidden text-center">
                @if ($withForm)
                    <form method="POST" action="{{ $action }}" @if ($formId) id="{{ $formId }}" @endif class="m-0 flex flex-col">
                        @csrf
                        @if (in_array($defaultMethod, ['PUT', 'PATCH', 'DELETE'], true))
                            @method($defaultMethod)
                        @endif
                @endif
                <div class="modal-body items-center">
                    <div class="{{ $defaultModal }} w-28 h-28 rounded-full {{ $defaultHaloClass }} flex items-center justify-center my-8 shrink-0 mx-auto">
                        <x-icon name="{{ $defaultIcon }}" size="52" class="animate-handdrawn" />
                    </div>

                    <h3 class="text-base font-bold {{ $defaultTitleColor }} mb-1" id="{{ $id }}Title">{{ $title }}</h3>

                    <div class="text-sm text-gray-600 leading-relaxed {{ $isLargeConfirm ? '' : 'max-w-sm' }} w-full">
                        <p class="text-sm text-gray-600 mb-2.5" id="{{ $messageId }}">-</p>

                        <div class="p-3.5 my-8 mx-6 bg-slate-50 border border-slate-200/90 rounded-xl text-center shadow-xs">
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-mono font-bold bg-slate-200/80 text-slate-700 rounded-md mb-1.5">
                                <span id="{{ $badgeId }}">-</span>
                            </span>
                            <div class="text-sm font-bold text-slate-900 leading-snug" id="{{ $itemTitleId }}">-</div>
                        </div>

                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium {{ $defaultMessageClass }} text-left w-full">
                            <x-icon name="alert-circle" size="14" class="shrink-0" />
                            <span @if ($hintId) id="{{ $hintId }}" @endif>{{ $defaultMessage }}</span>
                        </div>
                        {{ $hiddenInputs ?? '' }}
                    </div>
                </div>

                <div class="modal-footer grid grid-cols-2 gap-3 p-4 bg-slate-50/80 border-t border-slate-100">
                    <button type="button" class="btn-secondary w-full justify-center font-medium" id="{{ $cancelBtnId }}">Batal</button>
                    <button type="submit" @if (!$withForm && $formId) form="{{ $formId }}" @endif class="{{ $defaultBtnClass }} w-full justify-center font-medium" id="{{ $confirmBtnId }}">{{ $defaultConfirmText }}</button>
                </div>
                @if ($withForm)
                    </form>
                @endif
            </div>
        </div>
    @break

    @case('import')
        {{-- ============================================================
             TAMPILAN MODAL IMPORT SPREADSHEET (EXCEL/CSV)
             ============================================================ --}}
        <div class="modal-backdrop hidden" id="{{ $id }}" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}Title">
            <div class="modal-dialog {{ $sizeClass }}">
                @if ($title || $subtitle)
                    <div class="modal-header">
                        <div>
                            <h3 class="modal-title" id="{{ $id }}Title">{{ $title }}</h3>
                            @if ($subtitle)
                                <p class="modal-subtitle" id="{{ $id }}Subtitle">{{ $subtitle }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ $action }}" @if ($formId) id="{{ $formId }}" @endif enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        @if ($schoolId)
                            <x-form-input type="hidden" name="school_id" :value="$schoolId" />
                        @endif

                        @if ($templateUrl)
                            <div class="template-download-card">
                                <div class="flex items-center gap-3">
                                    <div class="template-download-icon">
                                        <x-icon name="table" size="20" />
                                    </div>
                                    <div>
                                        <div class="font-semibold text-sm">Unduh Template Berkas</div>
                                        <div class="text-xs text-muted">Format standar berkas Excel dilengkapi baris contoh data dan panduan.</div>
                                    </div>
                                </div>
                                <a href="{{ $templateUrl }}" class="btn-secondary whitespace-nowrap" data-no-progress download>
                                    <x-icon name="download" size="14" />
                                    <span>Format Template</span>
                                </a>
                            </div>
                        @endif

                        <x-file-upload
                            name="file"
                            id="{{ $id }}_file"
                            label="Pilih Berkas Excel / CSV"
                            hint="Mendukung berkas berekstensi .xlsx, .xls, atau .csv (Maksimal 5 MB)"
                            required
                        />

                        @if (!empty($guidelines))
                            <div class="form-section-divider my-3"></div>
                            <div class="guideline-box">
                                <div class="guideline-header">
                                    <x-icon name="alert-circle" size="15" class="text-brand shrink-0" />
                                    <span>Panduan Pengisian:</span>
                                </div>
                                <ul class="guideline-list">
                                    @foreach ($guidelines as $guideline)
                                        <li class="guideline-item">
                                            <span class="guideline-bullet"></span>
                                            <div>{!! $guideline !!}</div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" id="{{ $cancelBtnId }}">Batal</button>
                        <button type="submit" class="btn-primary" id="{{ $confirmBtnId }}">
                            <x-icon name="upload" size="16" />
                            <span>Unggah & Impor</span>
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
        <div class="modal-backdrop hidden" id="{{ $id }}" role="dialog" aria-modal="true"
            aria-labelledby="{{ $id }}Title">
            <div class="modal-dialog {{ $sizeClass }}">
                @if ($title || $subtitle)
                    <div class="modal-header">
                        <div>
                            <h3 class="modal-title" id="{{ $id }}Title">{{ $title }}</h3>
                            @if ($subtitle)
                                <p class="modal-subtitle" id="{{ $id }}Subtitle">{{ $subtitle }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($action !== null || $method)
                    <form method="POST" action="{{ $action }}" @if ($formId) id="{{ $formId }}" @endif @if ($enctype) enctype="{{ $enctype }}" @endif>
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
