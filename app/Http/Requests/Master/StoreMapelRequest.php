<?php

namespace App\Http\Requests\Master;

use App\Http\Requests\Concerns\ResolvesSingleSchoolInput;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMapelRequest extends FormRequest
{
    use ResolvesSingleSchoolInput;

    /**
     * Determine if the user is authorized to make this request.
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
        $this->mergeSingleSchoolId();

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'kode' => $this->filled('kode') ? strtoupper(trim((string) $this->input('kode'))) : null,
            'singkatan' => $this->filled('singkatan') ? strtoupper(trim((string) $this->input('singkatan'))) : null,
            'nama' => $this->filled('nama') ? trim((string) $this->input('nama')) : null,
            'kelompok' => $this->filled('kelompok') ? trim((string) $this->input('kelompok')) : null,
            'jurusan_id' => $this->filled('jurusan_id') ? (int) $this->input('jurusan_id') : null,
            'beban_jam' => $this->filled('beban_jam') ? (int) $this->input('beban_jam') : 2,
            'keterangan' => $this->filled('keterangan') ? trim((string) $this->input('keterangan')) : null,
        ]);
    }

    /**
     * Aturan validasi yang berlaku untuk permintaan ini.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $schoolId = (int) $this->input('school_id');

        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'jurusan_id' => [
                'nullable',
                'integer',
                Rule::exists('jurusan', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'kode' => ['required', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:150'],
            'singkatan' => ['nullable', 'string', 'max:20'],
            'kelompok' => [
                'required',
                'string',
                Rule::in(['Umum', 'Kejuruan / Peminatan', 'Muatan Lokal', 'Pilihan']),
            ],
            'beban_jam' => ['nullable', 'integer', 'between:1,20'],
            'is_active' => ['boolean'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Pesan kustom untuk kegagalan validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'school_id.required' => 'Unit sekolah wajib dipilih.',
            'school_id.exists' => 'Unit sekolah yang dipilih tidak valid dalam sistem.',
            'jurusan_id.exists' => 'Jurusan yang dipilih tidak valid untuk unit sekolah ini.',
            'kode.required' => 'Kode mata pelajaran wajib diisi.',
            'kode.max' => 'Kode mata pelajaran maksimal terdiri atas 20 karakter.',
            'nama.required' => 'Nama mata pelajaran wajib diisi.',
            'nama.max' => 'Nama mata pelajaran maksimal terdiri atas 150 karakter.',
            'singkatan.max' => 'Singkatan mata pelajaran maksimal terdiri atas 20 karakter.',
            'kelompok.required' => 'Kelompok mata pelajaran wajib dipilih.',
            'kelompok.in' => 'Kelompok mata pelajaran yang dipilih tidak valid.',
            'beban_jam.integer' => 'Beban jam (JP) harus berupa bilangan bulat.',
            'beban_jam.between' => 'Beban jam (JP) per minggu harus berada di antara rentang 1 sampai 20 JP.',
            'keterangan.max' => 'Keterangan mata pelajaran maksimal terdiri atas 1000 karakter.',
        ];
    }
}
