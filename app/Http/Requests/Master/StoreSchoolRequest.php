<?php

namespace App\Http\Requests\Master;

use App\Models\Referensi;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'school_type' => [
                'required',
                'string',
                Rule::in(array_keys(Referensi::getOptions('school_type'))),
            ],
            'status' => ['required', 'string', 'in:Negeri,Swasta'],
            'accreditation' => [
                'nullable',
                'string',
                Rule::in(array_keys(Referensi::getOptions('accreditation'))),
            ],
            'provinsi_id' => ['nullable', 'integer', 'exists:provinsi,id'],
            'kabupaten_id' => ['nullable', 'integer', 'exists:kabupaten,id'],
            'kecamatan_id' => ['nullable', 'integer', 'exists:kecamatan,id'],
            'kelurahan_id' => ['nullable', 'integer', 'exists:kelurahan,id'],
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
            'provinsi_id' => 'Provinsi',
            'kabupaten_id' => 'Kabupaten/Kota',
            'kecamatan_id' => 'Kecamatan',
            'kelurahan_id' => 'Kelurahan/Desa',
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
        $schoolTypes = array_keys(Referensi::getOptions('school_type'));
        $accreditations = array_keys(Referensi::getOptions('accreditation'));

        return [
            'name.required' => 'Nama sekolah wajib diisi.',
            'name.max' => 'Nama sekolah tidak boleh melebihi 255 karakter.',
            'npsn.required' => 'NPSN wajib diisi.',
            'npsn.size' => 'NPSN harus terdiri dari tepat 8 digit.',
            'npsn.regex' => 'NPSN harus berupa 8 digit angka.',
            'school_type.required' => 'Jenis sekolah wajib dipilih.',
            'school_type.in' => empty($schoolTypes)
                ? 'Data referensi jenis sekolah belum diatur. Silakan tambahkan data jenis sekolah terlebih dahulu pada menu Data Referensi.'
                : 'Jenis sekolah yang dipilih tidak valid atau belum terdaftar pada Data Referensi Sistem.',
            'status.required' => 'Status sekolah wajib dipilih.',
            'status.in' => 'Status sekolah harus Negeri atau Swasta.',
            'accreditation.in' => empty($accreditations)
                ? 'Data referensi akreditasi belum diatur. Silakan tambahkan data akreditasi terlebih dahulu pada menu Data Referensi.'
                : 'Akreditasi yang dipilih tidak valid atau belum terdaftar pada Data Referensi Sistem.',
            'email.email' => 'Format email sekolah tidak valid.',
            'website.url' => 'Format URL website tidak valid.',
        ];
    }
}
