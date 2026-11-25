<?php

use App\Models\Menu;
use App\Models\Module;
use App\Models\SubMenu;
use App\Models\SystemSetting;
use App\Models\TenantMenuPolicy;
use App\Models\TenantNode;
use App\Models\TenantNodeSnapshot;
use Illuminate\Support\Facades\Http;

function createSignedNode(array $overrides = []): array
{
    $creds = TenantNode::generateCredentials();
    $node = TenantNode::create(array_merge([
        'node_code' => 'smkn1-bone',
        'school_name' => 'SMKN 1 Bone',
        'npsn' => '40312345',
        'base_url' => 'https://smkn1bone.infora.test',
        'api_token_hash' => $creds['token_hash'],
        'hmac_secret' => $creds['hmac_secret'],
        'is_active' => true,
        'status' => 'pending',
        'db_status' => 'unknown',
        'policy_version' => 1,
    ], $overrides));

    return [$node, $creds['plain_token'], $creds['hmac_secret']];
}

function postSignedHeartbeat(string $nodeCode, string $token, string $secret, array $payload, ?int $timestamp = null)
{
    $ts = $timestamp ?? now()->timestamp;
    $raw = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $sig = hash_hmac('sha256', $ts.'.'.$raw, $secret);

    return test()->call(
        'POST',
        '/api/v1/master/heartbeat',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'HTTP_X_NODE_CODE' => $nodeCode,
            'HTTP_X_TIMESTAMP' => (string) $ts,
            'HTTP_X_SIGNATURE' => $sig,
        ],
        $raw
    );
}

test('heartbeat endpoint returns 404 when running in school mode', function () {
    config()->set('infora.node_role', 'school');
    [$node, $token, $secret] = createSignedNode();

    $response = postSignedHeartbeat($node->node_code, $token, $secret, [
        'database' => ['status' => 'up'],
    ]);

    $response->assertNotFound();
});

test('heartbeat endpoint rejects invalid signature or expired timestamp', function () {
    config()->set('infora.node_role', 'master');
    [$node, $token, $secret] = createSignedNode();

    $expired = postSignedHeartbeat(
        $node->node_code,
        $token,
        $secret,
        ['database' => ['status' => 'up']],
        now()->subMinutes(15)->timestamp
    );
    $expired->assertUnauthorized();

    $tampered = postSignedHeartbeat(
        $node->node_code,
        $token,
        'wrong-hmac-secret',
        ['database' => ['status' => 'up']]
    );
    $tampered->assertUnauthorized();
});

test('valid heartbeat updates node telemetry, snapshots, and returns menu policy when version differs', function () {
    config()->set('infora.node_role', 'master');
    [$node, $token, $secret] = createSignedNode(['policy_version' => 2]);

    TenantMenuPolicy::create([
        'tenant_node_id' => $node->id,
        'target_type' => 'menu',
        'sync_key' => 'route:backup-restore',
        'label_snapshot' => 'Backup Dan Restore',
        'is_allowed' => false,
    ]);

    $payload = [
        'school' => ['name' => 'SMKN 1 Bone', 'npsn' => '40312345'],
        'runtime' => ['app_version' => '1.2.0', 'maintenance_mode' => false, 'last_migration' => '2026_11_25_000001_test'],
        'database' => ['status' => 'up', 'latency_ms' => 7],
        'activity' => ['active_users_count' => 8],
        'policy' => ['applied_version' => 1],
        'telemetry' => [
            'modules_count' => 3,
            'menus_count' => 6,
            'sub_menus_count' => 9,
            'users_total' => 120,
            'users_breakdown' => ['admin' => 1, 'guru' => 19, 'murid' => 100],
            'dev_summary' => ['average_progress' => 75],
            'modules_tree' => [['name' => 'SISTEM', 'is_active' => true, 'menus' => []]],
        ],
    ];

    $response = postSignedHeartbeat($node->node_code, $token, $secret, $payload);

    $response->assertOk()->assertJson([
        'status' => 'ok',
        'policy_version' => 2,
        'sync_required' => true,
        'menu_policy' => [
            'menus' => ['route:backup-restore' => false],
            'sub_menus' => [],
        ],
    ]);

    $node->refresh();
    expect($node->computed_status)->toBe('online')
        ->and($node->db_status)->toBe('up')
        ->and($node->db_latency_ms)->toBe(7)
        ->and($node->active_users_count)->toBe(8)
        ->and(TenantNodeSnapshot::where('tenant_node_id', $node->id)->count())->toBe(1);

    // Next heartbeat with applied_version = 2 and 0 active users switches status to idle without resync
    $payload['policy']['applied_version'] = 2;
    $payload['activity']['active_users_count'] = 0;

    $second = postSignedHeartbeat($node->node_code, $token, $secret, $payload);
    $second->assertOk()->assertJson([
        'status' => 'ok',
        'policy_version' => 2,
        'sync_required' => false,
    ]);

    $node->refresh();
    expect($node->computed_status)->toBe('idle');

    // Simulate missed heartbeats past TTL => offline
    $node->forceFill(['last_heartbeat_at' => now()->subMinutes(10)])->save();
    expect($node->fresh()->computed_status)->toBe('offline');
});

test('heartbeat reports degraded status when database check fails', function () {
    config()->set('infora.node_role', 'master');
    [$node, $token, $secret] = createSignedNode();

    $response = postSignedHeartbeat($node->node_code, $token, $secret, [
        'database' => [
            'status' => 'down',
            'error' => 'SQLSTATE[HY000] Connection refused',
        ],
        'activity' => ['active_users_count' => 0],
    ]);

    $response->assertOk();
    $node->refresh();

    expect($node->computed_status)->toBe('degraded')
        ->and($node->last_error_message)->toContain('Connection refused');
});

test('school heartbeat command sends signed request and applies menu policy sync', function () {
    config()->set('infora.node_role', 'school');
    config()->set('infora.master_url', 'https://master.infora.test');
    config()->set('infora.node_code', 'smkn1-bone');
    config()->set('infora.node_token', 'token-test-123');
    config()->set('infora.node_secret', 'secret-test-456');

    $module = Module::create(['name' => 'SISTEM', 'order' => 1, 'is_active' => true]);
    $menu = Menu::create([
        'module_id' => $module->id,
        'name' => 'Backup Dan Restore',
        'route_name' => 'backup-restore.index',
        'order' => 1,
        'is_active' => true,
        'is_master_enabled' => true,
    ]);
    $sub = SubMenu::create([
        'menu_id' => $menu->id,
        'name' => 'Referensi',
        'route_name' => 'system.referensi.index',
        'order' => 1,
        'is_active' => true,
        'is_master_enabled' => true,
    ]);

    Http::fake([
        'https://master.infora.test/api/v1/master/heartbeat' => Http::response([
            'status' => 'ok',
            'policy_version' => 5,
            'sync_required' => true,
            'menu_policy' => [
                'menus' => ['route:backup-restore' => false],
                'sub_menus' => ['route:system.referensi' => false],
            ],
        ], 200),
    ]);

    $this->artisan('infora:heartbeat')->assertExitCode(0);

    expect($menu->fresh()->is_master_enabled)->toBeFalse()
        ->and($sub->fresh()->is_master_enabled)->toBeFalse()
        ->and(SystemSetting::getValue('master_policy_version'))->toBe('5');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://master.infora.test/api/v1/master/heartbeat'
            && $request->hasHeader('X-Node-Code', 'smkn1-bone')
            && $request->hasHeader('X-Signature')
            && $request->hasHeader('X-Timestamp');
    });
});
