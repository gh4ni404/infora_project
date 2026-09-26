@props([
    'name',
    'label' => null,
    'id' => null,
    'selected' => null,
    'placeholder' => null,
    'hint' => null,
    'required' => false,
    'groupClass' => '',
    'options' => [],
    'optionAttributes' => null,
])

@php
    $errors = $errors ?? view()->shared('errors', new \Illuminate\Support\ViewErrorBag);
    $selectId = $id ?? $name;
    $hasError = $errors->has($name);
    $selectedValue = old($name, $selected);
    $isCallableAttributes = is_callable($optionAttributes);
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

        @if (!empty($options))
            @foreach ($options as $value => $label)
                @php
                    if (is_object($label)) {
                        $val = $label->id ?? $value;
                        $text = $label->name ?? $label->label;
                    } elseif (is_array($label)) {
                        $val = $label['id'] ?? $value;
                        $text = $label['name'] ?? $label['label'];
                    } else {
                        $val = $value;
                        $text = $label;
                    }

                    $optAttrs = $isCallableAttributes
                        ? $optionAttributes($label, $val)
                        : ($optionAttributes[$val] ?? ($label['attributes'] ?? null));
                @endphp
                <option
                    value="{{ $val }}"
                    @selected(!blank($selectedValue) && (string) $val === (string) $selectedValue)
                    @if ($optAttrs) {{ new \Illuminate\View\ComponentAttributeBag($optAttrs) }} @endif
                >
                    {{ $text }}
                </option>
            @endforeach
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
