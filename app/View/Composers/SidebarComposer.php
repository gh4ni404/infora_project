<?php

namespace App\View\Composers;

use App\Models\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SidebarComposer
{
    /**
     * Bind dynamic sidebar navigation modules, menus, and sub-menus to the view.
     */
    public function compose(View $view): void
    {
        if (! Schema::hasTable('modules')) {
            $view->with('sidebarModules', new Collection);

            return;
        }

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

        $user = auth()->user();

        if ($user && ! $user->isSuperAdmin()) {
            $user->loadMissing('menuPermissions');
            $allowedMenuIds = $user->menuPermissions->where('can_view', true)->pluck('menu_id')->filter()->flip()->all();
            $allowedSubMenuIds = $user->menuPermissions->where('can_view', true)->pluck('sub_menu_id')->filter()->flip()->all();

            $sidebarModules = $sidebarModules->filter(function (Module $module) use ($allowedMenuIds, $allowedSubMenuIds) {
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

        $view->with('sidebarModules', $sidebarModules);
    }
}
