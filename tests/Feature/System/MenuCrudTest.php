<?php

use App\Models\Menu;
use App\Models\Module;
use App\Models\SubMenu;
use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'super_admin_menu_tester',
        'email' => 'superadmin_menu@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->module = Module::create([
        'name' => 'Tata Kelola Pengujian',
        'icon' => 'layers',
        'order' => 1,
        'is_active' => true,
    ]);
});

test('super admin can view menus list and filter by module', function () {
    $menu1 = Menu::create([
        'module_id' => $this->module->id,
        'name' => 'Menu Alpha',
        'route_name' => 'dashboard',
        'order' => 1,
        'is_active' => true,
    ]);

    $otherModule = Module::create(['name' => 'Modul Lain', 'order' => 2, 'is_active' => true]);
    $menu2 = Menu::create([
        'module_id' => $otherModule->id,
        'name' => 'Menu Beta',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('system.menus.index', ['module_id' => $this->module->id]));

    $response->assertOk();
    $response->assertSee('Tata Kelola Menu');
    expect($response->viewData('menus')->pluck('name'))
        ->toContain('Menu Alpha')
        ->not->toContain('Menu Beta');
});

test('super admin can view create menu page with module list', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('system.menus.create'));

    $response->assertOk();
    $response->assertSee('Tambah Menu Baru');
    $response->assertSee('Tata Kelola Pengujian');
});

test('super admin can store new menu with valid data', function () {
    $payload = [
        'module_id' => $this->module->id,
        'name' => 'Master Data Pengguna',
        'route_name' => 'dashboard',
        'icon' => 'users',
        'order' => 2,
        'is_active' => '1',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('system.menus.store'), $payload);

    $response->assertRedirect(route('system.menus.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('menus', [
        'module_id' => $this->module->id,
        'name' => 'Master Data Pengguna',
        'route_name' => 'dashboard',
        'icon' => 'users',
        'order' => 2,
        'is_active' => true,
    ]);
});

test('menu store validation requires module_id and name', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('system.menus.store'), [
        'module_id' => 99999, // non-existent module
        'name' => '',
    ]);

    $response->assertSessionHasErrors(['module_id', 'name']);
});

test('menu store allows duplicate names without unique constraint errors', function () {
    Menu::create([
        'module_id' => $this->module->id,
        'name' => 'Menu Duplikat',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('system.menus.store'), [
        'module_id' => $this->module->id,
        'name' => 'Menu Duplikat',
        'order' => 2,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('system.menus.index'));
    expect(Menu::where('name', 'Menu Duplikat')->count())->toBe(2);
});

test('super admin can view edit menu page', function () {
    $menu = Menu::create([
        'module_id' => $this->module->id,
        'name' => 'Menu Edit Test',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('system.menus.edit', $menu));

    $response->assertOk();
    $response->assertSee('Edit Menu: Menu Edit Test');
    $response->assertSee('value="Menu Edit Test"', false);
});

test('super admin can update existing menu', function () {
    $menu = Menu::create([
        'module_id' => $this->module->id,
        'name' => 'Menu Sebelum Edit',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->put(route('system.menus.update', $menu), [
        'module_id' => $this->module->id,
        'name' => 'Menu Setelah Edit',
        'route_name' => 'dashboard',
        'icon' => 'menu',
        'order' => 4,
        'is_active' => '0',
    ]);

    $response->assertRedirect(route('system.menus.index'));
    $response->assertSessionHas('success');

    $menu->refresh();
    expect($menu->name)->toBe('Menu Setelah Edit')
        ->and($menu->route_name)->toBe('dashboard')
        ->and($menu->order)->toBe(4)
        ->and($menu->is_active)->toBeFalse();
});

test('super admin can delete menu and cascades to sub-menus', function () {
    $menu = Menu::create(['module_id' => $this->module->id, 'name' => 'Menu Dihapus', 'order' => 1, 'is_active' => true]);
    $subMenu = SubMenu::create(['menu_id' => $menu->id, 'name' => 'SubMenu Terkait', 'order' => 1, 'is_active' => true]);

    $response = $this->actingAs($this->superAdmin)->delete(route('system.menus.destroy', $menu));

    $response->assertRedirect(route('system.menus.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    $this->assertDatabaseMissing('sub_menus', ['id' => $subMenu->id]);
});
