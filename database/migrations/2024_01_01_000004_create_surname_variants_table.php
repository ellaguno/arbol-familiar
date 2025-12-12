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
        Schema::create('surname_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('person_id');

            $table->string('original_surname', 100)->comment('Apellido original');
            $table->string('variant_1', 100)->nullable()->comment('Primera variante');
            $table->string('variant_2', 100)->nullable()->comment('Segunda variante');
            $table->text('notes')->nullable()->comment('Notas sobre el cambio');

            $table->timestamp('created_at')->useCurrent();

            // Indices
            $table->index('person_id');
            $table->index(['original_surname', 'variant_1', 'variant_2'], 'idx_sv_surnames');

            // Foreign key
            $table->foreign('person_id')->references('id')->on('persons')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surname_variants');
    }
};
