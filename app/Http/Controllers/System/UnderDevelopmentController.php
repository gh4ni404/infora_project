<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\SubMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UnderDevelopmentController extends Controller
{
    /**
     * Display the under-development placeholder page for a menu or sub-menu.
     */
    public function show(Request $request): View
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

        $rawRoute = $item->route_name;
        $cleanRoute = $rawRoute ?: Str::slug(Str::replace(['.', '_'], '-', $item->name));

        // Generate developer scaffolding suggestions
        $controllerBaseName = Str::studly(Str::replace(['.', '-', '_'], ' ', $cleanRoute)).'Controller';
        $suggestedRouteCode = sprintf(
            "Route::get('/%s', [%s::class, 'index'])->name('%s');",
            Str::slug(Str::replace('.', '/', $cleanRoute)),
            $controllerBaseName,
            $cleanRoute
        );
        $suggestedArtisanCmd = 'php artisan make:controller '.$controllerBaseName;

        return view('system.under-development', [
            'item' => $item,
            'itemType' => $itemType,
            'module' => $module,
            'menu' => $menu,
            'editUrl' => $editUrl,
            'rawRoute' => $rawRoute,
            'cleanRoute' => $cleanRoute,
            'controllerBaseName' => $controllerBaseName,
            'suggestedRouteCode' => $suggestedRouteCode,
            'suggestedArtisanCmd' => $suggestedArtisanCmd,
        ]);
    }
}
