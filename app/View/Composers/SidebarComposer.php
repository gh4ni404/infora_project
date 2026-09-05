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

        $view->with('sidebarModules', $sidebarModules);
    }
}
