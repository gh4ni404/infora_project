<?php

namespace App\Models;

use App\Support\TextFormatter;
use Database\Factories\SchoolFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
