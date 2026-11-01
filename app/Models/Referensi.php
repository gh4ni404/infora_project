<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Referensi extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'referensi';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'referensi_id',
        'nama',
        'kode',
        'is_active',
        'is_system',
        'extra_attributes',
    ];

    /**
     * Tipe casting atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'referensi_id' => 'integer',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'extra_attributes' => 'array',
        ];
    }

    /**
     * Relasi ke kategori induk (parent).
     *
     * @return BelongsTo<Referensi, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referensi_id');
    }

    /**
     * Relasi ke item-item anak dalam kategori ini.
     *
     * @return HasMany<Referensi, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'referensi_id');
    }

    /**
     * Scope untuk mengambil hanya kategori induk (referensi_id IS NULL).
     */
    public function scopeCategories(Builder $query): Builder
    {
        return $query->whereNull('referensi_id');
    }

    /**
     * Scope untuk mengambil item anak dari kategori tertentu (berdasarkan ID atau kode).
     */
    public function scopeItems(Builder $query, int|string|null $parent = null): Builder
    {
        if (is_numeric($parent)) {
            return $query->where('referensi_id', (int) $parent);
        }

        if (is_string($parent)) {
            return $query->whereHas('parent', function (Builder $q) use ($parent) {
                $q->where('kode', $parent);
            });
        }

        return $query->whereNotNull('referensi_id');
    }

    /**
     * Scope untuk mengambil record yang berstatus aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Dapatkan daftar opsi pasangan [kode => nama] untuk kategori tertentu (siap pakai di dropdown form Blade).
     *
     * @return array<string, string>
     */
    public static function getOptions(string $categoryCode): array
    {
        return Cache::remember("referensi_options_{$categoryCode}", 3600, function () use ($categoryCode) {
            $parent = self::categories()->where('kode', $categoryCode)->first();
            if (! $parent) {
                return [];
            }

            return self::where('referensi_id', $parent->id)
                ->where('is_active', true)
                ->orderBy('id')
                ->pluck('nama', 'kode')
                ->toArray();
        });
    }

    /**
     * Periksa apakah tipe sekolah tertentu merupakan satuan pendidikan madrasah/Kemenag.
     */
    public static function isMadrasah(?string $schoolTypeCode): bool
    {
        if (blank($schoolTypeCode)) {
            return false;
        }

        $code = trim((string) $schoolTypeCode);

        return Cache::remember("referensi_is_madrasah_{$code}", 3600, function () use ($code) {
            $item = self::items('school_type')
                ->where('kode', $code)
                ->first();

            return (bool) ($item?->extra_attributes['is_madrasah'] ?? false);
        });
    }

    /**
     * Bersihkan cache referensi saat ada perubahan data.
     */
    public static function clearCache(?string $categoryCode = null): void
    {
        if ($categoryCode) {
            Cache::forget("referensi_options_{$categoryCode}");
        }

        Cache::flush();
    }
}
