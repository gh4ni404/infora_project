<?php

use App\Models\Menu;
use App\Models\Module;
use App\Models\SubMenu;
use App\Models\User;
use Database\Seeders\SystemMenuSeeder;

beforeEach(function () {
    $this->user = User::factory()->create([
        'username' => 'superadmin_sidebar',
        'email' => 'superadmin_sidebar@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);
});

test('sidebar renders seeded baseline modules and menus dynamically', function () {
    $this->seed(SystemMenuSeeder::class);

    $response = $this->actingAs($this->user)->get('/dashboard');

    $response->assertOk();
    $response->assertSee('Navigasi Utama');
    $response->assertSee('Dashboard');
    $response->assertSee('Tata Kelola Sistem');
    $response->assertSee('Sistem');
    $response->assertSee('Modul');
    $response->assertSee('Menu');
    $response->assertSee('Sub-Menu');
    $response->assertSee('nav-count-badge');

    // Baseline system menus are now registered and link to their backend routes
    $response->assertSee(route('system.modules.index'));
    $response->assertSee(route('system.menus.index'));
    $response->assertSee(route('system.sub-menus.index'));

    // Verify unregistered routes fall back safely to '#'
    $systemModule = Module::where('name', 'Tata Kelola Sistem')->first();
    Menu::create([
        'module_id' => $systemModule->id,
        'name' => 'Menu Tanpa Rute',
        'route_name' => 'unregistered.dummy.route',
        'order' => 99,
        'is_active' => true,
    ]);

    $responseWithDummy = $this->actingAs($this->user)->get('/dashboard');
    $responseWithDummy->assertSee('href="#"', false);
});

test('sidebar correctly renders accordion group when a menu has sub-menus', function () {
    $module = Module::create([
        'name' => 'Akademik & Pembelajaran',
        'order' => 10,
        'is_active' => true,
    ]);

    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Kurikulum',
        'icon' => 'layers',
        'order' => 1,
        'is_active' => true,
    ]);

    SubMenu::create([
        'menu_id' => $menu->id,
        'name' => 'Silabus & RPP',
        'route_name' => 'dashboard', // Existing route for testing
        'order' => 1,
        'is_active' => true,
    ]);

    SubMenu::create([
        'menu_id' => $menu->id,
        'name' => 'Jadwal Pelajaran',
        'route_name' => 'nonexistent.route',
        'order' => 2,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->get('/dashboard');

    $response->assertOk();
    $response->assertSee('Akademik & Pembelajaran');
    $response->assertSee('Kurikulum');
    $response->assertSee('Silabus & RPP');
    $response->assertSee('Jadwal Pelajaran');
    $response->assertSee('nav-group-item', false);
    $response->assertSee('nav-group-trigger', false);
    $response->assertSee('nav-submenu-list', false);
    $response->assertSee('nav-submenu-item', false);
    $response->assertDontSee('nav-submenu-bullet', false);

    // Active parent highlighting because Silabus & RPP routes to current route 'dashboard'
    $response->assertSee('is-open active-parent', false);
});

test('sidebar hides inactive modules, menus, and submenus', function () {
    $activeModule = Module::create([
        'name' => 'Modul Terlihat',
        'order' => 1,
        'is_active' => true,
    ]);

    $inactiveModule = Module::create([
        'name' => 'Modul Tersembunyi',
        'order' => 2,
        'is_active' => false,
    ]);

    Menu::create([
        'module_id' => $activeModule->id,
        'name' => 'Menu Aktif',
        'route_name' => 'dashboard',
        'order' => 1,
        'is_active' => true,
    ]);

    Menu::create([
        'module_id' => $activeModule->id,
        'name' => 'Menu Nonaktif',
        'route_name' => 'dashboard',
        'order' => 2,
        'is_active' => false,
    ]);

    Menu::create([
        'module_id' => $inactiveModule->id,
        'name' => 'Menu Dari Modul Nonaktif',
        'route_name' => 'dashboard',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->get('/dashboard');

    $response->assertOk();
    $response->assertSee('Modul Terlihat');
    $response->assertSee('Menu Aktif');

    $response->assertDontSee('Modul Tersembunyi');
    $response->assertDontSee('Menu Nonaktif');
    $response->assertDontSee('Menu Dari Modul Nonaktif');
});

test('menus and submenus without .index suffix automatically resolve and match active route', function () {
    $module = Module::create([
        'name' => 'Sistem Pintar',
        'icon' => 'school',
        'order' => 1,
        'is_active' => true,
    ]);

    // Parent menu with submenu having route 'system.modules' without .index
    $parentMenu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Kelola',
        'route_name' => null,
        'icon' => 'settings',
        'order' => 1,
        'is_active' => true,
    ]);

    SubMenu::create([
        'menu_id' => $parentMenu->id,
        'name' => 'Data Modul',
        'route_name' => 'system.modules', // without .index
        'order' => 1,
        'is_active' => true,
    ]);

    // Visit /system/modules
    $response = $this->actingAs($this->user)->get(route('system.modules.index'));

    $response->assertOk();
    // Sub-menu URL should resolve to system.modules.index
    $response->assertSee(route('system.modules.index'));
    // Parent should be marked active
    $response->assertSee('is-open active-parent', false);
});

test('module and menu create views render visual icon picker with free Lucide catalog', function () {
    $responseMod = $this->actingAs($this->user)->get(route('system.modules.create'));
    $responseMod->assertOk();
    $responseMod->assertSee('icon-picker-component', false);
    $responseMod->assertSee('Katalog Ikon Navigasi');
    $responseMod->assertSee('Pilih Ikon');

    $responseMenu = $this->actingAs($this->user)->get(route('system.menus.create'));
    $responseMenu->assertOk();
    $responseMenu->assertSee('icon-picker-component', false);
    $responseMenu->assertSee('Panduan Nama Rute');
    $responseMenu->assertSee('registered_routes_list', false);
});
