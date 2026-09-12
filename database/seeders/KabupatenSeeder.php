<?php

namespace Database\Seeders;

use App\Models\Kabupaten;
use App\Models\Provinsi;
use Illuminate\Database\Seeder;

class KabupatenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Kode wilayah yang digunakan adalah kode resmi KEMENDAGRI (Permendagri / Kepmendagri),
     * bukan kode BPS.
     */
    public function run(): void
    {
        // 1. Wilayah Percontohan: Sulawesi Selatan (Kode Kemendagri: 73)
        $sulsel = Provinsi::where('kode', '73')->first();
        if ($sulsel) {
            $daftarSulsel = [
                ['kode' => '7301', 'tipe' => 'Kabupaten', 'nama' => 'Kepulauan Selayar'],
                ['kode' => '7302', 'tipe' => 'Kabupaten', 'nama' => 'Bulukumba'],
                ['kode' => '7303', 'tipe' => 'Kabupaten', 'nama' => 'Bantaeng'],
                ['kode' => '7304', 'tipe' => 'Kabupaten', 'nama' => 'Jeneponto'],
                ['kode' => '7305', 'tipe' => 'Kabupaten', 'nama' => 'Takalar'],
                ['kode' => '7306', 'tipe' => 'Kabupaten', 'nama' => 'Gowa'],
                ['kode' => '7307', 'tipe' => 'Kabupaten', 'nama' => 'Sinjai'],
                ['kode' => '7308', 'tipe' => 'Kabupaten', 'nama' => 'Bone'],
                ['kode' => '7309', 'tipe' => 'Kabupaten', 'nama' => 'Maros'],
                ['kode' => '7310', 'tipe' => 'Kabupaten', 'nama' => 'Pangkajene Dan Kepulauan'],
                ['kode' => '7311', 'tipe' => 'Kabupaten', 'nama' => 'Barru'],
                ['kode' => '7312', 'tipe' => 'Kabupaten', 'nama' => 'Soppeng'],
                ['kode' => '7313', 'tipe' => 'Kabupaten', 'nama' => 'Wajo'],
                ['kode' => '7314', 'tipe' => 'Kabupaten', 'nama' => 'Sidenreng Rappang'],
                ['kode' => '7315', 'tipe' => 'Kabupaten', 'nama' => 'Pinrang'],
                ['kode' => '7316', 'tipe' => 'Kabupaten', 'nama' => 'Enrekang'],
                ['kode' => '7317', 'tipe' => 'Kabupaten', 'nama' => 'Luwu'],
                ['kode' => '7318', 'tipe' => 'Kabupaten', 'nama' => 'Tana Toraja'],
                ['kode' => '7322', 'tipe' => 'Kabupaten', 'nama' => 'Luwu Utara'],
                ['kode' => '7324', 'tipe' => 'Kabupaten', 'nama' => 'Luwu Timur'],
                ['kode' => '7326', 'tipe' => 'Kabupaten', 'nama' => 'Toraja Utara'],
                ['kode' => '7371', 'tipe' => 'Kota', 'nama' => 'Makassar'],
                ['kode' => '7372', 'tipe' => 'Kota', 'nama' => 'Parepare'],
                ['kode' => '7373', 'tipe' => 'Kota', 'nama' => 'Palopo'],
            ];

            foreach ($daftarSulsel as $item) {
                Kabupaten::updateOrCreate(
                    ['kode' => $item['kode']],
                    [
                        'provinsi_id' => $sulsel->id,
                        'tipe' => $item['tipe'],
                        'nama' => $item['nama'],
                        'status' => true,
                    ]
                );
            }
        }

        // 2. Wilayah Percontohan: DKI Jakarta (Kode Kemendagri: 31)
        $dki = Provinsi::where('kode', '31')->first();
        if ($dki) {
            $daftarDki = [
                ['kode' => '3101', 'tipe' => 'Kabupaten', 'nama' => 'Kepulauan Seribu'],
                ['kode' => '3171', 'tipe' => 'Kota', 'nama' => 'Jakarta Pusat'],
                ['kode' => '3172', 'tipe' => 'Kota', 'nama' => 'Jakarta Utara'],
                ['kode' => '3173', 'tipe' => 'Kota', 'nama' => 'Jakarta Barat'],
                ['kode' => '3174', 'tipe' => 'Kota', 'nama' => 'Jakarta Selatan'],
                ['kode' => '3175', 'tipe' => 'Kota', 'nama' => 'Jakarta Timur'],
            ];

            foreach ($daftarDki as $item) {
                Kabupaten::updateOrCreate(
                    ['kode' => $item['kode']],
                    [
                        'provinsi_id' => $dki->id,
                        'tipe' => $item['tipe'],
                        'nama' => $item['nama'],
                        'status' => true,
                    ]
                );
            }
        }
    }
}
