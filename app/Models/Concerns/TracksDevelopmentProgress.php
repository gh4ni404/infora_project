<?php

namespace App\Models\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

trait TracksDevelopmentProgress
{
    /**
     * Dapatkan URL rute aktif jika rute menu/sub-menu sudah terdaftar di aplikasi.
     */
    public function resolveRegisteredUrl(): ?string
    {
        if (blank($this->route_name)) {
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
     *     note: string,
     *     version: string,
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
                    'dev_note',
                    'dev_version',
                    'updated_at',
                ]);

                if (! $record) {
                    return null;
                }

                return [
                    'id' => $record->id,
                    'route_name' => $record->route_name,
                    'dev_progress' => max(0, min(100, (int) ($record->dev_progress ?? 0))),
                    'dev_stage' => $record->dev_stage,
                    'dev_note' => $record->dev_note,
                    'dev_version' => $record->dev_version,
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

        $defaultNote = match (true) {
            $isReady => 'Implementasi selesai dan rute fitur sudah aktif.',
            $progress === 0 => 'Menunggu jadwal pengerjaan tim pengembang',
            $progress < 50 => 'Penyusunan skema data dan alur backend',
            $progress < 85 => 'Proses sinkronisasi komponen antarmuka',
            default => 'Uji coba kualitas dan persiapan rilis',
        };

        $updatedAt = $cached['updated_at'] ? Carbon::parse($cached['updated_at']) : null;

        return [
            'progress' => $progress,
            'stage' => filled($cached['dev_stage']) && ! $isReady ? (string) $cached['dev_stage'] : $defaultStage,
            'note' => filled($cached['dev_note']) && ! $isReady ? (string) $cached['dev_note'] : $defaultNote,
            'version' => filled($cached['dev_version']) ? (string) $cached['dev_version'] : ($isReady ? 'v1.0.0' : 'v0.9-alpha'),
            'ready' => $isReady,
            'redirect_url' => $redirectUrl,
            'updated_at' => $cached['updated_at'],
            'updated_human' => $updatedAt ? $updatedAt->diffForHumans() : 'Baru saja',
        ];
    }
}
