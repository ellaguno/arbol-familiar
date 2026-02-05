<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'show_online_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('show_online_status')->default(true)->after('privacy_level');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'show_online_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('show_online_status');
            });
        }
    }
};
