<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
}
