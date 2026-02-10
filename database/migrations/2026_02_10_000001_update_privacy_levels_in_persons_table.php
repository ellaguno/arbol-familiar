<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migrar niveles de privacidad de personas:
     * private      → direct_family
     * family       → extended_family
     * community    → community (sin cambio)
     * public       → community (merge - todos los registrados)
     *
     * Nuevo nivel: selected_users (familia + usuarios seleccionados)
     */
    public function up(): void
    {
        // Primero cambiar el enum para aceptar los nuevos valores
        // MySQL no permite ALTER ENUM directamente, usamos un campo temporal
        Schema::table('persons', function (Blueprint $table) {
            $table->string('privacy_level_new', 30)->default('extended_family')->after('privacy_level');
        });

        // Migrar valores
        DB::table('persons')->where('privacy_level', 'private')->update(['privacy_level_new' => 'direct_family']);
        DB::table('persons')->where('privacy_level', 'family')->update(['privacy_level_new' => 'extended_family']);
        DB::table('persons')->where('privacy_level', 'community')->update(['privacy_level_new' => 'community']);
        DB::table('persons')->where('privacy_level', 'public')->update(['privacy_level_new' => 'community']);

        // Eliminar columna vieja y renombrar nueva
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn('privacy_level');
        });

        Schema::table('persons', function (Blueprint $table) {
            $table->renameColumn('privacy_level_new', 'privacy_level');
        });
    }

    /**
     * Revertir migración.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->string('privacy_level_old', 30)->default('family')->after('privacy_level');
        });

        // Revertir valores
        DB::table('persons')->where('privacy_level', 'direct_family')->update(['privacy_level_old' => 'private']);
        DB::table('persons')->where('privacy_level', 'extended_family')->update(['privacy_level_old' => 'family']);
        DB::table('persons')->where('privacy_level', 'selected_users')->update(['privacy_level_old' => 'family']);
        DB::table('persons')->where('privacy_level', 'community')->update(['privacy_level_old' => 'community']);

        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn('privacy_level');
        });

        Schema::table('persons', function (Blueprint $table) {
            $table->renameColumn('privacy_level_old', 'privacy_level');
        });
    }
};
