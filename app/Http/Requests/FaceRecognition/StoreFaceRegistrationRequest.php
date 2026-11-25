<?php

namespace App\Http\Requests\FaceRecognition;

use App\Http\Requests\Concerns\ResolvesSingleSchoolInput;
use App\Models\MuridFaceProfile;
use App\Support\ImageUploader;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreFaceRegistrationRequest extends FormRequest
{
    use ResolvesSingleSchoolInput;

    /**
     * Tentukan apakah pengguna berwenang melakukan permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalisasi data sebelum proses validasi berjalan.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeSingleSchoolId();

        $rawDescriptor = $this->input('face_descriptor');
        if (is_string($rawDescriptor)) {
            $decoded = json_decode($rawDescriptor, true);
            $this->merge([
                'face_descriptor' => is_array($decoded) ? $decoded : null,
            ]);
        }

        $this->merge([
            'sync_murid_foto' => $this->boolean('sync_murid_foto', true),
        ]);
    }

    /**
     * Aturan validasi pendaftaran sampel wajah murid.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $schoolId = $this->input('school_id');

        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'murid_id' => [
                'required',
                'integer',
                Rule::exists('murids', 'id')
                    ->where(fn ($query) => $query->where('school_id', $schoolId)->whereNull('deleted_at')),
            ],
            'label' => ['required', 'string', 'max:60'],
            'foto_base64' => ['required', 'string', 'regex:/^data:image\/(png|jpeg|jpg|webp);base64,/i'],
            'face_descriptor' => ['required', 'array', 'size:128'],
            'face_descriptor.*' => ['required', 'numeric', 'between:-10,10'],
            'confidence' => ['nullable', 'numeric', 'between:0,1'],
            'sync_murid_foto' => ['boolean'],
        ];
    }

    /**
     * Validasi lanjutan terhadap payload gambar Base64 dan kuota maksimal sampel per murid.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $validator->errors()->has('foto_base64')) {
                    $base64 = (string) $this->input('foto_base64');
                    $extension = ImageUploader::extractExtension($base64);

                    if (! in_array($extension, ['png', 'jpg', 'webp'], true)) {
                        $validator->errors()->add('foto_base64', 'Format foto wajah harus berupa PNG, JPG, atau WebP yang valid.');
                    } else {
                        $payload = preg_replace('/^data:image\/[a-zA-Z0-9\+\-]+;base64,/', '', $base64);
                        $binary = base64_decode((string) $payload, true);

                        if ($binary === false || strlen($binary) === 0) {
                            $validator->errors()->add('foto_base64', 'Data gambar wajah tidak dapat diproses.');
                        } elseif (strlen($binary) > ImageUploader::DEFAULT_MAX_BYTES) {
                            $validator->errors()->add('foto_base64', 'Ukuran gambar wajah melebihi batas maksimal 1MB.');
                        }
                    }
                }

                if (! $validator->errors()->hasAny(['school_id', 'murid_id'])) {
                    $muridId = $this->integer('murid_id');
                    $existingCount = MuridFaceProfile::query()
                        ->where('murid_id', $muridId)
                        ->count();

                    if ($existingCount >= MuridFaceProfile::MAX_SAMPLES_PER_MURID) {
                        $validator->errors()->add(
                            'murid_id',
                            'Murid ini sudah memiliki maksimal '.MuridFaceProfile::MAX_SAMPLES_PER_MURID.' sampel wajah terdaftar. Hapus salah satu sampel lama terlebih dahulu jika ingin menambah yang baru.'
                        );
                    }
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
            'school_id.required' => 'Unit sekolah wajib dipilih.',
            'school_id.exists' => 'Unit sekolah yang dipilih tidak terdaftar.',
            'murid_id.required' => 'Peserta didik wajib dipilih.',
            'murid_id.exists' => 'Peserta didik tidak ditemukan atau bukan milik unit sekolah ini.',
            'label.required' => 'Label pose wajah wajib diisi.',
            'label.max' => 'Label pose wajah maksimal terdiri atas 60 karakter.',
            'foto_base64.required' => 'Foto wajah wajib diambil melalui kamera atau diunggah terlebih dahulu.',
            'foto_base64.regex' => 'Format foto wajah harus berupa PNG, JPG, atau WebP yang valid.',
            'face_descriptor.required' => 'Data vektor wajah (face descriptor) tidak ditemukan. Pastikan wajah terdeteksi dengan jelas sebelum menyimpan.',
            'face_descriptor.array' => 'Format data vektor wajah tidak valid.',
            'face_descriptor.size' => 'Vektor wajah harus terdiri tepat dari 128 dimensi fitur.',
            'face_descriptor.*.numeric' => 'Nilai fitur vektor wajah harus berupa angka.',
            'face_descriptor.*.between' => 'Nilai fitur vektor wajah berada di luar rentang normal.',
            'confidence.numeric' => 'Skor kepercayaan deteksi wajah harus berupa angka.',
            'confidence.between' => 'Skor kepercayaan deteksi wajah harus berada antara 0 dan 1.',
        ];
    }
}
