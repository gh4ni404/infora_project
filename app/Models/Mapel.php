<?php

namespace App\Models;

use App\Support\TextFormatter;
use Database\Factories\MapelFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mapel extends Model
{
    /** @use HasFactory<MapelFactory> */
    use HasFactory;

    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'mapel';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'jurusan_id',
        'kode',
        'nama',
        'singkatan',
        'kelompok',
        'beban_jam',
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
            'jurusan_id' => 'integer',
            'beban_jam' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Format kode mapel menjadi UPPERCASE standar.
     */
    protected function kode(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::upper($value),
        );
    }

    /**
     * Format singkatan mapel menjadi UPPERCASE standar.
     */
    protected function singkatan(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::upper($value),
        );
    }

    /**
     * Format nama mapel menjadi Title Case dengan preservasi akronim.
     */
    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::titleCase($value),
        );
    }

    /**
     * Format kelompok mapel menjadi Title Case standar.
     */
    protected function kelompok(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::titleCase($value),
        );
    }

    /**
     * Relasi ke unit sekolah pemilik mata pelajaran.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relasi ke jurusan terkait (opsional/khusus mapel kejuruan SMK).
     *
     * @return BelongsTo<Jurusan, $this>
     */
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    /**
     * Scope untuk menyaring hanya mata pelajaran yang aktif.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk menyaring mata pelajaran milik sekolah tertentu.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeUntukSekolah(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope untuk menyaring mata pelajaran berdasarkan kelompok kurikulum.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeKelompok(Builder $query, string $kelompok): Builder
    {
        return $query->where('kelompok', $kelompok);
    }
}
