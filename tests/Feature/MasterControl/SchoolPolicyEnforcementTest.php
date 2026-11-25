<?php

use App\Models\Menu;
use App\Models\Module;
use App\Models\User;
use App\Models\UserMenuPermission;
use App\Support\SidebarCache;

test('menus disabled by master are excluded from admin sidebar, permission matrix, and blocked by middleware', function () {
    config()->set('infora.node_role', 'school');

    $admin = User::factory()->create([
        'username' => 'admin_sekolah_a',
        'email' => 'admin_sekolah_a@infora.test',
        'user_type' => 'admin',
        'is_active' => true,
    ]);

    $superAdmin = User::factory()->create([
        'username' => 'superadmin_sekolah_a',
        'email' => 'superadmin_sekolah_a@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $module = Module::create(['name' => 'PENGATURAN SISTEM', 'order' => 1, 'is_active' => true]);

    $allowedMenu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Referensi',
        'route_name' => 'system.referensi.index',
        'order' => 1,
        'is_active' => true,
        'is_master_enabled' => true,
    ]);

    $lockedMenu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Backup Dan Restore',
        'route_name' => 'backup-restore.index',
        'order' => 2,
        'is_active' => true,
        'is_master_enabled' => false,
    ]);

    $targetUser = User::factory()->create([
        'username' => 'guru_sekolah_a',
        'email' => 'guru_sekolah_a@infora.test',
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    // Admin even has legacy permission row for both menus
    UserMenuPermission::create([
        'user_id' => $admin->id,
        'menu_id' => $allowedMenu->id,
        'can_view' => true,
    ]);
    UserMenuPermission::create([
        'user_id' => $admin->id,
        'menu_id' => $lockedMenu->id,
        'can_view' => true,
    ]);

    // Give admin access to menu management so they can open MenuAccessController::editUser
    $userAccessMenu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Akses Pengguna',
        'route_name' => 'sistem.user.index',
        'order' => 3,
        'is_active' => true,
        'is_master_enabled' => true,
    ]);
    UserMenuPermission::create([
        'user_id' => $admin->id,
        'menu_id' => $userAccessMenu->id,
        'can_view' => true,
    ]);

    SidebarCache::flushGlobal();

    // 1. Sidebar excludes locked menu
    $sidebarNames = SidebarCache::buildSidebar($admin)
        ->flatMap(fn ($m) => $m->menus->pluck('name'))
        ->all();

    expect($sidebarNames)->toContain('Referensi')
        ->not->toContain('Backup Dan Restore');

    // 2. Admin permission assignment UI hides locked menu
    $this->actingAs($admin)
        ->get(route('sistem.user.user', $targetUser))
        ->assertOk()
        ->assertSee('Referensi')
        ->assertDontSee('Backup Dan Restore');

    // 3. Direct access to locked route is denied for school admin (redirected to allowed page with error or 403)
    $blockedResponse = $this->actingAs($admin)->get(route('backup-restore.index'));
    expect(in_array($blockedResponse->getStatusCode(), [302, 403], true))->toBeTrue();
    if ($blockedResponse->getStatusCode() === 302) {
        $blockedResponse->assertSessionHas('error');
    }

    // 4. Super Admin still retains access on the school node
    $this->actingAs($superAdmin)
        ->get(route('backup-restore.index'))
        ->assertOk();

    // 5. Activity timestamp is populated when user interacts through middleware
    expect($admin->fresh()->last_seen_at)->not->toBeNull();
});
