<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\DB;

class Semester extends Model
{
    use HasFactory;

    /**
     * Nama tabel di basis data.
     *
     * @var string
     */
    protected $table = 'semester';

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tahun_ajaran_id',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
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
            'tahun_ajaran_id' => 'integer',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke entitas induk Tahun Ajaran.
     *
     * @return BelongsTo<TahunAjaran, $this>
     */
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    /**
     * Relasi ke entitas Sekolah melalui Tahun Ajaran.
     *
     * @return HasOneThrough<School, TahunAjaran, $this>
     */
    public function school(): HasOneThrough
    {
        return $this->hasOneThrough(
            School::class,
            TahunAjaran::class,
            'id', // Kunci primer lokal pada TahunAjaran
            'id', // Kunci primer lokal pada School
            'tahun_ajaran_id', // Kunci asing pada Semester
            'school_id' // Kunci asing pada TahunAjaran
        );
    }

    /**
     * Scope query untuk memfilter semester aktif.
     *
     * @param  Builder<Semester>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Helper statis untuk mendapatkan Semester yang sedang aktif di sekolah tertentu.
     */
    public static function activeFor(int $schoolId): ?self
    {
        return static::query()
            ->where('is_active', true)
            ->whereHas('tahunAjaran', function (Builder $q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->with('tahunAjaran')
            ->first();
    }

    /**
     * Accessor label semester terformat (Ganjil / Genap).
     */
    protected function labelSemester(): Attribute
    {
        return Attribute::make(
            get: fn () => ucfirst(strtolower((string) $this->semester))
        );
    }

    /**
     * Accessor nama lengkap semester (contoh: "Semester Ganjil 2026/2027").
     */
    protected function namaLengkap(): Attribute
    {
        return Attribute::make(
            get: function () {
                $tahun = $this->tahunAjaran?->tahun ?? '';
                $label = ucfirst(strtolower((string) $this->semester));

                return "Semester {$label} {$tahun}";
            }
        );
    }

    /**
     * Accessor rentang tanggal yang diformat ramah pengguna Indonesia.
     */
    protected function rentangTanggalFormatted(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->tanggal_mulai || ! $this->tanggal_selesai) {
                    return 'Belum diatur';
                }

                $mulai = Carbon::parse($this->tanggal_mulai)->locale('id')->isoFormat('D MMM Y');
                $selesai = Carbon::parse($this->tanggal_selesai)->locale('id')->isoFormat('D MMM Y');

                return "{$mulai} - {$selesai}";
            }
        );
    }

    /**
     * Mengaktifkan semester ini secara atomik (single-active period per unit sekolah).
     */
    public function activate(): bool
    {
        return DB::transaction(function () {
            $schoolId = $this->tahunAjaran->school_id;

            // 1. Nonaktifkan semua semester aktif lain di sekolah ini
            static::query()
                ->where('id', '!=', $this->id)
                ->whereHas('tahunAjaran', fn (Builder $q) => $q->where('school_id', $schoolId))
                ->update(['is_active' => false]);

            // 2. Nonaktifkan semua tahun ajaran lain di sekolah ini
            TahunAjaran::query()
                ->where('school_id', $schoolId)
                ->where('id', '!=', $this->tahun_ajaran_id)
                ->update(['is_active' => false]);

            // 3. Set Tahun Ajaran induk menjadi aktif
            $this->tahunAjaran()->update(['is_active' => true]);

            // 4. Set semester ini menjadi aktif di database dan instance lokal
            static::query()->where('id', $this->id)->update(['is_active' => true]);
            $this->is_active = true;

            return true;
        });
    }
}
