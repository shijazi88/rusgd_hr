<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Single-tenant key/value store for company-wide settings.
 *
 * Use the static get/set helpers — direct row access works but the helpers
 * make intent obvious and avoid scattering DB::table queries through the app.
 */
class CompanySetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $row = static::where('key', $key)->first();
        return $row?->value ?? $default;
    }

    public static function set(string $key, ?string $value): self
    {
        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Whole settings blob as an associative array. Used by the /company page.
     */
    public static function all_keyed(): array
    {
        return static::query()->pluck('value', 'key')->all();
    }
}
