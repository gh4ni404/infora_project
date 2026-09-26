<?php

namespace App\Support;

use App\Models\MenuAccessTemplate;
use App\Models\School;
use App\Models\User;
use App\Models\UserMenuPermission;
use Illuminate\Support\Facades\Hash;

class AdminAccountManager
{
    /**
     * Membuat atau menyinkronkan akun administrator sekolah berdasarkan data sekolah.
     */
    public static function createOrSyncAdmin(School $school): User
    {
        $user = User::query()
            ->where('school_id', $school->id)
            ->where('user_type', 'admin')
            ->first();

        if ($user) {
            $user->update([
                'name' => 'Admin '.$school->name,
                'username' => $school->npsn,
                'is_active' => $school->is_active,
            ]);
        } else {
            $user = User::create([
                'name' => 'Admin '.$school->name,
                'username' => $school->npsn,
                'password' => Hash::make($school->npsn),
                'user_type' => 'admin',
                'school_id' => $school->id,
                'is_active' => $school->is_active,
            ]);
        }

        static::applyDefaultPermissions($user);

        return $user;
    }

    /**
     * Mereset kata sandi akun admin sekolah kembali ke NPSN bawaan.
     */
    public static function resetAdminPassword(School $school): ?User
    {
        $user = User::query()
            ->where('school_id', $school->id)
            ->where('user_type', 'admin')
            ->first();

        if ($user) {
            $user->update([
                'password' => Hash::make($school->npsn),
            ]);
        }

        return $user;
    }

    /**
     * Menyalin matriks hak akses dari template peran 'admin_sekolah' ke akun user.
     */
    public static function applyDefaultPermissions(User $user): void
    {
        $templates = MenuAccessTemplate::query()
            ->where('role_key', 'admin_sekolah')
            ->get();

        if ($templates->isEmpty()) {
            return;
        }

        // Hapus izin lama dan pasang izin bawaan dari template
        $user->menuPermissions()->delete();

        foreach ($templates as $tmpl) {
            UserMenuPermission::create([
                'user_id' => $user->id,
                'menu_id' => $tmpl->menu_id,
                'sub_menu_id' => $tmpl->sub_menu_id,
                'can_view' => (bool) $tmpl->can_view,
                'can_create' => (bool) $tmpl->can_create,
                'can_edit' => (bool) $tmpl->can_edit,
                'can_delete' => (bool) $tmpl->can_delete,
            ]);
        }
    }
}
