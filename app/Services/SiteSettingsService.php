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
