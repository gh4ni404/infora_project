<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\SubMenu;
use App\Support\SidebarCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnderDevelopmentController extends Controller
{
    /**
     * Display the under-development placeholder page for a menu or sub-menu.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $type = $request->query('type');
        $id = $request->integer('id');

        if ($type === 'submenu') {
            /** @var SubMenu $item */
            $item = SubMenu::with('menu.module')->findOrFail($id);
            $menu = $item->menu;
            $module = $menu?->module;
            $itemType = 'Sub-Menu';
            $editUrl = route('system.sub-menus.edit', $item);
        } elseif ($type === 'menu') {
            /** @var Menu $item */
            $item = Menu::with('module')->findOrFail($id);
            $menu = $item;
            $module = $item->module;
            $itemType = 'Menu';
            $editUrl = route('system.menus.edit', $item);
        } else {
            abort(404, 'Item navigasi tidak valid.');
        }

        $registeredUrl = $item->resolveRegisteredUrl();
        if ($registeredUrl) {
            return redirect()->to($registeredUrl);
        }

        $modelClass = $type === 'submenu' ? SubMenu::class : Menu::class;
        $devStatus = $modelClass::cachedDevelopmentStatus($type, $item->id);

        if ($devStatus && ! $devStatus['ready'] && $devStatus['progress'] !== (int) $item->dev_progress) {
            $modelClass::clearDevelopmentCache($type, $item->id);
            $devStatus = $modelClass::cachedDevelopmentStatus($type, $item->id);
        }

        $rawRoute = $item->route_name;
        $cleanRoute = $rawRoute ?: Str::slug(Str::replace(['.', '_'], '-', $item->name));

        // Generate developer scaffolding suggestions (pecah titik sebagai subdirektori controller)
        $routeParts = array_values(array_filter(explode('.', trim($cleanRoute, '.'))));
        if (count($routeParts) > 1 && end($routeParts) === 'index') {
            array_pop($routeParts);
        }

        if (count($routeParts) > 1) {
            $controllerName = Str::studly(array_pop($routeParts)).'Controller';
            $folderNamespace = implode('/', array_map(fn ($p) => Str::studly($p), $routeParts));
            $suggestedArtisanPath = $folderNamespace.'/'.$controllerName;
        } else {
            $controllerName = Str::studly($routeParts[0] ?? $cleanRoute).'Controller';
            $suggestedArtisanPath = $controllerName;
        }

        $controllerBaseName = $controllerName;
        $suggestedRouteCode = sprintf(
            "Route::get('/%s', [%s::class, 'index'])->name('%s');",
            str_replace('.', '/', $cleanRoute),
            $controllerName,
            $cleanRoute
        );
        $suggestedArtisanCmd = 'php artisan make:controller '.$suggestedArtisanPath;

        $devStages = Menu::DEVELOPMENT_STAGES;

        return view('system.under-development', compact(
            'item',
            'itemType',
            'module',
            'menu',
            'editUrl',
            'rawRoute',
            'cleanRoute',
            'controllerBaseName',
            'suggestedRouteCode',
            'suggestedArtisanCmd',
            'devStatus',
            'devStages'
        ));
    }

    /**
     * Kembalikan status progres pengembangan fitur secara ringan berbasis cache dalam format JSON.
     */
    public function status(Request $request): JsonResponse
    {
        $type = (string) $request->query('type');
        $id = $request->integer('id');

        if ($id <= 0 || ! in_array($type, ['menu', 'submenu'], true)) {
            abort(404, 'Item navigasi tidak valid.');
        }

        $modelClass = $type === 'submenu' ? SubMenu::class : Menu::class;
        $devStatus = $modelClass::cachedDevelopmentStatus($type, $id);

        if (! $devStatus) {
            abort(404, 'Item navigasi tidak ditemukan.');
        }

        return response()
            ->json($devStatus)
            ->header('Cache-Control', 'no-store, private');
    }

    /**
     * Perbarui tahapan checklist progres pengembangan (khusus Super Administrator).
     */
    public function updateChecklist(Request $request): JsonResponse
    {
        if (! $request->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Administrator yang dapat memperbarui checklist pengembangan.');
        }

        $stages = Menu::DEVELOPMENT_STAGES;
        $stageKeys = array_keys($stages);

        $validated = $request->validate([
            'type' => ['required', Rule::in(['menu', 'submenu'])],
            'id' => ['required', 'integer', 'min:1'],
            'stage_key' => ['required', Rule::in($stageKeys)],
            'checked' => ['required', 'boolean'],
        ]);

        $type = $validated['type'];
        $id = (int) $validated['id'];
        $stageKey = $validated['stage_key'];
        $checked = (bool) $validated['checked'];

        /** @var class-string<Menu|SubMenu> $modelClass */
        $modelClass = $type === 'submenu' ? SubMenu::class : Menu::class;
        $item = $modelClass::findOrFail($id);

        $missingComponents = ($stageKey === 'done' && $checked)
            ? $this->findMissingComponents($item->route_name)
            : [];

        if ($missingComponents !== []) {
            $newProgress = 0;
            $newStage = null;
        } elseif ($checked) {
            $newProgress = $stages[$stageKey]['progress'];
            $newStage = $stages[$stageKey]['label'];
        } else {
            $index = array_search($stageKey, $stageKeys, true);
            $prevKey = ($index !== false && $index > 0) ? $stageKeys[$index - 1] : null;
            $newProgress = $prevKey !== null ? $stages[$prevKey]['progress'] : 0;
            $newStage = $prevKey !== null ? $stages[$prevKey]['label'] : null;
        }

        $item->update([
            'dev_progress' => $newProgress,
            'dev_stage' => $newStage,
        ]);

        $modelClass::clearDevelopmentCache($type, $item->id);
        SidebarCache::flushGlobal();
        $devStatus = $modelClass::cachedDevelopmentStatus($type, $item->id);

        if ($missingComponents !== []) {
            return response()->json([
                'message' => 'Fitur ini belum siap karena komponen berikut belum ada: '.implode(', ', $missingComponents).'.',
                'missing_components' => $missingComponents,
                'status' => $devStatus,
            ], 422);
        }

        return response()
            ->json($devStatus)
            ->header('Cache-Control', 'no-store, private');
    }

    /**
     * Periksa keberadaan 7 komponen utama fitur sebelum menandai status Selesai.
     *
     * @return list<string>
     */
    protected function findMissingComponents(?string $routeName): array
    {
        $cleanRoute = trim((string) $routeName);
        if (in_array($cleanRoute, ['dashboard', 'dashboard.admin'], true)) {
            return [];
        }

        if ($cleanRoute === '') {
            return array_values(array_map(
                fn (array $stage): string => $stage['label'],
                array_slice(Menu::DEVELOPMENT_STAGES, 0, 7)
            ));
        }

        $baseRoute = str_ends_with($cleanRoute, '.index')
            ? substr($cleanRoute, 0, -6)
            : $cleanRoute;

        $segments = array_values(array_filter(explode('.', $baseRoute)));
        $rawSlug = end($segments) ?: $baseRoute;
        $coreSlug = str_starts_with($rawSlug, 'data-') ? substr($rawSlug, 5) : $rawSlug;

        $entityNames = array_values(array_unique([
            Str::studly($coreSlug),
            Str::studly($rawSlug),
        ]));

        $snakeNames = array_values(array_unique([
            Str::snake($coreSlug),
            Str::snake(Str::plural($coreSlug)),
            Str::snake($rawSlug),
            Str::snake(Str::plural($rawSlug)),
        ]));

        $namespaceSegments = count($segments) > 1
            ? array_map(fn (string $part): string => Str::studly($part), array_slice($segments, 0, -1))
            : [];
        $namespacePath = implode('/', $namespaceSegments);

        $registeredRoute = Route::getRoutes()->getByName($cleanRoute)
            ?? Route::getRoutes()->getByName($cleanRoute.'.index');

        $hasRoute = $registeredRoute !== null;
        $hasMigration = $this->hasMatchingFile(database_path('migrations'), $snakeNames, '.php');
        $hasModel = $this->hasEntityFile(app_path('Models'), $entityNames, '.php');
        $hasRequest = $this->hasRequestFile($entityNames, $namespacePath);
        $hasController = $this->hasControllerFile($registeredRoute, $entityNames, $namespacePath);
        $hasView = $this->hasViewFile($segments);
        $hasTest = $this->hasEntityFile(base_path('tests/Feature'), $entityNames, 'Test.php', true);

        $checks = [
            'Migration' => $hasMigration,
            'Model' => $hasModel,
            'Form Request' => $hasRequest,
            'Controller' => $hasController,
            'Route' => $hasRoute,
            'View' => $hasView,
            'Test' => $hasTest,
        ];

        return array_keys(array_filter($checks, fn (bool $exists): bool => ! $exists));
    }

    /**
     * Periksa file migrasi berdasarkan kandidat nama tabel snake_case.
     *
     * @param  list<string>  $snakeNames
     */
    protected function hasMatchingFile(string $directory, array $snakeNames, string $extension): bool
    {
        if (! File::isDirectory($directory)) {
            return false;
        }

        foreach (File::files($directory) as $file) {
            $name = $file->getFilename();
            if (! str_ends_with($name, $extension)) {
                continue;
            }
            foreach ($snakeNames as $snake) {
                if (str_contains($name, "_{$snake}_") || str_contains($name, "_{$snake}{$extension}")) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Periksa keberadaan file entitas (Model / Feature Test).
     *
     * @param  list<string>  $entityNames
     */
    protected function hasEntityFile(string $directory, array $entityNames, string $suffix, bool $recursive = false): bool
    {
        if (! File::isDirectory($directory)) {
            return false;
        }

        $files = $recursive ? File::allFiles($directory) : File::files($directory);
        foreach ($files as $file) {
            foreach ($entityNames as $entity) {
                if ($file->getFilename() === "{$entity}{$suffix}") {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Periksa keberadaan Form Request untuk entitas terkait.
     *
     * @param  list<string>  $entityNames
     */
    protected function hasRequestFile(array $entityNames, string $namespacePath): bool
    {
        $baseDir = $namespacePath !== '' && File::isDirectory(app_path("Http/Requests/{$namespacePath}"))
            ? app_path("Http/Requests/{$namespacePath}")
            : app_path('Http/Requests');

        if (! File::isDirectory($baseDir)) {
            return false;
        }

        foreach (File::allFiles($baseDir) as $file) {
            $name = $file->getFilename();
            if (! str_ends_with($name, 'Request.php')) {
                continue;
            }
            foreach ($entityNames as $entity) {
                if (str_contains($name, $entity)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Periksa keberadaan Controller dari rute aktif atau konvensi direktori.
     *
     * @param  list<string>  $entityNames
     */
    protected function hasControllerFile(?RoutingRoute $registeredRoute, array $entityNames, string $namespacePath): bool
    {
        if ($registeredRoute) {
            $controllerClass = $registeredRoute->getControllerClass();
            if ($controllerClass !== '' && class_exists($controllerClass)) {
                return true;
            }
        }

        $dir = $namespacePath !== ''
            ? app_path("Http/Controllers/{$namespacePath}")
            : app_path('Http/Controllers');

        return $this->hasEntityFile($dir, $entityNames, 'Controller.php', true);
    }

    /**
     * Periksa keberadaan file Blade view utama untuk rute terkait.
     *
     * @param  list<string>  $segments
     */
    protected function hasViewFile(array $segments): bool
    {
        if ($segments === []) {
            return false;
        }

        $viewPath = implode('/', $segments);

        return File::exists(resource_path("views/{$viewPath}/index.blade.php"))
            || File::exists(resource_path("views/{$viewPath}.blade.php"));
    }
}
