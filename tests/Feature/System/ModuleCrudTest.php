<?php

use App\Models\Menu;
use App\Models\Module;
use App\Models\SubMenu;
use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'super_admin_module_tester',
        'email' => 'superadmin_module@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'regular_module_tester',
        'email' => 'regular_module@infora.test',
        'user_type' => 'guru',
        'is_active' => true,
    ]);
});

test('guest is redirected to login when accessing modules management', function () {
    $response = $this->get(route('system.modules.index'));

    $response->assertRedirect(route('login'));
});

test('non super admin cannot access modules management', function () {
    $response = $this->actingAs($this->regularUser)->get(route('system.modules.index'));

    $response->assertForbidden();
});

test('super admin can view modules list with menu counts', function () {
    $module = Module::create([
        'name' => 'Akademik Khusus',
        'order' => 1,
        'is_active' => true,
    ]);

    Menu::create([
        'module_id' => $module->id,
        'name' => 'Jadwal Kuliah',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('system.modules.index'));

    $response->assertOk();
    $response->assertSee('Tata Kelola Modul');
    $response->assertSee('Akademik Khusus');
    $response->assertSee('1 Menu');
    $response->assertSee('modalCreateModule', false);
    $response->assertSee('btnOpenCreateModule', false);
});

test('super admin can view create module page', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('system.modules.create'));

    $response->assertOk();
    $response->assertSee('Tambah Modul Baru');
    $response->assertSee('Formulir Pendaftaran Modul');
});

test('super admin can store new module with valid data', function () {
    $payload = [
        'name' => 'Kesiswaan & Ekstrakurikuler',
        'order' => 3,
        'is_active' => '1',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('system.modules.store'), $payload);

    $response->assertRedirect(route('system.modules.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('modules', [
        'name' => 'Kesiswaan & Ekstrakurikuler',
        'order' => 3,
        'is_active' => true,
    ]);
});

test('module store validation fails if name is missing', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('system.modules.store'), [
        'name' => '',
        'order' => 1,
    ]);

    $response->assertSessionHasErrors('name');
});

test('module store allows duplicate names without unique constraint errors', function () {
    Module::create([
        'name' => 'Modul Sama',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('system.modules.store'), [
        'name' => 'Modul Sama',
        'order' => 2,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('system.modules.index'));
    expect(Module::where('name', 'Modul Sama')->count())->toBe(2);
});

test('super admin can view edit module page', function () {
    $module = Module::create([
        'name' => 'Modul Edit Test',
        'order' => 2,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('system.modules.edit', $module));

    $response->assertOk();
    $response->assertSee('Edit Modul: Modul Edit Test');
    $response->assertSee('value="Modul Edit Test"', false);
});

test('super admin can update existing module', function () {
    $module = Module::create([
        'name' => 'Modul Sebelum Edit',
        'order' => 2,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superAdmin)->put(route('system.modules.update', $module), [
        'name' => 'Modul Setelah Edit',
        'order' => 5,
        'is_active' => '0',
    ]);

    $response->assertRedirect(route('system.modules.index'));
    $response->assertSessionHas('success');

    $module->refresh();
    expect($module->name)->toBe('Modul Setelah Edit')
        ->and($module->order)->toBe(5)
        ->and($module->is_active)->toBeFalse();
});

test('super admin can delete module and cascades to menus and sub-menus', function () {
    $module = Module::create(['name' => 'Modul Dihapus', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create(['module_id' => $module->id, 'name' => 'Menu Dihapus', 'order' => 1, 'is_active' => true]);
    $subMenu = SubMenu::create(['menu_id' => $menu->id, 'name' => 'SubMenu Dihapus', 'order' => 1, 'is_active' => true]);

    $response = $this->actingAs($this->superAdmin)->delete(route('system.modules.destroy', $module));

    $response->assertRedirect(route('system.modules.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('modules', ['id' => $module->id]);
    $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    $this->assertDatabaseMissing('sub_menus', ['id' => $subMenu->id]);
});
