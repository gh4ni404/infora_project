<?php

namespace App\Support;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Menu;
use App\Models\Module;
use App\Models\Murid;
use App\Models\School;
use App\Models\SubMenu;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Throwable;

class MasterHeartbeatClient
{
    /**
     * Kumpulkan payload telemetry lengkap milik node sekolah saat ini.
     *
     * @return array<string, mixed>
     */
    public function collectPayload(?bool $maintenanceMode = null): array
    {
        $dbStatus = 'up';
        $dbLatencyMs = null;
        $dbError = null;

        try {
            $start = hrtime(true);
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $dbLatencyMs = (int) round((hrtime(true) - $start) / 1_000_000);
        } catch (Throwable $e) {
            $dbStatus = 'down';
            $dbError = mb_substr($e->getMessage(), 0, 1000);
        }

        $schoolInfo = ['name' => null, 'npsn' => null];
        $lastMigration = null;
        $activeUsersCount = 0;
        $appliedPolicyVersion = 0;
        $telemetry = [];

        if ($dbStatus === 'up') {
            $school = Schema::hasTable('schools') ? School::current() : null;
            if ($school) {
                $schoolInfo = [
                    'name' => $school->name,
                    'npsn' => $school->npsn,
                ];
            }

            if (Schema::hasTable('migrations')) {
                $lastMigration = DB::table('migrations')->orderByDesc('id')->value('migration');
            }

            if (Schema::hasTable('users')) {
                $idleMinutes = (int) config('infora.heartbeat.idle_minutes', 15);
                $activeUsersCount = User::query()
                    ->where('is_active', true)
                    ->where('last_seen_at', '>=', now()->subMinutes($idleMinutes))
                    ->count();
            }

            if (Schema::hasTable('system_settings')) {
                $appliedPolicyVersion = (int) SystemSetting::getValue('master_policy_version', '0');
            }

            $telemetry = $this->collectTelemetry();
        }

        return [
            'sent_at' => now()->toIso8601String(),
            'school' => $schoolInfo,
            'runtime' => [
                'app_version' => (string) config('app.version', '1.0.0'),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'maintenance_mode' => $maintenanceMode ?? app()->isDownForMaintenance(),
                'last_migration' => $lastMigration,
            ],
            'database' => [
                'status' => $dbStatus,
                'latency_ms' => $dbLatencyMs,
                'error' => $dbError,
            ],
            'activity' => [
                'active_users_count' => $activeUsersCount,
            ],
            'policy' => [
                'applied_version' => $appliedPolicyVersion,
            ],
            'telemetry' => $telemetry,
        ];
    }

    /**
     * Kumpulkan statistik modul, menu, sub-menu, user, dan data operasional sekolah.
     *
     * @return array<string, mixed>
     */
    protected function collectTelemetry(): array
    {
        $modules = Schema::hasTable('modules')
            ? Module::query()
                ->orderBy('order')
                ->with(['menus' => fn ($q) => $q->orderBy('order')->with(['subMenus' => fn ($sq) => $sq->orderBy('order')])])
                ->get()
            : collect();

        $menusCount = 0;
        $subMenusCount = 0;
        $stageBreakdown = [];
        $progressValues = [];
        $modulesTree = [];

        foreach ($modules as $module) {
            $menuItems = [];
            foreach ($module->menus as $menu) {
                $menusCount++;
                $stage = $menu->dev_stage ?? 'planned';
                $stageBreakdown[$stage] = ($stageBreakdown[$stage] ?? 0) + 1;
                $progressValues[] = (int) ($menu->dev_progress ?? 0);

                $subMenuItems = [];
                foreach ($menu->subMenus as $subMenu) {
                    $subMenusCount++;
                    $subStage = $subMenu->dev_stage ?? 'planned';
                    $stageBreakdown[$subStage] = ($stageBreakdown[$subStage] ?? 0) + 1;
                    $progressValues[] = (int) ($subMenu->dev_progress ?? 0);

                    $subMenuItems[] = [
                        'name' => $subMenu->name,
                        'route_name' => $subMenu->route_name,
                        'sync_key' => $subMenu->resolveSyncKey(),
                        'is_active' => (bool) $subMenu->is_active,
                        'is_master_enabled' => (bool) $subMenu->is_master_enabled,
                        'dev_stage' => $subStage,
                        'dev_progress' => (int) ($subMenu->dev_progress ?? 0),
                        'dev_version' => $subMenu->dev_version,
                    ];
                }

                $menuItems[] = [
                    'name' => $menu->name,
                    'route_name' => $menu->route_name,
                    'sync_key' => $menu->resolveSyncKey(),
                    'is_active' => (bool) $menu->is_active,
                    'is_master_enabled' => (bool) $menu->is_master_enabled,
                    'dev_stage' => $stage,
                    'dev_progress' => (int) ($menu->dev_progress ?? 0),
                    'dev_version' => $menu->dev_version,
                    'sub_menus' => $subMenuItems,
                ];
            }

            $modulesTree[] = [
                'name' => $module->name,
                'is_active' => (bool) $module->is_active,
                'menus' => $menuItems,
            ];
        }

        $usersBreakdown = [];
        $usersTotal = 0;
        if (Schema::hasTable('users')) {
            $rawCounts = User::query()
                ->selectRaw('user_type, count(*) as aggregate')
                ->groupBy('user_type')
                ->pluck('aggregate', 'user_type')
                ->all();

            foreach (['super_admin', 'admin', 'guru', 'staf', 'murid'] as $role) {
                $count = (int) ($rawCounts[$role] ?? 0);
                $usersBreakdown[$role] = $count;
                $usersTotal += $count;
            }
        }

        $school = Schema::hasTable('schools') ? School::current() : null;

        return [
            'modules_count' => $modules->count(),
            'menus_count' => $menusCount,
            'sub_menus_count' => $subMenusCount,
            'users_total' => $usersTotal,
            'users_breakdown' => $usersBreakdown,
            'dev_summary' => [
                'average_progress' => count($progressValues) > 0 ? (int) round(array_sum($progressValues) / count($progressValues)) : 0,
                'stages' => $stageBreakdown,
            ],
            'operational_summary' => [
                'murids_count' => Schema::hasTable('murids') ? Murid::query()->count() : 0,
                'kelas_count' => Schema::hasTable('kelas') ? Kelas::query()->count() : 0,
                'jurusan_count' => Schema::hasTable('jurusan') ? Jurusan::query()->count() : 0,
                'mapel_count' => Schema::hasTable('mapel') ? Mapel::query()->count() : 0,
                'tahun_ajaran_aktif' => $school?->activeTahunAjaran()?->tahun,
            ],
            'modules_tree' => $modulesTree,
        ];
    }

    /**
     * Kirim heartbeat bertanda tangan HMAC ke Portal Master dan terapkan sinkronisasi menu jika ada.
     *
     * @return array{ok: bool, status_code: int|null, message: string, synced: bool}
     */
    public function send(?bool $maintenanceMode = null): array
    {
        if (config('infora.node_role') !== 'school') {
            return [
                'ok' => false,
                'status_code' => null,
                'message' => 'Node tidak berada pada mode school.',
                'synced' => false,
            ];
        }

        $masterUrl = (string) config('infora.master_url');
        $nodeCode = (string) config('infora.node_code');
        $nodeToken = (string) config('infora.node_token');
        $nodeSecret = (string) config('infora.node_secret');

        if ($masterUrl === '' || $nodeCode === '' || $nodeToken === '' || $nodeSecret === '') {
            return [
                'ok' => false,
                'status_code' => null,
                'message' => 'Konfigurasi INFORA_MASTER_URL atau kredensial node belum lengkap.',
                'synced' => false,
            ];
        }

        $payload = $this->collectPayload($maintenanceMode);
        $rawBody = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}';
        $timestamp = (string) now()->timestamp;
        $signature = hash_hmac('sha256', $timestamp.'.'.$rawBody, $nodeSecret);
        $timeout = (int) config('infora.heartbeat.timeout_seconds', 8);

        try {
            $response = Http::timeout($timeout)
                ->withToken($nodeToken)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'X-Node-Code' => $nodeCode,
                    'X-Timestamp' => $timestamp,
                    'X-Signature' => $signature,
                ])
                ->withBody($rawBody, 'application/json')
                ->post($masterUrl.'/api/v1/master/heartbeat');
        } catch (Throwable $e) {
            $this->recordLocalSetting('master_last_heartbeat_status', 'failed: '.$e->getMessage());

            return [
                'ok' => false,
                'status_code' => null,
                'message' => 'Gagal menghubungi Master: '.$e->getMessage(),
                'synced' => false,
            ];
        }

        if (! $response->successful()) {
            $this->recordLocalSetting('master_last_heartbeat_status', 'http_'.$response->status());

            return [
                'ok' => false,
                'status_code' => $response->status(),
                'message' => 'Master mengembalikan status HTTP '.$response->status().'.',
                'synced' => false,
            ];
        }

        $data = $response->json() ?? [];
        $synced = false;

        if (! empty($data['sync_required']) && isset($data['menu_policy']) && is_array($data['menu_policy'])) {
            $this->applyMenuPolicy($data['menu_policy'], (int) ($data['policy_version'] ?? 1));
            $synced = true;
        }

        $this->recordLocalSetting('master_last_heartbeat_at', now()->toIso8601String());
        $this->recordLocalSetting('master_last_heartbeat_status', 'ok');

        return [
            'ok' => true,
            'status_code' => $response->status(),
            'message' => $synced
                ? 'Heartbeat berhasil dikirim dan kebijakan menu berhasil disinkronkan.'
                : 'Heartbeat berhasil dikirim.',
            'synced' => $synced,
        ];
    }

    /**
     * Terapkan kebijakan izin menu dari Master ke tabel lokal menus & sub_menus.
     *
     * @param  array{menus?: array<string, bool>, sub_menus?: array<string, bool>}  $menuPolicy
     */
    public function applyMenuPolicy(array $menuPolicy, int $policyVersion): void
    {
        $menuRules = $menuPolicy['menus'] ?? [];
        $subMenuRules = $menuPolicy['sub_menus'] ?? [];

        DB::transaction(function () use ($menuRules, $subMenuRules, $policyVersion): void {
            if (Schema::hasTable('menus')) {
                foreach (Menu::with('module')->get() as $menu) {
                    $syncKey = $menu->resolveSyncKey();
                    $allowed = array_key_exists($syncKey, $menuRules) ? (bool) $menuRules[$syncKey] : true;

                    if ((bool) $menu->is_master_enabled !== $allowed || $menu->sync_key !== $syncKey) {
                        $menu->is_master_enabled = $allowed;
                        $menu->sync_key = $syncKey;
                        $menu->saveQuietly();
                    }
                }
            }

            if (Schema::hasTable('sub_menus')) {
                foreach (SubMenu::with('menu')->get() as $subMenu) {
                    $syncKey = $subMenu->resolveSyncKey();
                    $allowed = array_key_exists($syncKey, $subMenuRules) ? (bool) $subMenuRules[$syncKey] : true;

                    if ((bool) $subMenu->is_master_enabled !== $allowed || $subMenu->sync_key !== $syncKey) {
                        $subMenu->is_master_enabled = $allowed;
                        $subMenu->sync_key = $syncKey;
                        $subMenu->saveQuietly();
                    }
                }
            }

            if (Schema::hasTable('system_settings')) {
                SystemSetting::setValue('master_policy_version', (string) $policyVersion);
                SystemSetting::setValue('master_last_synced_at', now()->toIso8601String());
            }
        });

        SidebarCache::flushGlobal();
    }

    /**
     * Simpan status ringkas heartbeat lokal bila tabel system_settings tersedia.
     */
    protected function recordLocalSetting(string $key, string $value): void
    {
        try {
            if (Schema::hasTable('system_settings')) {
                SystemSetting::setValue($key, $value);
            }
        } catch (Throwable) {
            // Abaikan bila DB lokal sedang bermasalah agar pelaporan error tetap berjalan
        }
    }
}
