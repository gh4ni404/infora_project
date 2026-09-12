<?php

namespace App\Http\Requests\Wilayah;

use App\Models\Kabupaten;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreKecamatanRequest extends FormRequest
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
        return [
            'provinsi_id' => ['required', 'integer', 'exists:provinsi,id'],
            'kabupaten_id' => ['required', 'integer', 'exists:kabupaten,id'],
            'kode' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/', 'unique:kecamatan,kode'],
            'nama' => ['required', 'string', 'max:100'],
            'status' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Konfigurasi validator tambahan untuk memastikan konsistensi relasi dan kode wilayah.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $provinsiId = (int) $this->input('provinsi_id');
            $kabupatenId = (int) $this->input('kabupaten_id');
            $kode = (string) $this->input('kode');

            if ($kabupatenId) {
                $kabupaten = Kabupaten::with('provinsi')->find($kabupatenId);
                if ($kabupaten) {
                    if ($provinsiId && $kabupaten->provinsi_id !== $provinsiId) {
                        $validator->errors()->add(
                            'kabupaten_id',
                            "Kabupaten/Kota terpilih ({$kabupaten->nama_lengkap}) tidak berada di bawah provinsi yang dipilih."
                        );
                    }

                    if ($kode && strlen($kode) === 6 && ! str_starts_with($kode, $kabupaten->kode)) {
                        $validator->errors()->add(
                            'kode',
                            "Kode wilayah kecamatan ({$kode}) harus diawali dengan kode kabupaten terpilih ({$kabupaten->kode} - {$kabupaten->nama_lengkap})."
                        );
                    }
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
            'kabupaten_id' => 'Kabupaten/Kota',
            'kode' => 'Kode Kecamatan',
            'nama' => 'Nama Kecamatan',
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
            'provinsi_id.required' => 'Silakan pilih provinsi induk terlebih dahulu.',
            'kabupaten_id.required' => 'Silakan pilih kabupaten/kota induk.',
            'kabupaten_id.exists' => 'Kabupaten/Kota yang dipilih tidak valid dalam basis data.',
            'kode.required' => 'Kode wilayah kecamatan wajib diisi.',
            'kode.size' => 'Kode wilayah kecamatan harus tepat berjumlah 6 digit.',
            'kode.regex' => 'Format kode wilayah kecamatan hanya boleh berupa angka (6 digit).',
            'kode.unique' => 'Kode wilayah kecamatan ini telah terdaftar dalam sistem.',
            'nama.required' => 'Nama kecamatan wajib diisi.',
            'nama.max' => 'Nama kecamatan maksimal terdiri dari 100 karakter.',
        ];
    }
}
