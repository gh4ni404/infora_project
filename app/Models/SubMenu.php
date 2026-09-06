<?php

namespace App\Models;

use App\Support\TextFormatter;
use Database\Factories\SubMenuFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;

class SubMenu extends Model
{
    /** @use HasFactory<SubMenuFactory> */
    use HasFactory;

    /**
     * Interact with the sub-menu's name.
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
            return route('system.under-development', ['type' => 'submenu', 'id' => $this->id]);
        }

        return '#';
    }

    /**
     * Determine if this sub-menu corresponds to the active route.
     */
    public function isRouteActive(): bool
    {
        if (request()->routeIs('system.under-development') && request('type') === 'submenu' && (int) request('id') === $this->id) {
            return true;
        }

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
