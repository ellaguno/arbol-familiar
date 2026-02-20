<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('site_settings')->insertOrIgnore([
            'group' => 'welcome',
            'key' => 'hero_show',
            'value' => '1',
            'type' => 'boolean',
        ]);
    }

    public function down(): void
    {
        DB::table('site_settings')
            ->where('group', 'welcome')
            ->where('key', 'hero_show')
            ->delete();
    }
};
