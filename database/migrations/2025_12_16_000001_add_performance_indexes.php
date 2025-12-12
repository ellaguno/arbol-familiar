<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agrega índices para mejorar el rendimiento de consultas frecuentes.
     */
    public function up(): void
    {
        // Índices para tabla persons
        Schema::table('persons', function (Blueprint $table) {
            // Índice compuesto para filtros de privacidad y creador
            $table->index(['privacy_level', 'created_by'], 'persons_privacy_created_idx');
        });

        // Índices para tabla messages
        Schema::table('messages', function (Blueprint $table) {
            // Índice compuesto para bandeja de entrada (mensajes no eliminados, ordenados por lectura)
            $table->index(['recipient_id', 'deleted_at', 'read_at'], 'messages_inbox_idx');
            // Índice para mensajes enviados
            $table->index(['sender_id', 'created_at'], 'messages_sent_idx');
        });

        // Índices para tabla activity_log
        Schema::table('activity_log', function (Blueprint $table) {
            // Índice compuesto para filtrar actividad por usuario y fecha
            $table->index(['user_id', 'created_at'], 'activity_user_date_idx');
            // Índice para filtrar por acción
            $table->index('action', 'activity_action_idx');
        });

        // Índices para tabla families
        Schema::table('families', function (Blueprint $table) {
            // Índice para búsquedas por fecha de matrimonio
            $table->index('marriage_date', 'families_marriage_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropIndex('persons_privacy_created_idx');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_inbox_idx');
            $table->dropIndex('messages_sent_idx');
        });

        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex('activity_user_date_idx');
            $table->dropIndex('activity_action_idx');
        });

        Schema::table('families', function (Blueprint $table) {
            $table->dropIndex('families_marriage_date_idx');
        });
    }
};
