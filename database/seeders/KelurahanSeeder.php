<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Illuminate\Database\Seeder;

class KelurahanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi data kelurahan dan desa resmi Kemendagri untuk kecamatan se-Kabupaten Bone (Kode 7308) beserta kode pos resminya.
     */
    public function run(): void
    {
        $dataWilayah = [
            // 1. Bontocani (730801)
            [
                'kecamatan_kode' => '730801',
                'daftar' => [
                    ['kode' => '7308011001', 'tipe' => 'Kelurahan', 'nama' => 'Kahu', 'kode_pos' => '92777'],
                    ['kode' => '7308012002', 'tipe' => 'Desa', 'nama' => 'Bana', 'kode_pos' => '92777'],
                    ['kode' => '7308012003', 'tipe' => 'Desa', 'nama' => 'Bontojai', 'kode_pos' => '92777'],
                    ['kode' => '7308012004', 'tipe' => 'Desa', 'nama' => 'Bulu Sirua', 'kode_pos' => '92777'],
                    ['kode' => '7308012005', 'tipe' => 'Desa', 'nama' => 'Langi', 'kode_pos' => '92777'],
                    ['kode' => '7308012006', 'tipe' => 'Desa', 'nama' => 'Mattirowalie', 'kode_pos' => '92777'],
                    ['kode' => '7308012007', 'tipe' => 'Desa', 'nama' => 'Pammusureng', 'kode_pos' => '92777'],
                    ['kode' => '7308012008', 'tipe' => 'Desa', 'nama' => 'Pattuku', 'kode_pos' => '92777'],
                    ['kode' => '7308012009', 'tipe' => 'Desa', 'nama' => 'Watang Cani', 'kode_pos' => '92777'],
                ],
            ],
            // 2. Kahu (730802)
            [
                'kecamatan_kode' => '730802',
                'daftar' => [
                    ['kode' => '7308021001', 'tipe' => 'Kelurahan', 'nama' => 'Palattae', 'kode_pos' => '92773'],
                    ['kode' => '7308022002', 'tipe' => 'Desa', 'nama' => 'Arallae', 'kode_pos' => '92773'],
                    ['kode' => '7308022003', 'tipe' => 'Desa', 'nama' => 'Biru', 'kode_pos' => '92773'],
                    ['kode' => '7308022004', 'tipe' => 'Desa', 'nama' => 'Bonto Padang', 'kode_pos' => '92773'],
                    ['kode' => '7308022005', 'tipe' => 'Desa', 'nama' => 'Cakkela', 'kode_pos' => '92773'],
                    ['kode' => '7308022006', 'tipe' => 'Desa', 'nama' => 'Cammilo', 'kode_pos' => '92773'],
                    ['kode' => '7308022007', 'tipe' => 'Desa', 'nama' => 'Carima', 'kode_pos' => '92773'],
                    ['kode' => '7308022008', 'tipe' => 'Desa', 'nama' => 'Cenrana', 'kode_pos' => '92773'],
                ],
            ],
            // 3. Kajuara (730803)
            [
                'kecamatan_kode' => '730803',
                'daftar' => [
                    ['kode' => '7308031001', 'tipe' => 'Kelurahan', 'nama' => 'Awang Tangka', 'kode_pos' => '92776'],
                    ['kode' => '7308032002', 'tipe' => 'Desa', 'nama' => 'Ancu', 'kode_pos' => '92776'],
                    ['kode' => '7308032003', 'tipe' => 'Desa', 'nama' => 'Angkue', 'kode_pos' => '92776'],
                    ['kode' => '7308032004', 'tipe' => 'Desa', 'nama' => 'Buareng', 'kode_pos' => '92776'],
                    ['kode' => '7308032005', 'tipe' => 'Desa', 'nama' => 'Bulu Tanah', 'kode_pos' => '92776'],
                    ['kode' => '7308032006', 'tipe' => 'Desa', 'nama' => 'Gona', 'kode_pos' => '92776'],
                    ['kode' => '7308032007', 'tipe' => 'Desa', 'nama' => 'Kalero', 'kode_pos' => '92776'],
                    ['kode' => '7308032008', 'tipe' => 'Desa', 'nama' => 'Lappa Bosse', 'kode_pos' => '92776'],
                    ['kode' => '7308032009', 'tipe' => 'Desa', 'nama' => 'Mallahae', 'kode_pos' => '92776'],
                    ['kode' => '7308032010', 'tipe' => 'Desa', 'nama' => 'Polewali', 'kode_pos' => '92776'],
                    ['kode' => '7308032011', 'tipe' => 'Desa', 'nama' => 'Pude', 'kode_pos' => '92776'],
                    ['kode' => '7308032012', 'tipe' => 'Desa', 'nama' => 'Raja', 'kode_pos' => '92776'],
                    ['kode' => '7308032013', 'tipe' => 'Desa', 'nama' => 'Tarasu', 'kode_pos' => '92776'],
                    ['kode' => '7308032014', 'tipe' => 'Desa', 'nama' => 'Waetuwo', 'kode_pos' => '92776'],
                ],
            ],
            // 4. Salomekko (730804)
            [
                'kecamatan_kode' => '730804',
                'daftar' => [
                    ['kode' => '7308041001', 'tipe' => 'Kelurahan', 'nama' => 'Pancaitana', 'kode_pos' => '92775'],
                    ['kode' => '7308042002', 'tipe' => 'Desa', 'nama' => 'Bellu', 'kode_pos' => '92775'],
                    ['kode' => '7308042003', 'tipe' => 'Desa', 'nama' => 'Gattareng', 'kode_pos' => '92775'],
                    ['kode' => '7308042004', 'tipe' => 'Desa', 'nama' => 'Malimongeng', 'kode_pos' => '92775'],
                    ['kode' => '7308042005', 'tipe' => 'Desa', 'nama' => 'Manera', 'kode_pos' => '92775'],
                    ['kode' => '7308042006', 'tipe' => 'Desa', 'nama' => 'Mappatoba', 'kode_pos' => '92775'],
                    ['kode' => '7308042007', 'tipe' => 'Desa', 'nama' => 'Tebba', 'kode_pos' => '92775'],
                ],
            ],
            // 5. Tonra (730805)
            [
                'kecamatan_kode' => '730805',
                'daftar' => [
                    ['kode' => '7308052001', 'tipe' => 'Desa', 'nama' => 'Bacu', 'kode_pos' => '92775'],
                    ['kode' => '7308052002', 'tipe' => 'Desa', 'nama' => 'Biccoing', 'kode_pos' => '92775'],
                    ['kode' => '7308052003', 'tipe' => 'Desa', 'nama' => 'Bonepute', 'kode_pos' => '92775'],
                    ['kode' => '7308052004', 'tipe' => 'Desa', 'nama' => 'Bulu-Bulu', 'kode_pos' => '92775'],
                    ['kode' => '7308052005', 'tipe' => 'Desa', 'nama' => 'Gareccing', 'kode_pos' => '92775'],
                    ['kode' => '7308052006', 'tipe' => 'Desa', 'nama' => 'Libureng', 'kode_pos' => '92775'],
                    ['kode' => '7308052007', 'tipe' => 'Desa', 'nama' => 'Muara', 'kode_pos' => '92775'],
                    ['kode' => '7308052008', 'tipe' => 'Desa', 'nama' => 'Padang Loang', 'kode_pos' => '92775'],
                    ['kode' => '7308052009', 'tipe' => 'Desa', 'nama' => 'Rappo', 'kode_pos' => '92775'],
                    ['kode' => '7308052010', 'tipe' => 'Desa', 'nama' => 'Samaenre', 'kode_pos' => '92775'],
                    ['kode' => '7308052011', 'tipe' => 'Desa', 'nama' => 'Ujunge', 'kode_pos' => '92775'],
                ],
            ],
            // 6. Libureng (730806)
            [
                'kecamatan_kode' => '730806',
                'daftar' => [
                    ['kode' => '7308061001', 'tipe' => 'Kelurahan', 'nama' => 'Ceppaga', 'kode_pos' => '92766'],
                    ['kode' => '7308061002', 'tipe' => 'Kelurahan', 'nama' => 'Tanah Batue', 'kode_pos' => '92766'],
                    ['kode' => '7308062003', 'tipe' => 'Desa', 'nama' => 'Baringeng', 'kode_pos' => '92766'],
                    ['kode' => '7308062004', 'tipe' => 'Desa', 'nama' => 'Binuang', 'kode_pos' => '92766'],
                    ['kode' => '7308062005', 'tipe' => 'Desa', 'nama' => 'Cege', 'kode_pos' => '92766'],
                    ['kode' => '7308062006', 'tipe' => 'Desa', 'nama' => 'Laburasseng', 'kode_pos' => '92766'],
                    ['kode' => '7308062007', 'tipe' => 'Desa', 'nama' => 'Mallinrung', 'kode_pos' => '92766'],
                    ['kode' => '7308062008', 'tipe' => 'Desa', 'nama' => 'Mario', 'kode_pos' => '92766'],
                    ['kode' => '7308062009', 'tipe' => 'Desa', 'nama' => 'Mattiro Bulu', 'kode_pos' => '92766'],
                    ['kode' => '7308062010', 'tipe' => 'Desa', 'nama' => 'Mattiro Walie', 'kode_pos' => '92766'],
                    ['kode' => '7308062011', 'tipe' => 'Desa', 'nama' => 'Poleonro', 'kode_pos' => '92766'],
                    ['kode' => '7308062012', 'tipe' => 'Desa', 'nama' => 'Polewali', 'kode_pos' => '92766'],
                    ['kode' => '7308062013', 'tipe' => 'Desa', 'nama' => 'Ponre-Ponre', 'kode_pos' => '92766'],
                    ['kode' => '7308062014', 'tipe' => 'Desa', 'nama' => 'Suwa', 'kode_pos' => '92766'],
                    ['kode' => '7308062015', 'tipe' => 'Desa', 'nama' => 'Swadaya', 'kode_pos' => '92766'],
                    ['kode' => '7308062016', 'tipe' => 'Desa', 'nama' => 'Tappale', 'kode_pos' => '92766'],
                    ['kode' => '7308062017', 'tipe' => 'Desa', 'nama' => 'Tompong Patu', 'kode_pos' => '92766'],
                    ['kode' => '7308062018', 'tipe' => 'Desa', 'nama' => 'Wanuawaru', 'kode_pos' => '92766'],
                    ['kode' => '7308062019', 'tipe' => 'Desa', 'nama' => 'Pitumpidange', 'kode_pos' => '92766'],
                ],
            ],
            // 7. Mare (730807)
            [
                'kecamatan_kode' => '730807',
                'daftar' => [
                    ['kode' => '7308071001', 'tipe' => 'Kelurahan', 'nama' => 'Padaelo', 'kode_pos' => '92773'],
                    ['kode' => '7308072002', 'tipe' => 'Desa', 'nama' => 'Batu Gading', 'kode_pos' => '92773'],
                    ['kode' => '7308072003', 'tipe' => 'Desa', 'nama' => 'Cege', 'kode_pos' => '92773'],
                    ['kode' => '7308072004', 'tipe' => 'Desa', 'nama' => 'Data', 'kode_pos' => '92773'],
                    ['kode' => '7308072005', 'tipe' => 'Desa', 'nama' => 'Kadai', 'kode_pos' => '92773'],
                    ['kode' => '7308072006', 'tipe' => 'Desa', 'nama' => 'Karella', 'kode_pos' => '92773'],
                    ['kode' => '7308072007', 'tipe' => 'Desa', 'nama' => 'Lakukang', 'kode_pos' => '92773'],
                    ['kode' => '7308072008', 'tipe' => 'Desa', 'nama' => 'Lapasa', 'kode_pos' => '92773'],
                    ['kode' => '7308072009', 'tipe' => 'Desa', 'nama' => 'Mario', 'kode_pos' => '92773'],
                    ['kode' => '7308072010', 'tipe' => 'Desa', 'nama' => 'Mattampa Walie', 'kode_pos' => '92773'],
                    ['kode' => '7308072011', 'tipe' => 'Desa', 'nama' => 'Mattiro Walie', 'kode_pos' => '92773'],
                    ['kode' => '7308072012', 'tipe' => 'Desa', 'nama' => 'Sumaling', 'kode_pos' => '92773'],
                    ['kode' => '7308072013', 'tipe' => 'Desa', 'nama' => 'Tellu Boccoe', 'kode_pos' => '92773'],
                    ['kode' => '7308072014', 'tipe' => 'Desa', 'nama' => 'Ujung Salangketo', 'kode_pos' => '92773'],
                    ['kode' => '7308072015', 'tipe' => 'Desa', 'nama' => 'Ujung Tanah', 'kode_pos' => '92773'],
                    ['kode' => '7308072016', 'tipe' => 'Desa', 'nama' => 'Tellongeng', 'kode_pos' => '92773'],
                    ['kode' => '7308072017', 'tipe' => 'Desa', 'nama' => 'Pattiro', 'kode_pos' => '92773'],
                ],
            ],
            // 8. Sibulue (730808)
            [
                'kecamatan_kode' => '730808',
                'daftar' => [
                    ['kode' => '7308081008', 'tipe' => 'Kelurahan', 'nama' => 'Maroanging', 'kode_pos' => '92781'],
                    ['kode' => '7308082001', 'tipe' => 'Desa', 'nama' => 'Acekke', 'kode_pos' => '92781'],
                    ['kode' => '7308082002', 'tipe' => 'Desa', 'nama' => 'Balieng Toa', 'kode_pos' => '92781'],
                    ['kode' => '7308082003', 'tipe' => 'Desa', 'nama' => 'Cinnong', 'kode_pos' => '92781'],
                    ['kode' => '7308082004', 'tipe' => 'Desa', 'nama' => 'Kalibong', 'kode_pos' => '92781'],
                    ['kode' => '7308082005', 'tipe' => 'Desa', 'nama' => 'Letta Tanah', 'kode_pos' => '92781'],
                    ['kode' => '7308082006', 'tipe' => 'Desa', 'nama' => 'Mabbiring', 'kode_pos' => '92781'],
                    ['kode' => '7308082007', 'tipe' => 'Desa', 'nama' => 'Mallusetasi', 'kode_pos' => '92781'],
                ],
            ],
            // 9. Barebbo (730809)
            [
                'kecamatan_kode' => '730809',
                'daftar' => [
                    ['kode' => '7308091008', 'tipe' => 'Kelurahan', 'nama' => 'Apala', 'kode_pos' => '92771'],
                    ['kode' => '7308092001', 'tipe' => 'Desa', 'nama' => 'Bacu', 'kode_pos' => '92771'],
                    ['kode' => '7308092002', 'tipe' => 'Desa', 'nama' => 'Barebbo', 'kode_pos' => '92771'],
                    ['kode' => '7308092003', 'tipe' => 'Desa', 'nama' => 'Cempaniga', 'kode_pos' => '92771'],
                    ['kode' => '7308092004', 'tipe' => 'Desa', 'nama' => 'Cingkang', 'kode_pos' => '92771'],
                    ['kode' => '7308092005', 'tipe' => 'Desa', 'nama' => 'Kading', 'kode_pos' => '92771'],
                    ['kode' => '7308092006', 'tipe' => 'Desa', 'nama' => 'Parippung', 'kode_pos' => '92771'],
                    ['kode' => '7308092007', 'tipe' => 'Desa', 'nama' => 'Samaelo', 'kode_pos' => '92771'],
                ],
            ],
            // 10. Cina (730810)
            [
                'kecamatan_kode' => '730810',
                'daftar' => [
                    ['kode' => '7308101001', 'tipe' => 'Kelurahan', 'nama' => 'Tanete', 'kode_pos' => '92772'],
                    ['kode' => '7308102002', 'tipe' => 'Desa', 'nama' => 'Abbumpungeng', 'kode_pos' => '92772'],
                    ['kode' => '7308102003', 'tipe' => 'Desa', 'nama' => 'Ajangale', 'kode_pos' => '92772'],
                    ['kode' => '7308102004', 'tipe' => 'Desa', 'nama' => 'Arasoe', 'kode_pos' => '92772'],
                    ['kode' => '7308102005', 'tipe' => 'Desa', 'nama' => 'Awo', 'kode_pos' => '92772'],
                    ['kode' => '7308102006', 'tipe' => 'Desa', 'nama' => 'Cinennung', 'kode_pos' => '92772'],
                    ['kode' => '7308102007', 'tipe' => 'Desa', 'nama' => 'Kanco', 'kode_pos' => '92772'],
                    ['kode' => '7308102008', 'tipe' => 'Desa', 'nama' => 'Kawerang', 'kode_pos' => '92772'],
                    ['kode' => '7308102009', 'tipe' => 'Desa', 'nama' => 'Lompu', 'kode_pos' => '92772'],
                    ['kode' => '7308102010', 'tipe' => 'Desa', 'nama' => 'Padang Loang', 'kode_pos' => '92772'],
                    ['kode' => '7308102011', 'tipe' => 'Desa', 'nama' => 'Walimpong', 'kode_pos' => '92772'],
                ],
            ],
            // 11. Ponre (730811)
            [
                'kecamatan_kode' => '730811',
                'daftar' => [
                    ['kode' => '7308112001', 'tipe' => 'Desa', 'nama' => 'Bolli', 'kode_pos' => '92765'],
                    ['kode' => '7308112002', 'tipe' => 'Desa', 'nama' => 'Mappesangka', 'kode_pos' => '92765'],
                    ['kode' => '7308112003', 'tipe' => 'Desa', 'nama' => 'Mattampae', 'kode_pos' => '92765'],
                    ['kode' => '7308112004', 'tipe' => 'Desa', 'nama' => 'Pattimpa', 'kode_pos' => '92765'],
                    ['kode' => '7308112005', 'tipe' => 'Desa', 'nama' => 'Poleonro', 'kode_pos' => '92765'],
                    ['kode' => '7308112006', 'tipe' => 'Desa', 'nama' => 'Salebba', 'kode_pos' => '92765'],
                    ['kode' => '7308112007', 'tipe' => 'Desa', 'nama' => 'Tellu Boccoe', 'kode_pos' => '92765'],
                    ['kode' => '7308112008', 'tipe' => 'Desa', 'nama' => 'Tompo Bulu', 'kode_pos' => '92765'],
                    ['kode' => '7308112009', 'tipe' => 'Desa', 'nama' => 'Turu Adae', 'kode_pos' => '92765'],
                ],
            ],
            // 12. Lappariaja (730812)
            [
                'kecamatan_kode' => '730812',
                'daftar' => [
                    ['kode' => '7308122001', 'tipe' => 'Desa', 'nama' => 'Lili Riattang', 'kode_pos' => '92763'],
                    ['kode' => '7308122002', 'tipe' => 'Desa', 'nama' => 'Mattiro Walie', 'kode_pos' => '92763'],
                    ['kode' => '7308122003', 'tipe' => 'Desa', 'nama' => 'Pattuku Limpoe', 'kode_pos' => '92763'],
                    ['kode' => '7308122004', 'tipe' => 'Desa', 'nama' => 'Sengeng Palie', 'kode_pos' => '92763'],
                    ['kode' => '7308122005', 'tipe' => 'Desa', 'nama' => 'Tenri Pakkua', 'kode_pos' => '92763'],
                    ['kode' => '7308122006', 'tipe' => 'Desa', 'nama' => 'Tonronge', 'kode_pos' => '92763'],
                    ['kode' => '7308122007', 'tipe' => 'Desa', 'nama' => 'Ujung Lamuru', 'kode_pos' => '92763'],
                    ['kode' => '7308122008', 'tipe' => 'Desa', 'nama' => 'Waekeccee', 'kode_pos' => '92763'],
                    ['kode' => '7308122009', 'tipe' => 'Desa', 'nama' => 'Patangkai', 'kode_pos' => '92763'],
                ],
            ],
            // 13. Lamuru (730813)
            [
                'kecamatan_kode' => '730813',
                'daftar' => [
                    ['kode' => '7308131001', 'tipe' => 'Kelurahan', 'nama' => 'Lalebata', 'kode_pos' => '92762'],
                    ['kode' => '7308132002', 'tipe' => 'Desa', 'nama' => 'Barakkae', 'kode_pos' => '92762'],
                    ['kode' => '7308132003', 'tipe' => 'Desa', 'nama' => 'Barugae', 'kode_pos' => '92762'],
                    ['kode' => '7308132004', 'tipe' => 'Desa', 'nama' => 'Mamminasae', 'kode_pos' => '92762'],
                    ['kode' => '7308132005', 'tipe' => 'Desa', 'nama' => 'Mattampa Bulu', 'kode_pos' => '92762'],
                    ['kode' => '7308132006', 'tipe' => 'Desa', 'nama' => 'Mattampa Walie', 'kode_pos' => '92762'],
                    ['kode' => '7308132007', 'tipe' => 'Desa', 'nama' => 'Padaelo', 'kode_pos' => '92762'],
                    ['kode' => '7308132008', 'tipe' => 'Desa', 'nama' => 'Poleonro', 'kode_pos' => '92762'],
                    ['kode' => '7308132009', 'tipe' => 'Desa', 'nama' => 'Seberang', 'kode_pos' => '92762'],
                    ['kode' => '7308132010', 'tipe' => 'Desa', 'nama' => 'Sengeng Palie', 'kode_pos' => '92762'],
                    ['kode' => '7308132011', 'tipe' => 'Desa', 'nama' => 'Turucinnae', 'kode_pos' => '92762'],
                ],
            ],
            // 14. Ulaweng (730814)
            [
                'kecamatan_kode' => '730814',
                'daftar' => [
                    ['kode' => '7308141001', 'tipe' => 'Kelurahan', 'nama' => 'Cinnong', 'kode_pos' => '92762'],
                    ['kode' => '7308142002', 'tipe' => 'Desa', 'nama' => 'Ausu', 'kode_pos' => '92762'],
                    ['kode' => '7308142003', 'tipe' => 'Desa', 'nama' => 'Cani Sirenreng', 'kode_pos' => '92762'],
                    ['kode' => '7308142004', 'tipe' => 'Desa', 'nama' => 'Galung', 'kode_pos' => '92762'],
                    ['kode' => '7308142005', 'tipe' => 'Desa', 'nama' => 'Jalamancae', 'kode_pos' => '92762'],
                    ['kode' => '7308142006', 'tipe' => 'Desa', 'nama' => 'Lamakkaraseng', 'kode_pos' => '92762'],
                    ['kode' => '7308142007', 'tipe' => 'Desa', 'nama' => 'Lilina Ajangale', 'kode_pos' => '92762'],
                    ['kode' => '7308142008', 'tipe' => 'Desa', 'nama' => 'Manurunge', 'kode_pos' => '92762'],
                    ['kode' => '7308142009', 'tipe' => 'Desa', 'nama' => 'Mula Menree', 'kode_pos' => '92762'],
                    ['kode' => '7308142010', 'tipe' => 'Desa', 'nama' => 'Pallawa Rukka', 'kode_pos' => '92762'],
                    ['kode' => '7308142011', 'tipe' => 'Desa', 'nama' => 'Timusu', 'kode_pos' => '92762'],
                    ['kode' => '7308142012', 'tipe' => 'Desa', 'nama' => 'Tea Musu', 'kode_pos' => '92762'],
                    ['kode' => '7308142013', 'tipe' => 'Desa', 'nama' => 'Tea Malala', 'kode_pos' => '92762'],
                    ['kode' => '7308142014', 'tipe' => 'Desa', 'nama' => 'Tadang Palie', 'kode_pos' => '92762'],
                ],
            ],
            // 15. Palakka (730815)
            [
                'kecamatan_kode' => '730815',
                'daftar' => [
                    ['kode' => '7308152001', 'tipe' => 'Desa', 'nama' => 'Bainang', 'kode_pos' => '92761'],
                    ['kode' => '7308152002', 'tipe' => 'Desa', 'nama' => 'Cinennung', 'kode_pos' => '92761'],
                    ['kode' => '7308152003', 'tipe' => 'Desa', 'nama' => 'Melle', 'kode_pos' => '92761'],
                    ['kode' => '7308152004', 'tipe' => 'Desa', 'nama' => 'Maduri', 'kode_pos' => '92761'],
                    ['kode' => '7308152005', 'tipe' => 'Desa', 'nama' => 'Passippo', 'kode_pos' => '92761'],
                    ['kode' => '7308152006', 'tipe' => 'Desa', 'nama' => 'Siame', 'kode_pos' => '92761'],
                    ['kode' => '7308152007', 'tipe' => 'Desa', 'nama' => 'Tanah Tenga', 'kode_pos' => '92761'],
                    ['kode' => '7308152008', 'tipe' => 'Desa', 'nama' => 'Ureng', 'kode_pos' => '92761'],
                    ['kode' => '7308152009', 'tipe' => 'Desa', 'nama' => 'Usmanu', 'kode_pos' => '92761'],
                ],
            ],
            // 16. Awangpone (730816)
            [
                'kecamatan_kode' => '730816',
                'daftar' => [
                    ['kode' => '7308162001', 'tipe' => 'Desa', 'nama' => 'Abbanuange', 'kode_pos' => '92751'],
                    ['kode' => '7308162002', 'tipe' => 'Desa', 'nama' => 'Awolagading', 'kode_pos' => '92751'],
                    ['kode' => '7308162003', 'tipe' => 'Desa', 'nama' => 'Kading', 'kode_pos' => '92751'],
                    ['kode' => '7308162004', 'tipe' => 'Desa', 'nama' => 'Lappo Ase', 'kode_pos' => '92751'],
                    ['kode' => '7308162005', 'tipe' => 'Desa', 'nama' => 'Maccope', 'kode_pos' => '92751'],
                    ['kode' => '7308162006', 'tipe' => 'Desa', 'nama' => 'Matuju', 'kode_pos' => '92751'],
                    ['kode' => '7308162007', 'tipe' => 'Desa', 'nama' => 'Paccing', 'kode_pos' => '92751'],
                ],
            ],
            // 17. Tellu Siattinge (730817)
            [
                'kecamatan_kode' => '730817',
                'daftar' => [
                    ['kode' => '7308171001', 'tipe' => 'Kelurahan', 'nama' => 'Tokaseng', 'kode_pos' => '92752'],
                    ['kode' => '7308172002', 'tipe' => 'Desa', 'nama' => 'Ajjalireng', 'kode_pos' => '92752'],
                    ['kode' => '7308172003', 'tipe' => 'Desa', 'nama' => 'Itterung', 'kode_pos' => '92752'],
                    ['kode' => '7308172004', 'tipe' => 'Desa', 'nama' => 'Lamuru', 'kode_pos' => '92752'],
                    ['kode' => '7308172005', 'tipe' => 'Desa', 'nama' => 'Lanca', 'kode_pos' => '92752'],
                    ['kode' => '7308172006', 'tipe' => 'Desa', 'nama' => 'Mattoanging', 'kode_pos' => '92752'],
                    ['kode' => '7308172007', 'tipe' => 'Desa', 'nama' => 'Padaelo', 'kode_pos' => '92752'],
                    ['kode' => '7308172008', 'tipe' => 'Desa', 'nama' => 'Palongki', 'kode_pos' => '92752'],
                    ['kode' => '7308172009', 'tipe' => 'Desa', 'nama' => 'Patangnga', 'kode_pos' => '92752'],
                ],
            ],
            // 18. Ajangale (730818)
            [
                'kecamatan_kode' => '730818',
                'daftar' => [
                    ['kode' => '7308181001', 'tipe' => 'Kelurahan', 'nama' => 'Pompanua', 'kode_pos' => '92755'],
                    ['kode' => '7308181002', 'tipe' => 'Kelurahan', 'nama' => 'Pompanua Riattang', 'kode_pos' => '92755'],
                    ['kode' => '7308182003', 'tipe' => 'Desa', 'nama' => 'Welado', 'kode_pos' => '92755'],
                    ['kode' => '7308182004', 'tipe' => 'Desa', 'nama' => 'Pinceng Pute', 'kode_pos' => '92755'],
                    ['kode' => '7308182005', 'tipe' => 'Desa', 'nama' => 'Opo', 'kode_pos' => '92755'],
                    ['kode' => '7308182006', 'tipe' => 'Desa', 'nama' => 'Labissa', 'kode_pos' => '92755'],
                    ['kode' => '7308182007', 'tipe' => 'Desa', 'nama' => 'Timurung', 'kode_pos' => '92755'],
                    ['kode' => '7308182008', 'tipe' => 'Desa', 'nama' => 'Leppangeng', 'kode_pos' => '92755'],
                    ['kode' => '7308182009', 'tipe' => 'Desa', 'nama' => 'Allamungeng Patue', 'kode_pos' => '92755'],
                    ['kode' => '7308182010', 'tipe' => 'Desa', 'nama' => 'Amessangeng', 'kode_pos' => '92755'],
                    ['kode' => '7308182011', 'tipe' => 'Desa', 'nama' => 'Lebbae', 'kode_pos' => '92755'],
                    ['kode' => '7308182012', 'tipe' => 'Desa', 'nama' => 'Manciri', 'kode_pos' => '92755'],
                    ['kode' => '7308182013', 'tipe' => 'Desa', 'nama' => 'Telle', 'kode_pos' => '92755'],
                    ['kode' => '7308182014', 'tipe' => 'Desa', 'nama' => 'Pacciro', 'kode_pos' => '92755'],
                ],
            ],
            // 19. Dua Boccoe (730819)
            [
                'kecamatan_kode' => '730819',
                'daftar' => [
                    ['kode' => '7308191001', 'tipe' => 'Kelurahan', 'nama' => 'Uloe', 'kode_pos' => '92753'],
                    ['kode' => '7308192002', 'tipe' => 'Desa', 'nama' => 'Cabbeng', 'kode_pos' => '92753'],
                    ['kode' => '7308192003', 'tipe' => 'Desa', 'nama' => 'Lallatang', 'kode_pos' => '92753'],
                    ['kode' => '7308192004', 'tipe' => 'Desa', 'nama' => 'Mario', 'kode_pos' => '92753'],
                    ['kode' => '7308192005', 'tipe' => 'Desa', 'nama' => 'Pakkasalo', 'kode_pos' => '92753'],
                    ['kode' => '7308192006', 'tipe' => 'Desa', 'nama' => 'Sailong', 'kode_pos' => '92753'],
                    ['kode' => '7308192007', 'tipe' => 'Desa', 'nama' => 'Sanrangeng', 'kode_pos' => '92753'],
                    ['kode' => '7308192008', 'tipe' => 'Desa', 'nama' => 'Solo', 'kode_pos' => '92753'],
                    ['kode' => '7308192009', 'tipe' => 'Desa', 'nama' => 'Tawaroe', 'kode_pos' => '92753'],
                    ['kode' => '7308192010', 'tipe' => 'Desa', 'nama' => 'Tocina', 'kode_pos' => '92753'],
                ],
            ],
            // 20. Cenrana (730820)
            [
                'kecamatan_kode' => '730820',
                'daftar' => [
                    ['kode' => '7308201001', 'tipe' => 'Kelurahan', 'nama' => 'Cenrana', 'kode_pos' => '92754'],
                    ['kode' => '7308202002', 'tipe' => 'Desa', 'nama' => 'Awolagading', 'kode_pos' => '92754'],
                    ['kode' => '7308202003', 'tipe' => 'Desa', 'nama' => 'Cakkaware', 'kode_pos' => '92754'],
                    ['kode' => '7308202004', 'tipe' => 'Desa', 'nama' => 'Labotto', 'kode_pos' => '92754'],
                    ['kode' => '7308202005', 'tipe' => 'Desa', 'nama' => 'Laoni', 'kode_pos' => '92754'],
                    ['kode' => '7308202006', 'tipe' => 'Desa', 'nama' => 'Latonro', 'kode_pos' => '92754'],
                    ['kode' => '7308202007', 'tipe' => 'Desa', 'nama' => 'Nagauleng', 'kode_pos' => '92754'],
                    ['kode' => '7308202008', 'tipe' => 'Desa', 'nama' => 'Pacubbe', 'kode_pos' => '92754'],
                    ['kode' => '7308202009', 'tipe' => 'Desa', 'nama' => 'Pallae', 'kode_pos' => '92754'],
                    ['kode' => '7308202010', 'tipe' => 'Desa', 'nama' => 'Pallime', 'kode_pos' => '92754'],
                    ['kode' => '7308202011', 'tipe' => 'Desa', 'nama' => 'Panyiwi', 'kode_pos' => '92754'],
                    ['kode' => '7308202012', 'tipe' => 'Desa', 'nama' => 'Pusung', 'kode_pos' => '92754'],
                    ['kode' => '7308202013', 'tipe' => 'Desa', 'nama' => 'Watang Ta', 'kode_pos' => '92754'],
                    ['kode' => '7308202014', 'tipe' => 'Desa', 'nama' => 'Watu', 'kode_pos' => '92754'],
                    ['kode' => '7308202015', 'tipe' => 'Desa', 'nama' => 'Ujung Tanah', 'kode_pos' => '92754'],
                ],
            ],
            // 21. Tanete Riattang (730821)
            [
                'kecamatan_kode' => '730821',
                'daftar' => [
                    ['kode' => '7308211001', 'tipe' => 'Kelurahan', 'nama' => 'Biru', 'kode_pos' => '92711'],
                    ['kode' => '7308211002', 'tipe' => 'Kelurahan', 'nama' => 'Bukaka', 'kode_pos' => '92716'],
                    ['kode' => '7308211003', 'tipe' => 'Kelurahan', 'nama' => 'Masumpu', 'kode_pos' => '92718'],
                    ['kode' => '7308211004', 'tipe' => 'Kelurahan', 'nama' => 'Manurunge', 'kode_pos' => '92712'],
                    ['kode' => '7308211005', 'tipe' => 'Kelurahan', 'nama' => 'Pappolo', 'kode_pos' => '92717'],
                    ['kode' => '7308211006', 'tipe' => 'Kelurahan', 'nama' => 'Ta', 'kode_pos' => '92713'],
                    ['kode' => '7308211007', 'tipe' => 'Kelurahan', 'nama' => 'Walannae', 'kode_pos' => '92715'],
                    ['kode' => '7308211008', 'tipe' => 'Kelurahan', 'nama' => 'Watampone', 'kode_pos' => '92711'],
                ],
            ],
            // 22. Tanete Riattang Barat (730822)
            [
                'kecamatan_kode' => '730822',
                'daftar' => [
                    ['kode' => '7308221001', 'tipe' => 'Kelurahan', 'nama' => 'Bulu Tempe', 'kode_pos' => '92731'],
                    ['kode' => '7308221002', 'tipe' => 'Kelurahan', 'nama' => 'Jeppe\'e', 'kode_pos' => '92733'],
                    ['kode' => '7308221003', 'tipe' => 'Kelurahan', 'nama' => 'Macanang', 'kode_pos' => '92732'],
                    ['kode' => '7308221004', 'tipe' => 'Kelurahan', 'nama' => 'Majang', 'kode_pos' => '92734'],
                    ['kode' => '7308221005', 'tipe' => 'Kelurahan', 'nama' => 'Mattiro Walie', 'kode_pos' => '92735'],
                    ['kode' => '7308221006', 'tipe' => 'Kelurahan', 'nama' => 'Polewali', 'kode_pos' => '92735'],
                    ['kode' => '7308221007', 'tipe' => 'Kelurahan', 'nama' => 'Tibojong', 'kode_pos' => '92736'],
                    ['kode' => '7308221008', 'tipe' => 'Kelurahan', 'nama' => 'Watang Palakka', 'kode_pos' => '92734'],
                ],
            ],
            // 23. Tanete Riattang Timur (730823)
            [
                'kecamatan_kode' => '730823',
                'daftar' => [
                    ['kode' => '7308231001', 'tipe' => 'Kelurahan', 'nama' => 'Bajoe', 'kode_pos' => '92721'],
                    ['kode' => '7308231002', 'tipe' => 'Kelurahan', 'nama' => 'Cellu', 'kode_pos' => '92723'],
                    ['kode' => '7308231003', 'tipe' => 'Kelurahan', 'nama' => 'Lonrae', 'kode_pos' => '92725'],
                    ['kode' => '7308231004', 'tipe' => 'Kelurahan', 'nama' => 'Panyula', 'kode_pos' => '92722'],
                    ['kode' => '7308231005', 'tipe' => 'Kelurahan', 'nama' => 'Pallette', 'kode_pos' => '92726'],
                    ['kode' => '7308231006', 'tipe' => 'Kelurahan', 'nama' => 'Toro', 'kode_pos' => '92724'],
                    ['kode' => '7308231007', 'tipe' => 'Kelurahan', 'nama' => 'Waetuo', 'kode_pos' => '92727'],
                    ['kode' => '7308231008', 'tipe' => 'Kelurahan', 'nama' => 'Rompe', 'kode_pos' => '92728'],
                ],
            ],
            // 24. Amali (730824)
            [
                'kecamatan_kode' => '730824',
                'daftar' => [
                    ['kode' => '7308241001', 'tipe' => 'Kelurahan', 'nama' => 'Mampotu', 'kode_pos' => '92764'],
                    ['kode' => '7308242002', 'tipe' => 'Desa', 'nama' => 'Ajanglaleng', 'kode_pos' => '92764'],
                    ['kode' => '7308242003', 'tipe' => 'Desa', 'nama' => 'Amali Riattang', 'kode_pos' => '92764'],
                    ['kode' => '7308242004', 'tipe' => 'Desa', 'nama' => 'Benteng Tellue', 'kode_pos' => '92764'],
                    ['kode' => '7308242005', 'tipe' => 'Desa', 'nama' => 'Bila', 'kode_pos' => '92764'],
                    ['kode' => '7308242006', 'tipe' => 'Desa', 'nama' => 'Laponrong', 'kode_pos' => '92764'],
                    ['kode' => '7308242007', 'tipe' => 'Desa', 'nama' => 'Mattaro Purae', 'kode_pos' => '92764'],
                    ['kode' => '7308242008', 'tipe' => 'Desa', 'nama' => 'Tacipong', 'kode_pos' => '92764'],
                    ['kode' => '7308242009', 'tipe' => 'Desa', 'nama' => 'Taccipi', 'kode_pos' => '92764'],
                    ['kode' => '7308242010', 'tipe' => 'Desa', 'nama' => 'Ta\'dung Lawo', 'kode_pos' => '92764'],
                    ['kode' => '7308242011', 'tipe' => 'Desa', 'nama' => 'Ulaweng Riaja', 'kode_pos' => '92764'],
                    ['kode' => '7308242012', 'tipe' => 'Desa', 'nama' => 'Wellalang', 'kode_pos' => '92764'],
                ],
            ],
            // 25. Tellu Limpoe (730825)
            [
                'kecamatan_kode' => '730825',
                'daftar' => [
                    ['kode' => '7308252001', 'tipe' => 'Desa', 'nama' => 'Batu Putih', 'kode_pos' => '92767'],
                    ['kode' => '7308252002', 'tipe' => 'Desa', 'nama' => 'Bonto Masunggu', 'kode_pos' => '92767'],
                    ['kode' => '7308252003', 'tipe' => 'Desa', 'nama' => 'Gaya Baru', 'kode_pos' => '92767'],
                    ['kode' => '7308252004', 'tipe' => 'Desa', 'nama' => 'Lagori', 'kode_pos' => '92767'],
                    ['kode' => '7308252005', 'tipe' => 'Desa', 'nama' => 'Pallawa', 'kode_pos' => '92767'],
                    ['kode' => '7308252006', 'tipe' => 'Desa', 'nama' => 'Polewali', 'kode_pos' => '92767'],
                    ['kode' => '7308252007', 'tipe' => 'Desa', 'nama' => 'Sadar', 'kode_pos' => '92767'],
                    ['kode' => '7308252008', 'tipe' => 'Desa', 'nama' => 'Samaturue', 'kode_pos' => '92767'],
                    ['kode' => '7308252009', 'tipe' => 'Desa', 'nama' => 'Tapong', 'kode_pos' => '92767'],
                    ['kode' => '7308252010', 'tipe' => 'Desa', 'nama' => 'Tellang Kere', 'kode_pos' => '92767'],
                    ['kode' => '7308252011', 'tipe' => 'Desa', 'nama' => 'Tondong', 'kode_pos' => '92767'],
                ],
            ],
            // 26. Bengo (730826)
            [
                'kecamatan_kode' => '730826',
                'daftar' => [
                    ['kode' => '7308262001', 'tipe' => 'Desa', 'nama' => 'Bengo', 'kode_pos' => '92763'],
                    ['kode' => '7308262002', 'tipe' => 'Desa', 'nama' => 'Bulu Allaporenge', 'kode_pos' => '92763'],
                    ['kode' => '7308262003', 'tipe' => 'Desa', 'nama' => 'Lili Riawang', 'kode_pos' => '92763'],
                    ['kode' => '7308262004', 'tipe' => 'Desa', 'nama' => 'Mattaropocci', 'kode_pos' => '92763'],
                    ['kode' => '7308262005', 'tipe' => 'Desa', 'nama' => 'Selli', 'kode_pos' => '92763'],
                    ['kode' => '7308262006', 'tipe' => 'Desa', 'nama' => 'Samaenre', 'kode_pos' => '92763'],
                    ['kode' => '7308262007', 'tipe' => 'Desa', 'nama' => 'Tungke', 'kode_pos' => '92763'],
                    ['kode' => '7308262008', 'tipe' => 'Desa', 'nama' => 'Walimpong', 'kode_pos' => '92763'],
                ],
            ],
            // 27. Patimpeng (730827)
            [
                'kecamatan_kode' => '730827',
                'daftar' => [
                    ['kode' => '7308272001', 'tipe' => 'Desa', 'nama' => 'Batulappa', 'kode_pos' => '92774'],
                    ['kode' => '7308272002', 'tipe' => 'Desa', 'nama' => 'Bulu Ulaweng', 'kode_pos' => '92774'],
                    ['kode' => '7308272003', 'tipe' => 'Desa', 'nama' => 'Masago', 'kode_pos' => '92774'],
                    ['kode' => '7308272004', 'tipe' => 'Desa', 'nama' => 'Patimpeng', 'kode_pos' => '92774'],
                    ['kode' => '7308272005', 'tipe' => 'Desa', 'nama' => 'Pationgi', 'kode_pos' => '92774'],
                    ['kode' => '7308272006', 'tipe' => 'Desa', 'nama' => 'Poleonro', 'kode_pos' => '92774'],
                    ['kode' => '7308272007', 'tipe' => 'Desa', 'nama' => 'Suka Maju', 'kode_pos' => '92774'],
                    ['kode' => '7308272008', 'tipe' => 'Desa', 'nama' => 'Talabangi', 'kode_pos' => '92774'],
                    ['kode' => '7308272009', 'tipe' => 'Desa', 'nama' => 'Tompobulu', 'kode_pos' => '92774'],
                    ['kode' => '7308272010', 'tipe' => 'Desa', 'nama' => 'Madello', 'kode_pos' => '92774'],
                ],
            ],
        ];

        foreach ($dataWilayah as $grup) {
            $kecamatan = Kecamatan::where('kode', $grup['kecamatan_kode'])->first();
            if (! $kecamatan) {
                continue;
            }

            foreach ($grup['daftar'] as $item) {
                Kelurahan::updateOrCreate(
                    ['kode' => $item['kode']],
                    [
                        'kecamatan_id' => $kecamatan->id,
                        'tipe' => $item['tipe'],
                        'nama' => $item['nama'],
                        'kode_pos' => $item['kode_pos'],
                        'status' => true,
                    ]
                );
            }
        }
    }
}
