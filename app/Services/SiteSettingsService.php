<?php

namespace App\Services;

use App\Models\SiteSetting;

class SiteSettingsService
{
    protected array $colors;

    public function __construct()
    {
        $this->colors = SiteSetting::colors();
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
     * Generate CSS variables block for injection in <head>.
     */
    public function cssVariables(): string
    {
        $c = $this->colors;
        return ":root {\n" .
            "    --mf-primary: {$c['primary']};\n" .
            "    --mf-secondary: {$c['secondary']};\n" .
            "    --mf-accent: {$c['accent']};\n" .
            "    --mf-light: {$c['light']};\n" .
            "    --mf-dark: {$c['dark']};\n" .
            "}";
    }
}
