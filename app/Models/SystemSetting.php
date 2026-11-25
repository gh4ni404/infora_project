<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'setting_key',
        'setting_value',
    ];

    /**
     * Ambil nilai pengaturan sistem berdasarkan kunci.
     */
    public static function getValue(string $key, ?string $default = null): ?string
    {
        return static::query()
            ->where('setting_key', $key)
            ->value('setting_value') ?? $default;
    }

    /**
     * Simpan atau perbarui nilai pengaturan sistem berdasarkan kunci.
     */
    public static function setValue(string $key, ?string $value): self
    {
        return static::query()->updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }
}
