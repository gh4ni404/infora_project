<?php

namespace App\Models;

use App\Support\TextFormatter;
use Database\Factories\GtkFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gtk extends Model
{
    /** @use HasFactory<GtkFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Nama tabel di basis data.
     *
     * @var string
     */
    protected $table = 'gtks';

    /**
     * Daftar atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'user_id',
        'nama_lengkap',
        'gelar_depan',
        'gelar_belakang',
        'jenis_kelamin',
        'nik',
        'nip',
        'nuptk',
        'jenis_ptk',
        'status_kepegawaian',
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
        'pendidikan_terakhir',
        'jurusan_pendidikan',
        'foto_path',
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
            'provinsi_id' => 'integer',
            'kabupaten_id' => 'integer',
            'kecamatan_id' => 'integer',
            'kelurahan_id' => 'integer',
            'jenis_ptk' => 'array',
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
     * Format jurusan pendidikan menjadi Title Case.
     */
    protected function jurusanPendidikan(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::titleCase($value),
        );
    }

    /**
     * Pastikan jenis_ptk selalu mengembalikan array.
     */
    public function getJenisPtkAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            return filled($value) ? [$value] : [];
        }

        return [];
    }

    /**
     * Dapatkan nama lengkap beserta gelar depan dan gelar belakang yang tersusun rapi.
     */
    public function getNamaBergelarAttribute(): string
    {
        $depan = filled($this->gelar_depan) ? trim($this->gelar_depan).' ' : '';
        $belakang = filled($this->gelar_belakang) ? ', '.trim($this->gelar_belakang) : '';

        return $depan.$this->nama_lengkap.$belakang;
    }

    /**
     * Dapatkan alamat lengkap domisili beserta hirarki wilayah administratif.
     */
    public function getAlamatLengkapAttribute(): string
    {
        $parts = array_filter([
            $this->alamat,
            $this->kelurahan?->nama ? 'Kel./Desa '.$this->kelurahan->nama : null,
            $this->kecamatan?->nama ? 'Kec. '.$this->kecamatan->nama : null,
            $this->kabupaten?->nama,
            $this->provinsi?->nama,
        ]);

        return ! empty($parts) ? implode(', ', $parts) : ($this->alamat ?: '-');
    }

    /**
     * Cek apakah GTK tergolong sebagai Tenaga Pendidik (Guru).
     */
    public function getIsPendidikAttribute(): bool
    {
        $roles = (array) ($this->jenis_ptk ?? []);
        foreach ($roles as $role) {
            if (
                str_starts_with((string) $role, 'Guru') ||
                str_contains((string) $role, 'Wali') ||
                str_contains((string) $role, 'Kepala Program') ||
                str_contains((string) $role, 'Kepala Sekolah') ||
                str_contains((string) $role, 'Pembimbing') ||
                str_contains((string) $role, 'Pembina') ||
                str_contains((string) $role, 'Pendidik')
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cek apakah GTK tergolong sebagai Tenaga Kependidikan (Tendik).
     */
    public function getIsTendikAttribute(): bool
    {
        $roles = (array) ($this->jenis_ptk ?? []);
        foreach ($roles as $role) {
            if (
                str_contains((string) $role, 'Administrasi') ||
                str_contains((string) $role, 'Tata Usaha') ||
                str_contains((string) $role, 'Bendahara') ||
                str_contains((string) $role, 'Laboran') ||
                str_contains((string) $role, 'Pustakawan') ||
                str_contains((string) $role, 'Teknisi') ||
                str_contains((string) $role, 'Keamanan') ||
                str_contains((string) $role, 'Kebersihan')
            ) {
                return true;
            }
        }

        return ! $this->is_pendidik;
    }

    /**
     * Relasi ke unit sekolah pemilik data GTK.
     *
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relasi ke akun pengguna login sistem yang terhubung.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke entitas data wilayah Provinsi domisili.
     *
     * @return BelongsTo<Provinsi, $this>
     */
    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class);
    }

    /**
     * Relasi ke entitas data wilayah Kabupaten/Kota domisili.
     *
     * @return BelongsTo<Kabupaten, $this>
     */
    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class);
    }

    /**
     * Relasi ke entitas data wilayah Kecamatan domisili.
     *
     * @return BelongsTo<Kecamatan, $this>
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    /**
     * Relasi ke entitas data wilayah Kelurahan/Desa domisili.
     *
     * @return BelongsTo<Kelurahan, $this>
     */
    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class);
    }

    /**
     * Scope untuk menyaring hanya GTK yang berstatus aktif.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk menyaring GTK milik unit sekolah tertentu.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeUntukSekolah(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope untuk menyaring hanya Tenaga Pendidik (Guru / Kepala Sekolah / Wali Kelas).
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopePendidik(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereJsonContains('jenis_ptk', 'Guru Mata Pelajaran')
                ->orWhereJsonContains('jenis_ptk', 'Guru BK / Konselor')
                ->orWhereJsonContains('jenis_ptk', 'Guru Kelas')
                ->orWhereJsonContains('jenis_ptk', 'Guru Pendamping Khusus')
                ->orWhereJsonContains('jenis_ptk', 'Wali Kelas')
                ->orWhereJsonContains('jenis_ptk', 'Kepala Sekolah')
                ->orWhereJsonContains('jenis_ptk', 'Kepala Program Keahlian (Kaprog)')
                ->orWhereJsonContains('jenis_ptk', 'Pembimbing PKL / Magang')
                ->orWhereJsonContains('jenis_ptk', 'Pembina OSIS / Ekstrakurikuler')
                ->orWhere('jenis_ptk', 'like', '%Guru%');
        });
    }

    /**
     * Scope untuk menyaring Tenaga Kependidikan (Staf TU, Laboran, Perpustakaan, dll).
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeTendik(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereJsonContains('jenis_ptk', 'Tenaga Administrasi Sekolah (Staf TU)')
                ->orWhereJsonContains('jenis_ptk', 'Tenaga Administrasi Sekolah')
                ->orWhereJsonContains('jenis_ptk', 'Kepala Tata Usaha (KTU)')
                ->orWhereJsonContains('jenis_ptk', 'Bendahara Sekolah')
                ->orWhereJsonContains('jenis_ptk', 'Laboran / Teknisi Lab')
                ->orWhereJsonContains('jenis_ptk', 'Pustakawan')
                ->orWhereJsonContains('jenis_ptk', 'Teknisi IT / Operator Dapodik')
                ->orWhereJsonContains('jenis_ptk', 'Petugas Keamanan')
                ->orWhereJsonContains('jenis_ptk', 'Petugas Kebersihan')
                ->orWhere('jenis_ptk', 'like', '%Administrasi%')
                ->orWhere('jenis_ptk', 'like', '%Laboran%')
                ->orWhere('jenis_ptk', 'like', '%Pustakawan%')
                ->orWhere('jenis_ptk', 'like', '%Teknisi%');
        });
    }
}
