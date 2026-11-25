<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreHeartbeatRequest;
use App\Models\TenantMenuPolicy;
use App\Models\TenantNode;
use App\Models\TenantNodeSnapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HeartbeatController extends Controller
{
    /**
     * Terima heartbeat dan telemetry dari node sekolah serta kirim balik pembaruan hak menu.
     */
    public function store(StoreHeartbeatRequest $request): JsonResponse
    {
        /** @var TenantNode $node */
        $node = $request->attributes->get('tenant_node');
        $data = $request->validated();

        $dbStatus = (string) ($data['database']['status'] ?? 'down');
        $activeUsersCount = (int) ($data['activity']['active_users_count'] ?? 0);
        $maintenanceMode = (bool) ($data['runtime']['maintenance_mode'] ?? false);
        $appliedPolicyVersion = (int) ($data['policy']['applied_version'] ?? 0);

        $resolvedStatus = match (true) {
            $maintenanceMode => 'maintenance',
            $dbStatus === 'down' => 'degraded',
            $activeUsersCount > 0 => 'online',
            default => 'idle',
        };

        DB::transaction(function () use ($node, $data, $dbStatus, $activeUsersCount, $appliedPolicyVersion, $resolvedStatus): void {
            $node->update([
                'school_name' => $data['school']['name'] ?? $node->school_name,
                'npsn' => $data['school']['npsn'] ?? $node->npsn,
                'status' => $resolvedStatus,
                'db_status' => $dbStatus,
                'db_latency_ms' => $data['database']['latency_ms'] ?? null,
                'last_error_message' => $data['database']['error'] ?? null,
                'active_users_count' => $activeUsersCount,
                'last_applied_policy_version' => $appliedPolicyVersion,
                'app_version' => $data['runtime']['app_version'] ?? $node->app_version,
                'last_migration' => $data['runtime']['last_migration'] ?? $node->last_migration,
                'last_heartbeat_at' => now(),
            ]);

            if (! empty($data['telemetry'])) {
                $telemetry = $data['telemetry'];

                TenantNodeSnapshot::query()->updateOrCreate(
                    ['tenant_node_id' => $node->id],
                    [
                        'modules_count' => (int) ($telemetry['modules_count'] ?? 0),
                        'menus_count' => (int) ($telemetry['menus_count'] ?? 0),
                        'sub_menus_count' => (int) ($telemetry['sub_menus_count'] ?? 0),
                        'users_total' => (int) ($telemetry['users_total'] ?? 0),
                        'users_breakdown' => $telemetry['users_breakdown'] ?? [],
                        'dev_summary' => $telemetry['dev_summary'] ?? [],
                        'operational_summary' => $telemetry['operational_summary'] ?? [],
                        'modules_tree' => $telemetry['modules_tree'] ?? [],
                        'captured_at' => now(),
                    ]
                );
            }
        });

        $node->refresh();
        $syncRequired = $appliedPolicyVersion < $node->policy_version;

        $response = [
            'status' => 'ok',
            'server_time' => now()->toIso8601String(),
            'policy_version' => $node->policy_version,
            'sync_required' => $syncRequired,
        ];

        if ($syncRequired) {
            $policies = $node->menuPolicies()->get();

            $response['menu_policy'] = [
                'menus' => $policies
                    ->where('target_type', 'menu')
                    ->mapWithKeys(fn (TenantMenuPolicy $p) => [$p->sync_key => (bool) $p->is_allowed])
                    ->all(),
                'sub_menus' => $policies
                    ->where('target_type', 'sub_menu')
                    ->mapWithKeys(fn (TenantMenuPolicy $p) => [$p->sync_key => (bool) $p->is_allowed])
                    ->all(),
            ];
        }

        return response()->json($response);
    }
}
