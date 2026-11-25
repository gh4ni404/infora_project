<?php

namespace App\Http\Requests\MonitoringSekolah;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantMenuPolicyRequest extends FormRequest
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
            'items' => ['nullable', 'array'],
            'items.*.target_type' => ['required', 'string', 'in:menu,sub_menu'],
            'items.*.sync_key' => ['required', 'string', 'max:255'],
            'items.*.label_snapshot' => ['required', 'string', 'max:255'],
            'items.*.is_allowed' => ['nullable', 'boolean'],
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
            'items.array' => 'Format daftar kebijakan menu tidak valid.',
            'items.*.target_type.in' => 'Tipe item navigasi harus bernilai menu atau sub_menu.',
            'items.*.sync_key.required' => 'Kunci sinkronisasi item menu tidak boleh kosong.',
        ];
    }
}
