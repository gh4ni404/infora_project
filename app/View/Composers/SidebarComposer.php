<?php

namespace App\View\Composers;

use App\Models\School;
use App\Support\SidebarCache;
use Illuminate\View\View;

class SidebarComposer
{
    /**
     * Bind dynamic sidebar navigation modules, menus, and sub-menus to the view.
     * Menggunakan SidebarCache dengan Cache Versioning untuk performa tinggi.
     */
    public function compose(View $view): void
    {
        $user = auth()->user();

        $view->with([
            'sidebarModules' => SidebarCache::get($user),
            'activeSchool' => $user?->school_id ? $user->school : School::current(),
        ]);
    }
}
