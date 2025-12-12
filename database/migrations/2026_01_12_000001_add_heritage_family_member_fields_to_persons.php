<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            // Campos para usuarios que no tienen herencia etnica pero tienen familiar con herencia
            $table->string('heritage_family_member_name', 200)->nullable()
                ->after('migration_destination')
                ->comment('Nombre del familiar con herencia etnica (para usuarios sin herencia directa)');
            $table->string('heritage_family_relationship', 50)->nullable()
                ->after('heritage_family_member_name')
                ->comment('Tipo de relacion con el familiar con herencia etnica');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn(['heritage_family_member_name', 'heritage_family_relationship']);
        });
    }
};
