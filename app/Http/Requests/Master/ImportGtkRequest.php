<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportGtkRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki otorisasi untuk melakukan permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Persiapan data sebelum proses validasi.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('school_id') && $this->user()?->school_id) {
            $this->merge([
                'school_id' => $this->user()->school_id,
            ]);
        }
    }

    /**
     * Aturan validasi yang berlaku untuk request impor berkas spreadsheet GTK.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'school_id' => ['required', 'integer', 'exists:schools,id'],
        ];
    }

    /**
     * Pesan kustom untuk kegagalan validasi berkas impor.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Silakan pilih berkas spreadsheet Excel atau CSV yang akan diimpor.',
            'file.file' => 'Berkas yang diunggah tidak valid.',
            'file.mimes' => 'Format berkas harus berupa Excel (.xlsx, .xls) atau CSV (.csv).',
            'file.max' => 'Ukuran berkas spreadsheet maksimal adalah 5 MB.',
            'school_id.required' => 'Unit sekolah tujuan impor wajib ditentukan.',
            'school_id.exists' => 'Unit sekolah yang dipilih tidak terdaftar di sistem.',
        ];
    }
}
