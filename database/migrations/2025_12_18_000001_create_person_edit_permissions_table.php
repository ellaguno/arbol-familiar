<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear tabla de permisos de edición por persona
        Schema::create('person_edit_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('person_id')->comment('Persona que puede ser editada');
            $table->unsignedBigInteger('user_id')->comment('Usuario que tiene permiso de edición');
            $table->unsignedBigInteger('granted_by')->comment('Usuario que otorgó el permiso');
            $table->enum('relationship_type', ['father', 'mother', 'spouse', 'child', 'sibling', 'other'])
                  ->comment('Tipo de relación con la persona');
            $table->timestamp('granted_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Índices
            $table->unique(['person_id', 'user_id'], 'unique_person_edit_permission');
            $table->index('user_id');
            $table->index('granted_by');

            // Foreign keys
            $table->foreign('person_id')->references('id')->on('persons')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('granted_by')->references('id')->on('users')->cascadeOnDelete();
        });

        // Agregar tipo de mensaje para solicitud de edición de familia
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('invitation', 'consent_request', 'relationship_found', 'general', 'system', 'person_claim', 'person_merge', 'family_edit_request') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar mensajes con el nuevo tipo antes de revertir
        DB::table('messages')->where('type', 'family_edit_request')->delete();

        // Revertir el ENUM
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('invitation', 'consent_request', 'relationship_found', 'general', 'system', 'person_claim', 'person_merge') NOT NULL");

        Schema::dropIfExists('person_edit_permissions');
    }
};
