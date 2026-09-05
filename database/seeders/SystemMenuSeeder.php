<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Module;
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
                'icon' => 'compass',
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

        // 2. Module: Tata Kelola Sistem (3 Menu Dasar)
        $systemModule = Module::firstOrCreate(
            ['name' => 'Tata Kelola Sistem'],
            [
                'icon' => 'shield-check',
                'order' => 1,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            [
                'module_id' => $systemModule->id,
                'name' => 'Modul',
            ],
            [
                'route_name' => 'system.modules.index',
                'icon' => 'layers',
                'order' => 1,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            [
                'module_id' => $systemModule->id,
                'name' => 'Menu',
            ],
            [
                'route_name' => 'system.menus.index',
                'icon' => 'menu',
                'order' => 2,
                'is_active' => true,
            ]
        );

        Menu::firstOrCreate(
            [
                'module_id' => $systemModule->id,
                'name' => 'Sub Menu',
            ],
            [
                'route_name' => 'system.sub-menus.index',
                'icon' => 'list-tree',
                'order' => 3,
                'is_active' => true,
            ]
        );
    }
}
