<?php

namespace App\Http\Controllers\MasterControl;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterControl\UpdateTenantMenuPolicyRequest;
use App\Models\Module;
use App\Models\TenantMenuPolicy;
use App\Models\TenantNode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TenantMenuPolicyController extends Controller
{
    /**
     * Tampilkan matriks menu & sub-menu yang dapat diizinkan Master untuk Admin sekolah.
     */
    public function edit(TenantNode $tenantNode): View
    {
        $tenantNode->load(['snapshot', 'menuPolicies']);

        $existingPolicies = $tenantNode->menuPolicies
            ->keyBy(fn (TenantMenuPolicy $p) => $p->target_type.':'.$p->sync_key);

        $modulesTree = $this->resolveModulesTreeForNode($tenantNode);

        return view('master-control.nodes.menus', compact(
            'tenantNode',
            'modulesTree',
            'existingPolicies'
        ));
    }

    /**
     * Simpan kebijakan hak menu untuk node sekolah dan naikkan policy_version agar tersinkron di heartbeat.
     */
    public function update(UpdateTenantMenuPolicyRequest $request, TenantNode $tenantNode): RedirectResponse
    {
        $submittedItems = $request->validated('items', []);

        DB::transaction(function () use ($tenantNode, $submittedItems): void {
            $tenantNode->menuPolicies()->delete();

            $records = [];
            $now = now();

            foreach ($submittedItems as $item) {
                $records[] = [
                    'tenant_node_id' => $tenantNode->id,
                    'target_type' => $item['target_type'],
                    'sync_key' => $item['sync_key'],
                    'label_snapshot' => $item['label_snapshot'],
                    'is_allowed' => ! empty($item['is_allowed']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (! empty($records)) {
                TenantMenuPolicy::insert($records);
            }

            $tenantNode->increment('policy_version');
        });

        return redirect()
            ->route('master-control.nodes.menus.edit', $tenantNode)
            ->with('success', 'Hak akses menu untuk sekolah berhasil diperbarui. Perubahan akan otomatis tersinkron pada heartbeat berikutnya.');
    }

    /**
     * Susun pohon modul/menu dari snapshot terbaru sekolah atau katalog lokal Master sebagai fallback.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function resolveModulesTreeForNode(TenantNode $tenantNode): array
    {
        $snapshotTree = $tenantNode->snapshot?->modules_tree;
        if (is_array($snapshotTree) && count($snapshotTree) > 0) {
            return $snapshotTree;
        }

        return Module::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->with([
                'menus' => fn ($q) => $q->where('is_active', true)->orderBy('order')->with([
                    'subMenus' => fn ($sq) => $sq->where('is_active', true)->orderBy('order'),
                ]),
            ])
            ->get()
            ->map(function (Module $module): array {
                return [
                    'name' => $module->name,
                    'is_active' => (bool) $module->is_active,
                    'menus' => $module->menus->map(function ($menu): array {
                        return [
                            'name' => $menu->name,
                            'route_name' => $menu->route_name,
                            'sync_key' => $menu->resolveSyncKey(),
                            'is_active' => (bool) $menu->is_active,
                            'dev_stage' => $menu->dev_stage ?? 'planned',
                            'dev_progress' => (int) ($menu->dev_progress ?? 0),
                            'sub_menus' => $menu->subMenus->map(fn ($sub): array => [
                                'name' => $sub->name,
                                'route_name' => $sub->route_name,
                                'sync_key' => $sub->resolveSyncKey(),
                                'is_active' => (bool) $sub->is_active,
                                'dev_stage' => $sub->dev_stage ?? 'planned',
                                'dev_progress' => (int) ($sub->dev_progress ?? 0),
                            ])->all(),
                        ];
                    })->all(),
                ];
            })
            ->all();
    }
}
