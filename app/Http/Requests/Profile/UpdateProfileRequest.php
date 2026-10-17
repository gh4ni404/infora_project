<?php

namespace App\Http\Requests\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki otorisasi untuk melakukan permintaan ini.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Persiapan data sebelum proses validasi.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('remove_foto')) {
            $this->merge([
                'remove_foto' => $this->boolean('remove_foto'),
            ]);
        }
    }

    /**
     * Aturan validasi yang berlaku untuk pembaruan profil mandiri.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'tab' => ['nullable', 'string', 'in:personal,employment,contact,account,status,photo'],

            // 1. Informasi Pribadi
            'nama_lengkap' => ['sometimes', 'required', 'string', 'max:255'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'gelar_depan' => ['nullable', 'string', 'max:50'],
            'gelar_belakang' => ['nullable', 'string', 'max:50'],
            'jenis_kelamin' => ['sometimes', 'nullable', 'in:L,P'],
            'nik' => ['nullable', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'agama' => ['nullable', 'string', 'max:50'],

            // 2. Klasifikasi Kepegawaian & Kualifikasi Pendidikan
            'status_kepegawaian' => ['nullable', 'string', 'max:100'],
            'nip' => ['nullable', 'string', 'max:30'],
            'nuptk' => ['nullable', 'string', 'max:30'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:50'],
            'jurusan_pendidikan' => ['nullable', 'string', 'max:100'],

            // 3. Kontak & Domisili
            'no_hp' => ['nullable', 'string', 'max:30'],
            'email' => ['sometimes', 'nullable', 'email', 'max:100', 'unique:users,email,'.$userId],
            'provinsi_id' => ['nullable', 'integer', 'exists:provinsi,id'],
            'kabupaten_id' => ['nullable', 'integer', 'exists:kabupaten,id'],
            'kecamatan_id' => ['nullable', 'integer', 'exists:kecamatan,id'],
            'kelurahan_id' => ['nullable', 'integer', 'exists:kelurahan,id'],
            'alamat' => ['nullable', 'string', 'max:1000'],

            // 4. Kredensial Akun
            'username' => ['sometimes', 'required', 'string', 'min:3', 'max:50', 'alpha_dash', 'unique:users,username,'.$userId],

            // 5. Foto Profil
            'foto' => ['nullable', 'string'],
            'remove_foto' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Pesan error validasi kustom berbahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.max' => 'Nama lengkap tidak boleh lebih dari 255 karakter.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap tidak boleh lebih dari 255 karakter.',
            'username.required' => 'Username akun wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username tidak boleh lebih dari 50 karakter.',
            'username.alpha_dash' => 'Username hanya boleh berupa huruf, angka, tanda strip (-), dan garis bawah (_).',
            'username.unique' => 'Username ini telah digunakan oleh akun pengguna lain.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.max' => 'Alamat email tidak boleh lebih dari 100 karakter.',
            'email.unique' => 'Alamat email ini telah digunakan oleh akun pengguna lain.',
            'no_hp.max' => 'Nomor HP / WhatsApp tidak boleh lebih dari 30 karakter.',
            'alamat.max' => 'Alamat domisili tidak boleh lebih dari 1000 karakter.',
            'jenis_kelamin.in' => 'Pilihan jenis kelamin tidak valid.',
            'provinsi_id.exists' => 'Provinsi yang dipilih tidak terdaftar di sistem.',
            'kabupaten_id.exists' => 'Kabupaten / Kota yang dipilih tidak terdaftar di sistem.',
            'kecamatan_id.exists' => 'Kecamatan yang dipilih tidak terdaftar di sistem.',
            'kelurahan_id.exists' => 'Kelurahan / Desa yang dipilih tidak terdaftar di sistem.',
        ];
    }
}
