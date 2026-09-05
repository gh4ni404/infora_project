<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\StoreMenuRequest;
use App\Http\Requests\System\UpdateMenuRequest;
use App\Models\Menu;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    /**
     * Display a listing of the menus.
     */
    public function index(Request $request): View
    {
        $query = Menu::query()
            ->with(['module'])
            ->withCount('subMenus')
            ->orderBy('module_id')
            ->orderBy('order')
            ->orderBy('id');

        if ($request->filled('module_id')) {
            $query->where('module_id', $request->query('module_id'));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('route_name', 'like', "%{$search}%");
            });
        }

        $menus = $query->paginate(15)->withQueryString();
        $modules = Module::orderBy('order')->get();

        return view('system.menus.index', compact('menus', 'modules'));
    }

    /**
     * Show the form for creating a new menu.
     */
    public function create(Request $request): View
    {
        $modules = Module::orderBy('order')->get();
        $selectedModuleId = $request->query('module_id');

        return view('system.menus.create', compact('modules', 'selectedModuleId'));
    }

    /**
     * Store a newly created menu in storage.
     */
    public function store(StoreMenuRequest $request): RedirectResponse
    {
        Menu::create($request->validated());

        return redirect()
            ->route('system.menus.index')
            ->with('success', 'Menu berhasil ditambahkan ke dalam sistem.');
    }

    /**
     * Show the form for editing the specified menu.
     */
    public function edit(Menu $menu): View
    {
        $modules = Module::orderBy('order')->get();

        return view('system.menus.edit', compact('menu', 'modules'));
    }

    /**
     * Update the specified menu in storage.
     */
    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $menu->update($request->validated());

        return redirect()
            ->route('system.menus.index')
            ->with('success', 'Data menu berhasil diperbarui.');
    }

    /**
     * Remove the specified menu from storage.
     */
    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()
            ->route('system.menus.index')
            ->with('success', 'Menu dan seluruh sub-menu terkait berhasil dihapus.');
    }
}
