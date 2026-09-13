<?php

namespace App\Http\Requests\Wilayah;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreKelurahanRequest extends FormRequest
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
            'kecamatan_id' => ['required', 'integer', 'exists:kecamatan,id'],
            'tipe' => ['required', 'string', 'in:Kelurahan,Desa'],
            'kode' => ['required', 'string', 'size:10', 'regex:/^[0-9]{10}$/', 'unique:kelurahan,kode'],
            'nama' => ['required', 'string', 'max:100'],
            'kode_pos' => ['nullable', 'string', 'size:5', 'regex:/^[0-9]{5}$/'],
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
            $kecamatanId = (int) $this->input('kecamatan_id');
            $kode = (string) $this->input('kode');

            if ($kabupatenId) {
                $kabupaten = Kabupaten::with('provinsi')->find($kabupatenId);
                if ($kabupaten && $provinsiId && $kabupaten->provinsi_id !== $provinsiId) {
                    $validator->errors()->add(
                        'kabupaten_id',
                        "Kabupaten/Kota terpilih ({$kabupaten->nama_lengkap}) tidak berada di bawah provinsi yang dipilih."
                    );
                }
            }

            if ($kecamatanId) {
                $kecamatan = Kecamatan::with('kabupaten')->find($kecamatanId);
                if ($kecamatan) {
                    if ($kabupatenId && $kecamatan->kabupaten_id !== $kabupatenId) {
                        $validator->errors()->add(
                            'kecamatan_id',
                            "Kecamatan terpilih ({$kecamatan->nama_lengkap}) tidak berada di bawah kabupaten/kota yang dipilih."
                        );
                    }

                    if ($kode && strlen($kode) === 10 && ! str_starts_with($kode, $kecamatan->kode)) {
                        $validator->errors()->add(
                            'kode',
                            "Kode wilayah kelurahan/desa ({$kode}) harus diawali dengan kode kecamatan terpilih ({$kecamatan->kode} - {$kecamatan->nama_lengkap})."
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
            'kecamatan_id' => 'Kecamatan Induk',
            'tipe' => 'Tipe Wilayah',
            'kode' => 'Kode Kelurahan/Desa',
            'nama' => 'Nama Kelurahan/Desa',
            'kode_pos' => 'Kode Pos',
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
            'kecamatan_id.required' => 'Silakan pilih kecamatan induk.',
            'kecamatan_id.exists' => 'Kecamatan yang dipilih tidak valid dalam basis data.',
            'tipe.required' => 'Tipe wilayah (Kelurahan atau Desa) wajib dipilih.',
            'tipe.in' => 'Tipe wilayah harus berupa Kelurahan atau Desa.',
            'kode.required' => 'Kode wilayah kelurahan/desa wajib diisi.',
            'kode.size' => 'Kode wilayah kelurahan/desa harus tepat berjumlah 10 digit.',
            'kode.regex' => 'Format kode wilayah kelurahan/desa hanya boleh berupa angka (10 digit).',
            'kode.unique' => 'Kode wilayah kelurahan/desa ini telah terdaftar dalam sistem.',
            'nama.required' => 'Nama kelurahan/desa wajib diisi.',
            'nama.max' => 'Nama kelurahan/desa maksimal terdiri dari 100 karakter.',
            'kode_pos.size' => 'Kode pos harus tepat berjumlah 5 digit angka.',
            'kode_pos.regex' => 'Format kode pos hanya boleh berupa angka 5 digit.',
        ];
    }
}
