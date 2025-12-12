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
        Schema::create('family_children', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('family_id');
            $table->unsignedBigInteger('person_id');
            $table->unsignedTinyInteger('child_order')->default(0)->comment('Orden de nacimiento');
            $table->enum('relationship_type', ['biological', 'adopted', 'foster', 'step'])->default('biological');

            $table->timestamp('created_at')->useCurrent();

            // Unique constraint
            $table->unique(['family_id', 'person_id'], 'unique_family_child');
            $table->index('person_id');

            // Foreign keys
            $table->foreign('family_id')->references('id')->on('families')->cascadeOnDelete();
            $table->foreign('person_id')->references('id')->on('persons')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_children');
    }
};
