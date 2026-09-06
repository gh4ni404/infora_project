<?php

use App\Models\Menu;
use App\Models\MenuAccessTemplate;
use App\Models\Module;
use App\Models\SubMenu;
use App\Models\User;
use App\Models\UserMenuPermission;
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

    $this->siswaUser = User::factory()->create([
        'username' => 'siswa_ani',
        'email' => 'ani@sekolah.test',
        'name' => 'Ani Lestari',
        'user_type' => 'siswa',
        'is_active' => true,
    ]);
});

test('guest cannot access menu access routes', function () {
    $response = $this->get('/sistem/menu-akses');
    $response->assertRedirect('/login');
});

test('non-super admin cannot access menu access management', function () {
    $response = $this->actingAs($this->guruUser)->get('/sistem/menu-akses');
    $response->assertForbidden();
});

test('super admin can view menu access index with user list and template tab', function () {
    $this->seed(MenuAccessTemplateSeeder::class);

    $response = $this->actingAs($this->superAdmin)->get('/sistem/menu-akses');
    $response->assertOk();
    $response->assertSee('Tata Kelola Menu Akses');
    $response->assertSee('Budi Sudarsono');
    $response->assertSee('Ani Lestari');

    // Cek tab templates
    $responseTemplates = $this->actingAs($this->superAdmin)->get('/sistem/menu-akses?tab=templates');
    $responseTemplates->assertOk();
    $responseTemplates->assertSee('Guru Pengajar');
    $responseTemplates->assertSee('Wali Kelas');
    $responseTemplates->assertSee('Siswa PKL');
});

test('super admin can filter users by role and search query', function () {
    $responseFilterGuru = $this->actingAs($this->superAdmin)->get('/sistem/menu-akses?role=guru');
    $responseFilterGuru->assertOk();
    $responseFilterGuru->assertSee('Budi Sudarsono');
    $responseFilterGuru->assertDontSee('Ani Lestari');

    $responseSearch = $this->actingAs($this->superAdmin)->get('/sistem/menu-akses?search=Ani');
    $responseSearch->assertOk();
    $responseSearch->assertSee('Ani Lestari');
    $responseSearch->assertDontSee('Budi Sudarsono');
});

test('super admin can view and update granular permissions for a user', function () {
    $module = Module::create(['name' => 'AKADEMIK', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create(['module_id' => $module->id, 'name' => 'Kesiswaan', 'order' => 1, 'is_active' => true]);
    $subMenu = SubMenu::create(['menu_id' => $menu->id, 'name' => 'Data Siswa', 'order' => 1, 'is_active' => true]);

    $responseEdit = $this->actingAs($this->superAdmin)->get(route('sistem.menu-akses.user', $this->guruUser));
    $responseEdit->assertOk();
    $responseEdit->assertSee('Budi Sudarsono');
    $responseEdit->assertSee('Data Siswa');

    // Simpan izin granular
    $responseUpdate = $this->actingAs($this->superAdmin)->put(route('sistem.menu-akses.user.update', $this->guruUser), [
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

    $responseUpdate->assertRedirect(route('sistem.menu-akses', ['role' => 'guru']));

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

    $response = $this->actingAs($this->superAdmin)->post(route('sistem.menu-akses.user.apply-template', $this->guruUser), [
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

    $responseView = $this->actingAs($this->superAdmin)->get(route('sistem.menu-akses.template', 'siswa_pkl'));
    $responseView->assertOk();
    $responseView->assertSee('Daftar PKL');

    $responseUpdate = $this->actingAs($this->superAdmin)->put(route('sistem.menu-akses.template.update', 'siswa_pkl'), [
        'role_name' => 'Siswa PKL Berprestasi',
        'role_category' => 'siswa',
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

    $responseUpdate->assertRedirect(route('sistem.menu-akses', ['tab' => 'templates']));

    $this->assertDatabaseHas('menu_access_templates', [
        'role_key' => 'siswa_pkl',
        'role_name' => 'Siswa PKL Berprestasi',
        'menu_id' => $menu->id,
        'can_view' => true,
        'can_create' => true,
        'can_edit' => false,
    ]);
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
