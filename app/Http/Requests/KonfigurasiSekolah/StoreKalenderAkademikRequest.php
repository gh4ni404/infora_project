<?php

namespace App\Http\Requests\KonfigurasiSekolah;

use App\Models\TahunAjaran;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKalenderAkademikRequest extends FormRequest
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
        $merge = [
            'libur_kbm' => $this->boolean('libur_kbm'),
        ];

        if ($this->user()?->school_id) {
            $merge['school_id'] = $this->user()->school_id;
        }

        $this->merge($merge);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $schoolId = (int) $this->input('school_id');

        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'judul_kegiatan' => ['required', 'string', 'max:150'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'tahun_ajaran' => [
                'required',
                'string',
                'max:20',
                Rule::exists('tahun_ajaran', 'tahun')->where('school_id', $schoolId),
            ],
            'semester' => ['required', 'string', 'in:ganjil,genap'],
            'kategori' => ['required', 'string', 'max:50'],
            'warna' => ['required', 'string', 'in:blue,emerald,amber,rose,purple,indigo'],
            'libur_kbm' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Validasi tambahan pasca aturan standar.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $schoolId = (int) $this->input('school_id');
            if ($schoolId > 0) {
                $hasTahunAjaran = TahunAjaran::where('school_id', $schoolId)->exists();
                if (! $hasTahunAjaran) {
                    $validator->errors()->add('tahun_ajaran', 'Unit sekolah ini belum memiliki konfigurasi tahun ajaran. Silakan buat tahun ajaran terlebih dahulu.');

                    return;
                }

                $tahun = (string) $this->input('tahun_ajaran');
                $tahunAjaran = TahunAjaran::where('school_id', $schoolId)->where('tahun', $tahun)->first();
                if ($tahunAjaran && $tahunAjaran->semesters()->exists()) {
                    $validSemesters = $tahunAjaran->semesters()->pluck('semester')->all();
                    if (! in_array($this->input('semester'), $validSemesters, true)) {
                        $validator->errors()->add('semester', "Semester yang dipilih tidak terdaftar pada tahun ajaran {$tahun}.");
                    }
                }
            }
        });
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'school_id.required' => 'Sekolah wajib dipilih.',
            'school_id.exists' => 'Data sekolah yang dipilih tidak valid.',
            'judul_kegiatan.required' => 'Nama kegiatan wajib diisi.',
            'judul_kegiatan.max' => 'Nama kegiatan tidak boleh melebihi 150 karakter.',
            'tanggal_mulai.required' => 'Tanggal mulai kegiatan wajib diisi.',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',
            'tanggal_selesai.required' => 'Tanggal selesai kegiatan wajib diisi.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib dipilih.',
            'tahun_ajaran.exists' => 'Tahun ajaran yang dipilih tidak terdaftar pada unit sekolah ini.',
            'semester.required' => 'Semester wajib dipilih.',
            'semester.in' => 'Pilihan semester harus Ganjil atau Genap.',
            'kategori.required' => 'Kategori kegiatan wajib dipilih.',
            'warna.required' => 'Warna penanda wajib dipilih.',
            'warna.in' => 'Pilihan warna penanda tidak valid.',
            'keterangan.max' => 'Keterangan tidak boleh lebih dari 1000 karakter.',
        ];
    }
}
