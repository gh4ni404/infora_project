<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolRequest extends FormRequest
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
        $this->merge([
            'is_active' => $this->boolean('is_active'),
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
            'name' => ['required', 'string', 'max:255'],
            'npsn' => ['required', 'string', 'size:8', 'regex:/^\d{8}$/'],
            'nss' => ['nullable', 'string', 'max:20'],
            'school_type' => ['required', 'string', 'in:SMA,SMK'],
            'status' => ['required', 'string', 'in:Negeri,Swasta'],
            'accreditation' => ['nullable', 'string', 'in:A,B,C,Belum'],
            'address' => ['nullable', 'string', 'max:500'],
            'village' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'fax' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'principal_name' => ['nullable', 'string', 'max:255'],
            'principal_nip' => ['nullable', 'string', 'max:30'],
            'foundation_name' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'string'],
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
            'name' => 'Nama Sekolah',
            'npsn' => 'NPSN',
            'nss' => 'NSS',
            'school_type' => 'Jenis Sekolah',
            'status' => 'Status Sekolah',
            'accreditation' => 'Akreditasi',
            'address' => 'Alamat',
            'village' => 'Kelurahan/Desa',
            'district' => 'Kecamatan',
            'city' => 'Kabupaten/Kota',
            'province' => 'Provinsi',
            'postal_code' => 'Kode Pos',
            'phone' => 'Telepon',
            'fax' => 'Fax',
            'email' => 'Email',
            'website' => 'Website',
            'principal_name' => 'Nama Kepala Sekolah',
            'principal_nip' => 'NIP Kepala Sekolah',
            'foundation_name' => 'Nama Yayasan',
            'logo' => 'Logo Sekolah',
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
            'name.required' => 'Nama sekolah wajib diisi.',
            'name.max' => 'Nama sekolah tidak boleh melebihi 255 karakter.',
            'npsn.required' => 'NPSN wajib diisi.',
            'npsn.size' => 'NPSN harus terdiri dari tepat 8 digit.',
            'npsn.regex' => 'NPSN harus berupa 8 digit angka.',
            'school_type.required' => 'Jenis sekolah wajib dipilih.',
            'school_type.in' => 'Jenis sekolah harus SMA atau SMK.',
            'status.required' => 'Status sekolah wajib dipilih.',
            'status.in' => 'Status sekolah harus Negeri atau Swasta.',
            'accreditation.in' => 'Akreditasi harus salah satu dari A, B, C, atau Belum.',
            'email.email' => 'Format email sekolah tidak valid.',
            'website.url' => 'Format URL website tidak valid.',
        ];
    }
}
