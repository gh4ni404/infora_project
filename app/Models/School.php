<?php

namespace App\Models;

use App\Support\TextFormatter;
use Database\Factories\SchoolFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
