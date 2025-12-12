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
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('person_id')->nullable();
            $table->unsignedBigInteger('family_id')->nullable();

            $table->string('type', 50)->comment('BIRT, DEAT, MARR, DIV, BURI, BAPM, etc');
            $table->date('date')->nullable();
            $table->boolean('date_approx')->default(false);
            $table->string('place')->nullable();
            $table->text('description')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Indices
            $table->index('person_id');
            $table->index('family_id');
            $table->index('type');

            // Foreign keys
            $table->foreign('person_id')->references('id')->on('persons')->cascadeOnDelete();
            $table->foreign('family_id')->references('id')->on('families')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
