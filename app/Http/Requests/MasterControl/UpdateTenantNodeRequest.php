<?php

namespace App\Http\Requests\MasterControl;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantNodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->isSuperAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'node_code' => ['required', 'string', 'max:64', 'alpha_dash'],
            'school_name' => ['required', 'string', 'max:255'],
            'npsn' => ['nullable', 'string', 'max:30'],
            'base_url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
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
            'node_code.required' => 'Kode node sekolah wajib diisi.',
            'node_code.alpha_dash' => 'Kode node hanya boleh berisi huruf, angka, tanda hubung, atau garis bawah.',
            'school_name.required' => 'Nama sekolah wajib diisi.',
            'base_url.url' => 'URL aplikasi sekolah harus berupa alamat HTTP/HTTPS yang valid.',
        ];
    }
}
