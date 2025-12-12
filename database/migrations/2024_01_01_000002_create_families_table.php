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
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('gedcom_id', 50)->nullable()->comment('ID para compatibilidad GEDCOM (@F1@, etc)');

            // Conyuges
            $table->unsignedBigInteger('husband_id')->nullable();
            $table->unsignedBigInteger('wife_id')->nullable();

            // Matrimonio
            $table->date('marriage_date')->nullable();
            $table->boolean('marriage_date_approx')->default(false);
            $table->string('marriage_place')->nullable();

            // Divorcio
            $table->date('divorce_date')->nullable();
            $table->string('divorce_place')->nullable();

            // Estado de la relacion
            $table->enum('status', ['married', 'divorced', 'widowed', 'separated', 'partners', 'annulled'])->default('married');

            // Auditoria
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Indices
            $table->index('husband_id');
            $table->index('wife_id');
            $table->index(['husband_id', 'wife_id'], 'idx_families_spouses');

            // Foreign keys
            $table->foreign('husband_id')->references('id')->on('persons')->nullOnDelete();
            $table->foreign('wife_id')->references('id')->on('persons')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
