<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Module;
use App\Models\SubMenu;
use Illuminate\Database\Seeder;

class SystemMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Module: Navigasi Utama
        $mainModule = Module::firstOrCreate(
            ['name' => 'Navigasi Utama'],
            [
                'order' => 0,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            [
                'module_id' => $mainModule->id,
                'name' => 'Dashboard',
            ],
            [
                'route_name' => 'dashboard',
                'icon' => 'layout-dashboard',
                'order' => 0,
                'is_active' => true,
            ]
        );

        // 2. Module: Pengaturan Sistem
        $systemModule = Module::firstOrCreate(
            ['name' => 'Pengaturan Sistem'],
            [
                'order' => 1,
                'is_active' => true,
            ]
        );

        // Parent Accordion Menu: Sistem
        $systemMenu = Menu::firstOrCreate(
            [
                'module_id' => $systemModule->id,
                'name' => 'Sistem',
            ],
            [
                'route_name' => null,
                'icon' => 'settings',
                'order' => 1,
                'is_active' => true,
            ]
        );

        // 3 Baseline Sub-Menus under "Sistem"
        SubMenu::firstOrCreate(
            [
                'menu_id' => $systemMenu->id,
                'name' => 'Modul',
            ],
            [
                'route_name' => 'system.modules.index',
                'order' => 1,
                'is_active' => true,
            ]
        );

        SubMenu::firstOrCreate(
            [
                'menu_id' => $systemMenu->id,
                'name' => 'Menu',
            ],
            [
                'route_name' => 'system.menus.index',
                'order' => 2,
                'is_active' => true,
            ]
        );

        SubMenu::firstOrCreate(
            [
                'menu_id' => $systemMenu->id,
                'name' => 'Sub-Menu',
            ],
            [
                'route_name' => 'system.sub-menus.index',
                'order' => 3,
                'is_active' => true,
            ]
        );
    }
}
