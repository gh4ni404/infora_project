<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\StoreModuleRequest;
use App\Http\Requests\System\UpdateModuleRequest;
use App\Models\Module;
use App\Support\SidebarCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $nextOrder = Module::nextOrder();

        return view('system.modules.index', compact('modules', 'nextOrder'));
    }

    /**
     * Show the form for creating a new module.
     */
    public function create(): View
    {
        $nextOrder = Module::nextOrder();

        return view('system.modules.create', compact('nextOrder'));
    }

    /**
     * Store a newly created module in storage.
     */
    public function store(StoreModuleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            Module::adjustOrderForCreate($data['order']);
            Module::create($data);
        });

        SidebarCache::flushGlobal();

        return redirect()
            ->route('system.modules.index')
            ->with('success', 'Modul berhasil ditambahkan dan nomor urutan telah disesuaikan.');
    }

    /**
     * Show the form for editing the specified module.
     */
    public function edit(Module $module): RedirectResponse
    {
        return redirect()->route('system.modules.index');
    }

    /**
     * Update the specified module in storage.
     */
    public function update(UpdateModuleRequest $request, Module $module): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($module, $data) {
            Module::adjustOrderForUpdate($module, $data['order']);
            $module->update($data);
        });

        SidebarCache::flushGlobal();

        return redirect()
            ->route('system.modules.index')
            ->with('success', 'Data modul berhasil diperbarui dan nomor urutan telah disesuaikan.');
    }

    /**
     * Sinkronkan dan rapikan kembali seluruh nomor urutan modul agar berurutan dari 1 tanpa celah.
     */
    public function syncOrder(): RedirectResponse
    {
        $count = Module::syncSequence();

        return redirect()
            ->route('system.modules.index')
            ->with('success', "Urutan {$count} modul berhasil disinkronkan dan dirapikan kembali.");
    }

    /**
     * Remove the specified module from storage.
     */
    public function destroy(Module $module): RedirectResponse
    {
        $module->delete();
        SidebarCache::flushGlobal();

        return redirect()
            ->route('system.modules.index')
            ->with('success', 'Modul dan seluruh menu terkait berhasil dihapus.');
    }
}
