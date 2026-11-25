<?php

use App\Models\Menu;
use App\Models\Module;
use App\Models\SubMenu;
use App\Models\TenantMenuPolicy;
use App\Models\TenantNode;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'master_super',
        'email' => 'master_super@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->schoolAdmin = User::factory()->create([
        'username' => 'school_admin_test',
        'email' => 'school_admin_test@infora.test',
        'user_type' => 'admin',
        'is_active' => true,
    ]);
});

test('master control routes return 404 when node role is school', function () {
    config()->set('infora.node_role', 'school');

    $this->actingAs($this->superAdmin)
        ->get('/master-control/nodes')
        ->assertNotFound();

    $this->actingAs($this->superAdmin)
        ->get('/master-control/server-status')
        ->assertNotFound();
});

test('school operational routes return 404 when node role is master', function () {
    config()->set('infora.node_role', 'master');

    $this->actingAs($this->superAdmin)
        ->get('/master/data-murid')
        ->assertNotFound();

    $this->actingAs($this->superAdmin)
        ->get('/absen_murid')
        ->assertNotFound();

    $this->actingAs($this->superAdmin)
        ->get('/dashboard/admin')
        ->assertNotFound();
});

test('non super admin cannot access master control portal in master mode', function () {
    config()->set('infora.node_role', 'master');

    $this->actingAs($this->schoolAdmin)
        ->get('/master-control/nodes')
        ->assertForbidden();
});

test('super admin can register, inspect, ping, and update menu policies for a school node in master mode', function () {
    config()->set('infora.node_role', 'master');

    $module = Module::create(['name' => 'KESISWAAN', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Absen Murid',
        'route_name' => 'absen_murid.index',
        'order' => 1,
        'is_active' => true,
    ]);
    $subMenu = SubMenu::create([
        'menu_id' => $menu->id,
        'name' => 'Rekap Harian',
        'route_name' => 'absen_murid.rekap',
        'order' => 1,
        'is_active' => true,
    ]);

    // 1. Register new tenant node
    $storeResponse = $this->actingAs($this->superAdmin)->post('/master-control/nodes', [
        'school_name' => 'SMA Negeri 2 Makassar',
        'node_code' => 'sman2-mks',
        'npsn' => '40300111',
        'base_url' => 'https://sman2mks.infora.test',
        'is_active' => '1',
    ]);

    $node = TenantNode::where('node_code', 'sman2-mks')->firstOrFail();
    $storeResponse->assertRedirect(route('master-control.nodes.show', $node));
    $storeResponse->assertSessionHas('generated_credentials');

    // 2. View dashboard, nodes index, server status, and node detail
    $this->actingAs($this->superAdmin)->get('/dashboard')->assertOk()->assertSee('SMA Negeri 2 Makassar');
    $this->actingAs($this->superAdmin)->get('/master-control/nodes')->assertOk()->assertSee('sman2-mks');
    $this->actingAs($this->superAdmin)->get('/master-control/server-status')->assertOk()->assertSee('SMA Negeri 2 Makassar');
    $this->actingAs($this->superAdmin)->get(route('master-control.nodes.show', $node))->assertOk();

    // 3. Ping school server /up
    Http::fake([
        'https://sman2mks.infora.test/up' => Http::response('OK', 200),
    ]);

    $this->actingAs($this->superAdmin)
        ->post(route('master-control.nodes.ping', $node))
        ->assertSessionHas('success');

    // 4. Update menu policy for this node and verify policy_version increments
    $this->actingAs($this->superAdmin)
        ->get(route('master-control.nodes.menus.edit', $node))
        ->assertOk()
        ->assertSee('Absen Murid');

    $policyResponse = $this->actingAs($this->superAdmin)->put(route('master-control.nodes.menus.update', $node), [
        'items' => [
            [
                'target_type' => 'menu',
                'sync_key' => $menu->resolveSyncKey(),
                'label_snapshot' => $menu->name,
                'is_allowed' => '0',
            ],
            [
                'target_type' => 'sub_menu',
                'sync_key' => $subMenu->resolveSyncKey(),
                'label_snapshot' => $subMenu->name,
                'is_allowed' => '1',
            ],
        ],
    ]);

    $policyResponse->assertRedirect(route('master-control.nodes.menus.edit', $node));

    $node->refresh();
    expect($node->policy_version)->toBe(2)
        ->and(TenantMenuPolicy::where('tenant_node_id', $node->id)->where('is_allowed', false)->count())->toBe(1);
});
