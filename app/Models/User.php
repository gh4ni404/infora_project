<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'username', 'email', 'password', 'user_type', 'is_active', 'avatar_path', 'school_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke unit sekolah tempat user terdaftar.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relasi ke profil data Guru dan Tenaga Kependidikan (GTK).
     *
     * @return HasOne<Gtk, $this>
     */
    public function gtk(): HasOne
    {
        return $this->hasOne(Gtk::class);
    }

    /**
     * Check if the user is a Super Administrator (Platform/Developer entity).
     */
    public function isSuperAdmin(): bool
    {
        return $this->user_type === 'super_admin';
    }

    /**
     * Check if the user is a School Administrator / TU Operator.
     */
    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    /**
     * Check if the user is a Teacher.
     */
    public function isGuru(): bool
    {
        return $this->user_type === 'guru';
    }

    /**
     * Check if the user is a Student (Murid).
     */
    public function isMurid(): bool
    {
        return $this->user_type === 'murid';
    }

    /**
     * Check if the user is a Student (backward compatibility alias).
     */
    public function isSiswa(): bool
    {
        return $this->isMurid() || $this->user_type === 'siswa';
    }

    /**
     * Get all menu permissions assigned to this user.
     *
     * @return HasMany<UserMenuPermission, $this>
     */
    public function menuPermissions(): HasMany
    {
        return $this->hasMany(UserMenuPermission::class);
    }

    /**
     * Determine if the user has permission to access a menu for the specified action.
     */
    public function hasMenuAccess(int|Menu $menu, string $action = 'view'): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $menuId = $menu instanceof Menu ? $menu->id : $menu;
        $column = match ($action) {
            'create' => 'can_create',
            'edit' => 'can_edit',
            'delete' => 'can_delete',
            default => 'can_view',
        };

        $this->loadMissing('menuPermissions');

        $hasDirect = $this->menuPermissions->contains(
            fn (UserMenuPermission $perm) => $perm->menu_id === $menuId && (bool) $perm->$column
        );

        if ($hasDirect) {
            return true;
        }

        if ($action === 'view') {
            $menuModel = $menu instanceof Menu ? $menu : Menu::with('subMenus')->find($menuId);
            if ($menuModel && $menuModel->subMenus->isNotEmpty()) {
                $subMenuIds = $menuModel->subMenus->pluck('id')->all();

                return $this->menuPermissions->contains(
                    fn (UserMenuPermission $perm) => in_array($perm->sub_menu_id, $subMenuIds, true) && (bool) $perm->can_view
                );
            }
        }

        return false;
    }

    /**
     * Determine if the user has permission to access a sub-menu for the specified action.
     */
    public function hasSubMenuAccess(int|SubMenu $subMenu, string $action = 'view'): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $subMenuId = $subMenu instanceof SubMenu ? $subMenu->id : $subMenu;
        $column = match ($action) {
            'create' => 'can_create',
            'edit' => 'can_edit',
            'delete' => 'can_delete',
            default => 'can_view',
        };

        $this->loadMissing('menuPermissions');

        return $this->menuPermissions->contains(
            fn (UserMenuPermission $perm) => $perm->sub_menu_id === $subMenuId && (bool) $perm->$column
        );
    }

    /**
     * Dapatkan URL publik dari foto profil / avatar pengguna jika tersedia.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $path = $this->avatar_path ?: $this->gtk?->foto_path;

        if (! empty($path)) {
            return Storage::disk('public')->url($path);
        }

        return null;
    }

    /**
     * Evaluasi apakah akun pengguna masih perlu melengkapi profil atau mengganti kata sandi bawaan.
     */
    public function needsProfileCompletion(): bool
    {
        // Akun Super Administrator (pengembang / pemilik platform) dikecualikan dari kewajiban kelengkapan profil
        if ($this->isSuperAdmin()) {
            return false;
        }

        // 1. Cek kelengkapan email atau avatar profil akun
        if (empty($this->email) || empty($this->avatar_path)) {
            return true;
        }

        // 2. Cek kelengkapan kontak bagi akun yang terhubung ke data GTK
        if ($this->gtk && (empty($this->gtk->no_hp) || empty($this->gtk->alamat))) {
            return true;
        }

        // 3. Cek apakah pengguna masih menggunakan kata sandi bawaan sistem
        $defaultPasswords = ['infora123', 'password', 'admin'];

        if ($this->gtk) {
            if (! empty($this->gtk->nip)) {
                $defaultPasswords[] = preg_replace('/\s+/', '', (string) $this->gtk->nip);
            }
            if (! empty($this->gtk->nuptk)) {
                $defaultPasswords[] = preg_replace('/\s+/', '', (string) $this->gtk->nuptk);
            }
            if (! empty($this->gtk->nik)) {
                $defaultPasswords[] = preg_replace('/\s+/', '', (string) $this->gtk->nik);
            }
        }

        foreach (array_unique($defaultPasswords) as $defaultPassword) {
            if (! empty($defaultPassword) && Hash::check($defaultPassword, (string) $this->password)) {
                return true;
            }
        }

        return false;
    }
}
