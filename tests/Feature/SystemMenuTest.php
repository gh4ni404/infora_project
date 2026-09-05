<?php

use App\Models\Menu;
use App\Models\Module;
use App\Models\SubMenu;
use Database\Seeders\SystemMenuSeeder;

test('system menu seeder seeds baseline modules, sistem menu, and 3 sub-menus', function () {
    $this->seed(SystemMenuSeeder::class);

    $this->assertDatabaseHas('modules', [
        'name' => 'Tata Kelola Sistem',
        'is_active' => true,
    ]);

    $this->assertDatabaseHas('menus', [
        'name' => 'Sistem',
        'icon' => 'settings',
    ]);

    $this->assertDatabaseHas('sub_menus', [
        'name' => 'Modul',
        'route_name' => 'system.modules.index',
    ]);

    $this->assertDatabaseHas('sub_menus', [
        'name' => 'Menu',
        'route_name' => 'system.menus.index',
    ]);

    $this->assertDatabaseHas('sub_menus', [
        'name' => 'Sub-Menu',
        'route_name' => 'system.sub-menus.index',
    ]);

    $this->assertDatabaseHas('menus', [
        'name' => 'Dashboard',
        'route_name' => 'dashboard',
    ]);
});

test('module, menu, and sub_menu relationships function properly', function () {
    $module = Module::factory()->create(['name' => 'Akademik', 'order' => 1]);
    $menu = Menu::factory()->create([
        'module_id' => $module->id,
        'name' => 'Data Siswa',
        'order' => 1,
    ]);
    $subMenu = SubMenu::factory()->create([
        'menu_id' => $menu->id,
        'name' => 'Siswa Aktif',
        'order' => 1,
    ]);

    expect($module->menus)->toHaveCount(1)
        ->and($module->menus->first()->id)->toBe($menu->id)
        ->and($menu->module->id)->toBe($module->id)
        ->and($menu->subMenus)->toHaveCount(1)
        ->and($menu->subMenus->first()->id)->toBe($subMenu->id)
        ->and($subMenu->menu->id)->toBe($menu->id);
});

test('menus and sub_menus do not have unique constraints blocking duplicate names', function () {
    $module1 = Module::create(['name' => 'Modul A', 'order' => 1]);
    $module2 = Module::create(['name' => 'Modul B', 'order' => 2]);

    $menu1 = Menu::create([
        'module_id' => $module1->id,
        'name' => 'Dokumen',
        'order' => 1,
    ]);

    $menu2 = Menu::create([
        'module_id' => $module2->id,
        'name' => 'Dokumen',
        'order' => 1,
    ]);

    $sub1 = SubMenu::create([
        'menu_id' => $menu1->id,
        'name' => 'Daftar',
        'order' => 1,
    ]);

    $sub2 = SubMenu::create([
        'menu_id' => $menu2->id,
        'name' => 'Daftar',
        'order' => 1,
    ]);

    expect($menu1->name)->toBe('Dokumen')
        ->and($menu2->name)->toBe('Dokumen')
        ->and($sub1->name)->toBe('Daftar')
        ->and($sub2->name)->toBe('Daftar');
});

test('deleting a module cascades delete to its menus and sub_menus', function () {
    $module = Module::factory()->create();
    $menu = Menu::factory()->create(['module_id' => $module->id]);
    $subMenu = SubMenu::factory()->create(['menu_id' => $menu->id]);

    $module->delete();

    $this->assertDatabaseMissing('modules', ['id' => $module->id]);
    $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    $this->assertDatabaseMissing('sub_menus', ['id' => $subMenu->id]);
});
