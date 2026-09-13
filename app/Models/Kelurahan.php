<?php

namespace App\Models;

use App\Support\TextFormatter;
use Database\Factories\KelurahanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kelurahan extends Model
{
    /** @use HasFactory<KelurahanFactory> */
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'kelurahan';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var list<string>
     */
    protected $fillable = [
        'kecamatan_id',
        'kode',
        'tipe',
        'nama',
        'kode_pos',
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
            'kecamatan_id' => 'integer',
            'status' => 'boolean',
        ];
    }

    /**
     * Relasi ke entitas induk Kecamatan.
     *
     * @return BelongsTo<Kecamatan, $this>
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    /**
     * Mutator & accessor untuk nama kelurahan/desa.
     * Format otomatis Title Case dengan preservasi akronim resmi.
     */
    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::titleCase($value),
        );
    }

    /**
     * Accessor untuk nama lengkap kelurahan/desa (misal: "Kelurahan Macanang" atau "Desa Lampoko").
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
     * @param  Builder<Kelurahan>  $query
     */
    public function scopeAktif(Builder $query): void
    {
        $query->where('status', true);
    }

    /**
     * Scope query untuk menyaring berdasarkan tipe (Kelurahan / Desa).
     *
     * @param  Builder<Kelurahan>  $query
     */
    public function scopeFilterByTipe(Builder $query, ?string $tipe): void
    {
        if (filled($tipe) && in_array($tipe, ['Kelurahan', 'Desa'], true)) {
            $query->where('tipe', $tipe);
        }
    }

    /**
     * Scope query untuk menyaring berdasarkan ID kecamatan induk.
     *
     * @param  Builder<Kelurahan>  $query
     */
    public function scopeFilterByKecamatan(Builder $query, ?int $kecamatanId): void
    {
        if ($kecamatanId) {
            $query->where('kecamatan_id', $kecamatanId);
        }
    }

    /**
     * Scope query untuk menyaring berdasarkan ID kabupaten/kota induk.
     *
     * @param  Builder<Kelurahan>  $query
     */
    public function scopeFilterByKabupaten(Builder $query, ?int $kabupatenId): void
    {
        if ($kabupatenId) {
            $query->whereHas('kecamatan', function (Builder $q) use ($kabupatenId) {
                $q->where('kabupaten_id', $kabupatenId);
            });
        }
    }

    /**
     * Scope query untuk menyaring berdasarkan ID provinsi induk.
     *
     * @param  Builder<Kelurahan>  $query
     */
    public function scopeFilterByProvinsi(Builder $query, ?int $provinsiId): void
    {
        if ($provinsiId) {
            $query->whereHas('kecamatan.kabupaten', function (Builder $q) use ($provinsiId) {
                $q->where('provinsi_id', $provinsiId);
            });
        }
    }

    /**
     * Scope query untuk pencarian universal (kode, nama, kode pos, kecamatan, kabupaten, provinsi).
     *
     * @param  Builder<Kelurahan>  $query
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
                ->orWhere('kode_pos', 'like', "%{$trimmed}%")
                ->orWhereHas('kecamatan', function (Builder $kecQuery) use ($trimmed) {
                    $kecQuery->where('nama', 'like', "%{$trimmed}%")
                        ->orWhereHas('kabupaten', function (Builder $kabQuery) use ($trimmed) {
                            $kabQuery->where('nama', 'like', "%{$trimmed}%")
                                ->orWhereHas('provinsi', function (Builder $provQuery) use ($trimmed) {
                                    $provQuery->where('nama', 'like', "%{$trimmed}%");
                                });
                        });
                });
        });
    }
}
