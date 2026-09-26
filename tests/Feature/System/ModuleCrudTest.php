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
    $response->assertSee('AKADEMIK KHUSUS');
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
        'name' => 'Kesiswaan dan Ekstrakurikuler',
        'order' => 3,
        'is_active' => '1',
    ];

    $response = $this->actingAs($this->superAdmin)->post(route('system.modules.store'), $payload);

    $response->assertRedirect(route('system.modules.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('modules', [
        'name' => 'KESISWAAN DAN EKSTRAKURIKULER',
        'order' => 3,
        'is_active' => true,
    ]);
});

test('module store automatically formats name to uppercase', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('system.modules.store'), [
        'name' => 'akademik dan kurikulum smk',
        'order' => 4,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('system.modules.index'));
    $this->assertDatabaseHas('modules', [
        'name' => 'AKADEMIK DAN KURIKULUM SMK',
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
    expect(Module::where('name', 'MODUL SAMA')->count())->toBe(2);
});

test('super admin can view edit module modal component on index and edit route redirects to index', function () {
    $module = Module::create([
        'name' => 'Modul Edit Test',
        'order' => 2,
        'is_active' => true,
    ]);

    $redirectResponse = $this->actingAs($this->superAdmin)->get(route('system.modules.edit', $module));
    $redirectResponse->assertRedirect(route('system.modules.index'));

    $response = $this->actingAs($this->superAdmin)->get(route('system.modules.index'));
    $response->assertOk();
    $response->assertSee('MODUL EDIT TEST');
    $response->assertSee('modalEditModule', false);
    $response->assertSee('Formulir Perubahan Modul');
    $response->assertSee('btn-open-edit-module', false);
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
    expect($module->name)->toBe('MODUL SETELAH EDIT')
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

test('module store automatically assigns next order starting from 1 when order is omitted', function () {
    $currentMax = Module::max('order') ?? 0;
    $expectedOrder = $currentMax + 1;

    $response = $this->actingAs($this->superAdmin)->post(route('system.modules.store'), [
        'name' => 'Modul Otomatis Next Order',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('system.modules.index'));
    $this->assertDatabaseHas('modules', [
        'name' => 'MODUL OTOMATIS NEXT ORDER',
        'order' => $expectedOrder,
    ]);
});

test('module store validation fails when order is less than 1', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('system.modules.store'), [
        'name' => 'Modul Order Nol',
        'order' => 0,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors('order');
});

test('module store automatically shifts existing modules order when target order already exists', function () {
    $module1 = Module::create(['name' => 'Modul Satu', 'order' => 1, 'is_active' => true]);
    $module2 = Module::create(['name' => 'Modul Dua', 'order' => 2, 'is_active' => true]);
    $module3 = Module::create(['name' => 'Modul Tiga', 'order' => 3, 'is_active' => true]);

    // Insert module with order 2
    $response = $this->actingAs($this->superAdmin)->post(route('system.modules.store'), [
        'name' => 'Modul Baru Posisi Dua',
        'order' => 2,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('system.modules.index'));
    $response->assertSessionHas('success');

    expect($module1->fresh()->order)->toBe(1);
    expect(Module::where('name', 'MODUL BARU POSISI DUA')->first()->order)->toBe(2);
    expect($module2->fresh()->order)->toBe(3);
    expect($module3->fresh()->order)->toBe(4);
});

test('module update automatically shifts existing modules order when order changes', function () {
    $module1 = Module::create(['name' => 'Modul Satu', 'order' => 1, 'is_active' => true]);
    $module2 = Module::create(['name' => 'Modul Dua', 'order' => 2, 'is_active' => true]);
    $module3 = Module::create(['name' => 'Modul Tiga', 'order' => 3, 'is_active' => true]);

    // Move module 3 to order 1
    $response = $this->actingAs($this->superAdmin)->put(route('system.modules.update', $module3), [
        'name' => $module3->name,
        'order' => 1,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('system.modules.index'));

    expect($module3->fresh()->order)->toBe(1);
    expect($module1->fresh()->order)->toBe(2);
    expect($module2->fresh()->order)->toBe(3);
});

test('super admin can sync modules order sequence', function () {
    Module::query()->delete();

    $moduleA = Module::create(['name' => 'Modul A', 'order' => 5, 'is_active' => true]);
    $moduleB = Module::create(['name' => 'Modul B', 'order' => 5, 'is_active' => true]);
    $moduleC = Module::create(['name' => 'Modul C', 'order' => 12, 'is_active' => true]);

    $response = $this->actingAs($this->superAdmin)->post(route('system.modules.sync-order'));
    $response->assertRedirect(route('system.modules.index'));
    $response->assertSessionHas('success');

    $allModules = Module::orderBy('order')->get();
    $orders = $allModules->pluck('order')->all();

    expect($orders)->toBe([1, 2, 3]);
});

test('modules index view renders sync order button and modal', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('system.modules.index'));
    $response->assertOk();
    $response->assertSee('btnOpenSyncOrder', false);
    $response->assertSee('modalSyncOrder', false);
    $response->assertSee('Sinkronkan Urutan');
});
