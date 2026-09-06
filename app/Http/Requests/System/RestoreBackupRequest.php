<?php

namespace App\Http\Requests\System;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RestoreBackupRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'filename' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9_\-]+\.(sql|zip)$/'],
            'backup_file' => ['nullable', 'file', 'max:204800', 'mimes:sql,txt,zip'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (blank($this->input('filename')) && ! $this->hasFile('backup_file')) {
                $validator->errors()->add('backup_source', 'Silakan pilih berkas cadangan dari daftar atau unggah berkas .zip / .sql baru.');
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'filename' => 'nama berkas cadangan',
            'backup_file' => 'berkas cadangan unggahan',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'filename.regex' => 'Format nama berkas cadangan tidak valid (harus .sql atau .zip).',
            'backup_file.file' => 'Berkas unggahan harus berupa berkas valid.',
            'backup_file.max' => 'Ukuran berkas cadangan maksimal adalah 200 MB.',
            'backup_file.mimes' => 'Berkas cadangan harus berekstensi .zip, .sql, atau .txt.',
        ];
    }
}
