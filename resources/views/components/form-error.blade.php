@props([
    'name' => null,
    'message' => null,
])

@php
    $errors = $errors ?? view()->shared('errors', new \Illuminate\Support\ViewErrorBag);
    $errorMessage = $message ?? ($name ? $errors->first($name) : null);
@endphp

@if ($errorMessage)
    <div {{ $attributes->merge(['class' => 'form-error']) }}>
        <x-icon name="alert-circle" class="w-4 h-4 shrink-0 text-danger" />
        <span>{{ $errorMessage }}</span>
    </div>
@endif
