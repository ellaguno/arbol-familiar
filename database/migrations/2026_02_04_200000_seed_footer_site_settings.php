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

        $footerSettings = [
            [
                'group' => 'footer',
                'key' => 'footer_col_1',
                'value' => '<img src="/images/logo.png" alt="Mi Familia" class="h-20 object-contain">',
                'type' => 'html',
            ],
            [
                'group' => 'footer',
                'key' => 'footer_col_2',
                'value' => '<a href="/help" class="block text-gray-600 hover:text-[#3b82f6]">¿Cómo funciona Mi Familia?</a>' . "\n" .
                    '<a href="/ancestors-info" class="block text-gray-600 hover:text-[#3b82f6]">Donde encontrar más información de mis antepasados</a>' . "\n" .
                    '<a href="/privacy" class="block text-gray-600 hover:text-[#3b82f6]">Privacidad</a>' . "\n" .
                    '<a href="/terms" class="block text-gray-600 hover:text-[#3b82f6]">Términos y condiciones</a>',
                'type' => 'html',
            ],
            [
                'group' => 'footer',
                'key' => 'footer_col_3',
                'value' => '',
                'type' => 'html',
            ],
        ];

        foreach ($footerSettings as $setting) {
            SiteSetting::updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                ['value' => $setting['value'], 'type' => $setting['type']]
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('site_settings')) {
            SiteSetting::where('group', 'footer')->delete();
        }
    }
};
