<?php

namespace App\Models;

use Database\Factories\SubMenuFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;

class SubMenu extends Model
{
    /** @use HasFactory<SubMenuFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'menu_id',
        'name',
        'route_name',
        'order',
        'is_active',
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
        ];
    }

    /**
     * Get the menu that owns the sub menu.
     *
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Resolve the target URL for this sub-menu.
     * Supports exact route names, .index fallback, or returns '#'.
     */
    public function getRouteUrlAttribute(): string
    {
        if (blank($this->route_name)) {
            return '#';
        }

        if (Route::has($this->route_name)) {
            return route($this->route_name);
        }

        if (Route::has($this->route_name.'.index')) {
            return route($this->route_name.'.index');
        }

        return '#';
    }

    /**
     * Determine if this sub-menu corresponds to the active route.
     */
    public function isRouteActive(): bool
    {
        if (blank($this->route_name)) {
            return false;
        }

        $patterns = [
            $this->route_name,
            $this->route_name.'.*',
            $this->route_name.'.index',
        ];

        if (str_ends_with($this->route_name, '.index')) {
            $patterns[] = substr($this->route_name, 0, -6).'.*';
        }

        return request()->routeIs($patterns);
    }
}
