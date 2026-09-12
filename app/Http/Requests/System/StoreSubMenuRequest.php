<?php

namespace App\Http\Requests\System;

use App\Models\SubMenu;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubMenuRequest extends FormRequest
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
        $menuId = $this->filled('menu_id') ? (int) $this->input('menu_id') : null;

        $prefix = trim((string) $this->input('route_prefix', ''));
        $suffix = trim((string) $this->input('route_suffix', ''));

        if ($this->has('route_prefix') || $this->has('route_suffix')) {
            if ($prefix !== '' && $suffix !== '') {
                $routeName = "{$prefix}.{$suffix}";
            } elseif ($prefix !== '') {
                $routeName = $prefix;
            } elseif ($suffix !== '') {
                $routeName = $suffix;
            } else {
                $routeName = null;
            }
        } else {
            $routeName = $this->input('route_name');
        }

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'order' => $this->filled('order') ? (int) $this->input('order') : SubMenu::nextOrder($menuId),
            'route_name' => $routeName ? trim($routeName) : null,
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
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
            'name' => ['required', 'string', 'max:255'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'route_prefix' => ['nullable', 'string', 'max:100'],
            'route_suffix' => ['nullable', 'string', 'max:150'],
            'order' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
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
            'menu_id' => 'Induk Menu',
            'name' => 'Nama Sub-Menu',
            'route_name' => 'Nama Rute (Route)',
            'order' => 'Urutan',
            'is_active' => 'Status Aktif',
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
            'menu_id.required' => 'Induk menu wajib dipilih.',
            'menu_id.exists' => 'Menu yang dipilih tidak ditemukan dalam sistem.',
            'name.required' => 'Nama sub-menu wajib diisi.',
            'name.max' => 'Nama sub-menu tidak boleh melebihi 255 karakter.',
            'order.min' => 'Urutan sub-menu minimal bernilai 1.',
        ];
    }
}
