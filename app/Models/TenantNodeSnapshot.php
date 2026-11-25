<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantNodeSnapshot extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_node_id',
        'modules_count',
        'menus_count',
        'sub_menus_count',
        'users_total',
        'users_breakdown',
        'dev_summary',
        'operational_summary',
        'modules_tree',
        'captured_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'modules_count' => 'integer',
            'menus_count' => 'integer',
            'sub_menus_count' => 'integer',
            'users_total' => 'integer',
            'users_breakdown' => 'array',
            'dev_summary' => 'array',
            'operational_summary' => 'array',
            'modules_tree' => 'array',
            'captured_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke node sekolah pemilik snapshot ini.
     *
     * @return BelongsTo<TenantNode, $this>
     */
    public function tenantNode(): BelongsTo
    {
        return $this->belongsTo(TenantNode::class, 'tenant_node_id');
    }
}
