<?php

use App\Models\Menu;
use App\Models\Module;
use App\Models\SubMenu;
use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'super_admin_submenu_tester',
        'email' => 'superadmin_submenu@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->module = Module::create([
        'name' => 'Tata Kelola Pengujian',
        'order' => 1,
        'is_active' => true,
    ]);

    $this->menu = Menu::create([
        'module_id' => $this->module->id,
        'name' => 'Menu Induk Pengujian',
        'order' => 1,
        'is_active' => true,
    ]);
});

test('super admin can view sub-menus list and filter by menu', function () {
    $sub1 = SubMenu::create([
        'menu_id' => $this->menu->id,
        'name' => 'SubMenu Pertama',
        'order' => 1,
        'is_active' => true,
    ]);

    $otherMenu = Menu::create(['module_id' => $this->module->id, 'name' => 'Menu Lain', 'order' => 2, 'is_active' => true]);
    $sub2 = SubMenu::create([
        'menu_id' => $otherMenu->id,
        'name' => 'SubMenu Kedua',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('system.sub-menus.index', ['menu_id' => $this->menu->id]));

    $response->assertOk();
    $response->assertSee('Tata Kelola Sub-Menu');
    expect($response->viewData('subMenus')->pluck('name'))
        ->toContain('SubMenu Pertama')
        ->not->toContain('SubMenu Kedua');
});

test('super admin can view create sub-menu page', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('system.sub-menus.create'));

    $response->assertOk();
    $response->assertSee('Tambah Sub-Menu Baru');
    $response->assertSee('Menu Induk Pengujian');
});

test('super admin can store new sub-menu with valid data', function () {
    $payload = [
        'menu_id' => $this->menu->id,
        'name' => 'Riwayat Aktivitas',
        'route_name' => 'dashboard',
        'order' => 1,
        'is_active' => '1',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('system.sub-menus.store'), $payload);

    $response->assertRedirect(route('system.sub-menus.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('sub_menus', [
        'menu_id' => $this->menu->id,
        'name' => 'Riwayat Aktivitas',
        'route_name' => 'dashboard',
        'order' => 1,
        'is_active' => true,
    ]);
});

test('sub-menu store validation requires menu_id and name', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('system.sub-menus.store'), [
        'menu_id' => 88888, // non-existent menu
        'name' => '',
    ]);

    $response->assertSessionHasErrors(['menu_id', 'name']);
});

test('sub-menu store allows duplicate names without unique constraint errors', function () {
    SubMenu::create([
        'menu_id' => $this->menu->id,
        'name' => 'SubMenu Duplikat',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('system.sub-menus.store'), [
        'menu_id' => $this->menu->id,
        'name' => 'SubMenu Duplikat',
        'order' => 2,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('system.sub-menus.index'));
    expect(SubMenu::where('name', 'SubMenu Duplikat')->count())->toBe(2);
});

test('super admin can view edit sub-menu page', function () {
    $sub = SubMenu::create([
        'menu_id' => $this->menu->id,
        'name' => 'SubMenu Edit Test',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('system.sub-menus.edit', $sub));

    $response->assertOk();
    $response->assertSee('Edit Sub-Menu: SubMenu Edit Test');
    $response->assertSee('value="SubMenu Edit Test"', false);
});

test('super admin can update existing sub-menu', function () {
    $sub = SubMenu::create([
        'menu_id' => $this->menu->id,
        'name' => 'Sub Sebelum Edit',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->put(route('system.sub-menus.update', $sub), [
        'menu_id' => $this->menu->id,
        'name' => 'Sub Setelah Edit',
        'route_name' => 'dashboard',
        'order' => 3,
        'is_active' => '0',
    ]);

    $response->assertRedirect(route('system.sub-menus.index'));
    $response->assertSessionHas('success');

    $sub->refresh();
    expect($sub->name)->toBe('Sub Setelah Edit')
        ->and($sub->order)->toBe(3)
        ->and($sub->is_active)->toBeFalse();
});

test('super admin can delete sub-menu', function () {
    $sub = SubMenu::create(['menu_id' => $this->menu->id, 'name' => 'Sub Dihapus', 'order' => 1, 'is_active' => true]);

    $response = $this->actingAs($this->superAdmin)->delete(route('system.sub-menus.destroy', $sub));

    $response->assertRedirect(route('system.sub-menus.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('sub_menus', ['id' => $sub->id]);
});
