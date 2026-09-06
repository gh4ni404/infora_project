<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuAccessTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_key',
        'role_name',
        'role_category',
        'menu_id',
        'sub_menu_id',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'can_view' => 'boolean',
            'can_create' => 'boolean',
            'can_edit' => 'boolean',
            'can_delete' => 'boolean',
        ];
    }

    /**
     * Get the menu associated with this template.
     *
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the sub-menu associated with this template.
     *
     * @return BelongsTo<SubMenu, $this>
     */
    public function subMenu(): BelongsTo
    {
        return $this->belongsTo(SubMenu::class, 'sub_menu_id');
    }
}
