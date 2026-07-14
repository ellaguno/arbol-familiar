<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega columnas para las rutas de thumbnails generados con
     * intervention/image, tanto para media como para fotos de persona.
     */
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            if (!Schema::hasColumn('media', 'thumbnail_path')) {
                $table->string('thumbnail_path', 500)->nullable()->after('file_path');
            }
        });

        Schema::table('persons', function (Blueprint $table) {
            if (!Schema::hasColumn('persons', 'photo_thumbnail_path')) {
                $table->string('photo_thumbnail_path', 500)->nullable()->after('photo_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            if (Schema::hasColumn('media', 'thumbnail_path')) {
                $table->dropColumn('thumbnail_path');
            }
        });

        Schema::table('persons', function (Blueprint $table) {
            if (Schema::hasColumn('persons', 'photo_thumbnail_path')) {
                $table->dropColumn('photo_thumbnail_path');
            }
        });
    }
};
