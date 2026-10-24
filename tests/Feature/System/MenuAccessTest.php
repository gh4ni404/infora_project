<?php

use App\Models\Menu;
use App\Models\MenuAccessTemplate;
use App\Models\Module;
use App\Models\School;
use App\Models\SubMenu;
use App\Models\User;
use App\Models\UserMenuPermission;
use App\Support\AdminAccountManager;
use App\View\Composers\SidebarComposer;
use Database\Seeders\MenuAccessTemplateSeeder;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_acl',
        'email' => 'superadmin_acl@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->guruUser = User::factory()->create([
        'username' => 'guru_budi',
        'email' => 'budi@sekolah.test',
        'name' => 'Budi Sudarsono',
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $this->muridUser = User::factory()->create([
        'username' => 'murid_ani',
        'email' => 'ani@sekolah.test',
        'name' => 'Ani Lestari',
        'user_type' => 'murid',
        'is_active' => true,
    ]);

    $this->stafUser = User::factory()->create([
        'username' => 'staf_siti',
        'email' => 'siti@sekolah.test',
        'name' => 'Siti Rahmawati',
        'user_type' => 'staf',
        'is_active' => true,
    ]);
});

test('guest cannot access menu access routes', function () {
    $response = $this->get('/sistem/user');
    $response->assertRedirect('/login');
});

test('non-super admin cannot access menu access management', function () {
    $response = $this->actingAs($this->guruUser)->get('/sistem/user');
    $response->assertForbidden();
});

test('super admin can view menu access index with user list and template tab', function () {
    $this->seed(MenuAccessTemplateSeeder::class);

    $response = $this->actingAs($this->superAdmin)->get('/sistem/user');
    $response->assertOk();
    $response->assertSee('Tata Kelola Menu Akses');
    $response->assertSee('Budi Sudarsono');
    $response->assertSee('Ani Lestari');
    $response->assertSee('Siti Rahmawati');
    $response->assertSee('Staf (Tendik)');
    $response->assertSee('badge-role-staf');

    // Cek tab templates
    $responseTemplates = $this->actingAs($this->superAdmin)->get('/sistem/user?tab=templates');
    $responseTemplates->assertOk();
    $responseTemplates->assertSee('Admin Sekolah');
    $responseTemplates->assertSee('Guru Pengajar');
    $responseTemplates->assertSee('Wali Kelas');
    $responseTemplates->assertSee('Murid PKL');
});

test('super admin can filter users by role and search query', function () {
    $responseFilterGuru = $this->actingAs($this->superAdmin)->get('/sistem/user?role=guru');
    $responseFilterGuru->assertOk();
    $responseFilterGuru->assertSee('Budi Sudarsono');
    $responseFilterGuru->assertDontSee('Ani Lestari');
    $responseFilterGuru->assertDontSee('Siti Rahmawati');

    $responseFilterMurid = $this->actingAs($this->superAdmin)->get('/sistem/user?role=murid');
    $responseFilterMurid->assertOk();
    $responseFilterMurid->assertSee('Ani Lestari');
    $responseFilterMurid->assertDontSee('Budi Sudarsono');
    $responseFilterMurid->assertDontSee('Siti Rahmawati');

    $responseFilterStaf = $this->actingAs($this->superAdmin)->get('/sistem/user?role=staf');
    $responseFilterStaf->assertOk();
    $responseFilterStaf->assertSee('Siti Rahmawati');
    $responseFilterStaf->assertDontSee('Budi Sudarsono');
    $responseFilterStaf->assertDontSee('Ani Lestari');

    $responseFilterTendik = $this->actingAs($this->superAdmin)->get('/sistem/user?role=tendik');
    $responseFilterTendik->assertOk();
    $responseFilterTendik->assertSee('Siti Rahmawati');

    $responseSearch = $this->actingAs($this->superAdmin)->get('/sistem/user?search=Ani');
    $responseSearch->assertOk();
    $responseSearch->assertSee('Ani Lestari');
    $responseSearch->assertDontSee('Budi Sudarsono');
    $responseSearch->assertDontSee('Siti Rahmawati');
});

test('super admin can view and update granular permissions for a user', function () {
    $module = Module::create(['name' => 'AKADEMIK', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create(['module_id' => $module->id, 'name' => 'Kesiswaan', 'order' => 1, 'is_active' => true]);
    $subMenu = SubMenu::create(['menu_id' => $menu->id, 'name' => 'Data Siswa', 'order' => 1, 'is_active' => true]);

    $responseEdit = $this->actingAs($this->superAdmin)->get(route('sistem.user.user', $this->guruUser));
    $responseEdit->assertOk();
    $responseEdit->assertSee('Budi Sudarsono');
    $responseEdit->assertSee('Data Siswa');

    // Simpan izin granular
    $responseUpdate = $this->actingAs($this->superAdmin)->put(route('sistem.user.user.update', $this->guruUser), [
        'permissions' => [
            [
                'menu_id' => $menu->id,
                'sub_menu_id' => null,
                'can_view' => '1',
                'can_create' => null,
                'can_edit' => null,
                'can_delete' => null,
            ],
            [
                'menu_id' => null,
                'sub_menu_id' => $subMenu->id,
                'can_view' => '1',
                'can_create' => '1',
                'can_edit' => '1',
                'can_delete' => null, // Tidak boleh hapus
            ],
        ],
    ]);

    $responseUpdate->assertRedirect(route('sistem.user', ['role' => 'guru']));

    // Verifikasi di database
    $this->assertDatabaseHas('user_menu_permissions', [
        'user_id' => $this->guruUser->id,
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_create' => false,
    ]);

    $this->assertDatabaseHas('user_menu_permissions', [
        'user_id' => $this->guruUser->id,
        'sub_menu_id' => $subMenu->id,
        'can_view' => true,
        'can_create' => true,
        'can_edit' => true,
        'can_delete' => false,
    ]);

    // Verifikasi helper method pada Model User
    $this->guruUser->refresh();
    expect($this->guruUser->hasMenuAccess($menu, 'view'))->toBeTrue()
        ->and($this->guruUser->hasSubMenuAccess($subMenu, 'view'))->toBeTrue()
        ->and($this->guruUser->hasSubMenuAccess($subMenu, 'create'))->toBeTrue()
        ->and($this->guruUser->hasSubMenuAccess($subMenu, 'edit'))->toBeTrue()
        ->and($this->guruUser->hasSubMenuAccess($subMenu, 'delete'))->toBeFalse();
});

test('super admin can apply role template directly to a user', function () {
    $module = Module::create(['name' => 'KURIKULUM', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create(['module_id' => $module->id, 'name' => 'Jadwal Mengajar', 'order' => 1, 'is_active' => true]);

    MenuAccessTemplate::create([
        'role_key' => 'guru_pengajar',
        'role_name' => 'Guru Pengajar',
        'role_category' => 'guru',
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_create' => true,
        'can_edit' => true,
        'can_delete' => false,
    ]);

    $response = $this->actingAs($this->superAdmin)->post(route('sistem.user.user.apply-template', $this->guruUser), [
        'role_key' => 'guru_pengajar',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('user_menu_permissions', [
        'user_id' => $this->guruUser->id,
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_create' => true,
    ]);
});

test('super admin can configure role access template', function () {
    $this->seed(MenuAccessTemplateSeeder::class);

    $module = Module::create(['name' => 'INDUSTRI', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create(['module_id' => $module->id, 'name' => 'Daftar PKL', 'order' => 1, 'is_active' => true]);

    $responseView = $this->actingAs($this->superAdmin)->get(route('sistem.user.template', 'murid_pkl'));
    $responseView->assertOk();
    $responseView->assertSee('Daftar PKL');

    $responseUpdate = $this->actingAs($this->superAdmin)->put(route('sistem.user.template.update', 'murid_pkl'), [
        'role_name' => 'Murid PKL Berprestasi',
        'role_category' => 'murid',
        'permissions' => [
            [
                'menu_id' => $menu->id,
                'sub_menu_id' => null,
                'can_view' => '1',
                'can_create' => '1',
                'can_edit' => null,
                'can_delete' => null,
            ],
        ],
    ]);

    $responseUpdate->assertRedirect(route('sistem.user', ['tab' => 'templates']));

    $this->assertDatabaseHas('menu_access_templates', [
        'role_key' => 'murid_pkl',
        'role_name' => 'Murid PKL Berprestasi',
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_create' => true,
        'can_edit' => false,
    ]);
});

test('admin_sekolah template is seeded and can be applied to school admin', function () {
    $module = Module::create(['name' => 'ADMINISTRASI', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create(['module_id' => $module->id, 'name' => 'Data Guru', 'order' => 1, 'is_active' => true]);

    $this->seed(MenuAccessTemplateSeeder::class);

    $school = School::factory()->create(['npsn' => '87654321']);
    $admin = AdminAccountManager::createOrSyncAdmin($school);

    expect($admin->user_type)->toBe('admin');
    expect($admin->menuPermissions)->not->toBeEmpty();
    expect($admin->hasMenuAccess($menu, 'view'))->toBeTrue();
});

test('createOrSyncAdmin preserves existing permissions when syncing existing admin user', function () {
    $module = Module::create(['name' => 'MASTER', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create(['module_id' => $module->id, 'name' => 'Menu Khusus', 'order' => 1, 'is_active' => true]);

    $this->seed(MenuAccessTemplateSeeder::class);

    $school = School::factory()->create(['npsn' => '99887766']);
    $admin = AdminAccountManager::createOrSyncAdmin($school);

    // Kustomisasi izin secara spesifik untuk admin ini
    UserMenuPermission::updateOrCreate(
        ['user_id' => $admin->id, 'menu_id' => $menu->id, 'sub_menu_id' => null],
        ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true]
    );

    expect($admin->fresh()->hasMenuAccess($menu, 'edit'))->toBeTrue();

    // Jalankan sync ulang tanpa reset (seperti saat sekolah diupdate)
    $school->update(['name' => 'Nama Sekolah Baru']);
    $syncedAdmin = AdminAccountManager::createOrSyncAdmin($school);

    expect($syncedAdmin->fresh()->hasMenuAccess($menu, 'edit'))->toBeTrue();
    expect($syncedAdmin->name)->toBe('Admin Nama Sekolah Baru');
});

test('createOrSyncAdmin resets permissions when explicitly requested', function () {
    $module = Module::create(['name' => 'PENGATURAN SISTEM', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create(['module_id' => $module->id, 'name' => 'Menu Sistem Rahasia', 'order' => 1, 'is_active' => true]);

    $this->seed(MenuAccessTemplateSeeder::class);

    $school = School::factory()->create(['npsn' => '55443322']);
    $admin = AdminAccountManager::createOrSyncAdmin($school);

    // Beri izin ekstra yang bukan bagian dari template bawaan
    UserMenuPermission::create([
        'user_id' => $admin->id,
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_edit' => true,
    ]);

    expect($admin->fresh()->hasMenuAccess($menu, 'edit'))->toBeTrue();

    // Jalankan sync dengan resetPermissions = true
    $syncedAdmin = AdminAccountManager::createOrSyncAdmin($school, resetPermissions: true);

    // Izin kustom di luar template bawaan harus di-reset
    expect($syncedAdmin->fresh()->hasMenuAccess($menu, 'edit'))->toBeFalse();
});

test('sidebar composer filters visible menus based on user permissions for regular users', function () {
    $module1 = Module::create(['name' => 'MODUL RAHASIA', 'order' => 1, 'is_active' => true]);
    $menuRahasia = Menu::create(['module_id' => $module1->id, 'name' => 'Menu Rahasia', 'order' => 1, 'is_active' => true]);

    $module2 = Module::create(['name' => 'MODUL TERBUKA', 'order' => 2, 'is_active' => true]);
    $menuTerbuka = Menu::create(['module_id' => $module2->id, 'name' => 'Menu Terbuka', 'order' => 1, 'is_active' => true]);

    // Beri izin guru hanya untuk Modul Terbuka
    UserMenuPermission::create([
        'user_id' => $this->guruUser->id,
        'menu_id' => $menuTerbuka->id,
        'can_view' => true,
    ]);

    // Saat Guru mengakses dashboard
    $responseGuru = $this->actingAs($this->guruUser)->get('/dashboard');
    // Karena dashboard dilindungi middleware super_admin untuk saat ini, kita tes rendering view
    $viewData = (new SidebarComposer)->compose($view = view('dashboard', [
        'user' => $this->guruUser,
        'systemInfo' => ['laravel_version' => '11', 'php_version' => '8.4', 'environment' => 'testing'],
    ]));

    $modules = $view->getData()['sidebarModules'];
    $moduleNames = $modules->pluck('name')->all();

    expect($moduleNames)->toContain('MODUL TERBUKA')
        ->and($moduleNames)->not->toContain('MODUL RAHASIA');
});

test('user with dashboard permission can access dashboard route', function () {
    $mainModule = Module::create(['name' => 'NAVIGASI UTAMA', 'order' => 1, 'is_active' => true]);
    $dashboardMenu = Menu::create(['module_id' => $mainModule->id, 'name' => 'Dashboard', 'route_name' => 'dashboard', 'order' => 1, 'is_active' => true]);

    UserMenuPermission::create([
        'user_id' => $this->guruUser->id,
        'menu_id' => $dashboardMenu->id,
        'can_view' => true,
    ]);

    $response = $this->actingAs($this->guruUser)->get('/dashboard');
    $response->assertOk();
    $response->assertSee('Dashboard');
});

test('user without dashboard permission cannot access dashboard and is denied', function () {
    $mainModule = Module::create(['name' => 'NAVIGASI UTAMA', 'order' => 1, 'is_active' => true]);
    Menu::create(['module_id' => $mainModule->id, 'name' => 'Dashboard', 'route_name' => 'dashboard', 'order' => 1, 'is_active' => true]);

    // Guru belum diberi izin dashboard
    $response = $this->actingAs($this->guruUser)->get('/dashboard');
    $response->assertForbidden();
});

test('user with specific view permission can access resource index but cannot create without can_create', function () {
    $module = Module::create(['name' => 'ADMINISTRASI', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create(['module_id' => $module->id, 'name' => 'Data Sekolah', 'route_name' => 'master.data-sekolah.index', 'order' => 1, 'is_active' => true]);

    // Beri izin HANYA can_view = true (can_create = false)
    UserMenuPermission::create([
        'user_id' => $this->guruUser->id,
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_create' => false,
    ]);

    // 1. Bisa buka halaman index
    $responseIndex = $this->actingAs($this->guruUser)->get(route('master.data-sekolah.index'));
    $responseIndex->assertOk();

    // 2. Ditolak saat mencoba POST (tambah data)
    $responseStore = $this->actingAs($this->guruUser)->post(route('master.data-sekolah.store'), [
        'name' => 'SMK Negeri 1 Test',
        'npsn' => '12345678',
        'school_type' => 'SMK',
        'status' => 'Negeri',
    ]);
    $responseStore->assertForbidden();
});

test('user with can_create permission can perform store action on permitted resource', function () {
    $module = Module::create(['name' => 'ADMINISTRASI', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create(['module_id' => $module->id, 'name' => 'Data Sekolah', 'route_name' => 'master.data-sekolah.index', 'order' => 1, 'is_active' => true]);

    UserMenuPermission::create([
        'user_id' => $this->guruUser->id,
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_create' => true,
    ]);

    $responseStore = $this->actingAs($this->guruUser)->post(route('master.data-sekolah.store'), [
        'name' => 'SMK Negeri 2 Test',
        'npsn' => '87654321',
        'school_type' => 'SMK',
        'status' => 'Negeri',
    ]);

    $responseStore->assertRedirect(route('master.data-sekolah.index'));
    $this->assertDatabaseHas('schools', ['npsn' => '87654321']);
});

test('login redirects non-super admin to first allowed menu if dashboard not permitted', function () {
    $module = Module::create(['name' => 'ADMINISTRASI', 'order' => 1, 'is_active' => true]);
    $menuSekolah = Menu::create(['module_id' => $module->id, 'name' => 'Data Sekolah', 'route_name' => 'master.data-sekolah.index', 'order' => 1, 'is_active' => true]);

    // Guru hanya punya izin Data Sekolah, tidak punya Dashboard
    UserMenuPermission::create([
        'user_id' => $this->guruUser->id,
        'menu_id' => $menuSekolah->id,
        'can_view' => true,
    ]);

    $response = $this->post('/login', [
        'login' => $this->guruUser->username,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('master.data-sekolah.index'));
    $this->assertAuthenticatedAs($this->guruUser);
});

test('login rejects user with no active menu permissions', function () {
    $userWithoutPerms = User::factory()->create([
        'username' => 'user_polos',
        'password' => 'password',
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'login' => 'user_polos',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('login');
    $this->assertGuest();
});

test('super admin can activate inactive user from menu access page', function () {
    $inactiveUser = User::factory()->create([
        'username' => 'guru_nonaktif',
        'name' => 'Guru Nonaktif',
        'user_type' => 'guru',
        'is_active' => false,
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->patch(route('sistem.user.activate', $inactiveUser));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($inactiveUser->fresh()->is_active)->toBeTrue();
});
