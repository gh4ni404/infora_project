<?php

namespace App\Models;

use Database\Factories\AbsensiMuridFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiMurid extends Model
{
    /** @use HasFactory<AbsensiMuridFactory> */
    use HasFactory;

    /**
     * Nama tabel di basis data.
     *
     * @var string
     */
    protected $table = 'absensi_murids';

    /**
     * Daftar atribut yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'murid_id',
        'kelas_id',
        'tahun_ajaran_id',
        'semester_id',
        'face_profile_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status',
        'metode',
        'match_score',
        'foto_masuk_path',
        'foto_pulang_path',
        'keterangan',
        'recorded_by',
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
            'murid_id' => 'integer',
            'kelas_id' => 'integer',
            'tahun_ajaran_id' => 'integer',
            'semester_id' => 'integer',
            'face_profile_id' => 'integer',
            'tanggal' => 'date',
            'jam_masuk' => 'datetime',
            'jam_pulang' => 'datetime',
            'match_score' => 'float',
            'recorded_by' => 'integer',
        ];
    }

    /**
     * Dapatkan URL publik snapshot absen masuk jika tersedia.
     */
    public function getFotoMasukUrlAttribute(): ?string
    {
        return ! empty($this->foto_masuk_path) ? asset('storage/'.$this->foto_masuk_path) : null;
    }

    /**
     * Dapatkan URL publik snapshot absen pulang jika tersedia.
     */
    public function getFotoPulangUrlAttribute(): ?string
    {
        return ! empty($this->foto_pulang_path) ? asset('storage/'.$this->foto_pulang_path) : null;
    }

    /**
     * Relasi ke unit sekolah pemilik absensi.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relasi ke data peserta didik.
     *
     * @return BelongsTo<Murid, $this>
     */
    public function murid(): BelongsTo
    {
        return $this->belongsTo(Murid::class);
    }

    /**
     * Relasi ke rombel / kelas murid saat absen.
     *
     * @return BelongsTo<Kelas, $this>
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Relasi ke tahun ajaran aktif saat absen.
     *
     * @return BelongsTo<TahunAjaran, $this>
     */
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Relasi ke semester aktif saat absen.
     *
     * @return BelongsTo<Semester, $this>
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Relasi ke profil wajah yang cocok saat pemindaian.
     *
     * @return BelongsTo<MuridFaceProfile, $this>
     */
    public function faceProfile(): BelongsTo
    {
        return $this->belongsTo(MuridFaceProfile::class, 'face_profile_id');
    }

    /**
     * Relasi ke operator atau akun kiosk pencatat absen.
     *
     * @return BelongsTo<User, $this>
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Scope filter berdasarkan unit sekolah.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeUntukSekolah(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope filter berdasarkan tanggal absensi.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopePadaTanggal(Builder $query, string $date): Builder
    {
        return $query->whereDate('tanggal', $date);
    }
}
