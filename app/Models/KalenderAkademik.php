<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KalenderAkademik extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'kalender_akademik';

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'judul_kegiatan',
        'tanggal_mulai',
        'tanggal_selesai',
        'tahun_ajaran',
        'semester',
        'kategori',
        'warna',
        'libur_kbm',
        'keterangan',
    ];

    /**
     * Type casting atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date:Y-m-d',
            'tanggal_selesai' => 'date:Y-m-d',
            'libur_kbm' => 'boolean',
        ];
    }

    /**
     * Trim judul kegiatan.
     */
    protected function judulKegiatan(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value !== null ? trim($value) : null,
        );
    }

    /**
     * Relasi ke entitas Sekolah.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /**
     * Scope untuk filter berdasarkan sekolah.
     */
    public function scopeUntukSekolah(Builder $query, int|string $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope untuk filter berdasarkan tahun ajaran.
     */
    public function scopeTahunAjaran(Builder $query, ?string $tahunAjaran): Builder
    {
        if (blank($tahunAjaran)) {
            return $query;
        }

        return $query->where('tahun_ajaran', $tahunAjaran);
    }

    /**
     * Scope untuk filter berdasarkan semester.
     */
    public function scopeSemester(Builder $query, ?string $semester): Builder
    {
        if (blank($semester)) {
            return $query;
        }

        return $query->where('semester', $semester);
    }

    /**
     * Scope untuk filter berdasarkan kategori.
     */
    public function scopeKategori(Builder $query, ?string $kategori): Builder
    {
        if (blank($kategori)) {
            return $query;
        }

        return $query->where('kategori', $kategori);
    }

    /**
     * Scope untuk kegiatan yang bersinggungan dengan rentang bulan tertentu.
     */
    public function scopeBulan(Builder $query, int $tahun, int $bulan): Builder
    {
        $startOfMonth = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();

        return $query->whereDate('tanggal_mulai', '<=', $endOfMonth)
            ->whereDate('tanggal_selesai', '>=', $startOfMonth);
    }

    /**
     * Accessor untuk teks rentang tanggal format bahasa Indonesia.
     */
    public function getRentangTanggalFormattedAttribute(): string
    {
        $start = Carbon::parse($this->tanggal_mulai)->locale('id')->isoFormat('D MMMM Y');
        if (! $this->tanggal_selesai || $this->tanggal_mulai === $this->tanggal_selesai) {
            return $start;
        }

        $end = Carbon::parse($this->tanggal_selesai)->locale('id')->isoFormat('D MMMM Y');

        return "{$start} - {$end}";
    }

    /**
     * Accessor kelas badge warna berdasarkan atribut warna.
     */
    public function getBadgeClassAttribute(): string
    {
        return match ($this->warna) {
            'emerald' => 'badge-emerald',
            'amber' => 'badge-amber',
            'rose' => 'badge-rose',
            'purple' => 'badge-purple',
            'indigo' => 'badge-indigo',
            default => 'badge-blue',
        };
    }
}
