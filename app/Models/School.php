<?php

namespace App\Models;

use App\Support\TextFormatter;
use Database\Factories\SchoolFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class School extends Model
{
    /** @use HasFactory<SchoolFactory> */
    use HasFactory;

    /**
     * Interact with the school's name.
     * Always stored in Title Case format.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::titleCase($value),
        );
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'npsn',
        'nss',
        'school_type',
        'status',
        'accreditation',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'kelurahan_id',
        'address',
        'village',
        'district',
        'city',
        'province',
        'postal_code',
        'phone',
        'fax',
        'email',
        'website',
        'principal_name',
        'principal_nip',
        'foundation_name',
        'logo_path',
        'is_active',
    ];

    /**
     * Relasi ke master provinsi.
     */
    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id');
    }

    /**
     * Relasi ke master kabupaten/kota.
     */
    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id');
    }

    /**
     * Relasi ke master kecamatan.
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    /**
     * Relasi ke master kelurahan/desa.
     */
    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_id');
    }

    /**
     * Relasi ke agenda kalender akademik sekolah.
     */
    public function kalenderAkademik(): HasMany
    {
        return $this->hasMany(KalenderAkademik::class, 'school_id');
    }

    /**
     * Relasi ke data tahun ajaran sekolah.
     *
     * @return HasMany<TahunAjaran, $this>
     */
    public function tahunAjaran(): HasMany
    {
        return $this->hasMany(TahunAjaran::class, 'school_id')->orderBy('tahun', 'desc');
    }

    /**
     * Mendapatkan Tahun Ajaran yang sedang aktif di sekolah ini.
     */
    public function activeTahunAjaran(): ?TahunAjaran
    {
        return $this->tahunAjaran()->where('is_active', true)->first();
    }

    /**
     * Mendapatkan Semester yang sedang aktif di sekolah ini.
     */
    public function activeSemester(): ?Semester
    {
        return Semester::activeFor($this->id);
    }

    /**
     * Relasi ke data jurusan sekolah.
     *
     * @return HasMany<Jurusan, $this>
     */
    public function jurusans(): HasMany
    {
        return $this->hasMany(Jurusan::class, 'school_id')->orderBy('kode');
    }

    /**
     * Relasi ke seluruh akun pengguna yang terdaftar di sekolah ini.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'school_id');
    }

    /**
     * Relasi ke data guru dan tenaga kependidikan (GTK) di sekolah ini.
     *
     * @return HasMany<Gtk, $this>
     */
    public function gtks(): HasMany
    {
        return $this->hasMany(Gtk::class, 'school_id');
    }

    /**
     * Relasi ke data murid / peserta didik di sekolah ini.
     *
     * @return HasMany<Murid, $this>
     */
    public function murids(): HasMany
    {
        return $this->hasMany(Murid::class, 'school_id');
    }

    /**
     * Relasi ke akun administrator utama sekolah.
     *
     * @return HasOne<User, $this>
     */
    public function adminUser(): HasOne
    {
        return $this->hasOne(User::class, 'school_id')->where('user_type', 'admin');
    }

    /**
     * Relasi ke data tingkatan kelas di sekolah ini.
     *
     * @return HasMany<Tingkatan, $this>
     */
    public function tingkatan(): HasMany
    {
        return $this->hasMany(Tingkatan::class, 'school_id');
    }

    /**
     * Mengambil profil sekolah aktif tunggal dalam mode 1 sekolah = 1 database.
     */
    public static function current(): ?self
    {
        return static::query()
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->first();
    }

    /**
     * Mengambil ID profil sekolah aktif tunggal (atau 0 jika belum dikonfigurasi).
     */
    public static function currentId(): int
    {
        return (int) (static::query()
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->value('id') ?? 0);
    }

    /**
     * Menentukan apakah sekolah telah memiliki data operasional/akademik terkait.
     */
    public function hasOperationalData(): bool
    {
        return $this->users()->exists()
            || $this->gtks()->exists()
            || $this->murids()->exists()
            || $this->tahunAjaran()->exists()
            || $this->jurusans()->exists()
            || $this->tingkatan()->exists()
            || $this->kalenderAkademik()->exists()
            || Kelas::where('school_id', $this->id)->exists()
            || Mapel::where('school_id', $this->id)->exists()
            || AbsensiMurid::where('school_id', $this->id)->exists()
            || MuridFaceProfile::where('school_id', $this->id)->exists();
    }

    /**
     * Menentukan apakah satuan pendidikan berada di bawah naungan Kementerian Agama (Kemenag).
     */
    public function isMadrasah(): bool
    {
        return Referensi::isMadrasah($this->school_type);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
