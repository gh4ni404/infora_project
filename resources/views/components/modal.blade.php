@props([
    'id',
    'title',
    'subtitle' => null,
    'size' => 'default',
    'closeLabel' => 'Tutup',
    'method' => null,
    'action' => null,
    'formId' => null,
    'danger' => false,
])

@php
    $sizeClass = $size === 'lg' ? 'modal-lg' : '';
    $method = strtoupper($method ?? '');
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
                    <p class="modal-subtitle">{{ $subtitle }}</p>
                @endif
            </div>
            <button type="button" class="modal-close-btn" id="btnClose{{ Str::studly($id) }}" aria-label="{{ $closeLabel }}">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        @if ($action || $method)
            <form method="POST" action="{{ $action ?? '' }}" @if ($formId) id="{{ $formId }}" @endif>
                @csrf
                @if (in_array($method, ['PUT', 'PATCH', 'DELETE'], true))
                    @method($method)
                @endif

                <div class="modal-body">
                    {{ $slot }}
                </div>

                <div class="modal-footer">
                    {{ $footer ?? '' }}
                </div>
            </form>
        @else
            <div class="modal-body">
                {{ $slot }}
            </div>

            <div class="modal-footer">
                {{ $footer ?? '' }}
            </div>
        @endif
    </div>
</div>
