<?php

namespace App\Support;

use App\Models\MenuAccessTemplate;
use App\Models\Murid;
use App\Models\User;
use App\Models\UserMenuPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MuridAccountManager
{
    /**
     * Buat atau perbarui akun pengguna login untuk data Murid.
     *
     * @param  array<string, mixed>  $credentials
     */
    public static function createOrUpdateUserForMurid(Murid $murid, array $credentials = []): User
    {
        // 1. Tentukan username
        $username = ! empty($credentials['username'])
            ? trim((string) $credentials['username'])
            : static::resolveDefaultUsername($murid);

        $user = $murid->user;
        $uniqueUsername = $user
            ? (! empty($credentials['username']) ? trim((string) $credentials['username']) : $user->username)
            : static::ensureUniqueUsername($username);

        // 2. Tentukan password
        $customPassword = $credentials['password'] ?? null;
        $cleanPassword = static::resolveDefaultPassword($murid, $customPassword, $uniqueUsername);

        if ($user) {
            $userData = [
                'name' => $murid->nama_lengkap,
                'email' => $murid->email,
                'is_active' => $murid->is_active,
                'user_type' => 'murid',
            ];

            if (! empty($credentials['username'])) {
                $userData['username'] = trim((string) $credentials['username']);
            }

            if (filled($customPassword)) {
                $userData['password'] = Hash::make($cleanPassword);
            }

            $user->update($userData);
        } else {
            $user = User::create([
                'school_id' => $murid->school_id,
                'name' => $murid->nama_lengkap,
                'username' => $uniqueUsername,
                'email' => $murid->email,
                'password' => Hash::make($cleanPassword),
                'user_type' => 'murid',
                'is_active' => $murid->is_active,
            ]);

            $murid->update(['user_id' => $user->id]);
        }

        // Terapkan template peran murid jika ditentukan atau belum memiliki hak akses
        $roleKey = ! empty($credentials['role_template'])
            ? (string) $credentials['role_template']
            : 'murid_reguler';

        if (! empty($credentials['role_template']) || $user->menuPermissions()->doesntExist()) {
            static::applyRoleTemplate($user, $roleKey);
        }

        return $user;
    }

    /**
     * Resolusi username bawaan dari NISN, NIS, NIK, atau nama lengkap.
     */
    public static function resolveDefaultUsername(Murid $murid): string
    {
        if (filled($murid->nisn)) {
            return preg_replace('/\s+/', '', (string) $murid->nisn);
        }

        if (filled($murid->nis)) {
            return preg_replace('/\s+/', '', (string) $murid->nis);
        }

        if (filled($murid->nik)) {
            return preg_replace('/\s+/', '', (string) $murid->nik);
        }

        $slug = Str::slug($murid->nama_lengkap, '');

        return filled($slug) ? $slug : 'murid'.Str::random(6);
    }

    /**
     * Pastikan username belum dipakai oleh pengguna lain.
     */
    public static function ensureUniqueUsername(string $username, ?int $ignoreUserId = null): string
    {
        // Bersihkan akun non-aktif yang sudah tidak memiliki relasi Murid aktif
        User::where('username', $username)
            ->where('is_active', false)
            ->whereDoesntHave('murid')
            ->whereDoesntHave('gtk')
            ->delete();

        $base = $username;
        $counter = 1;

        while (User::where('username', $username)
            ->when($ignoreUserId, fn ($q) => $q->where('id', '!=', $ignoreUserId))
            ->exists()) {
            $username = $base.$counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Terapkan template hak akses peran sistem ke akun pengguna murid.
     */
    public static function applyRoleTemplate(User $user, string $roleKey): void
    {
        $templates = MenuAccessTemplate::where('role_key', $roleKey)->get();

        if ($templates->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($user, $templates) {
            $user->menuPermissions()->delete();

            $now = now();
            $records = [];

            foreach ($templates as $tmpl) {
                if ($tmpl->can_view || $tmpl->can_create || $tmpl->can_edit || $tmpl->can_delete) {
                    $records[] = [
                        'user_id' => $user->id,
                        'menu_id' => $tmpl->menu_id,
                        'sub_menu_id' => $tmpl->sub_menu_id,
                        'can_view' => (bool) $tmpl->can_view,
                        'can_create' => (bool) $tmpl->can_create,
                        'can_edit' => (bool) $tmpl->can_edit,
                        'can_delete' => (bool) $tmpl->can_delete,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (! empty($records)) {
                UserMenuPermission::insert($records);
            }
        });

        SidebarCache::forgetUser($user->id);
    }

    /**
     * Resolusi kata sandi pengguna Murid (kustom jika diisi, atau hierarkis NISN -> NIS -> fallback -> 'infora123').
     */
    public static function resolveDefaultPassword(Murid $murid, ?string $customPassword = null, ?string $fallbackUsername = null): string
    {
        if (filled($customPassword)) {
            return trim((string) $customPassword);
        }

        if (filled($murid->nisn)) {
            return preg_replace('/\s+/', '', (string) $murid->nisn);
        }

        if (filled($murid->nis)) {
            return preg_replace('/\s+/', '', (string) $murid->nis);
        }

        if (filled($fallbackUsername)) {
            return $fallbackUsername;
        }

        if ($murid->user?->username) {
            return $murid->user->username;
        }

        $nameSlug = Str::slug($murid->nama_lengkap, '');

        return filled($nameSlug) ? $nameSlug : 'infora123';
    }

    /**
     * Reset kata sandi pengguna Murid ke nilai bawaan sistem (NISN -> NIS -> 'infora123').
     */
    public static function resetPassword(Murid $murid): ?string
    {
        $user = $murid->user;
        if (! $user) {
            return null;
        }

        $cleanPassword = static::resolveDefaultPassword($murid, null, $user->username);

        $user->update([
            'password' => Hash::make($cleanPassword),
        ]);

        return $cleanPassword;
    }
}
