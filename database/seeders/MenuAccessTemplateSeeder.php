<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuAccessTemplate;
use Illuminate\Database\Seeder;

class MenuAccessTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds for initial role access templates.
     */
    public function run(): void
    {
        $dashboardMenu = Menu::where('route_name', 'dashboard')->first();

        $templates = [
            // Kategori Guru & Tenaga Pendidik
            [
                'role_key' => 'guru_pengajar',
                'role_name' => 'Guru Pengajar',
                'role_category' => 'guru',
            ],
            [
                'role_key' => 'wali_kelas',
                'role_name' => 'Wali Kelas',
                'role_category' => 'guru',
            ],
            [
                'role_key' => 'wakasek_kurikulum',
                'role_name' => 'Wakil Kepala Sekolah - Kurikulum',
                'role_category' => 'guru',
            ],
            [
                'role_key' => 'wakasek_kesiswaan',
                'role_name' => 'Wakil Kepala Sekolah - Kesiswaan',
                'role_category' => 'guru',
            ],
            // Kategori Staf & Tata Usaha
            [
                'role_key' => 'staf_tu',
                'role_name' => 'Staf Tata Usaha',
                'role_category' => 'staf',
            ],
            // Kategori Siswa
            [
                'role_key' => 'siswa_reguler',
                'role_name' => 'Siswa Reguler (Kelas 10-11)',
                'role_category' => 'siswa',
            ],
            [
                'role_key' => 'siswa_pkl',
                'role_name' => 'Siswa PKL (SMK Kelas 12)',
                'role_category' => 'siswa',
            ],
        ];

        foreach ($templates as $tmpl) {
            // Berikan izin bawaan melihat Dashboard untuk seluruh template
            if ($dashboardMenu) {
                MenuAccessTemplate::firstOrCreate(
                    [
                        'role_key' => $tmpl['role_key'],
                        'menu_id' => $dashboardMenu->id,
                        'sub_menu_id' => null,
                    ],
                    [
                        'role_name' => $tmpl['role_name'],
                        'role_category' => $tmpl['role_category'],
                        'can_view' => true,
                        'can_create' => false,
                        'can_edit' => false,
                        'can_delete' => false,
                    ]
                );
            } else {
                MenuAccessTemplate::firstOrCreate(
                    [
                        'role_key' => $tmpl['role_key'],
                    ],
                    [
                        'role_name' => $tmpl['role_name'],
                        'role_category' => $tmpl['role_category'],
                        'can_view' => false,
                        'can_create' => false,
                        'can_edit' => false,
                        'can_delete' => false,
                    ]
                );
            }
        }
    }
}
