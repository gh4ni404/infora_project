<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantMenuPolicy extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_node_id',
        'target_type',
        'sync_key',
        'label_snapshot',
        'is_allowed',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_allowed' => 'boolean',
        ];
    }

    /**
     * Relasi ke node sekolah pemilik kebijakan menu ini.
     *
     * @return BelongsTo<TenantNode, $this>
     */
    public function tenantNode(): BelongsTo
    {
        return $this->belongsTo(TenantNode::class, 'tenant_node_id');
    }
}
