<?php

namespace App\Http\Requests\Wilayah;

use App\Models\Kabupaten;
use App\Models\Provinsi;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateKabupatenRequest extends FormRequest
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
            'status' => $this->boolean('status'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Kabupaten|string|int|null $kabupatenParam */
        $kabupatenParam = $this->route('kabupaten');
        $kabupatenId = $kabupatenParam instanceof Kabupaten ? $kabupatenParam->id : $kabupatenParam;

        return [
            'provinsi_id' => ['required', 'integer', 'exists:provinsi,id'],
            'tipe' => ['required', 'string', 'in:Kabupaten,Kota'],
            'kode' => [
                'required',
                'string',
                'size:4',
                'regex:/^[0-9]{4}$/',
                Rule::unique('kabupaten', 'kode')->ignore($kabupatenId),
            ],
            'nama' => ['required', 'string', 'max:100'],
            'status' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Konfigurasi validator tambahan untuk memastikan konsistensi 2 digit awal kode wilayah.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $provinsiId = $this->input('provinsi_id');
            $kode = $this->input('kode');

            if ($provinsiId && $kode && strlen($kode) === 4) {
                $provinsi = Provinsi::find($provinsiId);
                if ($provinsi && ! str_starts_with($kode, $provinsi->kode)) {
                    $validator->errors()->add(
                        'kode',
                        "Kode wilayah ({$kode}) harus diawali dengan kode provinsi terpilih ({$provinsi->kode} - {$provinsi->nama})."
                    );
                }
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'provinsi_id' => 'Provinsi Induk',
            'tipe' => 'Tipe Wilayah',
            'kode' => 'Kode Wilayah',
            'nama' => 'Nama Kabupaten/Kota',
            'status' => 'Status Aktif',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'provinsi_id.required' => 'Provinsi induk wajib dipilih.',
            'provinsi_id.exists' => 'Provinsi yang dipilih tidak ditemukan dalam sistem.',
            'tipe.required' => 'Tipe wilayah (Kabupaten/Kota) wajib dipilih.',
            'tipe.in' => 'Tipe wilayah hanya boleh berupa Kabupaten atau Kota.',
            'kode.required' => 'Kode wilayah wajib diisi.',
            'kode.size' => 'Kode wilayah kabupaten/kota harus tepat 4 digit angka.',
            'kode.regex' => 'Kode wilayah harus berupa 4 digit angka (contoh: 7371).',
            'kode.unique' => 'Kode wilayah ini sudah digunakan oleh kabupaten/kota lain.',
            'nama.required' => 'Nama kabupaten/kota wajib diisi.',
            'nama.max' => 'Nama kabupaten/kota tidak boleh melebihi 100 karakter.',
        ];
    }
}
