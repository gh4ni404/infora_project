<?php

namespace Database\Seeders;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use Illuminate\Database\Seeder;

class KecamatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi 27 kecamatan resmi Kemendagri Kabupaten Bone (Kode 7308).
     */
    public function run(): void
    {
        $bone = Kabupaten::where('kode', '7308')->first();

        if (! $bone) {
            return;
        }

        $daftarKecamatanBone = [
            ['kode' => '730801', 'nama' => 'Bontocani'],
            ['kode' => '730802', 'nama' => 'Kahu'],
            ['kode' => '730803', 'nama' => 'Kajuara'],
            ['kode' => '730804', 'nama' => 'Salomekko'],
            ['kode' => '730805', 'nama' => 'Tonra'],
            ['kode' => '730806', 'nama' => 'Libureng'],
            ['kode' => '730807', 'nama' => 'Mare'],
            ['kode' => '730808', 'nama' => 'Sibulue'],
            ['kode' => '730809', 'nama' => 'Barebbo'],
            ['kode' => '730810', 'nama' => 'Cina'],
            ['kode' => '730811', 'nama' => 'Ponre'],
            ['kode' => '730812', 'nama' => 'Lappariaja'],
            ['kode' => '730813', 'nama' => 'Lamuru'],
            ['kode' => '730814', 'nama' => 'Ulaweng'],
            ['kode' => '730815', 'nama' => 'Palakka'],
            ['kode' => '730816', 'nama' => 'Awangpone'],
            ['kode' => '730817', 'nama' => 'Tellu Siattinge'],
            ['kode' => '730818', 'nama' => 'Ajangale'],
            ['kode' => '730819', 'nama' => 'Dua Boccoe'],
            ['kode' => '730820', 'nama' => 'Cenrana'],
            ['kode' => '730821', 'nama' => 'Tanete Riattang'],
            ['kode' => '730822', 'nama' => 'Tanete Riattang Barat'],
            ['kode' => '730823', 'nama' => 'Tanete Riattang Timur'],
            ['kode' => '730824', 'nama' => 'Amali'],
            ['kode' => '730825', 'nama' => 'Tellu Limpoe'],
            ['kode' => '730826', 'nama' => 'Bengo'],
            ['kode' => '730827', 'nama' => 'Patimpeng'],
        ];

        foreach ($daftarKecamatanBone as $item) {
            Kecamatan::updateOrCreate(
                ['kode' => $item['kode']],
                [
                    'kabupaten_id' => $bone->id,
                    'nama' => $item['nama'],
                    'status' => true,
                ]
            );
        }
    }
}
