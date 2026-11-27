<?php

use App\Models\Menu;
use App\Models\Module;
use App\Models\SubMenu;
use App\Models\User;
use App\Models\UserMenuPermission;
use Illuminate\Support\Facades\Cache;

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
        'name' => 'Data Murid Baru',
        'route_name' => 'unregistered.dummy.submenu',
        'order' => 3,
        'is_active' => true,
    ]);

    // SubMenu route_url should fall back to under-development route
    expect($subMenu->route_url)->toBe(route('system.under-development', ['type' => 'submenu', 'id' => $subMenu->id]));

    $response = $this->actingAs($this->user)->get($subMenu->route_url);

    $response->assertOk();
    $response->assertSee('Data Murid Baru');
    $response->assertSee('PENGATURAN SISTEM');
    $response->assertSee('Sistem');
    $response->assertSee('Status Fitur: Dalam Tahap Pengembangan');
    $response->assertSee('unregistered.dummy.submenu');
    $response->assertSee('/unregistered/dummy/submenu');
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

test('non superadmin user with menu permission can access under-development page and sees friendly view', function () {
    $gtkUser = User::factory()->create([
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $module = Module::create(['name' => 'AKADEMIK', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Absensi KBM',
        'route_name' => 'absensi',
        'order' => 1,
        'is_active' => true,
    ]);

    UserMenuPermission::create([
        'user_id' => $gtkUser->id,
        'menu_id' => $menu->id,
        'can_view' => true,
    ]);

    $response = $this->actingAs($gtkUser)
        ->get($menu->route_url);

    $response->assertOk();
    $response->assertSee('Absensi KBM');
    $response->assertSee('Fitur Sedang Dalam Tahap Persiapan');
    $response->assertSee('Segera Hadir');
    $response->assertDontSee('php artisan make:controller');
    $response->assertDontSee('Edit Konfigurasi Menu');
    $response->assertDontSee('Panduan Developer');
});

test('under-development page displays real database progress and status endpoint supports lightweight cached polling', function () {
    $gtkUser = User::factory()->create([
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $module = Module::create(['name' => 'AKADEMIK', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'E Rapor',
        'route_name' => 'akademik.erapor.unregistered',
        'order' => 1,
        'is_active' => true,
        'dev_progress' => 55,
        'dev_stage' => 'Controller',
    ]);

    UserMenuPermission::create([
        'user_id' => $gtkUser->id,
        'menu_id' => $menu->id,
        'can_view' => true,
    ]);

    $pageResponse = $this->actingAs($gtkUser)->get(route('system.under-development', ['type' => 'menu', 'id' => $menu->id]));
    $pageResponse->assertOk();
    $pageResponse->assertSee('55%');
    $pageResponse->assertSee('Controller');
    $pageResponse->assertSee('class="infora-progress"', false);

    // Polling JSON oleh non-superadmin berhasil dan ter-cache
    $statusUrl = route('system.under-development.status', ['type' => 'menu', 'id' => $menu->id]);
    $statusResponse = $this->actingAs($gtkUser)->getJson($statusUrl);
    $statusResponse->assertOk()
        ->assertJson([
            'progress' => 55,
            'stage' => 'Controller',
            'ready' => false,
            'redirect_url' => null,
        ]);

    expect(Cache::has(Menu::developmentCacheKey('menu', $menu->id)))->toBeTrue();

    // Update via checklist oleh Super Admin otomatis menginvalidasi cache dan memperbarui respon status
    $this->actingAs($this->user)
        ->patchJson(route('system.under-development.checklist'), [
            'type' => 'menu',
            'id' => $menu->id,
            'stage_key' => 'test',
            'checked' => true,
        ])
        ->assertOk()
        ->assertJson([
            'progress' => 95,
            'stage' => 'Test',
            'ready' => false,
        ]);

    $updatedStatusResponse = $this->actingAs($gtkUser)->getJson($statusUrl);
    $updatedStatusResponse->assertOk()
        ->assertJson([
            'progress' => 95,
            'stage' => 'Test',
            'ready' => false,
        ]);
});

test('under-development status and page stay locked until checklist selesai is checked even when route is registered', function () {
    $gtkUser = User::factory()->create([
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $module = Module::create(['name' => 'MASTER DATA', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Data Mapel',
        'route_name' => 'master.data-mapel.index',
        'order' => 1,
        'is_active' => true,
        'dev_progress' => 65,
        'dev_stage' => 'Route (routes/web.php)',
    ]);

    // Meskipun rute sudah terdaftar, selama belum dicentang Selesai (100%) tetap masuk under-development
    expect($menu->route_url)->toBe(route('system.under-development', ['type' => 'menu', 'id' => $menu->id]));

    $statusResponse = $this->actingAs($gtkUser)->getJson(
        route('system.under-development.status', ['type' => 'menu', 'id' => $menu->id])
    );

    $statusResponse->assertOk()
        ->assertJson([
            'progress' => 65,
            'ready' => false,
            'redirect_url' => null,
        ]);

    // Setelah Super Admin mencentang Selesai (done -> 100%) dan seluruh 7 komponen ada, menu bisa diakses
    $this->actingAs($this->user)
        ->patchJson(route('system.under-development.checklist'), [
            'type' => 'menu',
            'id' => $menu->id,
            'stage_key' => 'done',
            'checked' => true,
        ])
        ->assertOk()
        ->assertJson([
            'progress' => 100,
            'ready' => true,
            'redirect_url' => route('master.data-mapel.index'),
        ]);

    expect($menu->fresh()->route_url)->toBe(route('master.data-mapel.index'));

    $this->actingAs($gtkUser)
        ->get(route('system.under-development', ['type' => 'menu', 'id' => $menu->id]))
        ->assertRedirect(route('master.data-mapel.index'));
});

test('checking selesai fails with 422 and lists missing components when feature is incomplete', function () {
    $module = Module::create(['name' => 'AKADEMIK', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Fitur Belum Siap',
        'route_name' => 'akademik.fitur-khayalan.index',
        'order' => 1,
        'is_active' => true,
        'dev_progress' => 95,
        'dev_stage' => 'Feature Test',
    ]);

    $response = $this->actingAs($this->user)
        ->patchJson(route('system.under-development.checklist'), [
            'type' => 'menu',
            'id' => $menu->id,
            'stage_key' => 'done',
            'checked' => true,
        ]);

    $response->assertUnprocessable()
        ->assertJsonStructure(['message', 'missing_components', 'status'])
        ->assertJsonPath('status.progress', 0)
        ->assertJsonPath('status.stage', 'Tahap Persiapan Modul');

    expect($response->json('message'))->toContain('Fitur ini belum siap karena')
        ->and($response->json('missing_components'))->toContain(
            'Migration',
            'Model',
            'Form Request',
            'Controller',
            'Route',
            'View',
            'Test'
        )
        ->and($menu->fresh()->dev_progress)->toBe(0)
        ->and($menu->fresh()->dev_stage)->toBeNull();
});

test('under-development status returns 404 for invalid type or missing item', function () {
    $this->actingAs($this->user)
        ->getJson(route('system.under-development.status', ['type' => 'invalid', 'id' => 1]))
        ->assertNotFound();

    $this->actingAs($this->user)
        ->getJson(route('system.under-development.status', ['type' => 'submenu', 'id' => 99999]))
        ->assertNotFound();
});

test('super admin can check and uncheck checklist stages on submenu and invalidates cache', function () {
    $module = Module::create(['name' => 'KESISWAAN', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create(['module_id' => $module->id, 'name' => 'Bimbingan', 'order' => 1, 'is_active' => true]);
    $subMenu = SubMenu::create([
        'menu_id' => $menu->id,
        'name' => 'Konseling',
        'route_name' => 'kesiswaan.konseling.pending',
        'order' => 1,
        'is_active' => true,
    ]);

    // Centang tahap controller -> 55%
    $this->actingAs($this->user)
        ->patchJson(route('system.under-development.checklist'), [
            'type' => 'submenu',
            'id' => $subMenu->id,
            'stage_key' => 'controller',
            'checked' => true,
        ])
        ->assertOk()
        ->assertJson([
            'progress' => 55,
            'stage' => 'Controller',
        ]);

    $this->assertDatabaseHas('sub_menus', [
        'id' => $subMenu->id,
        'dev_progress' => 55,
        'dev_stage' => 'Controller',
    ]);

    // Lepas centang controller -> mundur ke Form Request (40%)
    $this->actingAs($this->user)
        ->patchJson(route('system.under-development.checklist'), [
            'type' => 'submenu',
            'id' => $subMenu->id,
            'stage_key' => 'controller',
            'checked' => false,
        ])
        ->assertOk()
        ->assertJson([
            'progress' => 40,
            'stage' => 'Form Request',
        ]);

    // Lepas centang migration -> kembali ke 0%
    $this->actingAs($this->user)
        ->patchJson(route('system.under-development.checklist'), [
            'type' => 'submenu',
            'id' => $subMenu->id,
            'stage_key' => 'migration',
            'checked' => false,
        ])
        ->assertOk()
        ->assertJson([
            'progress' => 0,
            'stage' => 'Tahap Persiapan Modul',
        ]);
});

test('non superadmin cannot update development checklist', function () {
    $gtkUser = User::factory()->create([
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $module = Module::create(['name' => 'KESISWAAN', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Bimbingan',
        'route_name' => 'kesiswaan.bimbingan.pending',
        'order' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($gtkUser)
        ->patchJson(route('system.under-development.checklist'), [
            'type' => 'menu',
            'id' => $menu->id,
            'stage_key' => 'migration',
            'checked' => true,
        ])
        ->assertForbidden();
});

test('under-development page suggests namespaced controller path for dotted routes', function () {
    $module = Module::create(['name' => 'AKADEMIK', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create(['module_id' => $module->id, 'name' => 'Akademik', 'order' => 1, 'is_active' => true]);
    $subMenu = SubMenu::create([
        'menu_id' => $menu->id,
        'name' => 'Jurnal KBM',
        'route_name' => 'akademik.jurnal-kbm',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('system.under-development', ['type' => 'submenu', 'id' => $subMenu->id]));

    $response->assertOk();
    $response->assertSee('php artisan make:controller Akademik/JurnalKbmController');
    $response->assertSee("Route::get('/akademik/jurnal-kbm', [JurnalKbmController::class, 'index'])->name('akademik.jurnal-kbm');");
});

test('dashboard route is exempt from under-development lock and component validation', function () {
    $module = Module::create(['name' => 'NAVIGASI UTAMA', 'order' => 1, 'is_active' => true]);
    $dashboardMenu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Dashboard',
        'route_name' => 'dashboard',
        'order' => 1,
        'is_active' => true,
        'dev_progress' => 0,
    ]);

    expect($dashboardMenu->route_url)->toBe(route('dashboard'));

    $this->actingAs($this->user)
        ->patchJson(route('system.under-development.checklist'), [
            'type' => 'menu',
            'id' => $dashboardMenu->id,
            'stage_key' => 'done',
            'checked' => true,
        ])
        ->assertOk()
        ->assertJson([
            'progress' => 100,
            'ready' => true,
            'redirect_url' => route('dashboard'),
        ]);
});
