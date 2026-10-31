<?php

namespace App\Http\Requests\Master;

use App\Models\School;
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

        if ($this->has('nisn')) {
            $this->merge([
                'nisn' => filled($this->input('nisn')) ? preg_replace('/\s+/', '', (string) $this->input('nisn')) : null,
            ]);
        }

        if ($this->has('nis')) {
            $this->merge([
                'nis' => filled($this->input('nis')) ? preg_replace('/\s+/', '', (string) $this->input('nis')) : null,
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
        $school = $schoolId ? School::find($schoolId) : null;
        $isMadrasah = $school?->isMadrasah() ?? false;

        $nisRules = [
            $isMadrasah ? 'required' : 'nullable',
            'string',
            $isMadrasah ? 'size:18' : 'max:30',
            Rule::unique('murids', 'nis')
                ->where(fn ($query) => $query->where('school_id', $schoolId)->whereNull('deleted_at')),
        ];

        if ($isMadrasah) {
            $nisRules[] = 'regex:/^\d{18}$/';
        }

        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'string', 'in:L,P'],
            'nisn' => [
                'nullable',
                'string',
                'max:10',
                'regex:/^\d{1,10}$/',
                Rule::unique('murids', 'nisn')
                    ->where(fn ($query) => $query->where('school_id', $schoolId)->whereNull('deleted_at')),
            ],
            'nis' => $nisRules,
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
        $schoolId = $this->input('school_id');
        $school = $schoolId ? School::find($schoolId) : null;
        $isMadrasah = $school?->isMadrasah() ?? false;

        return [
            'school_id.required' => 'Unit sekolah wajib dipilih.',
            'school_id.exists' => 'Unit sekolah yang dipilih tidak terdaftar di sistem.',
            'nama_lengkap.required' => 'Nama lengkap murid wajib diisi.',
            'nama_lengkap.max' => 'Nama lengkap maksimal terdiri atas 255 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin murid wajib dipilih.',
            'jenis_kelamin.in' => 'Pilihan jenis kelamin tidak valid (harus L atau P).',
            'nisn.max' => 'NISN maksimal terdiri dari 10 digit angka.',
            'nisn.regex' => 'NISN harus berupa digit angka dengan panjang maksimal 10 digit.',
            'nisn.unique' => 'NISN ini sudah terdaftar pada murid lain di sekolah yang sama.',
            'nis.required' => 'NISM (Nomor Induk Murid Madrasah) wajib diisi untuk madrasah di bawah naungan Kemenag.',
            'nis.size' => 'NISM harus terdiri tepat 18 digit angka (12 digit NSM, 2 digit tahun masuk, dan 4 digit nomor urut).',
            'nis.regex' => 'NISM harus berupa 18 digit angka.',
            'nis.max' => 'Nomor Induk Murid (NIS) maksimal terdiri dari 30 karakter.',
            'nis.unique' => $isMadrasah
                ? 'NISM ini sudah terdaftar pada murid lain di madrasah ini.'
                : 'Nomor Induk Murid (NIS) ini sudah terdaftar pada murid lain di sekolah yang sama.',
            'kelas_id.exists' => 'Rombongan belajar / kelas yang dipilih tidak valid untuk unit sekolah ini.',
            'email.email' => 'Format alamat email tidak valid.',
        ];
    }

    /**
     * Nama atribut khusus untuk pesan kesalahan validasi.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $schoolId = $this->input('school_id');
        $school = $schoolId ? School::find($schoolId) : null;
        $isMadrasah = $school?->isMadrasah() ?? false;

        return [
            'school_id' => 'unit sekolah',
            'nama_lengkap' => 'nama lengkap',
            'jenis_kelamin' => 'jenis kelamin',
            'nisn' => 'NISN',
            'nis' => $isMadrasah ? 'NISM' : 'NIS',
            'kelas_id' => 'kelas / rombel',
            'angkatan' => 'tahun angkatan',
            'email' => 'alamat email',
        ];
    }
}
