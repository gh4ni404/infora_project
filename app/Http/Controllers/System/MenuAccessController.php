<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\UpdateTemplatePermissionsRequest;
use App\Http\Requests\System\UpdateUserPermissionsRequest;
use App\Models\MenuAccessTemplate;
use App\Models\Module;
use App\Models\User;
use App\Models\UserMenuPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MenuAccessController extends Controller
{
    /**
     * Display a listing of users and configurable role access templates.
     */
    public function index(Request $request): View
    {
        $roleFilter = $request->query('role');
        $search = $request->query('search');

        $usersQuery = User::query()->with('menuPermissions');

        if ($roleFilter && in_array($roleFilter, ['super_admin', 'admin', 'guru', 'siswa'], true)) {
            $usersQuery->where('user_type', $roleFilter);
        }

        if ($search) {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery->orderBy('user_type')->orderBy('name')->paginate(15)->withQueryString();

        // Ambil daftar template peran dinamis
        $templates = MenuAccessTemplate::query()
            ->select('role_key', 'role_name', 'role_category')
            ->distinct()
            ->orderBy('role_category')
            ->orderBy('role_name')
            ->get();

        return view('system.menu-access.index', [
            'users' => $users,
            'templates' => $templates,
            'currentRole' => $roleFilter,
            'search' => $search,
            'activeTab' => $request->query('tab', 'users'),
        ]);
    }

    /**
     * Show the form for configuring granular menu permissions for a specific user.
     */
    public function editUser(User $user): View
    {
        $modules = Module::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->with([
                'menus' => function ($q) {
                    $q->where('is_active', true)
                        ->orderBy('order')
                        ->with([
                            'subMenus' => function ($sq) {
                                $sq->where('is_active', true)
                                    ->orderBy('order');
                            },
                        ]);
                },
            ])
            ->get();

        $userPermissions = $user->menuPermissions()
            ->get()
            ->keyBy(fn (UserMenuPermission $p) => $p->sub_menu_id ? 'sub_'.$p->sub_menu_id : 'menu_'.$p->menu_id);

        $availableTemplates = MenuAccessTemplate::query()
            ->select('role_key', 'role_name', 'role_category')
            ->distinct()
            ->orderBy('role_category')
            ->orderBy('role_name')
            ->get();

        return view('system.menu-access.edit-user', [
            'user' => $user,
            'modules' => $modules,
            'userPermissions' => $userPermissions,
            'availableTemplates' => $availableTemplates,
        ]);
    }

    /**
     * Update the granular menu permissions for a specific user.
     */
    public function updateUser(UpdateUserPermissionsRequest $request, User $user): RedirectResponse
    {
        $submittedPermissions = $request->input('permissions', []);

        DB::transaction(function () use ($user, $submittedPermissions) {
            $user->menuPermissions()->delete();

            $recordsToInsert = [];
            $now = now();

            foreach ($submittedPermissions as $perm) {
                $canView = ! empty($perm['can_view']);
                $canCreate = ! empty($perm['can_create']);
                $canEdit = ! empty($perm['can_edit']);
                $canDelete = ! empty($perm['can_delete']);

                // Hanya simpan jika minimal satu hak akses bernilai true
                if ($canView || $canCreate || $canEdit || $canDelete) {
                    $recordsToInsert[] = [
                        'user_id' => $user->id,
                        'menu_id' => ! empty($perm['menu_id']) ? (int) $perm['menu_id'] : null,
                        'sub_menu_id' => ! empty($perm['sub_menu_id']) ? (int) $perm['sub_menu_id'] : null,
                        'can_view' => $canView,
                        'can_create' => $canCreate,
                        'can_edit' => $canEdit,
                        'can_delete' => $canDelete,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (! empty($recordsToInsert)) {
                UserMenuPermission::insert($recordsToInsert);
            }
        });

        return redirect()
            ->route('sistem.menu-akses', ['role' => $user->user_type])
            ->with('success', "Hak akses navigasi untuk pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Apply an existing role template's permissions directly to a specific user.
     */
    public function applyTemplateToUser(Request $request, User $user): RedirectResponse
    {
        $roleKey = $request->input('role_key');
        $templatePermissions = MenuAccessTemplate::where('role_key', $roleKey)->get();

        if ($templatePermissions->isEmpty()) {
            return back()->with('error', 'Template peran yang dipilih belum memiliki konfigurasi menu.');
        }

        DB::transaction(function () use ($user, $templatePermissions) {
            $user->menuPermissions()->delete();

            $recordsToInsert = [];
            $now = now();

            foreach ($templatePermissions as $tmpl) {
                if ($tmpl->can_view || $tmpl->can_create || $tmpl->can_edit || $tmpl->can_delete) {
                    $recordsToInsert[] = [
                        'user_id' => $user->id,
                        'menu_id' => $tmpl->menu_id,
                        'sub_menu_id' => $tmpl->sub_menu_id,
                        'can_view' => $tmpl->can_view,
                        'can_create' => $tmpl->can_create,
                        'can_edit' => $tmpl->can_edit,
                        'can_delete' => $tmpl->can_delete,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (! empty($recordsToInsert)) {
                UserMenuPermission::insert($recordsToInsert);
            }
        });

        $templateName = $templatePermissions->first()->role_name ?? $roleKey;

        return back()->with('success', "Template peran '{$templateName}' berhasil diterapkan ke akun {$user->name}.");
    }

    /**
     * Show the form for configuring a role access template.
     */
    public function editTemplate(string $roleKey): View
    {
        $templateRecords = MenuAccessTemplate::where('role_key', $roleKey)->get();

        if ($templateRecords->isEmpty()) {
            abort(404, 'Template peran tidak ditemukan.');
        }

        $templateInfo = $templateRecords->first();

        $modules = Module::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->with([
                'menus' => function ($q) {
                    $q->where('is_active', true)
                        ->orderBy('order')
                        ->with([
                            'subMenus' => function ($sq) {
                                $sq->where('is_active', true)
                                    ->orderBy('order');
                            },
                        ]);
                },
            ])
            ->get();

        $templatePermissions = $templateRecords->keyBy(
            fn (MenuAccessTemplate $t) => $t->sub_menu_id ? 'sub_'.$t->sub_menu_id : 'menu_'.$t->menu_id
        );

        return view('system.menu-access.edit-template', [
            'roleKey' => $roleKey,
            'templateInfo' => $templateInfo,
            'modules' => $modules,
            'templatePermissions' => $templatePermissions,
        ]);
    }

    /**
     * Update a role access template's permissions.
     */
    public function updateTemplate(UpdateTemplatePermissionsRequest $request, string $roleKey): RedirectResponse
    {
        $existing = MenuAccessTemplate::where('role_key', $roleKey)->firstOrFail();
        $roleName = $request->input('role_name', $existing->role_name);
        $roleCategory = $request->input('role_category', $existing->role_category);
        $submittedPermissions = $request->input('permissions', []);

        DB::transaction(function () use ($roleKey, $roleName, $roleCategory, $submittedPermissions) {
            MenuAccessTemplate::where('role_key', $roleKey)->delete();

            $recordsToInsert = [];
            $now = now();

            foreach ($submittedPermissions as $perm) {
                $canView = ! empty($perm['can_view']);
                $canCreate = ! empty($perm['can_create']);
                $canEdit = ! empty($perm['can_edit']);
                $canDelete = ! empty($perm['can_delete']);

                if ($canView || $canCreate || $canEdit || $canDelete) {
                    $recordsToInsert[] = [
                        'role_key' => $roleKey,
                        'role_name' => $roleName,
                        'role_category' => $roleCategory,
                        'menu_id' => ! empty($perm['menu_id']) ? (int) $perm['menu_id'] : null,
                        'sub_menu_id' => ! empty($perm['sub_menu_id']) ? (int) $perm['sub_menu_id'] : null,
                        'can_view' => $canView,
                        'can_create' => $canCreate,
                        'can_edit' => $canEdit,
                        'can_delete' => $canDelete,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Jika tidak ada izin yang dicentang, tetap sisakan satu baris placeholder agar template tidak hilang
            if (empty($recordsToInsert)) {
                $recordsToInsert[] = [
                    'role_key' => $roleKey,
                    'role_name' => $roleName,
                    'role_category' => $roleCategory,
                    'menu_id' => null,
                    'sub_menu_id' => null,
                    'can_view' => false,
                    'can_create' => false,
                    'can_edit' => false,
                    'can_delete' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            MenuAccessTemplate::insert($recordsToInsert);
        });

        return redirect()
            ->route('sistem.menu-akses', ['tab' => 'templates'])
            ->with('success', "Konfigurasi menu bawaan untuk template peran '{$roleName}' berhasil disimpan.");
    }
}
