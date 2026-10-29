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
            'nik' => ['nullable', 'string', 'max:20'],
            'kelas_id' => [
                'nullable',
                'integer',
                Rule::exists('kelas', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'angkatan' => ['nullable', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'agama' => ['nullable', 'string', 'max:30'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'provinsi_id' => ['nullable', 'integer', 'exists:provinsi,id'],
            'kabupaten_id' => ['nullable', 'integer', 'exists:kabupaten,id'],
            'kecamatan_id' => ['nullable', 'integer', 'exists:kecamatan,id'],
            'kelurahan_id' => ['nullable', 'integer', 'exists:kelurahan,id'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'nama_ayah' => ['nullable', 'string', 'max:150'],
            'nama_ibu' => ['nullable', 'string', 'max:150'],
            'nama_wali' => ['nullable', 'string', 'max:150'],
            'no_hp_ortu' => ['nullable', 'string', 'max:30'],
            'foto' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:aktif,lulus,mutasi,keluar,dropout'],
            'is_active' => ['boolean'],

            // Akun pengguna login
            'create_user_account' => ['boolean'],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'password' => ['nullable', 'string', 'min:6'],
            'role_template' => ['nullable', 'string'],
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
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'username.unique' => 'Username ini telah digunakan oleh pengguna lain.',
            'password.min' => 'Kata sandi akun minimal terdiri atas 6 karakter.',
        ];
    }
}
