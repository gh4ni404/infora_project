<?php

namespace App\Models;

use App\Support\SidebarCache;
use App\Support\TextFormatter;
use Database\Factories\ModuleFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    /** @use HasFactory<ModuleFactory> */
    use HasFactory;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saved(fn () => SidebarCache::flushGlobal());
        static::deleted(fn () => SidebarCache::flushGlobal());
    }

    /**
     * Interact with the module's name.
     * Always stored in UPPERCASE format.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::upper($value),
        );
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
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
     * Get the menus for the module.
     *
     * @return HasMany<Menu, $this>
     */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class)->orderBy('order');
    }

    /**
     * Dapatkan nomor urutan tampil berikutnya untuk modul baru (mulai dari 1).
     */
    public static function nextOrder(): int
    {
        $max = static::max('order');

        return is_null($max) ? 1 : ((int) $max + 1);
    }
}
