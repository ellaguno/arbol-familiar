<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

    /**
     * Get all settings for a group as key => value collection.
     */
    public static function getByGroup(string $group): array
    {
        return Cache::remember("site_settings.{$group}", 3600, function () use ($group) {
            return static::where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Get a single setting value.
     */
    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        $settings = static::getByGroup($group);
        return $settings[$key] ?? $default;
    }

    /**
     * Set a single setting value.
     */
    public static function set(string $group, string $key, ?string $value, string $type = 'text'): void
    {
        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value, 'type' => $type]
        );

        Cache::forget("site_settings.{$group}");
    }

    /**
     * Set multiple settings for a group.
     */
    public static function setMany(string $group, array $data): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                static::set($group, $key, $value['value'], $value['type'] ?? 'text');
            } else {
                static::set($group, $key, $value);
            }
        }

        Cache::forget("site_settings.{$group}");
    }

    /**
     * Get all color settings with defaults.
     */
    public static function colors(): array
    {
        $defaults = [
            'primary' => '#3b82f6',
            'secondary' => '#2563eb',
            'accent' => '#f59e0b',
            'light' => '#dbeafe',
            'dark' => '#1d4ed8',
        ];

        $saved = static::getByGroup('colors');

        return array_merge($defaults, $saved);
    }

    /**
     * Clear all caches for site settings.
     */
    public static function clearCache(): void
    {
        $groups = static::select('group')->distinct()->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("site_settings.{$group}");
        }
    }
}
