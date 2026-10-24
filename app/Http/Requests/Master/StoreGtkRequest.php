<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreGtkRequest extends FormRequest
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
        // Jika school_id tidak dikirim, ambil dari unit sekolah akun pengguna aktif
        if (! $this->filled('school_id') && $this->user()?->school_id) {
            $this->merge([
                'school_id' => $this->user()->school_id,
            ]);
        }

        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);

        if (is_string($this->jenis_ptk)) {
            $val = trim($this->jenis_ptk);
            if (strcasecmp($val, 'guru') === 0 || strcasecmp($val, 'pendidik') === 0) {
                $this->merge(['jenis_ptk' => ['Guru Mata Pelajaran']]);
            } elseif (in_array(strtolower($val), ['staf', 'staff', 'tendik', 'tu', 'pegawai'], true)) {
                $this->merge(['jenis_ptk' => ['Tenaga Administrasi Sekolah (Staf TU)']]);
            } elseif (filled($val)) {
                $this->merge(['jenis_ptk' => [$val]]);
            }
        }
    }

    /**
     * Aturan validasi yang berlaku untuk request penyimpanan data GTK baru.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'gelar_depan' => ['nullable', 'string', 'max:50'],
            'gelar_belakang' => ['nullable', 'string', 'max:50'],
            'jenis_kelamin' => ['required', 'string', 'in:L,P'],
            'nik' => ['nullable', 'string', 'max:20'],
            'nip' => ['nullable', 'string', 'max:30'],
            'nuptk' => ['nullable', 'string', 'max:30'],
            'jenis_ptk' => ['required', 'array', 'min:1'],
            'jenis_ptk.*' => ['required', 'string', 'max:100'],
            'status_kepegawaian' => ['required', 'string', 'max:50'],
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
            'pendidikan_terakhir' => ['nullable', 'string', 'max:50'],
            'jurusan_pendidikan' => ['nullable', 'string', 'max:100'],
            'foto' => ['nullable', 'string'],
            'is_active' => ['boolean'],

            // Validasi Kredensial Akun Login User
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
            'nama_lengkap.required' => 'Nama lengkap GTK wajib diisi.',
            'nama_lengkap.max' => 'Nama lengkap maksimal terdiri atas 255 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Pilihan jenis kelamin tidak valid (harus L atau P).',
            'jenis_ptk.required' => 'Pilih minimal satu jenis PTK atau tugas penugasan GTK.',
            'jenis_ptk.min' => 'Pilih minimal satu jenis PTK atau tugas penugasan GTK.',
            'status_kepegawaian.required' => 'Status kepegawaian wajib dipilih.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'email.email' => 'Format alamat email tidak valid.',
            'provinsi_id.exists' => 'Provinsi yang dipilih tidak valid.',
            'kabupaten_id.exists' => 'Kabupaten/Kota yang dipilih tidak valid.',
            'kecamatan_id.exists' => 'Kecamatan yang dipilih tidak valid.',
            'kelurahan_id.exists' => 'Kelurahan/Desa yang dipilih tidak valid.',
            'username.unique' => 'Username tersebut sudah digunakan oleh akun lain.',
            'password.min' => 'Kata sandi minimal terdiri atas 6 karakter.',
        ];
    }
}
