<?php

namespace Plugin\PhotoBanner;

use App\Plugins\PluginServiceProvider;

class PhotoBannerPlugin extends PluginServiceProvider
{
    public function hooks(): array
    {
        return [
            'dashboard.banner' => 'photo-banner::hooks.banner',
        ];
    }

    public function getDefaultSettings(): array
    {
        return [
            'banner_height' => 120,
            'scroll_speed' => 30,
            'max_images' => 50,
            'image_gap' => 4,
        ];
    }
}
