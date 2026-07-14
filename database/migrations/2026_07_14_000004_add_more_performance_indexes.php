<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indices adicionales segun el uso real de busqueda/reportes:
     * - persons.patronymic: el indice compuesto idx_persons_names es
     *   (first_name, patronymic), por lo que patronymic no es "leftmost" y no
     *   se aprovecha en groupBy/where sobre patronymic solo (apellidos).
     * - persons.gender: filtro de la busqueda avanzada.
     * - events.date: orderBy('date') en la busqueda de eventos.
     */
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->index('patronymic', 'persons_patronymic_index');
            $table->index('gender', 'persons_gender_index');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->index('date', 'events_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropIndex('persons_patronymic_index');
            $table->dropIndex('persons_gender_index');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_date_index');
        });
    }
};
