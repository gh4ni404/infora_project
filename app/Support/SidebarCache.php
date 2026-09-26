<?php

namespace App\Support;

use App\Models\Menu;
use App\Models\MenuAccessTemplate;
use App\Models\Module;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
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
        MenuAccessTemplate::class,
    ];

    /**
     * Dapatkan modul sidebar untuk user dari cache.
     * Menggunakan pola Cache Versioning: infora_sidebar_v{version}_user_{id}.
     */
    public static function get(?User $user): Collection
    {
        if (app()->runningInConsole() && ! Schema::hasTable('modules')) {
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
     * Dapatkan pohon hirarki modul, menu, dan submenu aktif dari cache.
     * Digunakan bersama oleh Sidebar, MenuAccessController (editUser, editTemplate), dll.
     */
    public static function getActiveTree(): Collection
    {
        $version = self::version();
        $cacheKey = "infora_active_modules_tree_v{$version}";

        $serialized = Cache::remember($cacheKey, now()->addHours(self::TTL_HOURS), function () {
            return serialize(self::fetchActiveTree());
        });

        if (! is_string($serialized)) {
            $tree = self::fetchActiveTree();
            Cache::put($cacheKey, serialize($tree), now()->addHours(self::TTL_HOURS));

            return $tree;
        }

        $unpacked = unserialize($serialized, ['allowed_classes' => self::ALLOWED_CLASSES]);

        if (! $unpacked instanceof Collection) {
            return self::fetchActiveTree();
        }

        return $unpacked;
    }

    /**
     * Ambil pohon hirarki modul, menu, dan submenu aktif langsung dari database.
     */
    protected static function fetchActiveTree(): Collection
    {
        return Module::query()
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
    }

    /**
     * Dapatkan daftar template peran yang tersedia dari cache.
     */
    public static function getRoleTemplates(): Collection
    {
        $version = self::version();
        $cacheKey = "infora_role_templates_v{$version}";

        $serialized = Cache::remember($cacheKey, now()->addHours(self::TTL_HOURS), function () {
            return serialize(
                MenuAccessTemplate::query()
                    ->select('role_key', 'role_name', 'role_category')
                    ->distinct()
                    ->orderBy('role_category')
                    ->orderBy('role_name')
                    ->get()
            );
        });

        if (! is_string($serialized)) {
            $templates = MenuAccessTemplate::query()
                ->select('role_key', 'role_name', 'role_category')
                ->distinct()
                ->orderBy('role_category')
                ->orderBy('role_name')
                ->get();
            Cache::put($cacheKey, serialize($templates), now()->addHours(self::TTL_HOURS));

            return $templates;
        }

        $unpacked = unserialize($serialized, ['allowed_classes' => self::ALLOWED_CLASSES]);

        if (! $unpacked instanceof Collection) {
            return MenuAccessTemplate::query()
                ->select('role_key', 'role_name', 'role_category')
                ->distinct()
                ->orderBy('role_category')
                ->orderBy('role_name')
                ->get();
        }

        return $unpacked;
    }

    /**
     * Bangun struktur modul, menu, dan submenu yang telah difilter sesuai hak akses user.
     */
    public static function buildSidebar(User $user): Collection
    {
        $sidebarModules = self::getActiveTree();

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

    /**
     * Dapatkan URL rute pertama yang diizinkan untuk diakses oleh user.
     */
    public static function getFirstAllowedUrl(User $user): ?string
    {
        $sidebarModules = self::get($user);
        $fallbackUrl = null;

        foreach ($sidebarModules as $module) {
            foreach ($module->menus as $menu) {
                if ($menu->subMenus && $menu->subMenus->isNotEmpty()) {
                    foreach ($menu->subMenus as $subMenu) {
                        $url = $subMenu->route_url;
                        if ($url && $url !== '#') {
                            if (! str_contains($url, 'under-development')) {
                                return $url;
                            }
                            $fallbackUrl ??= $url;
                        }
                    }
                } else {
                    $url = $menu->route_url;
                    if ($url && $url !== '#') {
                        if (! str_contains($url, 'under-development')) {
                            return $url;
                        }
                        $fallbackUrl ??= $url;
                    }
                }
            }
        }

        return $fallbackUrl;
    }

    /**
     * Cari Menu atau SubMenu yang cocok dengan rute request saat ini.
     *
     * @return array{type: 'menu'|'submenu', item: Menu|SubMenu}|null
     */
    public static function findByRoute(Request $request): ?array
    {
        $activeTree = self::getActiveTree();

        // 1. Prioritaskan kecocokan rute persis (Exact Match) di seluruh pohon
        foreach ($activeTree as $module) {
            foreach ($module->menus as $menu) {
                foreach ($menu->subMenus as $subMenu) {
                    if ($subMenu->route_name && $request->routeIs($subMenu->route_name)) {
                        return ['type' => 'submenu', 'item' => $subMenu];
                    }
                }

                if ($menu->route_name && $request->routeIs($menu->route_name)) {
                    return ['type' => 'menu', 'item' => $menu];
                }
            }
        }

        // 2. Jika tidak ada kecocokan persis, cari berdasarkan pola prefix/CRUD wildcard
        foreach ($activeTree as $module) {
            foreach ($module->menus as $menu) {
                foreach ($menu->subMenus as $subMenu) {
                    if (self::matchesRoute($request, $subMenu->route_name)) {
                        return ['type' => 'submenu', 'item' => $subMenu];
                    }
                }

                if (self::matchesRoute($request, $menu->route_name)) {
                    return ['type' => 'menu', 'item' => $menu];
                }
            }
        }

        // 3. Fallback pencarian langsung jika pohon aktif belum terindeks di cache
        if (Schema::hasTable('sub_menus')) {
            $subMenus = SubMenu::where('is_active', true)->whereNotNull('route_name')->get();
            foreach ($subMenus as $subMenu) {
                if ($request->routeIs($subMenu->route_name)) {
                    return ['type' => 'submenu', 'item' => $subMenu];
                }
            }

            foreach ($subMenus as $subMenu) {
                if (self::matchesRoute($request, $subMenu->route_name)) {
                    return ['type' => 'submenu', 'item' => $subMenu];
                }
            }
        }

        if (Schema::hasTable('menus')) {
            $menus = Menu::where('is_active', true)->whereNotNull('route_name')->get();
            foreach ($menus as $menu) {
                if ($request->routeIs($menu->route_name)) {
                    return ['type' => 'menu', 'item' => $menu];
                }
            }

            foreach ($menus as $menu) {
                if (self::matchesRoute($request, $menu->route_name)) {
                    return ['type' => 'menu', 'item' => $menu];
                }
            }
        }

        return null;
    }

    /**
     * Periksa apakah request saat ini cocok dengan pola rute yang diberikan.
     */
    public static function matchesRoute(Request $request, ?string $routeName): bool
    {
        if (blank($routeName)) {
            return false;
        }

        $patterns = [
            $routeName,
            $routeName.'.*',
            $routeName.'.index',
        ];

        if (str_ends_with($routeName, '.index')) {
            $base = substr($routeName, 0, -6);
            $patterns[] = $base;
            $patterns[] = $base.'.*';
        }

        return $request->routeIs($patterns);
    }
}
