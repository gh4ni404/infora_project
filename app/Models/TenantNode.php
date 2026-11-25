<?php

namespace App\Models;

use App\Support\TextFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class TenantNode extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'node_code',
        'school_name',
        'npsn',
        'base_url',
        'api_token_hash',
        'hmac_secret',
        'is_active',
        'status',
        'db_status',
        'db_latency_ms',
        'active_users_count',
        'policy_version',
        'last_applied_policy_version',
        'app_version',
        'last_migration',
        'last_heartbeat_at',
        'last_error_message',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'api_token_hash',
        'hmac_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hmac_secret' => 'encrypted',
            'is_active' => 'boolean',
            'db_latency_ms' => 'integer',
            'active_users_count' => 'integer',
            'policy_version' => 'integer',
            'last_applied_policy_version' => 'integer',
            'last_heartbeat_at' => 'datetime',
        ];
    }

    /**
     * Normalisasi nama sekolah ke format Title Case.
     */
    protected function schoolName(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => TextFormatter::titleCase($value),
        );
    }

    /**
     * Filter node berdasarkan pencarian nama sekolah, kode node, atau NPSN.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $search = trim((string) $term);
        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('school_name', 'like', "%{$search}%")
                ->orWhere('node_code', 'like', "%{$search}%")
                ->orWhere('npsn', 'like', "%{$search}%");
        });
    }

    /**
     * Filter hanya node tenant yang diaktifkan Master.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Snapshot terbaru milik node sekolah.
     *
     * @return HasOne<TenantNodeSnapshot, $this>
     */
    public function snapshot(): HasOne
    {
        return $this->hasOne(TenantNodeSnapshot::class, 'tenant_node_id')->latestOfMany('id');
    }

    /**
     * Kebijakan izin menu dari Master untuk node sekolah ini.
     *
     * @return HasMany<TenantMenuPolicy, $this>
     */
    public function menuPolicies(): HasMany
    {
        return $this->hasMany(TenantMenuPolicy::class, 'tenant_node_id');
    }

    /**
     * Hitung status realtime server/database berdasarkan TTL heartbeat terakhir.
     */
    public function getComputedStatusAttribute(): string
    {
        if (! $this->is_active) {
            return 'suspended';
        }

        if ($this->last_heartbeat_at === null) {
            return 'pending';
        }

        $offlineMinutes = (int) config('infora.heartbeat.offline_minutes', 6);
        if ($this->last_heartbeat_at->lt(now()->subMinutes($offlineMinutes))) {
            return 'offline';
        }

        if ($this->status === 'maintenance') {
            return 'maintenance';
        }

        if ($this->db_status === 'down') {
            return 'degraded';
        }

        return $this->active_users_count > 0 ? 'online' : 'idle';
    }

    /**
     * Label status yang ramah dibaca untuk UI portal Master.
     */
    public function getComputedStatusLabelAttribute(): string
    {
        return match ($this->computed_status) {
            'online' => 'Online (Aktif Digunakan)',
            'idle' => 'Online (Idle)',
            'degraded' => 'Database Gangguan',
            'maintenance' => 'Maintenance',
            'offline' => 'Offline / Tidak Merespons',
            'suspended' => 'Dinonaktifkan',
            default => 'Menunggu Heartbeat',
        };
    }

    /**
     * Kelas badge CSS sesuai desain sistem INFORA untuk status node.
     */
    public function getComputedStatusBadgeAttribute(): string
    {
        return match ($this->computed_status) {
            'online' => 'badge-success',
            'idle' => 'badge-cyan',
            'degraded', 'maintenance' => 'badge-warning',
            'offline', 'suspended' => 'badge-rose',
            default => 'badge-neutral',
        };
    }

    /**
     * Buat pasangan kredensial baru (token & HMAC secret) yang aman.
     *
     * @return array{plain_token: string, token_hash: string, hmac_secret: string}
     */
    public static function generateCredentials(): array
    {
        $plainToken = 'inf_'.Str::random(48);

        return [
            'plain_token' => $plainToken,
            'token_hash' => hash('sha256', $plainToken),
            'hmac_secret' => Str::random(64),
        ];
    }
}
