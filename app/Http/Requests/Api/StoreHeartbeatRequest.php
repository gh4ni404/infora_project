<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreHeartbeatRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'sent_at' => ['nullable', 'string'],
            'school' => ['nullable', 'array'],
            'school.name' => ['nullable', 'string', 'max:255'],
            'school.npsn' => ['nullable', 'string', 'max:30'],
            'runtime' => ['nullable', 'array'],
            'runtime.app_version' => ['nullable', 'string', 'max:50'],
            'runtime.maintenance_mode' => ['nullable', 'boolean'],
            'runtime.last_migration' => ['nullable', 'string', 'max:255'],
            'database' => ['required', 'array'],
            'database.status' => ['required', 'string', 'in:up,down'],
            'database.latency_ms' => ['nullable', 'integer', 'min:0'],
            'database.error' => ['nullable', 'string', 'max:2000'],
            'activity' => ['nullable', 'array'],
            'activity.active_users_count' => ['nullable', 'integer', 'min:0'],
            'policy' => ['nullable', 'array'],
            'policy.applied_version' => ['nullable', 'integer', 'min:0'],
            'telemetry' => ['nullable', 'array'],
            'telemetry.modules_count' => ['nullable', 'integer', 'min:0'],
            'telemetry.menus_count' => ['nullable', 'integer', 'min:0'],
            'telemetry.sub_menus_count' => ['nullable', 'integer', 'min:0'],
            'telemetry.users_total' => ['nullable', 'integer', 'min:0'],
            'telemetry.users_breakdown' => ['nullable', 'array'],
            'telemetry.dev_summary' => ['nullable', 'array'],
            'telemetry.operational_summary' => ['nullable', 'array'],
            'telemetry.modules_tree' => ['nullable', 'array'],
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
            'database.required' => 'Status database wajib disertakan pada heartbeat.',
            'database.status.required' => 'Nilai status database wajib diisi.',
            'database.status.in' => 'Status database harus bernilai up atau down.',
        ];
    }
}
