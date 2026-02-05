<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SiteSetting::updateOrCreate(
            ['group' => 'navigation', 'key' => 'show_research'],
            ['value' => '0', 'type' => 'text']
        );
        SiteSetting::updateOrCreate(
            ['group' => 'navigation', 'key' => 'show_help'],
            ['value' => '0', 'type' => 'text']
        );
    }

    public function down(): void
    {
        SiteSetting::where('group', 'navigation')->delete();
    }
};
