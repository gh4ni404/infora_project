<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMuridRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki otorisasi untuk melakukan permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Persiapan data sebelum proses validasi.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('school_id') && $this->user()?->school_id) {
            $this->merge([
                'school_id' => $this->user()->school_id,
            ]);
        }

        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'create_user_account' => $this->boolean('create_user_account', true),
        ]);
    }

    /**
     * Aturan validasi yang berlaku untuk request penyimpanan data murid baru.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $schoolId = $this->input('school_id');

        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'string', 'in:L,P'],
            'nisn' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('murids', 'nisn')
                    ->where(fn ($query) => $query->where('school_id', $schoolId)->whereNull('deleted_at')),
            ],
            'nis' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('murids', 'nis')
                    ->where(fn ($query) => $query->where('school_id', $schoolId)->whereNull('deleted_at')),
            ],
            'kelas_id' => [
                'nullable',
                'integer',
                Rule::exists('kelas', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'angkatan' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'is_active' => ['boolean'],
            'create_user_account' => ['boolean'],
        ];
    }

    /**
     * Pesan kustom untuk kegagalan validasi berbahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'school_id.required' => 'Unit sekolah wajib dipilih.',
            'school_id.exists' => 'Unit sekolah yang dipilih tidak terdaftar di sistem.',
            'nama_lengkap.required' => 'Nama lengkap murid wajib diisi.',
            'nama_lengkap.max' => 'Nama lengkap maksimal terdiri atas 255 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin murid wajib dipilih.',
            'jenis_kelamin.in' => 'Pilihan jenis kelamin tidak valid (harus L atau P).',
            'nisn.unique' => 'NISN ini sudah terdaftar pada murid lain di sekolah yang sama.',
            'nis.unique' => 'Nomor Induk Siswa (NIS) ini sudah terdaftar pada murid lain di sekolah yang sama.',
            'kelas_id.exists' => 'Rombongan belajar / kelas yang dipilih tidak valid untuk unit sekolah ini.',
            'email.email' => 'Format alamat email tidak valid.',
        ];
    }
}
