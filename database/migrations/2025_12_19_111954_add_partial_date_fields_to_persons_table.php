<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agrega campos para fechas parciales (solo año, o año-mes, o fecha completa)
     */
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            // Campos para fecha de nacimiento parcial
            $table->smallInteger('birth_year')->nullable()->after('birth_date');
            $table->tinyInteger('birth_month')->nullable()->after('birth_year');
            $table->tinyInteger('birth_day')->nullable()->after('birth_month');

            // Campos para fecha de defunción parcial
            $table->smallInteger('death_year')->nullable()->after('death_date');
            $table->tinyInteger('death_month')->nullable()->after('death_year');
            $table->tinyInteger('death_day')->nullable()->after('death_month');
        });

        // Migrar datos existentes de birth_date y death_date a los nuevos campos
        DB::statement("
            UPDATE persons
            SET birth_year = YEAR(birth_date),
                birth_month = MONTH(birth_date),
                birth_day = DAY(birth_date)
            WHERE birth_date IS NOT NULL
        ");

        DB::statement("
            UPDATE persons
            SET death_year = YEAR(death_date),
                death_month = MONTH(death_date),
                death_day = DAY(death_date)
            WHERE death_date IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn([
                'birth_year',
                'birth_month',
                'birth_day',
                'death_year',
                'death_month',
                'death_day',
            ]);
        });
    }
};
