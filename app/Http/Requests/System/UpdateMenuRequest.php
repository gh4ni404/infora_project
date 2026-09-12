<?php

namespace App\Http\Requests\System;

use App\Models\Menu;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuRequest extends FormRequest
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
        $menu = $this->route('menu');
        $newModuleId = $this->filled('module_id') ? (int) $this->input('module_id') : null;

        if ($menu instanceof Menu && $newModuleId && $newModuleId !== $menu->module_id) {
            $fallbackOrder = Menu::nextOrder($newModuleId);
        } else {
            $fallbackOrder = $menu instanceof Menu ? $menu->order : 1;
        }

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
            'order' => $this->filled('order') ? (int) $this->input('order') : $fallbackOrder,
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
            'module_id' => ['required', 'integer', 'exists:modules,id'],
            'name' => ['required', 'string', 'max:255'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
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
            'module_id' => 'Induk Modul',
            'name' => 'Nama Menu',
            'route_name' => 'Nama Rute (Route)',
            'icon' => 'Ikon Menu',
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
            'module_id.required' => 'Induk modul wajib dipilih.',
            'module_id.exists' => 'Modul yang dipilih tidak ditemukan dalam sistem.',
            'name.required' => 'Nama menu wajib diisi.',
            'name.max' => 'Nama menu tidak boleh melebihi 255 karakter.',
            'order.min' => 'Urutan menu minimal bernilai 1.',
        ];
    }
}
