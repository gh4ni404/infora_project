<?php

namespace App\Models\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

trait TracksDevelopmentProgress
{
    /**
     * Daftar tahapan pengembangan berurutan beserta bobot persentase kumulatifnya.
     *
     * @var array<string, array{label: string, weight: int, progress: int}>
     */
    public const array DEVELOPMENT_STAGES = [
        'migration' => ['label' => 'Migration', 'weight' => 10, 'progress' => 10],
        'model' => ['label' => 'Model', 'weight' => 15, 'progress' => 25],
        'request' => ['label' => 'Form Request', 'weight' => 15, 'progress' => 40],
        'controller' => ['label' => 'Controller', 'weight' => 15, 'progress' => 55],
        'route' => ['label' => 'Route', 'weight' => 10, 'progress' => 65],
        'view' => ['label' => 'View', 'weight' => 20, 'progress' => 85],
        'test' => ['label' => 'Test', 'weight' => 10, 'progress' => 95],
        'done' => ['label' => 'Selesai', 'weight' => 5, 'progress' => 100],
    ];

    /**
     * Dapatkan URL rute aktif jika progres sudah selesai (100%) dan rute terdaftar di aplikasi.
     */
    public function resolveRegisteredUrl(): ?string
    {
        if ((int) $this->dev_progress < 100 || blank($this->route_name)) {
            return null;
        }

        if (Route::has($this->route_name)) {
            return route($this->route_name);
        }

        if (Route::has($this->route_name.'.index')) {
            return route($this->route_name.'.index');
        }

        return null;
    }

    /**
     * Kembalikan cache key untuk status pengembangan item navigasi.
     */
    public static function developmentCacheKey(string $type, int $id): string
    {
        return "under_dev_status_{$type}_{$id}";
    }

    /**
     * Bersihkan cache status pengembangan saat item diperbarui atau dihapus.
     */
    public static function clearDevelopmentCache(string $type, int $id): void
    {
        Cache::forget(static::developmentCacheKey($type, $id));
    }

    /**
     * Susun payload status pengembangan berbasis data nyata dengan caching ringan.
     *
     * @return array{
     *     progress: int,
     *     stage: string,
     *     ready: bool,
     *     redirect_url: ?string,
     *     updated_at: ?string,
     *     updated_human: string
     * }
     */
    public static function cachedDevelopmentStatus(string $type, int $id): ?array
    {
        $cached = Cache::remember(
            static::developmentCacheKey($type, $id),
            now()->addMinutes(5),
            function () use ($id): ?array {
                $record = static::query()->find($id, [
                    'id',
                    'route_name',
                    'dev_progress',
                    'dev_stage',
                    'updated_at',
                ]);

                if (! $record) {
                    return null;
                }

                return [
                    'id' => $record->id,
                    'route_name' => $record->route_name,
                    'dev_progress' => max(0, min(100, (int) $record->dev_progress)),
                    'dev_stage' => $record->dev_stage,
                    'updated_at' => $record->updated_at?->toIso8601String(),
                ];
            }
        );

        if (! $cached) {
            return null;
        }

        $tempModel = new static;
        $tempModel->forceFill($cached);
        $redirectUrl = $tempModel->resolveRegisteredUrl();
        $isReady = $redirectUrl !== null;

        $progress = $isReady ? 100 : (int) $cached['dev_progress'];

        $defaultStage = match (true) {
            $isReady => 'Fitur Siap Digunakan',
            $progress === 0 => 'Tahap Persiapan Modul',
            $progress < 50 => 'Pengembangan Struktur & Skema',
            $progress < 85 => 'Integrasi Logika & Antarmuka',
            default => 'Tahap Pengujian & Finalisasi',
        };

        $updatedAt = $cached['updated_at'] ? Carbon::parse($cached['updated_at']) : null;

        return [
            'progress' => $progress,
            'stage' => filled($cached['dev_stage']) && ! $isReady ? (string) $cached['dev_stage'] : $defaultStage,
            'ready' => $isReady,
            'redirect_url' => $redirectUrl,
            'updated_at' => $cached['updated_at'],
            'updated_human' => $updatedAt ? $updatedAt->diffForHumans() : 'Baru saja',
        ];
    }
}
