<?php

namespace App\Http\Requests\Wilayah;

use App\Models\Provinsi;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProvinsiRequest extends FormRequest
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
            'status' => $this->boolean('status'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $provinsi = $this->route('provinsi');
        $id = $provinsi instanceof Provinsi ? $provinsi->id : $provinsi;

        return [
            'kode' => [
                'required',
                'string',
                'size:2',
                'regex:/^[0-9]{2}$/',
                Rule::unique('provinsi', 'kode')->ignore($id),
            ],
            'nama' => ['required', 'string', 'max:100'],
            'status' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'kode' => 'Kode Wilayah',
            'nama' => 'Nama Provinsi',
            'status' => 'Status Aktif',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kode.required' => 'Kode wilayah provinsi wajib diisi.',
            'kode.size' => 'Kode wilayah provinsi harus tepat 2 digit angka.',
            'kode.regex' => 'Kode wilayah harus berupa 2 digit angka (contoh: 73).',
            'kode.unique' => 'Kode wilayah provinsi ini sudah digunakan oleh provinsi lain.',
            'nama.required' => 'Nama provinsi wajib diisi.',
            'nama.max' => 'Nama provinsi tidak boleh melebihi 100 karakter.',
        ];
    }
}
