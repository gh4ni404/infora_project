<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\StoreSubMenuRequest;
use App\Http\Requests\System\UpdateSubMenuRequest;
use App\Models\Menu;
use App\Models\SubMenu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubMenuController extends Controller
{
    /**
     * Display a listing of the sub-menus.
     */
    public function index(Request $request): View
    {
        $query = SubMenu::query()
            ->with(['menu.module'])
            ->orderBy('menu_id')
            ->orderBy('order')
            ->orderBy('id');

        if ($request->filled('menu_id')) {
            $query->where('menu_id', $request->query('menu_id'));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('route_name', 'like', "%{$search}%");
            });
        }

        $subMenus = $query->paginate(15)->withQueryString();
        $menus = Menu::with('module')->orderBy('module_id')->orderBy('order')->get();

        return view('system.sub-menus.index', compact('subMenus', 'menus'));
    }

    /**
     * Show the form for creating a new sub-menu.
     */
    public function create(Request $request): View
    {
        $menus = Menu::with('module')->orderBy('module_id')->orderBy('order')->get();
        $selectedMenuId = $request->query('menu_id');

        return view('system.sub-menus.create', compact('menus', 'selectedMenuId'));
    }

    /**
     * Store a newly created sub-menu in storage.
     */
    public function store(StoreSubMenuRequest $request): RedirectResponse
    {
        SubMenu::create($request->validated());

        return redirect()
            ->route('system.sub-menus.index')
            ->with('success', 'Sub-menu berhasil ditambahkan ke dalam sistem.');
    }

    /**
     * Show the form for editing the specified sub-menu.
     */
    public function edit(SubMenu $subMenu): View
    {
        $menus = Menu::with('module')->orderBy('module_id')->orderBy('order')->get();

        return view('system.sub-menus.edit', compact('subMenu', 'menus'));
    }

    /**
     * Update the specified sub-menu in storage.
     */
    public function update(UpdateSubMenuRequest $request, SubMenu $subMenu): RedirectResponse
    {
        $subMenu->update($request->validated());

        return redirect()
            ->route('system.sub-menus.index')
            ->with('success', 'Data sub-menu berhasil diperbarui.');
    }

    /**
     * Remove the specified sub-menu from storage.
     */
    public function destroy(SubMenu $subMenu): RedirectResponse
    {
        $subMenu->delete();

        return redirect()
            ->route('system.sub-menus.index')
            ->with('success', 'Sub-menu berhasil dihapus.');
    }
}
