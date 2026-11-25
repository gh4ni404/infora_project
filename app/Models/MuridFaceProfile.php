<?php

namespace App\Models;

use App\Support\TextFormatter;
use Database\Factories\MuridFaceProfileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class MuridFaceProfile extends Model
{
    /** @use HasFactory<MuridFaceProfileFactory> */
    use HasFactory;

    /**
     * Batas maksimal sampel wajah per peserta didik agar pencocokan tetap ringan dan cepat.
     */
    public const MAX_SAMPLES_PER_MURID = 5;

    /**
     * Versi model ekstraksi descriptor wajah bawaan sistem.
     */
    public const DEFAULT_MODEL_VERSION = 'vladmandic-face-api-tiny-128d';

    /**
     * Nama tabel di basis data.
     *
     * @var string
     */
    protected $table = 'murid_face_profiles';

    /**
     * Daftar atribut yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'murid_id',
        'label',
        'foto_path',
        'face_descriptor',
        'confidence',
        'model_version',
        'is_active',
        'registered_by',
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
            'face_descriptor' => 'array',
            'confidence' => 'float',
            'is_active' => 'boolean',
            'registered_by' => 'integer',
        ];
    }

    /**
     * Format label pose wajah ke dalam Title Case yang rapi.
     */
    protected function label(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => filled($value) ? TextFormatter::titleCase($value) : 'Tampak Depan',
        );
    }

    /**
     * Dapatkan URL publik foto sampel wajah jika tersedia.
     */
    public function getFotoUrlAttribute(): ?string
    {
        return ! empty($this->foto_path) ? asset('storage/'.$this->foto_path) : null;
    }

    /**
     * Relasi ke unit sekolah pemilik profil wajah.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relasi ke data peserta didik pemilik sampel wajah.
     *
     * @return BelongsTo<Murid, $this>
     */
    public function murid(): BelongsTo
    {
        return $this->belongsTo(Murid::class);
    }

    /**
     * Relasi ke pengguna yang mendaftarkan sampel wajah.
     *
     * @return BelongsTo<User, $this>
     */
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     * Scope untuk menyaring hanya profil wajah yang aktif digunakan untuk absensi.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk menyaring profil wajah berdasarkan unit sekolah tertentu.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeUntukSekolah(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Dapatkan cache key terversi untuk payload descriptor wajah (aman untuk file cache driver).
     */
    public static function descriptorCacheKey(int $schoolId, ?int $kelasId = null): string
    {
        $version = (int) Cache::get("face_descriptors_ver_{$schoolId}", 1);
        $kelasSuffix = $kelasId !== null ? (string) $kelasId : 'all';

        return "face_descriptors_{$schoolId}_v{$version}_k{$kelasSuffix}";
    }

    /**
     * Invalidasi cache descriptor wajah untuk sekolah tertentu.
     */
    public static function clearDescriptorCache(int $schoolId): void
    {
        $version = (int) Cache::get("face_descriptors_ver_{$schoolId}", 1);
        Cache::forever("face_descriptors_ver_{$schoolId}", $version + 1);
    }
}
