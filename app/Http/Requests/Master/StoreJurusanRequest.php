<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreJurusanRequest extends FormRequest
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
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'singkatan' => $this->filled('singkatan') ? $this->input('singkatan') : $this->input('kode'),
        ]);
    }

    /**
     * Aturan validasi yang berlaku untuk request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'kode' => ['required', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:150'],
            'singkatan' => ['nullable', 'string', 'max:20'],
            'jenjang' => ['required', 'string', 'in:SMK,SMA'],
            'bidang_keahlian' => ['nullable', 'string', 'max:100'],
            'program_keahlian' => ['nullable', 'string', 'max:100'],
            'kepala_jurusan' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
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
            'school_id.exists' => 'Unit sekolah yang dipilih tidak valid dalam sistem.',
            'kode.required' => 'Kode jurusan wajib diisi.',
            'kode.max' => 'Kode jurusan maksimal terdiri atas 20 karakter.',
            'nama.required' => 'Nama jurusan wajib diisi.',
            'nama.max' => 'Nama jurusan maksimal terdiri atas 150 karakter.',
            'jenjang.required' => 'Jenjang pendidikan jurusan wajib dipilih.',
            'jenjang.in' => 'Jenjang harus berupa SMK atau SMA.',
            'deskripsi.max' => 'Deskripsi jurusan maksimal terdiri atas 1000 karakter.',
        ];
    }
}
