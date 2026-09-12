<?php

namespace App\Models;

use App\Support\TextFormatter;
use Database\Factories\KabupatenFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kabupaten extends Model
{
    /** @use HasFactory<KabupatenFactory> */
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'kabupaten';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var list<string>
     */
    protected $fillable = [
        'provinsi_id',
        'kode',
        'tipe',
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
            'provinsi_id' => 'integer',
            'status' => 'boolean',
        ];
    }

    /**
     * Relasi ke entitas induk Provinsi.
     *
     * @return BelongsTo<Provinsi, $this>
     */
    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id');
    }

    /**
     * Mutator & accessor untuk nama kabupaten/kota.
     * Format otomatis Title Case dengan preservasi akronim resmi.
     */
    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::titleCase($value),
        );
    }

    /**
     * Accessor untuk nama lengkap kabupaten/kota (misal: "Kota Makassar" atau "Kabupaten Maros").
     */
    protected function namaLengkap(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->tipe} {$this->nama}"),
        );
    }

    /**
     * Scope query untuk menyaring hanya data yang aktif.
     *
     * @param  Builder<Kabupaten>  $query
     */
    public function scopeAktif(Builder $query): void
    {
        $query->where('status', true);
    }

    /**
     * Scope query untuk menyaring berdasarkan ID provinsi.
     *
     * @param  Builder<Kabupaten>  $query
     */
    public function scopeFilterByProvinsi(Builder $query, ?int $provinsiId): void
    {
        if ($provinsiId) {
            $query->where('provinsi_id', $provinsiId);
        }
    }

    /**
     * Scope query untuk menyaring berdasarkan tipe (Kabupaten / Kota).
     *
     * @param  Builder<Kabupaten>  $query
     */
    public function scopeFilterByTipe(Builder $query, ?string $tipe): void
    {
        if (filled($tipe) && in_array($tipe, ['Kabupaten', 'Kota'], true)) {
            $query->where('tipe', $tipe);
        }
    }

    /**
     * Scope query untuk pencarian berdasarkan kode wilayah, nama kabupaten, atau nama provinsi.
     *
     * @param  Builder<Kabupaten>  $query
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
                ->orWhereHas('provinsi', function (Builder $provQuery) use ($trimmed) {
                    $provQuery->where('nama', 'like', "%{$trimmed}%");
                });
        });
    }
}
