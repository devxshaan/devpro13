<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'label',
        'description',
        'type',
        'options',
        'group',
        'is_deletable',
        'sort_order',
    ];

    protected $casts = [
        'options'      => 'array',
        'is_deletable' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Setting $setting) {
            if (!$setting->is_deletable) {
                throw new \Exception("Setting '{$setting->key}' cannot be deleted.");
            }
        });

        // Clear cache on update/delete
        static::saved(function () {
            Cache::forget('settings_cache');
        });

        static::deleted(function () {
            Cache::forget('settings_cache');
        });
    }

    /**
     * Get setting (cached)
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = Cache::remember('settings_cache', 3600, function () {
            return static::pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }

    /**
     * Set or update setting
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget('settings_cache');
    }
}