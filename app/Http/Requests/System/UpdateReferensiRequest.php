<?php

namespace App\Http\Requests\System;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReferensiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'kode' => filled($this->input('kode')) ? trim((string) $this->input('kode')) : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'kode' => ['required', 'string', 'max:50'],
            'is_active' => ['boolean'],
            'is_madrasah' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom message for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama referensi wajib diisi.',
            'nama.max' => 'Nama referensi maksimal terdiri dari 150 karakter.',
            'kode.required' => 'Kode referensi wajib diisi.',
            'kode.max' => 'Kode referensi maksimal terdiri dari 50 karakter.',
        ];
    }
}
