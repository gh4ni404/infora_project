@props([
    'name',
    'label' => null,
    'id' => null,
    'value' => null,
    'rows' => 3,
    'hint' => null,
    'required' => false,
    'groupClass' => '',
])

@php
    $errors = $errors ?? view()->shared('errors', new \Illuminate\Support\ViewErrorBag);
    $textareaId = $id ?? $name;
    $hasError = $errors->has($name);
    $textValue = old($name, $value ?? ($slot->isNotEmpty() ? $slot : ''));
@endphp

<div class="form-group {{ $groupClass }}">
    @if ($label)
        <label for="{{ $textareaId }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $textareaId }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'form-textarea' . ($hasError ? ' border-danger' : '')]) }}
    >{{ $textValue }}</textarea>

    @if ($hint)
        <div class="form-hint">{{ $hint }}</div>
    @endif

    @error($name)
        <div class="form-error">{{ $message }}</div>
    @enderror
</div>
