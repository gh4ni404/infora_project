<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\StoreModuleRequest;
use App\Http\Requests\System\UpdateModuleRequest;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleController extends Controller
{
    /**
     * Display a listing of the modules.
     */
    public function index(Request $request): View
    {
        $query = Module::query()
            ->withCount('menus')
            ->orderBy('order')
            ->orderBy('id');

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where('name', 'like', "%{$search}%");
        }

        $modules = $query->paginate(15)->withQueryString();

        return view('system.modules.index', compact('modules'));
    }

    /**
     * Show the form for creating a new module.
     */
    public function create(): View
    {
        return view('system.modules.create');
    }

    /**
     * Store a newly created module in storage.
     */
    public function store(StoreModuleRequest $request): RedirectResponse
    {
        Module::create($request->validated());

        return redirect()
            ->route('system.modules.index')
            ->with('success', 'Modul berhasil ditambahkan ke dalam sistem.');
    }

    /**
     * Show the form for editing the specified module.
     */
    public function edit(Module $module): View
    {
        return view('system.modules.edit', compact('module'));
    }

    /**
     * Update the specified module in storage.
     */
    public function update(UpdateModuleRequest $request, Module $module): RedirectResponse
    {
        $module->update($request->validated());

        return redirect()
            ->route('system.modules.index')
            ->with('success', 'Data modul berhasil diperbarui.');
    }

    /**
     * Remove the specified module from storage.
     */
    public function destroy(Module $module): RedirectResponse
    {
        $module->delete();

        return redirect()
            ->route('system.modules.index')
            ->with('success', 'Modul dan seluruh menu terkait berhasil dihapus.');
    }
}
