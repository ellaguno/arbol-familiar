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

        $defaultRegions = [
            'region_1' => 'Region 1',
            'region_2' => 'Region 2',
            'region_3' => 'Region 3',
            'region_4' => 'Region 4',
            'other' => 'Otra region',
            'unknown' => 'Desconocida',
        ];

        $defaultDecades = [
            '1850-1860' => '1850 - 1860',
            '1860-1870' => '1860 - 1870',
            '1870-1880' => '1870 - 1880',
            '1880-1890' => '1880 - 1890',
            '1890-1900' => '1890 - 1900',
            '1900-1910' => '1900 - 1910',
            '1910-1920' => '1910 - 1920',
            '1920-1930' => '1920 - 1930',
            '1930-1940' => '1930 - 1940',
            '1940-1950' => '1940 - 1950',
            '1950-1960' => '1950 - 1960',
            '1960-1970' => '1960 - 1970',
            '1970-1980' => '1970 - 1980',
            '1980-1990' => '1980 - 1990',
            '1990-2000' => '1990 - 2000',
            '2000-2010' => '2000 - 2010',
            '2010-2020' => '2010 - 2020',
            '2020-present' => '2020 - Presente',
        ];

        $settings = [
            ['group' => 'heritage', 'key' => 'heritage_enabled', 'value' => '0', 'type' => 'text'],
            ['group' => 'heritage', 'key' => 'heritage_label', 'value' => 'Herencia cultural', 'type' => 'text'],
            ['group' => 'heritage', 'key' => 'heritage_regions', 'value' => json_encode($defaultRegions), 'type' => 'json'],
            ['group' => 'heritage', 'key' => 'heritage_decades', 'value' => json_encode($defaultDecades), 'type' => 'json'],
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
        if (Schema::hasTable('site_settings')) {
            SiteSetting::where('group', 'heritage')->delete();
        }
    }
};
