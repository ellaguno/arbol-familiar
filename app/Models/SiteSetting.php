<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type', 'language'];

    /**
     * Check if the 'language' column exists (cached per-request).
     */
    protected static function hasLanguageColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn === null) {
            $hasColumn = Schema::hasColumn('site_settings', 'language');
        }
        return $hasColumn;
    }

    /**
     * Get all settings for a group as key => value collection.
     * Filters by locale with fallback to 'es' for missing keys.
     * Gracefully works before the language migration has run.
     */
    public static function getByGroup(string $group, ?string $lang = null): array
    {
        $hasLang = static::hasLanguageColumn();
        $lang = $hasLang ? ($lang ?? app()->getLocale()) : 'es';

        return Cache::remember("site_settings.{$group}.{$lang}", 3600, function () use ($group, $lang, $hasLang) {
            if (!$hasLang) {
                return static::where('group', $group)
                    ->pluck('value', 'key')
                    ->toArray();
            }

            if ($lang === 'es') {
                return static::where('group', $group)
                    ->where('language', 'es')
                    ->pluck('value', 'key')
                    ->toArray();
            }

            // Fallback: start with Spanish, overlay with requested language
            $fallback = static::where('group', $group)
                ->where('language', 'es')
                ->pluck('value', 'key')
                ->toArray();

            $translated = static::where('group', $group)
                ->where('language', $lang)
                ->pluck('value', 'key')
                ->toArray();

            // Only overlay non-empty translated values
            foreach ($translated as $key => $value) {
                if ($value !== null && $value !== '') {
                    $fallback[$key] = $value;
                }
            }

            return $fallback;
        });
    }

    /**
     * Get a single setting value.
     */
    public static function get(string $group, string $key, mixed $default = null, ?string $lang = null): mixed
    {
        $settings = static::getByGroup($group, $lang);
        return $settings[$key] ?? $default;
    }

    /**
     * Set a single setting value.
     */
    public static function set(string $group, string $key, ?string $value, string $type = 'text', ?string $lang = null): void
    {
        $lang = $lang ?? 'es';

        $match = ['group' => $group, 'key' => $key];
        if (static::hasLanguageColumn()) {
            $match['language'] = $lang;
        }

        static::updateOrCreate($match, ['value' => $value, 'type' => $type]);

        // Invalidate cache for both languages
        Cache::forget("site_settings.{$group}.es");
        Cache::forget("site_settings.{$group}.en");
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

        Cache::forget("site_settings.{$group}.es");
        Cache::forget("site_settings.{$group}.en");
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

        $saved = static::getByGroup('colors', 'es');

        return array_merge($defaults, $saved);
    }

    /**
     * Clear all caches for site settings.
     */
    public static function clearCache(): void
    {
        $groups = static::select('group')->distinct()->pluck('group');
        $languages = ['es', 'en'];
        foreach ($groups as $group) {
            foreach ($languages as $lang) {
                Cache::forget("site_settings.{$group}.{$lang}");
            }
        }
    }
}
