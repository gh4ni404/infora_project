<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuAccessTemplate;
use App\Models\Module;
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
            // Kategori Administrator Sekolah
            [
                'role_key' => 'admin_sekolah',
                'role_name' => 'Admin Sekolah',
                'role_category' => 'admin',
            ],
            // Kategori Guru dan Tenaga Pendidik
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
            // Kategori Staf dan Tata Usaha
            [
                'role_key' => 'staf_tu',
                'role_name' => 'Staf Tata Usaha',
                'role_category' => 'staf',
            ],
            // Kategori Murid
            [
                'role_key' => 'murid_reguler',
                'role_name' => 'Murid Reguler (Kelas 10-11)',
                'role_category' => 'murid',
            ],
            [
                'role_key' => 'murid_pkl',
                'role_name' => 'Murid PKL (SMK Kelas 12)',
                'role_category' => 'murid',
            ],
        ];

        foreach ($templates as $tmpl) {
            if ($tmpl['role_key'] === 'admin_sekolah') {
                // Berikan akses operasional sekolah penuh untuk Admin Sekolah (semua modul KECUALI PENGATURAN SISTEM)
                $operationalModules = Module::query()
                    ->where('name', '!=', 'PENGATURAN SISTEM')
                    ->with(['menus.subMenus'])
                    ->get();

                $assignedAny = false;

                if ($dashboardMenu) {
                    MenuAccessTemplate::updateOrCreate(
                        [
                            'role_key' => 'admin_sekolah',
                            'menu_id' => $dashboardMenu->id,
                            'sub_menu_id' => null,
                        ],
                        [
                            'role_name' => 'Admin Sekolah',
                            'role_category' => 'admin',
                            'can_view' => true,
                            'can_create' => true,
                            'can_edit' => true,
                            'can_delete' => true,
                        ]
                    );
                    $assignedAny = true;
                }

                foreach ($operationalModules as $module) {
                    foreach ($module->menus as $menu) {
                        if ($menu->subMenus->isNotEmpty()) {
                            foreach ($menu->subMenus as $subMenu) {
                                MenuAccessTemplate::updateOrCreate(
                                    [
                                        'role_key' => 'admin_sekolah',
                                        'menu_id' => $menu->id,
                                        'sub_menu_id' => $subMenu->id,
                                    ],
                                    [
                                        'role_name' => 'Admin Sekolah',
                                        'role_category' => 'admin',
                                        'can_view' => true,
                                        'can_create' => true,
                                        'can_edit' => true,
                                        'can_delete' => true,
                                    ]
                                );
                                $assignedAny = true;
                            }
                        } else {
                            MenuAccessTemplate::updateOrCreate(
                                [
                                    'role_key' => 'admin_sekolah',
                                    'menu_id' => $menu->id,
                                    'sub_menu_id' => null,
                                ],
                                [
                                    'role_name' => 'Admin Sekolah',
                                    'role_category' => 'admin',
                                    'can_view' => true,
                                    'can_create' => true,
                                    'can_edit' => true,
                                    'can_delete' => true,
                                ]
                            );
                            $assignedAny = true;
                        }
                    }
                }

                if (! $assignedAny) {
                    MenuAccessTemplate::updateOrCreate(
                        [
                            'role_key' => 'admin_sekolah',
                        ],
                        [
                            'role_name' => 'Admin Sekolah',
                            'role_category' => 'admin',
                            'can_view' => false,
                            'can_create' => false,
                            'can_edit' => false,
                            'can_delete' => false,
                        ]
                    );
                }
            } else {
                // Berikan izin bawaan melihat Dashboard untuk template non-admin
                if ($dashboardMenu) {
                    MenuAccessTemplate::updateOrCreate(
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
                    MenuAccessTemplate::updateOrCreate(
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
}
