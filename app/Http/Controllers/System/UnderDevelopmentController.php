<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\SubMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

        $devStatus = $type === 'submenu'
            ? SubMenu::cachedDevelopmentStatus('submenu', $item->id)
            : Menu::cachedDevelopmentStatus('menu', $item->id);

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
            'devStatus'
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

        $devStatus = $type === 'submenu'
            ? SubMenu::cachedDevelopmentStatus('submenu', $id)
            : Menu::cachedDevelopmentStatus('menu', $id);

        if (! $devStatus) {
            abort(404, 'Item navigasi tidak ditemukan.');
        }

        return response()
            ->json($devStatus)
            ->header('Cache-Control', 'no-store, private');
    }
}
