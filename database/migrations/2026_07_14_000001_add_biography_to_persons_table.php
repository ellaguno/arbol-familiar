<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna 'biography' que ya usaban el parser y el exportador
     * GEDCOM (App\Services\GedcomParser escribe la nota del individuo aqui y
     * App\Services\GedcomExporter la lee), pero que no existia como columna,
     * por lo que las notas/biografias se descartaban silenciosamente.
     */
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            if (!Schema::hasColumn('persons', 'biography')) {
                $table->text('biography')->nullable()->after('occupation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            if (Schema::hasColumn('persons', 'biography')) {
                $table->dropColumn('biography');
            }
        });
    }
};
