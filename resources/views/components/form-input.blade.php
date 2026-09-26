@props([
    'name',
    'label' => null,
    'id' => null,
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'required' => false,
    'groupClass' => '',
])

@php
    $errors = $errors ?? view()->shared('errors', new \Illuminate\Support\ViewErrorBag);
    $inputId = $id ?? $name;
    $hasError = $errors->has($name);
    $inputValue = old($name, $value);
@endphp

@if ($type === 'hidden')
    <input
        type="hidden"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ $inputValue }}"
        {{ $attributes }}
    >
@else
    <div class="form-group {{ $groupClass }}">
        @if ($label)
            <label for="{{ $inputId }}" class="form-label">
                {{ $label }}
                @if ($required)
                    <span class="text-danger">*</span>
                @endif
            </label>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $inputId }}"
            value="{{ $inputValue }}"
            @if ($required) required @endif
            {{ $attributes->merge(['class' => 'form-input' . ($hasError ? ' border-danger' : '')]) }}
        >

        @if ($hint)
            <div class="form-hint">{{ $hint }}</div>
        @endif

        @error($name)
            <div class="form-error">{{ $message }}</div>
        @enderror

        {{ $slot ?? '' }}
    </div>
@endif
