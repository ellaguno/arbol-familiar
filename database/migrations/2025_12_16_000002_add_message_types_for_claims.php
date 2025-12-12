<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar el ENUM para agregar person_claim y person_merge
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('invitation', 'consent_request', 'relationship_found', 'general', 'system', 'person_claim', 'person_merge') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar mensajes con los tipos nuevos antes de revertir
        DB::table('messages')->whereIn('type', ['person_claim', 'person_merge'])->delete();

        // Revertir al ENUM original
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('invitation', 'consent_request', 'relationship_found', 'general', 'system') NOT NULL");
    }
};
