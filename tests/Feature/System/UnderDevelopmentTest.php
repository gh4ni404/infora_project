<?php

use App\Models\Menu;
use App\Models\Module;
use App\Models\SubMenu;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'username' => 'superadmin_dev',
        'email' => 'superadmin_dev@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);
});

test('guest cannot access under-development page and is redirected to login', function () {
    $response = $this->get('/system/under-development?type=submenu&id=1');

    $response->assertRedirect('/login');
});

test('super admin can access under-development page for a submenu without registered route', function () {
    $module = Module::create(['name' => 'PENGATURAN SISTEM', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Sistem',
        'route_name' => null,
        'order' => 1,
        'is_active' => true,
    ]);
    $subMenu = SubMenu::create([
        'menu_id' => $menu->id,
        'name' => 'Data Siswa Baru',
        'route_name' => 'unregistered.dummy.submenu',
        'order' => 3,
        'is_active' => true,
    ]);

    // SubMenu route_url should fall back to under-development route
    expect($subMenu->route_url)->toBe(route('system.under-development', ['type' => 'submenu', 'id' => $subMenu->id]));

    $response = $this->actingAs($this->user)->get($subMenu->route_url);

    $response->assertOk();
    $response->assertSee('Data Siswa Baru');
    $response->assertSee('PENGATURAN SISTEM');
    $response->assertSee('Sistem');
    $response->assertSee('Status Fitur: Dalam Tahap Pengembangan');
    $response->assertSee('unregistered.dummy.submenu');
    $response->assertSee('php artisan make:controller');
});

test('super admin can access under-development page for a menu without registered route', function () {
    $module = Module::create(['name' => 'AKADEMIK', 'order' => 2, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Kalender Akademik',
        'route_name' => 'akademik.kalender',
        'order' => 1,
        'is_active' => true,
    ]);

    expect($menu->route_url)->toBe(route('system.under-development', ['type' => 'menu', 'id' => $menu->id]));

    $response = $this->actingAs($this->user)->get($menu->route_url);

    $response->assertOk();
    $response->assertSee('Kalender Akademik');
    $response->assertSee('AKADEMIK');
    $response->assertSee('Status Fitur: Dalam Tahap Pengembangan');
});

test('accessing under-development page with invalid type or missing id returns 404', function () {
    $responseInvalidType = $this->actingAs($this->user)->get('/system/under-development?type=invalid&id=999');
    $responseInvalidType->assertNotFound();

    $responseMissingId = $this->actingAs($this->user)->get('/system/under-development?type=submenu&id=99999');
    $responseMissingId->assertNotFound();
});

test('isRouteActive returns true on under-development page for the corresponding item', function () {
    $module = Module::create(['name' => 'SISTEM', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Konfigurasi',
        'route_name' => null,
        'order' => 1,
        'is_active' => true,
    ]);
    $subMenu = SubMenu::create([
        'menu_id' => $menu->id,
        'name' => 'Laporan Khusus',
        'route_name' => 'unregistered.laporan.khusus',
        'order' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($this->user)->get(route('system.under-development', ['type' => 'submenu', 'id' => $subMenu->id]));

    expect($subMenu->isRouteActive())->toBeTrue()
        ->and($menu->hasActiveSubMenu())->toBeTrue();
});
