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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // Participantes
            $table->unsignedBigInteger('sender_id')->nullable()->comment('NULL para mensajes del sistema');
            $table->unsignedBigInteger('recipient_id');

            // Tipo y contenido
            $table->enum('type', ['invitation', 'consent_request', 'relationship_found', 'general', 'system', 'person_claim', 'person_merge', 'family_edit_request']);
            $table->string('subject');
            $table->text('body');

            // Datos relacionados
            $table->unsignedBigInteger('related_person_id')->nullable();

            // Acciones
            $table->boolean('action_required')->default(false);
            $table->enum('action_status', ['pending', 'accepted', 'denied', 'expired'])->nullable();
            $table->timestamp('action_taken_at')->nullable();

            // Estado de lectura
            $table->timestamp('read_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Indices
            $table->index('recipient_id');
            $table->index(['recipient_id', 'read_at'], 'idx_messages_unread');
            $table->index('type');
            $table->index('sender_id');
            $table->index(['recipient_id', 'action_required', 'action_status'], 'idx_messages_action');

            // Foreign keys
            $table->foreign('sender_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('recipient_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('related_person_id')->references('id')->on('persons')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
