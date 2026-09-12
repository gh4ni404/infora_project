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
        'name' => 'Sub-Menu Pertama',
        'order' => 1,
        'is_active' => true,
    ]);

    $otherMenu = Menu::create(['module_id' => $this->module->id, 'name' => 'Menu Lain', 'order' => 2, 'is_active' => true]);
    $sub2 = SubMenu::create([
        'menu_id' => $otherMenu->id,
        'name' => 'Sub-Menu Kedua',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('system.sub-menus.index', ['menu_id' => $this->menu->id]));

    $response->assertOk();
    $response->assertSee('Tata Kelola Sub-Menu');
    $response->assertSee('modalCreateSubMenu', false);
    $response->assertSee('btnOpenCreateSubMenu', false);
    expect($response->viewData('subMenus')->pluck('name'))
        ->toContain('Sub-Menu Pertama')
        ->not->toContain('Sub-Menu Kedua');
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

test('sub-menu store automatically formats name to title case with acronyms', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('system.sub-menus.store'), [
        'menu_id' => $this->menu->id,
        'name' => 'laporan pkl smk & rekapitulasi ban-sm',
        'order' => 2,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('system.sub-menus.index'));
    $this->assertDatabaseHas('sub_menus', [
        'menu_id' => $this->menu->id,
        'name' => 'Laporan PKL SMK & Rekapitulasi BAN-SM',
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
        'name' => 'Sub-Menu Duplikat',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('system.sub-menus.store'), [
        'menu_id' => $this->menu->id,
        'name' => 'Sub-Menu Duplikat',
        'order' => 2,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('system.sub-menus.index'));
    expect(SubMenu::where('name', 'Sub-Menu Duplikat')->count())->toBe(2);
});

test('super admin can view edit sub-menu modal component on index and edit route redirects to index', function () {
    $sub = SubMenu::create([
        'menu_id' => $this->menu->id,
        'name' => 'Sub-Menu Edit Test',
        'order' => 1,
        'is_active' => true,
    ]);

    $redirectResponse = $this->actingAs($this->superAdmin)->get(route('system.sub-menus.edit', $sub));
    $redirectResponse->assertRedirect(route('system.sub-menus.index'));

    $response = $this->actingAs($this->superAdmin)->get(route('system.sub-menus.index'));
    $response->assertOk();
    $response->assertSee('Sub-Menu Edit Test');
    $response->assertSee('modalEditSubMenu', false);
    $response->assertSee('Formulir Perubahan Sub-Menu');
    $response->assertSee('btn-open-edit-sub-menu', false);
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

test('sub-menu store automatically assigns next order for menu when order is omitted', function () {
    $currentMax = SubMenu::where('menu_id', $this->menu->id)->max('order') ?? 0;
    $expectedOrder = $currentMax + 1;

    $response = $this->actingAs($this->superAdmin)->post(route('system.sub-menus.store'), [
        'menu_id' => $this->menu->id,
        'name' => 'Sub-Menu Otomatis Next Order',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('system.sub-menus.index'));
    $this->assertDatabaseHas('sub_menus', [
        'menu_id' => $this->menu->id,
        'name' => 'Sub-Menu Otomatis Next Order',
        'order' => $expectedOrder,
    ]);
});

test('sub-menu store validation fails when order is less than 1', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('system.sub-menus.store'), [
        'menu_id' => $this->menu->id,
        'name' => 'Sub-Menu Order Nol',
        'order' => 0,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors('order');
});

test('updating sub-menu to a new parent menu automatically assigns next order of the new parent when order is omitted', function () {
    $targetMenu = Menu::create([
        'module_id' => $this->module->id,
        'name' => 'Menu Target Lain',
        'order' => 10,
        'is_active' => true,
    ]);

    SubMenu::create([
        'menu_id' => $targetMenu->id,
        'name' => 'Sub Target 1',
        'order' => 1,
        'is_active' => true,
    ]);
    SubMenu::create([
        'menu_id' => $targetMenu->id,
        'name' => 'Sub Target 2',
        'order' => 2,
        'is_active' => true,
    ]);

    $subToMove = SubMenu::create([
        'menu_id' => $this->menu->id,
        'name' => 'Sub Yang Dipindahkan',
        'order' => 1,
        'is_active' => true,
    ]);

    // Update parent menu to targetMenu without specifying order
    $response = $this->actingAs($this->superAdmin)->put(route('system.sub-menus.update', $subToMove), [
        'menu_id' => $targetMenu->id,
        'name' => 'Sub Yang Dipindahkan',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('system.sub-menus.index'));
    $subToMove->refresh();
    expect($subToMove->menu_id)->toBe($targetMenu->id)
        ->and($subToMove->order)->toBe(3); // targetMenu max order 2 + 1 = 3
});

test('menu route_prefix accessor generates expected prefix slug from menu name', function () {
    $menuSystem = Menu::create(['module_id' => $this->module->id, 'name' => 'Sistem', 'order' => 1, 'is_active' => true]);
    $menuMaster = Menu::create(['module_id' => $this->module->id, 'name' => 'Master', 'order' => 2, 'is_active' => true]);
    $menuRombel = Menu::create(['module_id' => $this->module->id, 'name' => 'Rombel & Bimbingan', 'order' => 3, 'is_active' => true]);

    expect($menuSystem->route_prefix)->toBe('system')
        ->and($menuMaster->route_prefix)->toBe('master')
        ->and($menuRombel->route_prefix)->toBe('rombel');
});

test('sub-menu store combines route_prefix and route_suffix into single route_name', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('system.sub-menus.store'), [
        'menu_id' => $this->menu->id,
        'name' => 'Data Siswa',
        'route_prefix' => 'master',
        'route_suffix' => 'data-siswa',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('system.sub-menus.index'));
    $this->assertDatabaseHas('sub_menus', [
        'menu_id' => $this->menu->id,
        'name' => 'Data Siswa',
        'route_name' => 'master.data-siswa',
    ]);
});

test('sub-menu update combines route_prefix and route_suffix into single route_name', function () {
    $subMenu = SubMenu::create([
        'menu_id' => $this->menu->id,
        'name' => 'Data Lama',
        'route_name' => 'old.data',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->put(route('system.sub-menus.update', $subMenu), [
        'menu_id' => $this->menu->id,
        'name' => 'Data Baru',
        'route_prefix' => 'system',
        'route_suffix' => 'settings',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('system.sub-menus.index'));
    $subMenu->refresh();
    expect($subMenu->route_name)->toBe('system.settings');
});

test('sub-menu handles single route when only route_suffix or route_prefix is provided', function () {
    // Only suffix (e.g. dashboard)
    $response1 = $this->actingAs($this->superAdmin)->post(route('system.sub-menus.store'), [
        'menu_id' => $this->menu->id,
        'name' => 'Dashboard Khusus',
        'route_prefix' => '',
        'route_suffix' => 'dashboard',
        'is_active' => true,
    ]);
    $response1->assertRedirect(route('system.sub-menus.index'));
    $this->assertDatabaseHas('sub_menus', [
        'name' => 'Dashboard Khusus',
        'route_name' => 'dashboard',
    ]);

    // Only prefix (e.g. single module route)
    $response2 = $this->actingAs($this->superAdmin)->post(route('system.sub-menus.store'), [
        'menu_id' => $this->menu->id,
        'name' => 'Portal Mandiri',
        'route_prefix' => 'portal',
        'route_suffix' => '',
        'is_active' => true,
    ]);
    $response2->assertRedirect(route('system.sub-menus.index'));
    $this->assertDatabaseHas('sub_menus', [
        'name' => 'Portal Mandiri',
        'route_name' => 'portal',
    ]);
});
