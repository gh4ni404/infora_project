<?php

namespace App\Support;

use App\Models\Gtk;
use App\Models\MenuAccessTemplate;
use App\Models\User;
use App\Models\UserMenuPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GtkAccountManager
{
    /**
     * Buat atau perbarui akun pengguna login untuk data GTK.
     *
     * @param  array<string, mixed>  $credentials
     */
    public static function createOrUpdateUserForGtk(Gtk $gtk, array $credentials = []): User
    {
        // 1. Tentukan username
        $username = ! empty($credentials['username'])
            ? trim((string) $credentials['username'])
            : static::resolveDefaultUsername($gtk);

        // 2. Tentukan user_type
        $userType = $gtk->is_pendidik ? 'guru' : 'staf';

        // 3. Tentukan password
        $customPassword = $credentials['password'] ?? null;
        $cleanPassword = static::resolveDefaultPassword($gtk, $customPassword);

        $user = $gtk->user;

        if ($user) {
            $userData = [
                'name' => $gtk->nama_bergelar,
                'email' => $gtk->email,
                'is_active' => $gtk->is_active,
                'user_type' => $userType,
            ];

            if (! empty($credentials['username'])) {
                $userData['username'] = trim((string) $credentials['username']);
            }

            if (filled($customPassword)) {
                $userData['password'] = Hash::make($cleanPassword);
            }

            $user->update($userData);
        } else {
            // Pastikan username unik di basis data
            $uniqueUsername = static::ensureUniqueUsername($username);

            $user = User::create([
                'school_id' => $gtk->school_id,
                'name' => $gtk->nama_bergelar,
                'username' => $uniqueUsername,
                'email' => $gtk->email,
                'password' => Hash::make($cleanPassword),
                'user_type' => $userType,
                'is_active' => $gtk->is_active,
            ]);

            $gtk->update(['user_id' => $user->id]);
        }

        // Terapkan template peran jika ditentukan atau jika akun belum memiliki permission
        $roleKey = ! empty($credentials['role_template'])
            ? (string) $credentials['role_template']
            : ($gtk->is_pendidik ? 'guru_pengajar' : 'staf_tu');

        if (! empty($credentials['role_template']) || $user->menuPermissions()->doesntExist()) {
            static::applyRoleTemplate($user, $roleKey);
        }

        return $user;
    }

    /**
     * Resolusi username bawaan dari NIP, NUPTK, NIK, atau nama.
     */
    public static function resolveDefaultUsername(Gtk $gtk): string
    {
        if (filled($gtk->nip)) {
            return preg_replace('/\s+/', '', (string) $gtk->nip);
        }

        if (filled($gtk->nuptk)) {
            return preg_replace('/\s+/', '', (string) $gtk->nuptk);
        }

        if (filled($gtk->nik)) {
            return preg_replace('/\s+/', '', (string) $gtk->nik);
        }

        return Str::slug($gtk->nama_lengkap, '');
    }

    /**
     * Pastikan username belum dipakai oleh pengguna lain.
     */
    public static function ensureUniqueUsername(string $username, ?int $ignoreUserId = null): string
    {
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
     * Terapkan template hak akses peran sistem ke akun pengguna.
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
     * Resolusi kata sandi pengguna GTK (kustom jika diisi, atau hierarkis NIP -> NUPTK -> NIK -> 'infora123').
     */
    public static function resolveDefaultPassword(Gtk $gtk, ?string $customPassword = null): string
    {
        if (filled($customPassword)) {
            return trim((string) $customPassword);
        }

        if (filled($gtk->nip)) {
            return preg_replace('/\s+/', '', (string) $gtk->nip);
        }

        if (filled($gtk->nuptk)) {
            return preg_replace('/\s+/', '', (string) $gtk->nuptk);
        }

        if (filled($gtk->nik)) {
            return preg_replace('/\s+/', '', (string) $gtk->nik);
        }

        return 'infora123';
    }

    /**
     * Reset kata sandi pengguna GTK ke nilai bawaan sistem (NIP -> NUPTK -> NIK -> 'infora123').
     */
    public static function resetPassword(Gtk $gtk): ?string
    {
        $user = $gtk->user;
        if (! $user) {
            return null;
        }

        $cleanPassword = static::resolveDefaultPassword($gtk);

        $user->update([
            'password' => Hash::make($cleanPassword),
        ]);

        return $cleanPassword;
    }
}
