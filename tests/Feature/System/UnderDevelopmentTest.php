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
        'dev_progress' => 65,
        'dev_stage' => 'Integrasi Penilaian Sumatif',
        'dev_note' => 'Menunggu finalisasi cetak leger nilai',
        'dev_version' => 'v0.8-beta',
    ]);

    UserMenuPermission::create([
        'user_id' => $gtkUser->id,
        'menu_id' => $menu->id,
        'can_view' => true,
    ]);

    $pageResponse = $this->actingAs($gtkUser)->get(route('system.under-development', ['type' => 'menu', 'id' => $menu->id]));
    $pageResponse->assertOk();
    $pageResponse->assertSee('65%');
    $pageResponse->assertSee('Integrasi Penilaian Sumatif');
    $pageResponse->assertSee('Menunggu finalisasi cetak leger nilai');
    $pageResponse->assertSee('v0.8-beta');
    $pageResponse->assertSee('class="infora-progress"', false);

    // Polling JSON oleh non-superadmin berhasil dan ter-cache
    $statusUrl = route('system.under-development.status', ['type' => 'menu', 'id' => $menu->id]);
    $statusResponse = $this->actingAs($gtkUser)->getJson($statusUrl);
    $statusResponse->assertOk()
        ->assertJson([
            'progress' => 65,
            'stage' => 'Integrasi Penilaian Sumatif',
            'note' => 'Menunggu finalisasi cetak leger nilai',
            'version' => 'v0.8-beta',
            'ready' => false,
            'redirect_url' => null,
        ]);

    expect(Cache::has(Menu::developmentCacheKey('menu', $menu->id)))->toBeTrue();

    // Update via MenuController otomatis menginvalidasi cache dan memperbarui respon status
    $this->actingAs($this->user)
        ->put(route('system.menus.update', $menu), [
            'module_id' => $module->id,
            'name' => 'E Rapor',
            'route_name' => 'akademik.erapor.unregistered',
            'order' => 1,
            'is_active' => '1',
            'dev_progress' => 90,
            'dev_stage' => 'Tahap Uji Coba Final',
            'dev_note' => 'Pengujian bersama operator kurikulum',
            'dev_version' => 'v0.9-rc1',
        ])
        ->assertRedirect(route('system.menus.index'));

    $updatedStatusResponse = $this->actingAs($gtkUser)->getJson($statusUrl);
    $updatedStatusResponse->assertOk()
        ->assertJson([
            'progress' => 90,
            'stage' => 'Tahap Uji Coba Final',
            'note' => 'Pengujian bersama operator kurikulum',
            'version' => 'v0.9-rc1',
            'ready' => false,
        ]);
});

test('under-development status and page automatically detect when feature route becomes registered', function () {
    $gtkUser = User::factory()->create([
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $module = Module::create(['name' => 'UMUM', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Profil',
        'route_name' => 'profile.index',
        'order' => 1,
        'is_active' => true,
        'dev_progress' => 50,
    ]);

    $statusResponse = $this->actingAs($gtkUser)->getJson(
        route('system.under-development.status', ['type' => 'menu', 'id' => $menu->id])
    );

    $statusResponse->assertOk()
        ->assertJson([
            'progress' => 100,
            'ready' => true,
            'redirect_url' => route('profile.index'),
        ]);

    // Mengunjungi placeholder saat rute sudah terdaftar otomatis redirect ke rute tujuan
    $this->actingAs($gtkUser)
        ->get(route('system.under-development', ['type' => 'menu', 'id' => $menu->id]))
        ->assertRedirect(route('profile.index'));
});

test('under-development status returns 404 for invalid type or missing item', function () {
    $this->actingAs($this->user)
        ->getJson(route('system.under-development.status', ['type' => 'invalid', 'id' => 1]))
        ->assertNotFound();

    $this->actingAs($this->user)
        ->getJson(route('system.under-development.status', ['type' => 'submenu', 'id' => 99999]))
        ->assertNotFound();
});

test('sub-menu update validates and stores development progress metadata', function () {
    $module = Module::create(['name' => 'KESISWAAN', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create(['module_id' => $module->id, 'name' => 'Bimbingan', 'order' => 1, 'is_active' => true]);
    $subMenu = SubMenu::create([
        'menu_id' => $menu->id,
        'name' => 'Konseling',
        'route_name' => 'kesiswaan.konseling.pending',
        'order' => 1,
        'is_active' => true,
    ]);

    // Validasi menolak progress > 100
    $this->actingAs($this->user)
        ->put(route('system.sub-menus.update', $subMenu), [
            'menu_id' => $menu->id,
            'name' => 'Konseling',
            'route_prefix' => 'kesiswaan',
            'route_suffix' => 'konseling.pending',
            'dev_progress' => 150,
        ])
        ->assertSessionHasErrors(['dev_progress']);

    // Simpan progress valid
    $this->actingAs($this->user)
        ->put(route('system.sub-menus.update', $subMenu), [
            'menu_id' => $menu->id,
            'name' => 'Konseling',
            'route_prefix' => 'kesiswaan',
            'route_suffix' => 'konseling.pending',
            'is_active' => '1',
            'dev_progress' => 45,
            'dev_stage' => 'Desain Formulir Konseling',
            'dev_note' => 'Penyusunan validasi catatan BK',
            'dev_version' => 'v0.5-alpha',
        ])
        ->assertRedirect(route('system.sub-menus.index'));

    $this->assertDatabaseHas('sub_menus', [
        'id' => $subMenu->id,
        'dev_progress' => 45,
        'dev_stage' => 'Desain Formulir Konseling',
        'dev_version' => 'v0.5-alpha',
    ]);
});

test('under-development page suggests namespaced controller path for dotted routes', function () {
    $module = Module::create(['name' => 'MONITORING', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create(['module_id' => $module->id, 'name' => 'Monitoring Sekolah', 'order' => 1, 'is_active' => true]);
    $subMenu = SubMenu::create([
        'menu_id' => $menu->id,
        'name' => 'Node Sekolah',
        'route_name' => 'monitoring-sekolah.node',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('system.under-development', ['type' => 'submenu', 'id' => $subMenu->id]));

    $response->assertOk();
    $response->assertSee('php artisan make:controller MonitoringSekolah/NodeController');
    $response->assertSee("Route::get('/monitoring-sekolah/node', [NodeController::class, 'index'])->name('monitoring-sekolah.node');");
});
