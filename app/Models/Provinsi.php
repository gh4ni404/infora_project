<?php

namespace App\Models;

use App\Support\TextFormatter;
use Database\Factories\ProvinsiFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    /** @use HasFactory<ProvinsiFactory> */
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'provinsi';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var list<string>
     */
    protected $fillable = [
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
            'status' => 'boolean',
        ];
    }

    /**
     * Mutator & accessor untuk nama provinsi.
     * Format otomatis Title Case dengan preservasi akronim wilayah (DKI, DI).
     */
    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::titleCase($value),
        );
    }

    /**
     * Scope query untuk menyaring hanya data yang aktif.
     *
     * @param  Builder<Provinsi>  $query
     */
    public function scopeAktif(Builder $query): void
    {
        $query->where('status', true);
    }

    /**
     * Scope query untuk pencarian berdasarkan kode atau nama provinsi.
     *
     * @param  Builder<Provinsi>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        if (blank($term)) {
            return;
        }

        $trimmed = trim($term);
        $query->where(function (Builder $q) use ($trimmed) {
            $q->where('kode', 'like', "%{$trimmed}%")
                ->orWhere('nama', 'like', "%{$trimmed}%");
        });
    }
}
