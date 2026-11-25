<?php

namespace App\Http\Requests\Rombel;

use App\Http\Requests\Concerns\ResolvesSingleSchoolInput;
use App\Support\TextFormatter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKelasRequest extends FormRequest
{
    use ResolvesSingleSchoolInput;

    /**
     * Tentukan apakah pengguna diizinkan untuk membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Persiapan dan sanitasi data sebelum proses validasi.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeSingleSchoolId();

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'nama' => $this->filled('nama') ? TextFormatter::titleCase((string) $this->input('nama')) : null,
            'kode' => $this->filled('kode') ? TextFormatter::upper((string) $this->input('kode')) : null,
            'ruangan' => $this->filled('ruangan') ? TextFormatter::titleCase((string) $this->input('ruangan')) : null,
            'kapasitas' => $this->filled('kapasitas') ? (int) $this->input('kapasitas') : 36,
            'tahun_ajaran_id' => $this->filled('tahun_ajaran_id') ? (int) $this->input('tahun_ajaran_id') : null,
            'tingkatan_id' => $this->filled('tingkatan_id') ? (int) $this->input('tingkatan_id') : null,
            'jurusan_id' => $this->filled('jurusan_id') ? (int) $this->input('jurusan_id') : null,
            'wali_kelas_id' => $this->filled('wali_kelas_id') ? (int) $this->input('wali_kelas_id') : null,
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
        $tahunAjaranId = (int) $this->input('tahun_ajaran_id');

        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'tahun_ajaran_id' => [
                'required',
                'integer',
                Rule::exists('tahun_ajaran', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'tingkatan_id' => [
                'required',
                'integer',
                Rule::exists('tingkatan', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'jurusan_id' => [
                'nullable',
                'integer',
                Rule::exists('jurusan', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'wali_kelas_id' => [
                'nullable',
                'integer',
                Rule::exists('gtks', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kelas', 'nama')->where(function ($query) use ($schoolId, $tahunAjaranId) {
                    return $query->where('school_id', $schoolId)
                        ->where('tahun_ajaran_id', $tahunAjaranId);
                }),
            ],
            'kode' => ['nullable', 'string', 'max:50'],
            'ruangan' => ['nullable', 'string', 'max:100'],
            'kapasitas' => ['required', 'integer', 'between:1,100'],
            'is_active' => ['boolean'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
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
            'school_id.exists' => 'Unit sekolah yang dipilih tidak valid dalam sistem.',
            'tahun_ajaran_id.required' => 'Tahun ajaran wajib dipilih.',
            'tahun_ajaran_id.exists' => 'Tahun ajaran yang dipilih tidak valid untuk unit sekolah ini.',
            'tingkatan_id.required' => 'Tingkatan kelas wajib dipilih.',
            'tingkatan_id.exists' => 'Tingkatan kelas yang dipilih tidak valid untuk unit sekolah ini.',
            'jurusan_id.exists' => 'Konsentrasi keahlian / jurusan yang dipilih tidak valid untuk unit sekolah ini.',
            'wali_kelas_id.exists' => 'Wali kelas (GTK) yang dipilih tidak terdaftar di unit sekolah ini.',
            'nama.required' => 'Nama rombongan belajar / kelas wajib diisi.',
            'nama.max' => 'Nama kelas maksimal terdiri atas 100 karakter.',
            'nama.unique' => 'Nama kelas sudah terdaftar pada tahun ajaran ini di unit sekolah yang sama.',
            'kode.max' => 'Kode kelas maksimal terdiri atas 50 karakter.',
            'ruangan.max' => 'Nama ruangan maksimal terdiri atas 100 karakter.',
            'kapasitas.required' => 'Daya tampung kapasitas murid wajib diisi.',
            'kapasitas.integer' => 'Kapasitas murid harus berupa angka bulat positif.',
            'kapasitas.between' => 'Kapasitas murid harus berada di antara rentang 1 sampai 100 murid per rombel.',
            'keterangan.max' => 'Keterangan tambahan maksimal terdiri atas 1000 karakter.',
        ];
    }
}
