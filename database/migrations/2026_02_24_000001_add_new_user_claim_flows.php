<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.6.0 - Flujos de reclamación para usuarios nuevos.
     *
     * 1. Agrega columna metadata (json) a messages - corrige bug latente
     * 2. Agrega tipo 'relationship_claim' al enum type
     * 3. Agrega claiming_person_id para rastrear persona dummy del solicitante
     */
    public function up(): void
    {
        // 1. Agregar columna metadata (json)
        Schema::table('messages', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('related_person_id');
        });

        // 2. Ampliar enum type para incluir relationship_claim
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM(
            'invitation','consent_request','relationship_found','general','system',
            'person_claim','person_merge','family_edit_request','broadcast',
            'relationship_claim'
        )");

        // 3. Agregar claiming_person_id
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('claiming_person_id')->nullable()->after('related_person_id');
            $table->foreign('claiming_person_id')->references('id')->on('persons')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['claiming_person_id']);
            $table->dropColumn('claiming_person_id');
        });

        // Revertir enum (sin relationship_claim)
        // Primero actualizar registros existentes
        DB::table('messages')->where('type', 'relationship_claim')->update(['type' => 'general']);

        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM(
            'invitation','consent_request','relationship_found','general','system',
            'person_claim','person_merge','family_edit_request','broadcast'
        )");

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};
