<?php

namespace App\Models;

use App\Support\TextFormatter;
use Database\Factories\TingkatanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tingkatan extends Model
{
    /** @use HasFactory<TingkatanFactory> */
    use HasFactory;

    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'tingkatan';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'kode',
        'nama',
        'tingkat_angka',
        'fase',
        'is_active',
        'keterangan',
    ];

    /**
     * Tipe casting atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'school_id' => 'integer',
            'tingkat_angka' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Format kode tingkatan menjadi UPPERCASE standar.
     */
    protected function kode(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::upper($value),
        );
    }

    /**
     * Format nama tingkatan menjadi Title Case dengan preservasi akronim.
     */
    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::titleCase($value),
        );
    }

    /**
     * Format fase menjadi Title Case jika diisi.
     */
    protected function fase(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => filled($value) ? TextFormatter::titleCase($value) : null,
        );
    }

    /**
     * Relasi ke unit sekolah pemilik tingkatan.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Scope untuk menyaring hanya tingkatan yang berstatus aktif.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk menyaring tingkatan milik unit sekolah tertentu.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeUntukSekolah(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope untuk mengurutkan tingkatan berdasarkan urutan alami (tingkat_angka ASC).
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('tingkat_angka', 'asc');
    }
}
