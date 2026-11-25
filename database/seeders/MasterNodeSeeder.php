<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Module;
use App\Models\SubMenu;
use Illuminate\Database\Seeder;

class MasterNodeSeeder extends Seeder
{
    /**
     * Seed baseline data khusus node Portal Master (Super Admin).
     */
    public function run(): void
    {
        $this->call([
            SuperAdminSeeder::class,
            SystemMenuSeeder::class,
        ]);

        // Pastikan modul Pusat Kontrol Tenant tersedia di sidebar Master
        $masterModule = Module::firstOrCreate(
            ['name' => 'PUSAT KONTROL TENANT'],
            [
                'order' => 2,
                'is_active' => true,
            ]
        );

        $monitoringMenu = Menu::firstOrCreate(
            [
                'module_id' => $masterModule->id,
                'name' => 'Monitoring Sekolah',
            ],
            [
                'route_name' => null,
                'icon' => 'activity',
                'order' => 1,
                'is_active' => true,
                'is_master_enabled' => true,
                'dev_progress' => 100,
                'dev_stage' => 'done',
            ]
        );

        SubMenu::firstOrCreate(
            [
                'menu_id' => $monitoringMenu->id,
                'name' => 'Daftar & Perkembangan Sekolah',
            ],
            [
                'route_name' => 'master-control.nodes.index',
                'order' => 1,
                'is_active' => true,
                'is_master_enabled' => true,
                'dev_progress' => 100,
                'dev_stage' => 'done',
            ]
        );

        SubMenu::firstOrCreate(
            [
                'menu_id' => $monitoringMenu->id,
                'name' => 'Status Server & Database',
            ],
            [
                'route_name' => 'master-control.server-status.index',
                'order' => 2,
                'is_active' => true,
                'is_master_enabled' => true,
                'dev_progress' => 100,
                'dev_stage' => 'done',
            ]
        );
    }
}
