<?php

namespace App\Http\Requests\FaceRecognition;

use App\Support\ImageUploader;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAbsenMuridRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berwenang melakukan permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalisasi data masukan sebelum validasi dijalankan.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('school_id') && $this->user()?->school_id) {
            $this->merge([
                'school_id' => $this->user()->school_id,
            ]);
        }

        if (! $this->filled('mode')) {
            $this->merge([
                'mode' => 'masuk',
            ]);
        }
    }

    /**
     * Aturan validasi pencatatan absensi wajah murid.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $schoolId = $this->input('school_id');
        $muridId = $this->input('murid_id');

        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'murid_id' => [
                'required',
                'integer',
                Rule::exists('murids', 'id')
                    ->where(fn ($query) => $query->where('school_id', $schoolId)->where('is_active', true)->whereNull('deleted_at')),
            ],
            'face_profile_id' => [
                'nullable',
                'integer',
                Rule::exists('murid_face_profiles', 'id')
                    ->where(fn ($query) => $query->where('murid_id', $muridId)->where('school_id', $schoolId)),
            ],
            'mode' => ['required', 'string', 'in:masuk,pulang'],
            'match_score' => ['nullable', 'numeric', 'between:0,1'],
            'jam_batas_masuk' => ['nullable', 'date_format:H:i'],
            'foto_base64' => ['nullable', 'string', 'regex:/^data:image\/(png|jpeg|jpg|webp);base64,/i'],
        ];
    }

    /**
     * Validasi ukuran dan integritas snapshot foto opsional.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('foto_base64') || ! $this->filled('foto_base64')) {
                    return;
                }

                $base64 = (string) $this->input('foto_base64');
                $extension = ImageUploader::extractExtension($base64);

                if (! in_array($extension, ['png', 'jpg', 'webp'], true)) {
                    $validator->errors()->add('foto_base64', 'Format foto snapshot tidak valid.');

                    return;
                }

                $payload = preg_replace('/^data:image\/[a-zA-Z0-9\+\-]+;base64,/', '', $base64);
                $binary = base64_decode((string) $payload, true);

                if ($binary === false || strlen($binary) === 0 || strlen($binary) > ImageUploader::DEFAULT_MAX_BYTES) {
                    $validator->errors()->add('foto_base64', 'Ukuran atau isi snapshot absensi tidak valid (maksimal 1MB).');
                }
            },
        ];
    }

    /**
     * Pesan kesalahan validasi berbahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'school_id.required' => 'Unit sekolah wajib ditentukan.',
            'school_id.exists' => 'Unit sekolah tidak ditemukan.',
            'murid_id.required' => 'Data murid wajib tersedia.',
            'murid_id.exists' => 'Murid aktif tidak ditemukan pada unit sekolah ini.',
            'face_profile_id.exists' => 'Profil wajah tidak cocok dengan murid yang dipilih.',
            'mode.in' => 'Mode absensi harus masuk atau pulang.',
            'match_score.between' => 'Skor kemiripan wajah harus antara 0 dan 1.',
            'jam_batas_masuk.date_format' => 'Format jam batas masuk harus HH:MM (contoh 07:15).',
            'foto_base64.regex' => 'Format snapshot wajah harus PNG, JPG, atau WebP.',
        ];
    }
}
