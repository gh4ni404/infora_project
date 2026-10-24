<?php

namespace App\Http\Requests\Master;

use App\Models\Tingkatan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTingkatanRequest extends FormRequest
{
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
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'kode' => $this->filled('kode') ? strtoupper(trim((string) $this->input('kode'))) : null,
            'nama' => $this->filled('nama') ? trim((string) $this->input('nama')) : null,
            'fase' => $this->filled('fase') ? trim((string) $this->input('fase')) : null,
            'keterangan' => $this->filled('keterangan') ? trim((string) $this->input('keterangan')) : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Tingkatan|int|string $tingkatan */
        $tingkatan = $this->route('tingkatan');
        if (! $tingkatan instanceof Tingkatan) {
            $tingkatan = Tingkatan::findOrFail($tingkatan);
        }

        $schoolId = $tingkatan->school_id;
        $tingkatanId = $tingkatan->id;

        return [
            'kode' => [
                'required',
                'string',
                'max:20',
                Rule::unique('tingkatan', 'kode')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($tingkatanId),
            ],
            'nama' => ['required', 'string', 'max:100'],
            'tingkat_angka' => [
                'required',
                'integer',
                'between:1,13',
                Rule::unique('tingkatan', 'tingkat_angka')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($tingkatanId),
            ],
            'fase' => ['nullable', 'string', 'max:20'],
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
            'kode.required' => 'Kode tingkatan wajib diisi.',
            'kode.max' => 'Kode tingkatan maksimal terdiri atas 20 karakter.',
            'kode.unique' => 'Kode tingkatan ini sudah terdaftar pada sekolah yang dipilih.',
            'nama.required' => 'Nama tingkatan wajib diisi.',
            'nama.max' => 'Nama tingkatan maksimal terdiri atas 100 karakter.',
            'tingkat_angka.required' => 'Tingkat angka wajib diisi.',
            'tingkat_angka.integer' => 'Tingkat angka harus berupa bilangan bulat.',
            'tingkat_angka.between' => 'Tingkat angka harus berada di antara rentang 1 sampai 13.',
            'tingkat_angka.unique' => 'Tingkat angka ini sudah digunakan pada sekolah yang dipilih.',
            'fase.max' => 'Fase kurikulum maksimal terdiri atas 20 karakter.',
            'keterangan.max' => 'Keterangan tingkatan maksimal terdiri atas 1000 karakter.',
        ];
    }
}
