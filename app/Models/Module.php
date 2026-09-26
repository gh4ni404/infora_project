<?php

namespace App\Models;

use App\Support\SidebarCache;
use App\Support\TextFormatter;
use Database\Factories\ModuleFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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

    /**
     * Sesuaikan urutan modul lain saat modul baru akan dibuat pada urutan $targetOrder.
     * Jika sudah ada modul dengan order >= $targetOrder, geser naik sebesar +1.
     */
    public static function adjustOrderForCreate(int $targetOrder): void
    {
        static::query()
            ->where('order', '>=', $targetOrder)
            ->orderBy('order', 'desc')
            ->increment('order');
    }

    /**
     * Sesuaikan urutan modul lain saat urutan modul diubah dari $oldOrder ke $newOrder.
     */
    public static function adjustOrderForUpdate(self $module, int $newOrder): void
    {
        $oldOrder = $module->order;

        if ($newOrder === $oldOrder) {
            return;
        }

        if ($newOrder < $oldOrder) {
            // Modul bergeser ke posisi lebih kecil/atas: geser modul di antara newOrder dan oldOrder-1 naik (+1)
            static::query()
                ->where('id', '!=', $module->id)
                ->where('order', '>=', $newOrder)
                ->where('order', '<', $oldOrder)
                ->orderBy('order', 'desc')
                ->increment('order');
        } else {
            // Modul bergeser ke posisi lebih besar/bawah: geser modul di antara oldOrder+1 dan newOrder turun (-1)
            static::query()
                ->where('id', '!=', $module->id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->orderBy('order', 'asc')
                ->decrement('order');
        }
    }

    /**
     * Sinkronkan dan rapikan kembali seluruh nomor urutan modul agar berurutan (1, 2, 3, ...) tanpa celah atau duplikasi.
     */
    public static function syncSequence(): int
    {
        return DB::transaction(function () {
            $modules = static::query()
                ->orderBy('order')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $index = 1;
            foreach ($modules as $module) {
                if ($module->order !== $index) {
                    $module->update(['order' => $index]);
                }
                $index++;
            }

            SidebarCache::flushGlobal();

            return $modules->count();
        });
    }
}
