<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('language', 5)->default('es')->after('type');

            $table->dropUnique('site_settings_group_key_unique');

            $table->unique(['group', 'key', 'language']);
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropUnique('site_settings_group_key_language_unique');

            $table->unique(['group', 'key']);

            $table->dropColumn('language');
        });
    }
};
