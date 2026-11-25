<?php

namespace App\Models;

use App\Models\Concerns\TracksDevelopmentProgress;
use App\Support\SidebarCache;
use App\Support\TextFormatter;
use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory, TracksDevelopmentProgress;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(function (Menu $menu): void {
            if (blank($menu->sync_key) || ! blank($menu->route_name)) {
                $menu->sync_key = $menu->resolveSyncKey();
            }
        });

        static::saved(function (Menu $menu): void {
            SidebarCache::flushGlobal();
            static::clearDevelopmentCache('menu', $menu->id);
        });
        static::deleted(function (Menu $menu): void {
            SidebarCache::flushGlobal();
            static::clearDevelopmentCache('menu', $menu->id);
        });
    }

    /**
     * Interact with the menu's name.
     * Always stored in Capitalize Each Word (Title Case) format with acronym preservation.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::titleCase($value),
        );
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'module_id',
        'name',
        'route_name',
        'sync_key',
        'icon',
        'order',
        'is_active',
        'is_master_enabled',
        'dev_progress',
        'dev_stage',
        'dev_note',
        'dev_version',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_active' => 'boolean',
            'is_master_enabled' => 'boolean',
            'dev_progress' => 'integer',
        ];
    }

    /**
     * Get the module that owns the menu.
     *
     * @return BelongsTo<Module, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the sub menus for the menu.
     *
     * @return HasMany<SubMenu, $this>
     */
    public function subMenus(): HasMany
    {
        return $this->hasMany(SubMenu::class)->orderBy('order');
    }

    /**
     * Resolve the target URL for this menu.
     * Supports exact route names, .index fallback, or system.under-development placeholder.
     */
    public function getRouteUrlAttribute(): string
    {
        if (! blank($this->route_name)) {
            if (Route::has($this->route_name)) {
                return route($this->route_name);
            }

            if (Route::has($this->route_name.'.index')) {
                return route($this->route_name.'.index');
            }
        }

        if (Route::has('system.under-development')) {
            return route('system.under-development', ['type' => 'menu', 'id' => $this->id]);
        }

        return '#';
    }

    /**
     * Determine if this menu corresponds to the active route.
     */
    public function isRouteActive(): bool
    {
        if (request()->routeIs('system.under-development') && request('type') === 'menu' && (int) request('id') === $this->id) {
            return true;
        }

        if (blank($this->route_name)) {
            return false;
        }

        if (request()->routeIs($this->route_name)) {
            return true;
        }

        $patterns = [
            $this->route_name.'.*',
            $this->route_name.'.index',
        ];

        if (str_ends_with($this->route_name, '.index')) {
            $patterns[] = substr($this->route_name, 0, -6).'.*';
        }

        if (request()->routeIs($patterns)) {
            $resolved = SidebarCache::findByRoute(request());
            if ($resolved && $resolved['type'] === 'menu') {
                return $resolved['item']->id === $this->id;
            }

            return true;
        }

        return false;
    }

    /**
     * Determine if any of the sub-menus are currently active.
     */
    public function hasActiveSubMenu(): bool
    {
        return $this->subMenus->contains(fn (SubMenu $sub) => $sub->isRouteActive());
    }

    /**
     * Dapatkan nomor urutan tampil berikutnya untuk menu baru dalam suatu modul (mulai dari 1).
     */
    public static function nextOrder(?int $moduleId = null): int
    {
        $query = static::query();

        if ($moduleId) {
            $query->where('module_id', $moduleId);
        }

        $max = $query->max('order');

        return is_null($max) ? 1 : ((int) $max + 1);
    }

    /**
     * Dapatkan rekomendasi prefix rute turunan dari menu ini.
     */
    public function getRoutePrefixAttribute(): string
    {
        if (strcasecmp($this->name, 'Sistem') === 0) {
            return 'system';
        }

        // Ambil kata pertama sebelum tanda '&', '/', atau kata hubung 'dan', lalu ubah ke slug
        $base = preg_split('#\s+(?:dan|&)\s+|[&/]#i', $this->name)[0] ?? $this->name;

        return Str::slug(trim($base));
    }

    /**
     * Hasilkan kunci sinkronisasi stabil lintas database untuk entitas navigasi.
     */
    public static function buildSyncKey(?string $routeName, string $fallbackPrefix, string $fallbackName): string
    {
        if (! blank($routeName)) {
            $normalized = preg_replace('/\.index$/', '', strtolower(trim((string) $routeName)));

            return 'route:'.$normalized;
        }

        return $fallbackPrefix.':'.Str::slug($fallbackName);
    }

    /**
     * Dapatkan kunci sinkronisasi stabil milik menu ini.
     */
    public function resolveSyncKey(): string
    {
        if (! blank($this->sync_key) && blank($this->route_name)) {
            return $this->sync_key;
        }

        return static::buildSyncKey(
            $this->route_name,
            'menu',
            ($this->module?->name ?? 'module').'-'.$this->name
        );
    }
}
