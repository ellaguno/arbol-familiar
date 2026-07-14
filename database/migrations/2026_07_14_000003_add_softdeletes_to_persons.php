<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega soft deletes a persons: borrar una persona pasa a ser reversible
     * (antes era un hard delete con cascada, con riesgo de perdida irreversible
     * de datos genealogicos).
     */
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            if (!Schema::hasColumn('persons', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            if (Schema::hasColumn('persons', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
