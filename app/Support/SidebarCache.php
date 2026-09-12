<?php

namespace App\Support;

use App\Models\Menu;
use App\Models\Module;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SidebarCache
{
    public const VERSION_KEY = 'infora_sidebar_nav_version';

    public const CACHE_PREFIX = 'infora_sidebar_nav_';

    public const TTL_HOURS = 24;

    /**
     * Daftar class yang diizinkan untuk di-unserialize secara aman.
     */
    protected const ALLOWED_CLASSES = [
        Collection::class,
        EloquentCollection::class,
        Module::class,
        Menu::class,
        SubMenu::class,
    ];

    /**
     * Dapatkan modul sidebar untuk user dari cache.
     * Menggunakan pola Cache Versioning: infora_sidebar_v{version}_user_{id}.
     */
    public static function get(?User $user): Collection
    {
        if (! Schema::hasTable('modules')) {
            return new Collection;
        }

        if (! $user) {
            return new Collection;
        }

        $version = self::version();
        $cacheKey = self::CACHE_PREFIX."v{$version}_user_{$user->id}";

        $serialized = Cache::remember($cacheKey, now()->addHours(self::TTL_HOURS), function () use ($user) {
            return serialize(self::buildSidebar($user));
        });

        if (! is_string($serialized)) {
            $sidebar = self::buildSidebar($user);
            Cache::put($cacheKey, serialize($sidebar), now()->addHours(self::TTL_HOURS));

            return $sidebar;
        }

        $unpacked = unserialize($serialized, ['allowed_classes' => self::ALLOWED_CLASSES]);

        if (! $unpacked instanceof Collection) {
            return self::buildSidebar($user);
        }

        return $unpacked;
    }

    /**
     * Bangun struktur modul, menu, dan submenu yang telah difilter sesuai hak akses user.
     */
    public static function buildSidebar(User $user): Collection
    {
        $sidebarModules = Module::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->with([
                'menus' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('order')
                        ->with([
                            'subMenus' => function ($subQuery) {
                                $subQuery->where('is_active', true)
                                    ->orderBy('order');
                            },
                        ]);
                },
            ])
            ->get();

        if ($user->isSuperAdmin()) {
            return $sidebarModules;
        }

        $user->loadMissing('menuPermissions');
        $allowedMenuIds = $user->menuPermissions
            ->where('can_view', true)
            ->pluck('menu_id')
            ->filter()
            ->flip()
            ->all();

        $allowedSubMenuIds = $user->menuPermissions
            ->where('can_view', true)
            ->pluck('sub_menu_id')
            ->filter()
            ->flip()
            ->all();

        return $sidebarModules->filter(function (Module $module) use ($allowedMenuIds, $allowedSubMenuIds) {
            $filteredMenus = $module->menus->filter(function ($menu) use ($allowedMenuIds, $allowedSubMenuIds) {
                if ($menu->subMenus->isNotEmpty()) {
                    $menu->setRelation('subMenus', $menu->subMenus->filter(
                        fn ($sub) => isset($allowedSubMenuIds[$sub->id])
                    ));

                    return $menu->subMenus->isNotEmpty() || isset($allowedMenuIds[$menu->id]);
                }

                return isset($allowedMenuIds[$menu->id]);
            });

            $module->setRelation('menus', $filteredMenus);

            return $module->menus->isNotEmpty();
        });
    }

    /**
     * Dapatkan versi cache global saat ini.
     */
    public static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    /**
     * Hapus cache khusus untuk satu user (misal saat hak akses personal diubah).
     */
    public static function forgetUser(int|string $userId): void
    {
        $version = self::version();
        Cache::forget(self::CACHE_PREFIX."v{$version}_user_{$userId}");
    }

    /**
     * Invalidasi seluruh cache sidebar untuk semua user secara instan (Cache Versioning).
     * Dipanggil saat Admin menambah/mengedit Modul, Menu, SubMenu, atau Template Peran.
     */
    public static function flushGlobal(): void
    {
        $current = (int) Cache::get(self::VERSION_KEY, 1);
        Cache::forever(self::VERSION_KEY, $current + 1);
    }
}
