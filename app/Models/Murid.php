<?php

namespace App\Models;

use App\Support\TextFormatter;
use Database\Factories\MuridFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Murid extends Model
{
    /** @use HasFactory<MuridFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Nama tabel di basis data.
     *
     * @var string
     */
    protected $table = 'murids';

    /**
     * Daftar atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'user_id',
        'kelas_id',
        'angkatan',
        'nisn',
        'nis',
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'no_hp',
        'email',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'kelurahan_id',
        'alamat',
        'nama_ayah',
        'nama_ibu',
        'nama_wali',
        'no_hp_ortu',
        'foto_path',
        'status',
        'is_active',
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
            'user_id' => 'integer',
            'kelas_id' => 'integer',
            'provinsi_id' => 'integer',
            'kabupaten_id' => 'integer',
            'kecamatan_id' => 'integer',
            'kelurahan_id' => 'integer',
            'tanggal_lahir' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Format nama lengkap menjadi Title Case dengan preservasi akronim.
     */
    protected function namaLengkap(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::titleCase($value),
        );
    }

    /**
     * Format tempat lahir menjadi Title Case.
     */
    protected function tempatLahir(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::titleCase($value),
        );
    }

    /**
     * Format nama ayah menjadi Title Case.
     */
    protected function namaAyah(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => filled($value) ? TextFormatter::titleCase($value) : null,
        );
    }

    /**
     * Format nama ibu menjadi Title Case.
     */
    protected function namaIbu(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => filled($value) ? TextFormatter::titleCase($value) : null,
        );
    }

    /**
     * Format nama wali menjadi Title Case.
     */
    protected function namaWali(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => filled($value) ? TextFormatter::titleCase($value) : null,
        );
    }

    /**
     * Format NISN bersih dari spasi dan karakter non-digit.
     */
    protected function nisn(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => filled($value) ? preg_replace('/\s+/', '', (string) $value) : null,
        );
    }

    /**
     * Format NIS bersih dari spasi luar.
     */
    protected function nis(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => filled($value) ? trim((string) $value) : null,
        );
    }

    /**
     * Format NIK bersih dari spasi.
     */
    protected function nik(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => filled($value) ? preg_replace('/\s+/', '', (string) $value) : null,
        );
    }

    /**
     * Dapatkan representasi teks lengkap alamat domisili murid beserta jenjang wilayahnya.
     */
    public function getAlamatLengkapAttribute(): ?string
    {
        $parts = array_filter([
            $this->alamat,
            $this->kelurahan?->nama ? 'Desa/Kel. '.$this->kelurahan->nama : null,
            $this->kecamatan?->nama ? 'Kec. '.$this->kecamatan->nama : null,
            $this->kabupaten?->nama,
            $this->provinsi?->nama,
        ]);

        return ! empty($parts) ? implode(', ', $parts) : null;
    }

    /**
     * Dapatkan label identitas murid (Nama & NISN/NIS).
     */
    public function getIdentitasLabelAttribute(): string
    {
        $reg = $this->nisn ?: $this->nis;

        return $reg ? "{$this->nama_lengkap} ({$reg})" : $this->nama_lengkap;
    }

    /**
     * Relasi ke unit sekolah pemilik data murid.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relasi ke akun login pengguna sistem.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke rombongan belajar / kelas tempat murid terdaftar.
     *
     * @return BelongsTo<Kelas, $this>
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Relasi ke master provinsi alamat domisili.
     *
     * @return BelongsTo<Provinsi, $this>
     */
    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id');
    }

    /**
     * Relasi ke master kabupaten/kota domisili.
     *
     * @return BelongsTo<Kabupaten, $this>
     */
    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id');
    }

    /**
     * Relasi ke master kecamatan domisili.
     *
     * @return BelongsTo<Kecamatan, $this>
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    /**
     * Relasi ke master kelurahan/desa domisili.
     *
     * @return BelongsTo<Kelurahan, $this>
     */
    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_id');
    }

    /**
     * Scope untuk menyaring hanya data murid yang aktif.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk menyaring murid milik unit sekolah tertentu.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeUntukSekolah(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope untuk menyaring murid yang belum dialokasikan ke kelas (tanpa rombel).
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeTanpaKelas(Builder $query): Builder
    {
        return $query->whereNull('kelas_id');
    }

    /**
     * Scope untuk menyaring murid berdasarkan kelas tertentu.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeUntukKelas(Builder $query, int $kelasId): Builder
    {
        return $query->where('kelas_id', $kelasId);
    }
}
