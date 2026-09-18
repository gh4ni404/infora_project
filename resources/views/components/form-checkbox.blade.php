@props([
    'name',
    'label',
    'id' => null,
    'value' => '1',
    'checked' => false,
    'hint' => null,
    'groupClass' => '',
])

@php
    $errors = $errors ?? view()->shared('errors', new \Illuminate\Support\ViewErrorBag);
    $checkboxId = $id ?? $name;
    $hasError = $errors->has($name);
    $isChecked = old($name) !== null ? (bool) old($name) : (bool) $checked;
@endphp

<div class="form-group {{ $groupClass }}">
    <label class="form-check" for="{{ $checkboxId }}">
        <input
            type="checkbox"
            name="{{ $name }}"
            value="{{ $value }}"
            id="{{ $checkboxId }}"
            @checked($isChecked)
            {{ $attributes->merge(['class' => 'form-check-input' . ($hasError ? ' border-danger' : '')]) }}
        >
        <span class="form-check-label">{{ $label }}</span>
    </label>

    @if ($hint)
        <div class="form-hint">{{ $hint }}</div>
    @endif

    @error($name)
        <div class="form-error">{{ $message }}</div>
    @enderror
</div>
