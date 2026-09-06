<?php

namespace App\Http\Requests\System;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTemplatePermissionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role_name' => ['nullable', 'string', 'max:255'],
            'role_category' => ['nullable', 'string', 'in:guru,staf,siswa'],
            'permissions' => ['nullable', 'array'],
            'permissions.*.menu_id' => ['nullable', 'integer', 'exists:menus,id'],
            'permissions.*.sub_menu_id' => ['nullable', 'integer', 'exists:sub_menus,id'],
            'permissions.*.can_view' => ['nullable'],
            'permissions.*.can_create' => ['nullable'],
            'permissions.*.can_edit' => ['nullable'],
            'permissions.*.can_delete' => ['nullable'],
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
            'role_name' => 'Nama Peran Template',
            'role_category' => 'Kategori Peran',
            'permissions' => 'Daftar Hak Akses Template',
        ];
    }
}
