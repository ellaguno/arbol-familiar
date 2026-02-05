<?php

namespace App\Services;

use App\Models\SiteSetting;

class SiteSettingsService
{
    protected array $colors;
    protected string $font;

    /**
     * Available fonts mapped to their Bunny Fonts slug and weights.
     */
    public const AVAILABLE_FONTS = [
        'Ubuntu' => ['slug' => 'ubuntu', 'weights' => '400,500,600,700'],
        'Montserrat' => ['slug' => 'montserrat', 'weights' => '400,500,600,700'],
        'Inter' => ['slug' => 'inter', 'weights' => '400,500,600,700'],
        'Roboto' => ['slug' => 'roboto', 'weights' => '400,500,700'],
        'Open Sans' => ['slug' => 'open-sans', 'weights' => '400,500,600,700'],
        'Lato' => ['slug' => 'lato', 'weights' => '400,700'],
        'Poppins' => ['slug' => 'poppins', 'weights' => '400,500,600,700'],
        'Nunito' => ['slug' => 'nunito', 'weights' => '400,500,600,700'],
        'Raleway' => ['slug' => 'raleway', 'weights' => '400,500,600,700'],
        'Source Sans Pro' => ['slug' => 'source-sans-pro', 'weights' => '400,600,700'],
        'Figtree' => ['slug' => 'figtree', 'weights' => '400,500,600,700'],
        'Quicksand' => ['slug' => 'quicksand', 'weights' => '400,500,600,700'],
    ];

    public function __construct()
    {
        $this->colors = SiteSetting::colors();
        $this->font = SiteSetting::get('colors', 'font', 'Ubuntu') ?? 'Ubuntu';
    }

    /**
     * Get content value with fallback to default.
     */
    public function content(string $group, string $key, string $default = ''): string
    {
        return SiteSetting::get($group, $key, $default) ?? $default;
    }

    /**
     * Get all content for a group.
     */
    public function group(string $group): array
    {
        return SiteSetting::getByGroup($group);
    }

    /**
     * Get color palette.
     */
    public function colors(): array
    {
        return $this->colors;
    }

    /**
     * Get the selected font name.
     */
    public function font(): string
    {
        return $this->font;
    }

    /**
     * Get the Bunny Fonts URL for the selected font.
     */
    public function fontUrl(): string
    {
        $fontData = self::AVAILABLE_FONTS[$this->font] ?? self::AVAILABLE_FONTS['Ubuntu'];
        return "https://fonts.bunny.net/css?family={$fontData['slug']}:{$fontData['weights']}&display=swap";
    }

    /**
     * Check if heritage feature is enabled.
     */
    public function heritageEnabled(): bool
    {
        return (bool) (SiteSetting::get('heritage', 'heritage_enabled', '0') ?? '0');
    }

    /**
     * Get heritage regions as associative array.
     */
    public function heritageRegions(): array
    {
        $json = SiteSetting::get('heritage', 'heritage_regions', '');
        if ($json) {
            $decoded = json_decode($json, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }
        return config('mi-familia.heritage_regions', []);
    }

    /**
     * Get heritage migration decades as associative array.
     */
    public function heritageDecades(): array
    {
        $json = SiteSetting::get('heritage', 'heritage_decades', '');
        if ($json) {
            $decoded = json_decode($json, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }
        return config('mi-familia.migration_decades', []);
    }

    /**
     * Get heritage feature label.
     */
    public function heritageLabel(): string
    {
        return SiteSetting::get('heritage', 'heritage_label', 'Herencia cultural') ?? 'Herencia cultural';
    }

    /**
     * Check if research menu item is visible.
     */
    public function showResearch(): bool
    {
        return (bool) (SiteSetting::get('navigation', 'show_research', '0') ?? '0');
    }

    /**
     * Check if help menu item is visible.
     */
    public function showHelp(): bool
    {
        return (bool) (SiteSetting::get('navigation', 'show_help', '0') ?? '0');
    }

    /**
     * Get the global theme mode (light/dark).
     */
    public function themeMode(): string
    {
        return SiteSetting::get('colors', 'theme_mode', 'dark') ?? 'dark';
    }

    /**
     * Get the custom background color.
     */
    public function bgColor(): string
    {
        return SiteSetting::get('colors', 'bg_color', '') ?? '';
    }

    /**
     * Get the custom background image path.
     */
    public function bgImage(): string
    {
        return SiteSetting::get('colors', 'bg_image', '') ?? '';
    }

    /**
     * Get the CSS class for the html element based on theme.
     */
    public function themeClass($user = null): string
    {
        $preference = $user->theme_preference ?? 'default';
        $mode = ($preference === 'default') ? $this->themeMode() : $preference;
        return $mode === 'dark' ? 'dark' : '';
    }

    /**
     * Generate CSS variables block for injection in <head>.
     */
    public function cssVariables(): string
    {
        $c = $this->colors;
        $font = $this->font;
        return ":root {\n" .
            "    --mf-primary: {$c['primary']};\n" .
            "    --mf-secondary: {$c['secondary']};\n" .
            "    --mf-accent: {$c['accent']};\n" .
            "    --mf-light: {$c['light']};\n" .
            "    --mf-dark: {$c['dark']};\n" .
            "    --mf-font: '{$font}', ui-sans-serif, system-ui, sans-serif;\n" .
            "}";
    }
}
