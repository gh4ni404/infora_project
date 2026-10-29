<?php

namespace App\Models;

use App\Support\TextFormatter;
use Database\Factories\KelasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kelas extends Model
{
    /** @use HasFactory<KelasFactory> */
    use HasFactory;

    /**
     * Nama tabel di basis data.
     *
     * @var string
     */
    protected $table = 'kelas';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'tahun_ajaran_id',
        'tingkatan_id',
        'jurusan_id',
        'wali_kelas_id',
        'nama',
        'kode',
        'ruangan',
        'kapasitas',
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
            'tahun_ajaran_id' => 'integer',
            'tingkatan_id' => 'integer',
            'jurusan_id' => 'integer',
            'wali_kelas_id' => 'integer',
            'kapasitas' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Format nama kelas menjadi Title Case dengan preservasi singkatan/akronim.
     */
    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::titleCase($value),
        );
    }

    /**
     * Format kode kelas menjadi UPPERCASE standar.
     */
    protected function kode(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::upper($value),
        );
    }

    /**
     * Format nama ruangan menjadi Title Case jika diisi.
     */
    protected function ruangan(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => filled($value) ? TextFormatter::titleCase($value) : null,
        );
    }

    /**
     * Relasi ke unit sekolah pemilik rombel/kelas.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relasi ke tahun ajaran berlakunya kelas ini.
     *
     * @return BelongsTo<TahunAjaran, $this>
     */
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Relasi ke tingkatan / jenjang kelas (X, XI, XII).
     *
     * @return BelongsTo<Tingkatan, $this>
     */
    public function tingkatan(): BelongsTo
    {
        return $this->belongsTo(Tingkatan::class);
    }

    /**
     * Relasi ke jurusan / program keahlian kelas.
     *
     * @return BelongsTo<Jurusan, $this>
     */
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    /**
     * Relasi ke GTK yang ditugaskan sebagai wali kelas.
     *
     * @return BelongsTo<Gtk, $this>
     */
    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(Gtk::class, 'wali_kelas_id');
    }

    /**
     * Scope untuk menyaring hanya kelas yang aktif.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk menyaring kelas milik unit sekolah tertentu.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeUntukSekolah(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope untuk menyaring kelas berdasarkan tahun ajaran tertentu.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeUntukTahunAjaran(Builder $query, int $tahunAjaranId): Builder
    {
        return $query->where('tahun_ajaran_id', $tahunAjaranId);
    }
}
