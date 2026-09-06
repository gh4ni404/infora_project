<?php

use App\Models\Menu;
use App\Models\Module;
use App\Models\SubMenu;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('database seeder seeds baseline modules, menus, and backup & restore direct menu', function () {
    $this->seed(DatabaseSeeder::class);

    // 1. Verify Super Admin is seeded
    $superAdmin = User::where('username', 'superadmin')->first();
    expect($superAdmin)->not()->toBeNull()
        ->and($superAdmin->isSuperAdmin())->toBeTrue();

    // 2. Verify Module: NAVIGASI UTAMA & Dashboard Menu
    $mainModule = Module::where('name', 'NAVIGASI UTAMA')->first();
    expect($mainModule)->not()->toBeNull();

    $dashboardMenu = Menu::where('module_id', $mainModule->id)->where('name', 'Dashboard')->first();
    expect($dashboardMenu)->not()->toBeNull()
        ->and($dashboardMenu->route_name)->toBe('dashboard')
        ->and($dashboardMenu->route_url)->toBe(route('dashboard'));

    // 3. Verify Module: PENGATURAN SISTEM & Sistem Accordion Menu
    $systemModule = Module::where('name', 'PENGATURAN SISTEM')->first();
    expect($systemModule)->not()->toBeNull();

    $systemMenu = Menu::where('module_id', $systemModule->id)->where('name', 'Sistem')->first();
    expect($systemMenu)->not()->toBeNull()
        ->and($systemMenu->route_name)->toBeNull();

    // Verify 3 baseline sub-menus under Sistem
    $subMenus = SubMenu::where('menu_id', $systemMenu->id)->orderBy('order')->get();
    expect($subMenus)->toHaveCount(3);
    expect($subMenus->pluck('name')->toArray())->toBe(['Modul', 'Menu', 'Sub-Menu']);

    // 4. Verify Baseline Direct Menu: Backup & Restore
    $backupMenu = Menu::where('module_id', $systemModule->id)->where('name', 'Backup & Restore')->first();
    expect($backupMenu)->not()->toBeNull()
        ->and($backupMenu->route_name)->toBe('backup-restore')
        ->and($backupMenu->icon)->toBe('folder')
        ->and($backupMenu->order)->toBe(2)
        ->and($backupMenu->is_active)->toBeTrue()
        ->and($backupMenu->route_url)->toBe(route('backup-restore'));
});
