<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, string $default): string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function setValue(string $key, string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );
    }

    public static function userRegistrationMode(): string
    {
        return static::getValue('user_registration_mode', 'open');
    }

    public static function organizationRegistrationMode(): string
    {
        return static::getValue('organization_registration_mode', 'open');
    }
}
