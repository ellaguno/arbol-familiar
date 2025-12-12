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
            $table->enum('marital_status', ['single', 'married', 'common_law', 'divorced', 'widowed'])
                  ->nullable()
                  ->after('gender')
                  ->comment('Estado civil: single=Soltero/a, married=Casado/a, common_law=Union libre, divorced=Divorciado/a, widowed=Viudo/a');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn('marital_status');
        });
    }
};
