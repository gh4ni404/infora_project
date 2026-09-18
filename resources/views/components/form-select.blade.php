@props([
    'name',
    'label' => null,
    'id' => null,
    'selected' => null,
    'placeholder' => null,
    'hint' => null,
    'required' => false,
    'groupClass' => '',
])

@php
    $errors = $errors ?? view()->shared('errors', new \Illuminate\Support\ViewErrorBag);
    $selectId = $id ?? $name;
    $hasError = $errors->has($name);
    $selectedValue = old($name, $selected);
@endphp

<div class="form-group {{ $groupClass }}">
    @if ($label)
        <label for="{{ $selectId }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $selectId }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'form-select' . ($hasError ? ' border-danger' : '')]) }}
    >
        @if ($placeholder)
            <option value="" @selected(blank($selectedValue))>{{ $placeholder }}</option>
        @endif

        {{ $slot }}
    </select>

    @if ($hint)
        <div class="form-hint">{{ $hint }}</div>
    @endif

    @error($name)
        <div class="form-error">{{ $message }}</div>
    @enderror
</div>
