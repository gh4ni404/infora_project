<?php

namespace App\Http\Requests\KonfigurasiSekolah;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTahunAjaranRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki otorisasi untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Menyiapkan data sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'tahun' => trim((string) $this->input('tahun')),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Aturan validasi yang diterapkan pada request ini.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'tahun' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'is_active' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Pesan kustom untuk kegagalan validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'school_id.required' => 'Unit sekolah wajib dipilih.',
            'school_id.exists' => 'Data sekolah yang dipilih tidak valid.',
            'tahun.required' => 'Tahun ajaran wajib diisi.',
            'tahun.regex' => 'Format tahun ajaran harus mengikuti pola YYYY/YYYY (contoh: 2026/2027).',
            'keterangan.max' => 'Keterangan tidak boleh lebih dari 500 karakter.',
        ];
    }
}
