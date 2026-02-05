<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('site_settings')) {
            return;
        }

        $settings = [
            ['group' => 'colors', 'key' => 'theme_mode', 'value' => 'dark', 'type' => 'text'],
            ['group' => 'colors', 'key' => 'bg_color', 'value' => '', 'type' => 'text'],
            ['group' => 'colors', 'key' => 'bg_image', 'value' => '', 'type' => 'text'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                ['value' => $setting['value'], 'type' => $setting['type']]
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('site_settings')) {
            return;
        }

        SiteSetting::where('group', 'colors')
            ->whereIn('key', ['theme_mode', 'bg_color', 'bg_image'])
            ->delete();
    }
};
