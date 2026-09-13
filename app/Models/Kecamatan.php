<?php

namespace App\Models;

use App\Support\TextFormatter;
use Database\Factories\KecamatanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    /** @use HasFactory<KecamatanFactory> */
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'kecamatan';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var list<string>
     */
    protected $fillable = [
        'kabupaten_id',
        'kode',
        'nama',
        'status',
    ];

    /**
     * Definisi type-casting atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kabupaten_id' => 'integer',
            'status' => 'boolean',
        ];
    }

    /**
     * Relasi ke entitas induk Kabupaten/Kota.
     *
     * @return BelongsTo<Kabupaten, $this>
     */
    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id');
    }

    /**
     * Relasi ke entitas anak Kelurahan / Desa.
     *
     * @return HasMany<Kelurahan, $this>
     */
    public function kelurahan(): HasMany
    {
        return $this->hasMany(Kelurahan::class, 'kecamatan_id');
    }

    /**
     * Mutator & accessor untuk nama kecamatan.
     * Format otomatis Title Case dengan preservasi akronim resmi.
     */
    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::titleCase($value),
        );
    }

    /**
     * Accessor untuk nama lengkap kecamatan (misal: "Kecamatan Tanete Riattang").
     */
    protected function namaLengkap(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Kecamatan '.$this->nama,
        );
    }

    /**
     * Scope query untuk menyaring hanya data yang aktif.
     *
     * @param  Builder<Kecamatan>  $query
     */
    public function scopeAktif(Builder $query): void
    {
        $query->where('status', true);
    }

    /**
     * Scope query untuk menyaring berdasarkan ID kabupaten/kota.
     *
     * @param  Builder<Kecamatan>  $query
     */
    public function scopeFilterByKabupaten(Builder $query, ?int $kabupatenId): void
    {
        if ($kabupatenId) {
            $query->where('kabupaten_id', $kabupatenId);
        }
    }

    /**
     * Scope query untuk menyaring berdasarkan ID provinsi induk.
     *
     * @param  Builder<Kecamatan>  $query
     */
    public function scopeFilterByProvinsi(Builder $query, ?int $provinsiId): void
    {
        if ($provinsiId) {
            $query->whereHas('kabupaten', function (Builder $q) use ($provinsiId) {
                $q->where('provinsi_id', $provinsiId);
            });
        }
    }

    /**
     * Scope query untuk pencarian berdasarkan kode wilayah, nama kecamatan, nama kabupaten, atau nama provinsi.
     *
     * @param  Builder<Kecamatan>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        if (blank($term)) {
            return;
        }

        $trimmed = trim($term);
        $query->where(function (Builder $q) use ($trimmed) {
            $q->where('kode', 'like', "%{$trimmed}%")
                ->orWhere('nama', 'like', "%{$trimmed}%")
                ->orWhereHas('kabupaten', function (Builder $kabQuery) use ($trimmed) {
                    $kabQuery->where('nama', 'like', "%{$trimmed}%")
                        ->orWhereHas('provinsi', function (Builder $provQuery) use ($trimmed) {
                            $provQuery->where('nama', 'like', "%{$trimmed}%");
                        });
                });
        });
    }
}
