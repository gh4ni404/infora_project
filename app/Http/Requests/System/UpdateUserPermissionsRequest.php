<?php

namespace App\Http\Requests\System;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPermissionsRequest extends FormRequest
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
            'permissions' => 'Daftar Hak Akses',
        ];
    }
}
