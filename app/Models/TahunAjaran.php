<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAjaran extends Model
{
    use HasFactory;

    /**
     * Nama tabel di basis data.
     *
     * @var string
     */
    protected $table = 'tahun_ajaran';

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'tahun',
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke entitas Sekolah.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /**
     * Relasi ke entitas Semester anak.
     *
     * @return HasMany<Semester, $this>
     */
    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class, 'tahun_ajaran_id')->orderBy('semester');
    }

    /**
     * Scope query untuk memfilter tahun ajaran aktif.
     *
     * @param  Builder<TahunAjaran>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope query untuk memfilter tahun ajaran berdasarkan unit sekolah.
     *
     * @param  Builder<TahunAjaran>  $query
     */
    public function scopeForSchool(Builder $query, int $schoolId): void
    {
        $query->where('school_id', $schoolId);
    }

    /**
     * Helper statis untuk mendapatkan Tahun Ajaran yang sedang aktif di sekolah tertentu.
     */
    public static function activeFor(int $schoolId): ?self
    {
        return static::query()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->with('semesters')
            ->first();
    }

    /**
     * Memeriksa apakah tahun ajaran ini memiliki semester yang sedang aktif.
     */
    public function hasActiveSemester(): bool
    {
        return $this->semesters->contains(fn (Semester $s) => $s->is_active);
    }
}
